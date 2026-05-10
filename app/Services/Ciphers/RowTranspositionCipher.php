<?php

namespace App\Services\Ciphers;

/**
 * Row Transposition Cipher Implementation
 * The plaintext is written into a grid row by row, then columns are
 * rearranged according to the alphabetical order of the key, and
 * the cipher text is read off column by column.
 *
 * Example: key="KEY", text="HELLO WORLD"
 *   Key order:  K(2) E(1) Y(3)  →  column order: 1, 0, 2
 *   Grid:
 *     H E L
 *     L O W
 *     O R L
 *     D X X  (X = padding)
 *   Read col 1 first (E order): E,O,R,X → EORX
 *   Then col 0 (K order): H,L,O,D → HLOD
 *   Then col 2 (Y order): L,W,L,X → LWLX
 */
class RowTranspositionCipher
{
    /**
     * Get the column order based on alphabetical sorting of key characters
     *
     * @param string $key The transposition key
     * @return array Column indices in sorted order
     */
    private function getColumnOrder(string $key): array
    {
        $key     = strtoupper($key);
        $indexed = [];

        // Associate each character with its original position
        for ($i = 0; $i < strlen($key); $i++) {
            $indexed[] = ['char' => $key[$i], 'pos' => $i];
        }

        // Sort by character (alphabetical order)
        usort($indexed, fn($a, $b) => strcmp($a['char'], $b['char']));

        // Return sorted column indices
        return array_column($indexed, 'pos');
    }

    /**
     * Encrypt plain text using Row Transposition cipher
     *
     * @param string $text The plain text to encrypt
     * @param string $key  The keyword that defines column order
     * @return string The encrypted cipher text
     */
    public function encrypt(string $text, string $key): string
    {
        $key    = strtoupper(preg_replace('/[^a-zA-Z]/', '', $key));
        $cols   = strlen($key);
        $text   = strtoupper(preg_replace('/\s+/', '', $text)); // Remove spaces for grid

        // Pad the text with 'X' to fill the last row completely
        $padLen = ($cols - (strlen($text) % $cols)) % $cols;
        $text  .= str_repeat('X', $padLen);

        $rows   = strlen($text) / $cols;
        $order  = $this->getColumnOrder($key);
        $result = '';

        // Read columns in sorted key order
        foreach ($order as $col) {
            for ($row = 0; $row < $rows; $row++) {
                $result .= $text[$row * $cols + $col];
            }
        }

        return $result;
    }

    /**
     * Decrypt cipher text using Row Transposition cipher
     * Reverse the column permutation to restore the original row order.
     *
     * @param string $text The cipher text to decrypt
     * @param string $key  The keyword used during encryption
     * @return string The decrypted plain text
     */
    public function decrypt(string $text, string $key): string
    {
        $key    = strtoupper(preg_replace('/[^a-zA-Z]/', '', $key));
        $cols   = strlen($key);
        $text   = strtoupper($text);
        $len    = strlen($text);
        $rows   = (int)ceil($len / $cols);
        $order  = $this->getColumnOrder($key);

        // Build reverse mapping: sorted position → original column
        $revOrder = array_fill(0, $cols, 0);
        foreach ($order as $sortedIdx => $origCol) {
            $revOrder[$origCol] = $sortedIdx;
        }

        // Calculate characters per column (may vary if text isn't perfectly divisible)
        $colLengths = array_fill(0, $cols, $rows);

        // Split cipher text back into columns (in sorted key order)
        $columns = [];
        $offset  = 0;
        foreach ($order as $col) {
            $columns[$col] = str_split(substr($text, $offset, $colLengths[$col]));
            $offset       += $colLengths[$col];
        }

        // Read row by row across original columns to reconstruct text
        $result = '';
        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                if (isset($columns[$col][$row])) {
                    $result .= $columns[$col][$row];
                }
            }
        }

        // Remove padding 'X' characters from the end
        return rtrim($result, 'X');
    }
}
