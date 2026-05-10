<?php

namespace App\Services\Ciphers;

/**
 * Rail Fence Cipher Implementation
 * A transposition cipher that writes the plaintext in a zigzag pattern
 * across a number of "rails" (rows), then reads off each rail sequentially.
 *
 * Example with 3 rails and "HELLO WORLD":
 *   Rail 0: H . . . O . . . L .
 *   Rail 1: . E . L . W . R . D
 *   Rail 2: . . L . . . O . . .
 * Result: "HOLEWR DLOL" (spaces preserved)
 */
class RailFenceCipher
{
    /**
     * Encrypt plain text using Rail Fence cipher
     *
     * @param string $text  The plain text to encrypt
     * @param int    $rails The number of rails (key)
     * @return string The encrypted cipher text
     */
    public function encrypt(string $text, int $rails): string
    {
        if ($rails < 2) {
            return $text; // No encryption possible with fewer than 2 rails
        }

        // Create an array of strings, one per rail
        $fence = array_fill(0, $rails, '');
        $rail      = 0;
        $direction = 1; // 1 = going down, -1 = going up

        foreach (str_split($text) as $char) {
            // Append current character to its rail
            $fence[$rail] .= $char;

            // Change direction at top or bottom rails
            if ($rail === 0) {
                $direction = 1;
            } elseif ($rail === $rails - 1) {
                $direction = -1;
            }

            $rail += $direction;
        }

        // Read off all rails from top to bottom
        return implode('', $fence);
    }

    /**
     * Decrypt cipher text using Rail Fence cipher
     * Reconstruct which positions belong to each rail, then fill them in.
     *
     * @param string $text  The cipher text to decrypt
     * @param int    $rails The number of rails used during encryption
     * @return string The decrypted plain text
     */
    public function decrypt(string $text, int $rails): string
    {
        if ($rails < 2) {
            return $text;
        }

        $len = strlen($text);
        // Build index pattern — determine which rail each position belongs to
        $pattern = [];
        $rail     = 0;
        $dir      = 1;

        for ($i = 0; $i < $len; $i++) {
            $pattern[] = $rail;
            if ($rail === 0) {
                $dir = 1;
            } elseif ($rail === $rails - 1) {
                $dir = -1;
            }
            $rail += $dir;
        }

        // Calculate the length of each rail
        $railLengths = array_count_values($pattern);

        // Split the cipher text into chunks, one per rail
        $railStrings = [];
        $offset      = 0;
        for ($r = 0; $r < $rails; $r++) {
            $len_r               = $railLengths[$r] ?? 0;
            $railStrings[$r]     = substr($text, $offset, $len_r);
            $offset             += $len_r;
        }

        // Reconstruct the original text by reading characters in zigzag order
        $result  = '';
        $indices = array_fill(0, $rails, 0); // Current read position per rail

        foreach ($pattern as $r) {
            $result             .= $railStrings[$r][$indices[$r]];
            $indices[$r]++;
        }

        return $result;
    }
}
