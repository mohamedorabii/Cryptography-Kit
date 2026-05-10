<?php

namespace App\Services\Ciphers;

/**
 * Vigenere Cipher Implementation
 * A polyalphabetic substitution cipher that uses a keyword to shift each letter.
 * The key is repeated to match the length of the plaintext.
 * Formula: C = (P + K) mod 26  |  P = (C - K + 26) mod 26
 */
class VigenereCipher
{
    /**
     * Encrypt plain text using the Vigenere cipher
     *
     * @param string $text The plain text to encrypt
     * @param string $key  The keyword used for encryption
     * @return string The encrypted cipher text
     */
    public function encrypt(string $text, string $key): string
    {
        $text   = strtoupper($text);
        $key    = strtoupper(preg_replace('/[^a-zA-Z]/', '', $key)); // Remove non-alpha from key
        $result = '';
        $keyLen = strlen($key);
        $keyIdx = 0; // Separate index to skip non-alpha characters properly

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                // Get the shift value from the repeating key
                $shift   = ord($key[$keyIdx % $keyLen]) - ord('A');
                $result .= chr((ord($char) - ord('A') + $shift) % 26 + ord('A'));
                $keyIdx++;
            } else {
                // Preserve spaces and non-alpha chars
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * Decrypt cipher text using the Vigenere cipher
     *
     * @param string $text The cipher text to decrypt
     * @param string $key  The keyword used during encryption
     * @return string The decrypted plain text
     */
    public function decrypt(string $text, string $key): string
    {
        $text   = strtoupper($text);
        $key    = strtoupper(preg_replace('/[^a-zA-Z]/', '', $key));
        $result = '';
        $keyLen = strlen($key);
        $keyIdx = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                // Subtract the key shift and wrap with +26 to avoid negative modulo
                $shift   = ord($key[$keyIdx % $keyLen]) - ord('A');
                $result .= chr((ord($char) - ord('A') - $shift + 26) % 26 + ord('A'));
                $keyIdx++;
            } else {
                $result .= $char;
            }
        }

        return $result;
    }
}
