<?php

namespace App\Services\Ciphers;

/**
 * Playfair Cipher Implementation
 * Uses a 5x5 matrix built from a keyword. Letters I and J share one cell.
 * The plaintext is split into digraphs (pairs of letters) and each pair is
 * encrypted using three rules based on their positions in the matrix.
 *
 * Rules:
 *  1. Same row    → each letter is replaced by the next letter in the row (wrap around)
 *  2. Same column → each letter is replaced by the letter below it (wrap around)
 *  3. Rectangle   → each letter is replaced by the letter in the same row but the other corner
 *
 * Special cases:
 *  - If both letters in a pair are the same, insert 'X' between them
 *  - If the plaintext has odd length, append 'X' at the end
 *  - J is treated as I
 */
class PlayfairCipher
{
    /** @var array 5x5 Playfair matrix */
    private array $matrix = [];

    /** @var array Lookup: letter → [row, col] */
    private array $position = [];

    /**
     * Build the 5x5 Playfair key matrix from a keyword
     *
     * @param string $key The keyword
     */
    private function buildMatrix(string $key): void
    {
        $this->matrix   = [];
        $this->position = [];

        // Prepare key: uppercase, replace J with I, remove non-alpha
        $key   = strtoupper($key);
        $key   = str_replace('J', 'I', $key);
        $key   = preg_replace('/[^A-Z]/', '', $key);

        // Build alphabet list: key letters first, then remaining letters in order
        $seen    = [];
        $letters = '';

        foreach (str_split($key) as $char) {
            if (!isset($seen[$char])) {
                $letters    .= $char;
                $seen[$char] = true;
            }
        }

        // Add remaining alphabet letters (skipping J)
        foreach (range('A', 'Z') as $char) {
            if ($char === 'J') continue;
            if (!isset($seen[$char])) {
                $letters    .= $char;
                $seen[$char] = true;
            }
        }

        // Fill the 5x5 matrix
        for ($row = 0; $row < 5; $row++) {
            for ($col = 0; $col < 5; $col++) {
                $letter = $letters[$row * 5 + $col];
                $this->matrix[$row][$col]  = $letter;
                $this->position[$letter]   = [$row, $col];
            }
        }
    }

    /**
     * Prepare plain text: remove spaces, replace J→I, handle double letters
     * Returns array of digraphs ready for encryption
     *
     * @param string $text
     * @return array Array of 2-char strings (digraphs)
     */
    private function prepareText(string $text): array
    {
        $text   = strtoupper($text);
        $text   = str_replace('J', 'I', $text);
        $text   = preg_replace('/[^A-Z]/', '', $text); // Remove all non-alpha

        $pairs  = [];
        $i      = 0;

        while ($i < strlen($text)) {
            $a = $text[$i];
            $b = ($i + 1 < strlen($text)) ? $text[$i + 1] : 'X';

            if ($a === $b) {
                // If both letters same, insert X as second letter
                $pairs[] = $a . 'X';
                $i++;
            } else {
                $pairs[] = $a . $b;
                $i += 2;
            }
        }

        return $pairs;
    }

    /**
     * Encrypt a single digraph (pair of letters)
     *
     * @param string $a First letter
     * @param string $b Second letter
     * @return string Encrypted 2-char string
     */
    private function encryptPair(string $a, string $b): string
    {
        [$r1, $c1] = $this->position[$a];
        [$r2, $c2] = $this->position[$b];

        if ($r1 === $r2) {
            // Same row → shift right (wrap around)
            return $this->matrix[$r1][($c1 + 1) % 5] .
                   $this->matrix[$r2][($c2 + 1) % 5];
        } elseif ($c1 === $c2) {
            // Same column → shift down (wrap around)
            return $this->matrix[($r1 + 1) % 5][$c1] .
                   $this->matrix[($r2 + 1) % 5][$c2];
        } else {
            // Rectangle → swap columns
            return $this->matrix[$r1][$c2] .
                   $this->matrix[$r2][$c1];
        }
    }

    /**
     * Decrypt a single digraph (pair of letters)
     *
     * @param string $a First letter
     * @param string $b Second letter
     * @return string Decrypted 2-char string
     */
    private function decryptPair(string $a, string $b): string
    {
        [$r1, $c1] = $this->position[$a];
        [$r2, $c2] = $this->position[$b];

        if ($r1 === $r2) {
            // Same row → shift left (wrap around)
            return $this->matrix[$r1][($c1 + 4) % 5] .
                   $this->matrix[$r2][($c2 + 4) % 5];
        } elseif ($c1 === $c2) {
            // Same column → shift up (wrap around)
            return $this->matrix[($r1 + 4) % 5][$c1] .
                   $this->matrix[($r2 + 4) % 5][$c2];
        } else {
            // Rectangle → swap columns (same as encryption)
            return $this->matrix[$r1][$c2] .
                   $this->matrix[$r2][$c1];
        }
    }

    /**
     * Encrypt plain text using Playfair cipher
     *
     * @param string $text The plain text to encrypt
     * @param string $key  The keyword for building the matrix
     * @return string The encrypted cipher text
     */
    public function encrypt(string $text, string $key): string
    {
        $this->buildMatrix($key);
        $pairs  = $this->prepareText($text);
        $result = '';

        foreach ($pairs as $pair) {
            $result .= $this->encryptPair($pair[0], $pair[1]) . ' ';
        }

        return rtrim($result);
    }

    /**
     * Decrypt cipher text using Playfair cipher
     *
     * @param string $text The cipher text to decrypt
     * @param string $key  The keyword used during encryption
     * @return string The decrypted plain text
     */
    public function decrypt(string $text, string $key): string
    {
        $this->buildMatrix($key);
        // Remove spaces and split into pairs of 2
        $text   = strtoupper(preg_replace('/\s+/', '', $text));
        $pairs  = str_split($text, 2);
        $result = '';

        foreach ($pairs as $pair) {
            if (strlen($pair) === 2) {
                $result .= $this->decryptPair($pair[0], $pair[1]) . ' ';
            }
        }

        return rtrim($result);
    }
}
