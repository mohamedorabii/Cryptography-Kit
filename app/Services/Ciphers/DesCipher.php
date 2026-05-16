<?php

namespace App\Services\Ciphers;

/**
 * DES (Data Encryption Standard) Implementation
 *
 * DES is a symmetric-key block cipher that encrypts data in 64-bit blocks
 * using a 56-bit key (provided as 64 bits, every 8th bit is a parity bit).
 *
 * Main steps:
 *  1. Initial Permutation (IP) on the 64-bit block
 *  2. 16 rounds of Feistel network (each round uses a 48-bit sub-key)
 *  3. Final Permutation (IP⁻¹) — inverse of IP
 *
 * The Feistel function (F):
 *  a) Expand R from 32 to 48 bits using the E table
 *  b) XOR with the 48-bit round sub-key
 *  c) S-Box substitution: 48 bits → 32 bits
 *  d) P-Box permutation
 */
class DesCipher
{
    // ─────────────────────────────────────────────────────────────
    // DES TABLES (standard FIPS 46-3 values)
    // ─────────────────────────────────────────────────────────────

    /** Initial Permutation table */
    private const IP = [
        58,50,42,34,26,18,10,2, 60,52,44,36,28,20,12,4,
        62,54,46,38,30,22,14,6, 64,56,48,40,32,24,16,8,
        57,49,41,33,25,17, 9,1, 59,51,43,35,27,19,11,3,
        61,53,45,37,29,21,13,5, 63,55,47,39,31,23,15,7,
    ];

    /** Final (Inverse) Permutation table */
    private const IP_INV = [
        40,8,48,16,56,24,64,32, 39,7,47,15,55,23,63,31,
        38,6,46,14,54,22,62,30, 37,5,45,13,53,21,61,29,
        36,4,44,12,52,20,60,28, 35,3,43,11,51,19,59,27,
        34,2,42,10,50,18,58,26, 33,1,41, 9,49,17,57,25,
    ];

    /** Expansion (E) table: expands 32-bit R to 48 bits */
    private const E = [
        32, 1, 2, 3, 4, 5,  4, 5, 6, 7, 8, 9,
         8, 9,10,11,12,13, 12,13,14,15,16,17,
        16,17,18,19,20,21, 20,21,22,23,24,25,
        24,25,26,27,28,29, 28,29,30,31,32, 1,
    ];

    /** P-Box permutation (applied after S-Boxes) */
    private const P = [
        16,7,20,21,29,12,28,17, 1,15,23,26,5,18,31,10,
         2,8,24,14,32,27, 3, 9,19,13,30, 6,22,11, 4,25,
    ];

    /** PC-1: Permuted Choice 1 (64-bit key → 56 bits, drops parity bits) */
    private const PC1 = [
        57,49,41,33,25,17, 9,  1,58,50,42,34,26,18,
        10, 2,59,51,43,35,27, 19,11, 3,60,52,44,36,
        63,55,47,39,31,23,15,  7,62,54,46,38,30,22,
        14, 6,61,53,45,37,29, 21,13, 5,28,20,12, 4,
    ];

    /** PC-2: Permuted Choice 2 (56-bit key → 48-bit sub-key) */
    private const PC2 = [
        14,17,11,24, 1, 5,  3,28,15, 6,21,10,
        23,19,12, 4,26, 8, 16, 7,27,20,13, 2,
        41,52,31,37,47,55, 30,40,51,45,33,48,
        44,49,39,56,34,53, 46,42,50,36,29,32,
    ];

    /**
     * Left-shift schedule for key schedule rounds.
     * Each entry = how many positions to left-rotate C and D halves.
     */
    private const SHIFTS = [1,1,2,2,2,2,2,2,1,2,2,2,2,2,2,1];

