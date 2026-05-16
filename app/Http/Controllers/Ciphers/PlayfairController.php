<?php

namespace App\Http\Controllers\Ciphers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ciphers\PlayfairCipher;

/**
 * PlayfairController
 * Handles encrypt/decrypt requests for the Playfair Cipher only.
 * Key must be an alphabetic word or phrase (J is treated as I).
 */
class PlayfairController extends Controller
{
    public function __construct(private PlayfairCipher $cipher) {}

    public function encrypt(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:5000',
            'key'  => 'required|string|regex:/^[a-zA-Z\s]+$/',
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
            'key'  => 'required|string|regex:/^[a-zA-Z\s]+$/',
        ]);

        return response()->json([
            'success' => true,
            'result'  => $this->cipher->decrypt($data['text'], $data['key']),
        ]);
    }
}
