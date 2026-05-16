<?php

namespace App\Http\Controllers\Ciphers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ciphers\CaesarCipher;

/**
 * CaesarController
 * Handles encrypt/decrypt requests for the Caesar Cipher only.
 * Key must be a numeric shift value (e.g. 3).
 */
class CaesarController extends Controller
{
    public function __construct(private CaesarCipher $cipher) {}

    public function encrypt(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:5000',
            'key'  => 'required|numeric',
        ]);

        return response()->json([
            'success' => true,
            'result'  => $this->cipher->encrypt($data['text'], (int)$data['key']),
        ]);
    }

    public function decrypt(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:5000',
            'key'  => 'required|numeric',
        ]);

        return response()->json([
            'success' => true,
            'result'  => $this->cipher->decrypt($data['text'], (int)$data['key']),
        ]);
    }
}