    /**
     * Eight S-Boxes: each maps a 6-bit input → 4-bit output.
     * Input: row = bits 1&6, col = bits 2–5
     */
    private const S = [
        // S1
        [[14,4,13,1,2,15,11,8,3,10,6,12,5,9,0,7],
         [0,15,7,4,14,2,13,1,10,6,12,11,9,5,3,8],
         [4,1,14,8,13,6,2,11,15,12,9,7,3,10,5,0],
         [15,12,8,2,4,9,1,7,5,11,3,14,10,0,6,13]],
        // S2
        [[15,1,8,14,6,11,3,4,9,7,2,13,12,0,5,10],
         [3,13,4,7,15,2,8,14,12,0,1,10,6,9,11,5],
         [0,14,7,11,10,4,13,1,5,8,12,6,9,3,2,15],
         [13,8,10,1,3,15,4,2,11,6,7,12,0,5,14,9]],
        // S3
        [[10,0,9,14,6,3,15,5,1,13,12,7,11,4,2,8],
         [13,7,0,9,3,4,6,10,2,8,5,14,12,11,15,1],
         [13,6,4,9,8,15,3,0,11,1,2,12,5,10,14,7],
         [1,10,13,0,6,9,8,7,4,15,14,3,11,5,2,12]],
        // S4
        [[7,13,14,3,0,6,9,10,1,2,8,5,11,12,4,15],
         [13,8,11,5,6,15,0,3,4,7,2,12,1,10,14,9],
         [10,6,9,0,12,11,7,13,15,1,3,14,5,2,8,4],
         [3,15,0,6,10,1,13,8,9,4,5,11,12,7,2,14]],
        // S5
        [[2,12,4,1,7,10,11,6,8,5,3,15,13,0,14,9],
         [14,11,2,12,4,7,13,1,5,0,15,10,3,9,8,6],
         [4,2,1,11,10,13,7,8,15,9,12,5,6,3,0,14],
         [11,8,12,7,1,14,2,13,6,15,0,9,10,4,5,3]],
        // S6
        [[12,1,10,15,9,2,6,8,0,13,3,4,14,7,5,11],
         [10,15,4,2,7,12,9,5,6,1,13,14,0,11,3,8],
         [9,14,15,5,2,8,12,3,7,0,4,10,1,13,11,6],
         [4,3,2,12,9,5,15,10,11,14,1,7,6,0,8,13]],
        // S7
        [[4,11,2,14,15,0,8,13,3,12,9,7,5,10,6,1],
         [13,0,11,7,4,9,1,10,14,3,5,12,2,15,8,6],
         [1,4,11,13,12,3,7,14,10,15,6,8,0,5,9,2],
         [6,11,13,8,1,4,10,7,9,5,0,15,14,2,3,12]],
        // S8
        [[13,2,8,4,6,15,11,1,10,9,3,14,5,0,12,7],
         [1,15,13,8,10,3,7,4,12,5,6,11,0,14,9,2],
         [7,11,4,1,9,12,14,2,0,6,10,13,15,3,5,8],
         [2,1,14,7,4,10,8,13,15,12,9,0,3,5,6,11]],
    ];

    // ─────────────────────────────────────────────────────────────
    // BIT MANIPULATION HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Convert a hex string to a bit array (array of 0s and 1s).
     * Each hex character → 4 bits.
     */
    private function hexToBits(string $hex): array
    {
        $bits = [];
        foreach (str_split($hex) as $h) {
            $val = hexdec($h);
            for ($i = 3; $i >= 0; $i--) {
                $bits[] = ($val >> $i) & 1;
            }
        }
        return $bits;
    }

    /**
     * Convert a bit array back to a hex string.
     * Groups of 4 bits → one hex character.
     */
    private function bitsToHex(array $bits): string
    {
        $hex = '';
        foreach (array_chunk($bits, 4) as $nibble) {
            $val = 0;
            foreach ($nibble as $i => $b) {
                $val |= ($b << (3 - $i));
            }
            $hex .= dechex($val);
        }
        return strtoupper($hex);
    }

