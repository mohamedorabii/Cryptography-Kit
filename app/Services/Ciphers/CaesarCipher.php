<?php

namespace App\Services\Ciphers;

/**
 * Caesar Cipher Implementation
 * A substitution cipher where each letter is shifted by a fixed number of positions.
 * Example: shift=3, A→D, B→E, Z→C
 */
class CaesarCipher
{
    /**
     * Encrypt plain text using Caesar cipher
     *
     * @param string $text  The plain text to encrypt
     * @param int    $shift The shift value (key)
     * @return string The encrypted cipher text
     */
    public function encrypt(string $text, int $shift): string
    {
        // Normalize shift to be within 0–25
        $shift = ((int)$shift % 26 + 26) % 26;
        $result = '';

        foreach (str_split(strtoupper($text)) as $char) {
            if (ctype_alpha($char)) {
                // Shift letter and wrap around with modulo 26
                $result .= chr((ord($char) - ord('A') + $shift) % 26 + ord('A'));
            } else {
                // Non-alphabet characters are kept as-is (spaces, punctuation, etc.)
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * Decrypt cipher text using Caesar cipher
     * Decryption is the reverse: shift backwards
     *
     * @param string $text  The cipher text to decrypt
     * @param int    $shift The shift value (key) used during encryption
     * @return string The decrypted plain text
     */
    public function decrypt(string $text, int $shift): string
    {
        // Decrypt by shifting in the opposite direction
        return $this->encrypt($text, -$shift);
    }
}
