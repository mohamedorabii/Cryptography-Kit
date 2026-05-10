<?php

namespace App\Services\Ciphers;

/**
 * Autokey Cipher Implementation
 * Similar to Vigenere but uses the plaintext itself as part of the key after the initial keyword.
 * This eliminates key repetition, making it harder to break than Vigenere.
 * Key stream = keyword + plaintext (for encryption)
 * Key stream = keyword + decrypted text (for decryption)
 */
class AutokeyCipher
{
    /**
     * Encrypt plain text using Autokey cipher
     *
     * @param string $text The plain text to encrypt
     * @param string $key  The initial keyword
     * @return string The encrypted cipher text
     */
    public function encrypt(string $text, string $key): string
    {
        $text    = strtoupper($text);
        $key     = strtoupper(preg_replace('/[^a-zA-Z]/', '', $key));
        // Strip non-alpha from text to build the extended key
        $letters = preg_replace('/[^A-Z]/', '', $text);

        // Extended key = initial key + plaintext letters
        $extKey  = $key . $letters;

        $result  = '';
        $keyIdx  = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                $shift   = ord($extKey[$keyIdx]) - ord('A');
                $result .= chr((ord($char) - ord('A') + $shift) % 26 + ord('A'));
                $keyIdx++;
            } else {
                // Preserve spaces
                $result .= $char;
            }
        }

        return $result;
    }

    /**
     * Decrypt cipher text using Autokey cipher
     * Must decrypt character by character, building the key as we go.
     *
     * @param string $text The cipher text to decrypt
     * @param string $key  The initial keyword used during encryption
     * @return string The decrypted plain text
     */
    public function decrypt(string $text, string $key): string
    {
        $text   = strtoupper($text);
        $key    = strtoupper(preg_replace('/[^a-zA-Z]/', '', $key));
        $result = '';
        $keyIdx = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                if ($keyIdx < strlen($key)) {
                    // Use the initial keyword first
                    $shift = ord($key[$keyIdx]) - ord('A');
                } else {
                    // Then use the previously decrypted letters as key
                    // Get the decrypted letter at (keyIdx - keyLength) position
                    $letters = preg_replace('/[^A-Z]/', '', $result);
                    $shift   = ord($letters[$keyIdx - strlen($key)]) - ord('A');
                }

                $result .= chr((ord($char) - ord('A') - $shift + 26) % 26 + ord('A'));
                $keyIdx++;
            } else {
                $result .= $char;
            }
        }

        return $result;
    }
}
