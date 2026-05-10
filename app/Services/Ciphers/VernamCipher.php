<?php

namespace App\Services\Ciphers;

/**
 * Vernam Cipher Implementation
 * Also known as One-Time Pad (OTP). Uses XOR operation between the plaintext and key.
 * For letters: C = (P XOR K)  where letters are treated as 0–25 values.
 * The key must be at least as long as the plaintext for true OTP security.
 * If the key is shorter, it is repeated (making it a running key cipher).
 */
class VernamCipher
{
    /**
     * Encrypt plain text using Vernam (XOR) cipher
     *
     * @param string $text The plain text to encrypt
     * @param string $key  The key (repeated if shorter than text)
     * @return string The encrypted cipher text
     */
    public function encrypt(string $text, string $key): string
    {
        $text   = strtoupper($text);
        $key    = strtoupper(preg_replace('/[^a-zA-Z]/', '', $key));
        $keyLen = strlen($key);
        $result = '';
        $keyIdx = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                // XOR the numeric values (0–25) of the plaintext and key characters
                $p       = ord($char) - ord('A');
                $k       = ord($key[$keyIdx % $keyLen]) - ord('A');
                $c       = $p ^ $k;                    // XOR operation
                $result .= chr($c + ord('A'));
                $keyIdx++;
            } else {
                // Preserve non-alpha characters (spaces, punctuation, etc.)
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * Decrypt cipher text using Vernam cipher
     * XOR is its own inverse: (C XOR K) = P, so decryption = encryption
     *
     * @param string $text The cipher text to decrypt
     * @param string $key  The key used during encryption
     * @return string The decrypted plain text
     */
    public function decrypt(string $text, string $key): string
    {
        // XOR is self-inverse: applying it again reverses the operation
        return $this->encrypt($text, $key);
    }
}
