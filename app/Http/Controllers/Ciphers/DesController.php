<?php

namespace App\Http\Controllers\Ciphers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ciphers\DesCipher;

/**
 * DesController
 * Handles encrypt/decrypt requests for the DES Cipher only.
 * Key: any string up to 8 characters.
 * Encrypted output is a HEX string — must be passed back for decryption.
 */
class DesController extends Controller
{
    public function __construct(private DesCipher $cipher) {}

    public function encrypt(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:5000',
            'key'  => 'required|string|max:8',
        ]);

        return response()->json([
            'success' => true,
            'result'  => $this->cipher->encrypt($data['text'], $data['key']),
        ]);
    }

    public function decrypt(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:5000',  // expects HEX string
            'key'  => 'required|string|max:8',
        ]);

        try {
            return response()->json([
                'success' => true,
                'result'  => $this->cipher->decrypt($data['text'], $data['key']),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 422);
        }
    }
}