    /**
     * Convert a plain ASCII string to hex, then to bits.
     * Pads to a multiple of 64 bits (8 bytes) using null bytes.
     */
    private function textToBits(string $text): array
    {
        // Pad text to a multiple of 8 bytes
        $padLen = 8 - (strlen($text) % 8);
        $text  .= str_repeat(chr($padLen), $padLen); // PKCS#5 padding
        $hex    = bin2hex($text);
        return $this->hexToBits($hex);
    }

    /**
     * Convert a bit array back to an ASCII string and strip PKCS#5 padding.
     */
    private function bitsToText(array $bits): string
    {
        $hex  = $this->bitsToHex($bits);
        $text = hex2bin($hex);
        // Remove PKCS#5 padding
        $pad  = ord($text[strlen($text) - 1]);
        if ($pad > 0 && $pad <= 8) {
            $text = substr($text, 0, -$pad);
        }
        return $text;
    }

    /**
     * Apply a permutation table to a bit array.
     * The table uses 1-based indexing.
     */
    private function permute(array $bits, array $table): array
    {
        $out = [];
        foreach ($table as $pos) {
            $out[] = $bits[$pos - 1];
        }
        return $out;
    }

    /**
     * XOR two bit arrays of equal length.
     */
    private function xor(array $a, array $b): array
    {
        $out = [];
        for ($i = 0; $i < count($a); $i++) {
            $out[] = $a[$i] ^ $b[$i];
        }
        return $out;
    }

    /**
     * Left-rotate an array of bits by $n positions.
     */
    private function leftRotate(array $bits, int $n): array
    {
        return array_merge(array_slice($bits, $n), array_slice($bits, 0, $n));
    }

    // ─────────────────────────────────────────────────────────────
    // KEY SCHEDULE — Generate 16 round sub-keys
    // ─────────────────────────────────────────────────────────────

    /**
     * Generate 16 48-bit sub-keys from the 64-bit key.
     *
     * @param array $keyBits 64-bit key as bit array
     * @return array  16 sub-keys, each a 48-bit array
     */
    private function generateSubKeys(array $keyBits): array
    {
        // PC-1: drop parity bits, get 56-bit key
        $key56 = $this->permute($keyBits, self::PC1);

        // Split into two 28-bit halves: C and D
        $C = array_slice($key56, 0, 28);
        $D = array_slice($key56, 28, 28);

        $subKeys = [];
        for ($round = 0; $round < 16; $round++) {
            // Left-shift both halves by the schedule amount
            $C = $this->leftRotate($C, self::SHIFTS[$round]);
            $D = $this->leftRotate($D, self::SHIFTS[$round]);

            // PC-2: combine C+D (56 bits) → 48-bit sub-key
            $subKeys[] = $this->permute(array_merge($C, $D), self::PC2);
        }

        return $subKeys;
    }

    // ─────────────────────────────────────────────────────────────
    // FEISTEL FUNCTION (F)
    // ─────────────────────────────────────────────────────────────

    /**
     * The Feistel (F) function applied to the right 32-bit half.
     *
     * Steps: Expand(R) → XOR(subKey) → S-Boxes → P-permutation
     *
     * @param array $R       32-bit right half
     * @param array $subKey  48-bit round sub-key
     * @return array         32-bit output
     */
    private function feistel(array $R, array $subKey): array
    {
        // 1. Expand R from 32 → 48 bits using E table
        $expanded = $this->permute($R, self::E);

        // 2. XOR with sub-key
        $xored = $this->xor($expanded, $subKey);

        // 3. S-Box substitution: process 8 groups of 6 bits → 4 bits each
        $sOut = [];
        for ($i = 0; $i < 8; $i++) {
            $block = array_slice($xored, $i * 6, 6);

            // Row = first and last bit combined
            $row = ($block[0] << 1) | $block[5];

            // Column = middle 4 bits
            $col = ($block[1] << 3) | ($block[2] << 2) | ($block[3] << 1) | $block[4];

            $val = self::S[$i][$row][$col]; // Look up S-Box value

            // Convert 4-bit value to bits
            for ($j = 3; $j >= 0; $j--) {
                $sOut[] = ($val >> $j) & 1;
            }
        }

        // 4. P-permutation on the 32-bit S-Box output
        return $this->permute($sOut, self::P);
    }

