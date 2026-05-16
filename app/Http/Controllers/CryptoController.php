<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * CryptoController
 * Responsible ONLY for rendering the main Cryptography Kit UI.
 * All cipher logic is delegated to individual controllers under:
 * App\Http\Controllers\Ciphers\
 */
class CryptoController extends Controller
{
    /**
     * Display the main Cryptography Kit interface.
     */
    public function index()
    {
        return view('crypto.index');
    }
}
