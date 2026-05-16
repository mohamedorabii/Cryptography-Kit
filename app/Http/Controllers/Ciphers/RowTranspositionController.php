<?php

namespace App\Http\Controllers\Ciphers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ciphers\RowTranspositionCipher;

/**
 * RowTranspositionController
 * Handles encrypt/decrypt requests for the Row Transposition Cipher only.
 * Key must be an alphabetic word (column order determined by letter order).
 */
class RowTranspositionController extends Controller
{
    public function __construct(private RowTranspositionCipher $cipher) {}

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
            'result'  => $this->cipher->decrypt($data['text'], $data['key']),
        ]);
    }
}
