<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Ciphers\AutokeyCipher;
use App\Services\Ciphers\CaesarCipher;
use App\Services\Ciphers\PlayfairCipher;
use App\Services\Ciphers\RailFenceCipher;
use App\Services\Ciphers\RowTranspositionCipher;
use App\Services\Ciphers\VernamCipher;
use App\Services\Ciphers\VigenereCipher;
use Illuminate\Http\Request;

/**
 * CryptoController
 * Handles routing between all cipher algorithms.
 * Receives algorithm selection, text, key, and operation (encrypt/decrypt)
 * then delegates to the appropriate cipher service class.
 */
class CryptoController extends Controller
{
    /**
     * Display the main Cryptography Kit interface
     */
    public function index()
    {
        return view('crypto.index');
    }

    /**
     * Process the encryption or decryption request
     * Validates input, selects the correct cipher, and returns the result.
     *
     * @param Request $request HTTP request containing algorithm, text, key, and operation
     * @return \Illuminate\Http\JsonResponse JSON response with result or error
     */
    public function process(Request $request)
    {
        // Validate incoming request fields
        $validated = $request->validate([
            'algorithm' => 'required|string|in:caesar,playfair,vigenere,autokey,vernam,railfence,rowtransposition',
            'text'      => 'required|string|max:5000',
            'key'       => 'required|string|max:500',
            'operation' => 'required|in:encrypt,decrypt',
        ]);

        $algorithm = $validated['algorithm'];
        $text      = $validated['text'];
        $key       = $validated['key'];
        $operation = $validated['operation'];

        try {
            // Route to the appropriate cipher service based on algorithm selection
            $result = match ($algorithm) {

                'caesar' => $this->processCaesar($text, $key, $operation),

                'playfair' => $this->processPlayfair($text, $key, $operation),

                'vigenere' => $this->processVigenere($text, $key, $operation),

                'autokey' => $this->processAutokey($text, $key, $operation),

                'vernam' => $this->processVernam($text, $key, $operation),

                'railfence' => $this->processRailFence($text, $key, $operation),

                'rowtransposition' => $this->processRowTransposition($text, $key, $operation),
            };

            return response()->json([
                'success' => true,
                'result'  => $result,
            ]);

        } catch (\Exception $e) {
            // Return a friendly error message if something goes wrong
            return response()->json([
                'success' => false,
                'error'   => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Process Caesar cipher — key must be an integer (shift value)
     */
    private function processCaesar(string $text, string $key, string $op): string
    {
        if (!is_numeric($key)) {
            throw new \InvalidArgumentException('Caesar cipher requires a numeric key (e.g. 3).');
        }
        $cipher = new CaesarCipher();
        return $op === 'encrypt'
            ? $cipher->encrypt($text, (int)$key)
            : $cipher->decrypt($text, (int)$key);
    }

    /**
     * Process Playfair cipher — key must be alphabetic only
     */
    private function processPlayfair(string $text, string $key, string $op): string
    {
        $cipher = new PlayfairCipher();
        return $op === 'encrypt'
            ? $cipher->encrypt($text, $key)
            : $cipher->decrypt($text, $key);
    }

    /**
     * Process Vigenere cipher — key must be alphabetic
     */
    private function processVigenere(string $text, string $key, string $op): string
    {
        $alphaKey = preg_replace('/[^a-zA-Z]/', '', $key);
        if (empty($alphaKey)) {
            throw new \InvalidArgumentException('Vigenere cipher requires an alphabetic key.');
        }
        $cipher = new VigenereCipher();
        return $op === 'encrypt'
            ? $cipher->encrypt($text, $key)
            : $cipher->decrypt($text, $key);
    }

    /**
     * Process Autokey cipher — key must be alphabetic
     */
    private function processAutokey(string $text, string $key, string $op): string
    {
        $alphaKey = preg_replace('/[^a-zA-Z]/', '', $key);
        if (empty($alphaKey)) {
            throw new \InvalidArgumentException('Autokey cipher requires an alphabetic key.');
        }
        $cipher = new AutokeyCipher();
        return $op === 'encrypt'
            ? $cipher->encrypt($text, $key)
            : $cipher->decrypt($text, $key);
    }

    /**
     * Process Vernam (XOR) cipher — key must be alphabetic
     */
    private function processVernam(string $text, string $key, string $op): string
    {
        $alphaKey = preg_replace('/[^a-zA-Z]/', '', $key);
        if (empty($alphaKey)) {
            throw new \InvalidArgumentException('Vernam cipher requires an alphabetic key.');
        }
        $cipher = new VernamCipher();
        return $op === 'encrypt'
            ? $cipher->encrypt($text, $key)
            : $cipher->decrypt($text, $key);
    }

    /**
     * Process Rail Fence cipher — key must be an integer ≥ 2 (number of rails)
     */
    private function processRailFence(string $text, string $key, string $op): string
    {
        if (!is_numeric($key) || (int)$key < 2) {
            throw new \InvalidArgumentException('Rail Fence cipher requires a numeric key ≥ 2 (number of rails).');
        }
        $cipher = new RailFenceCipher();
        return $op === 'encrypt'
            ? $cipher->encrypt($text, (int)$key)
            : $cipher->decrypt($text, (int)$key);
    }

    /**
     * Process Row Transposition cipher — key must be alphabetic
     */
    private function processRowTransposition(string $text, string $key, string $op): string
    {
        $alphaKey = preg_replace('/[^a-zA-Z]/', '', $key);
        if (empty($alphaKey)) {
            throw new \InvalidArgumentException('Row Transposition cipher requires an alphabetic key.');
        }
        $cipher = new RowTranspositionCipher();
        return $op === 'encrypt'
            ? $cipher->encrypt($text, $key)
            : $cipher->decrypt($text, $key);
    }
}