    // ─────────────────────────────────────────────────────────────
    // CORE DES BLOCK CIPHER
    // ─────────────────────────────────────────────────────────────

    /**
     * Encrypt or decrypt a single 64-bit block.
     *
     * @param array $block   64-bit block as bit array
     * @param array $subKeys 16 sub-keys (forward for encrypt, reversed for decrypt)
     * @return array         64-bit output block
     */
    private function desBlock(array $block, array $subKeys): array
    {
        // Initial Permutation
        $permuted = $this->permute($block, self::IP);

        // Split into Left (L) and Right (R) halves
        $L = array_slice($permuted, 0, 32);
        $R = array_slice($permuted, 32, 32);

        // 16 Feistel rounds
        for ($round = 0; $round < 16; $round++) {
            $prevL = $L;
            $L     = $R;                                       // New L = old R
            $R     = $this->xor($prevL, $this->feistel($R, $subKeys[$round])); // New R = old L XOR F(R, K)
        }

        // Swap L and R before final permutation (32-bit swap)
        $combined = array_merge($R, $L);

        // Final (Inverse) Permutation
        return $this->permute($combined, self::IP_INV);
    }

    // ─────────────────────────────────────────────────────────────
    // KEY PREPARATION
    // ─────────────────────────────────────────────────────────────

    /**
     * Convert a text key to a 64-bit (8-byte) key bit array.
     * If the key is shorter than 8 characters, it is padded with zeros.
     * If longer, only the first 8 characters are used.
     */
    private function prepareKey(string $key): array
    {
        // Pad or truncate key to exactly 8 bytes
        $key = str_pad(substr($key, 0, 8), 8, "\0");
        return $this->hexToBits(bin2hex($key));
    }

    // ─────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────

    /**
     * Encrypt plain text using DES in ECB mode.
     * The text is split into 64-bit blocks and each block is encrypted.
     *
     * @param string $text The plain text to encrypt
     * @param string $key  The encryption key (up to 8 characters used)
     * @return string The encrypted text as a hexadecimal string
     */
    public function encrypt(string $text, string $key): string
    {
        $keyBits  = $this->prepareKey($key);
        $subKeys  = $this->generateSubKeys($keyBits);    // Forward sub-keys for encryption

        $textBits = $this->textToBits($text);             // Convert + pad text
        $result   = [];

        // Process each 64-bit block
        foreach (array_chunk($textBits, 64) as $block) {
            $result = array_merge($result, $this->desBlock($block, $subKeys));
        }

        // Return as uppercase hex string (easy to display & copy)
        return $this->bitsToHex($result);
    }

    /**
     * Decrypt DES-encrypted hex string back to plain text.
     * Decryption = same as encryption but sub-keys applied in REVERSE order.
     *
     * @param string $hexText The encrypted text as a hexadecimal string
     * @param string $key     The key used during encryption
     * @return string The decrypted plain text
     */
    public function decrypt(string $hexText, string $key): string
    {
        // Remove any spaces from hex input
        $hexText = strtoupper(preg_replace('/\s+/', '', $hexText));

        if (!ctype_xdigit($hexText) || strlen($hexText) % 16 !== 0) {
            throw new \InvalidArgumentException(
                'DES decrypt expects a hex string (length must be a multiple of 16). '.
                'Make sure you are pasting the encrypted output exactly.'
            );
        }

        $keyBits  = $this->prepareKey($key);
        $subKeys  = array_reverse($this->generateSubKeys($keyBits)); // Reversed for decryption

        $bits   = $this->hexToBits($hexText);
        $result = [];

        foreach (array_chunk($bits, 64) as $block) {
            $result = array_merge($result, $this->desBlock($block, $subKeys));
        }

        return $this->bitsToText($result);
    }
}
