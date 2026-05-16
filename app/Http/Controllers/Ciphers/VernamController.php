<?php

namespace App\Http\Controllers\Ciphers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ciphers\VernamCipher;

/**
 * VernamController
 * Handles encrypt/decrypt requests for the Vernam (XOR) Cipher only.
 * Key must be alphabetic. XOR is self-inverse so encrypt = decrypt.
 */
class VernamController extends Controller
{
    public function __construct(private VernamCipher $cipher) {}

    public function encrypt(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:5000',
            'key'  => 'required|string|regex:/^[a-zA-Z]+$/',
        ]);

        return response()->json([
            'success' => true,
            'result'  => $this->cipher->encrypt($data['text'], $data['key']),
        ]);
    }

    public function decrypt(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:5000',
            'key'  => 'required|string|regex:/^[a-zA-Z]+$/',
        ]);

        return response()->json([
            'success' => true,
            // Vernam decrypt = encrypt (XOR is its own inverse)
            'result'  => $this->cipher->decrypt($data['text'], $data['key']),
        ]);
    }
}
