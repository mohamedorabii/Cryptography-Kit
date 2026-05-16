<?php

namespace App\Http\Controllers\Ciphers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ciphers\RailFenceCipher;

/**
 * RailFenceController
 * Handles encrypt/decrypt requests for the Rail Fence Cipher only.
 * Key must be an integer >= 2 (number of rails).
 */
class RailFenceController extends Controller
{
    public function __construct(private RailFenceCipher $cipher) {}

    public function encrypt(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string|max:5000',
            'key'  => 'required|integer|min:2',
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
            'key'  => 'required|integer|min:2',
        ]);

        return response()->json([
            'success' => true,
            'result'  => $this->cipher->decrypt($data['text'], (int)$data['key']),
        ]);
    }
}
