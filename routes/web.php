<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CryptoController;

/*
|--------------------------------------------------------------------------
| Cryptography Kit Routes
|--------------------------------------------------------------------------
| GET  /         → shows the main UI (index view)
| POST /process  → handles encrypt/decrypt requests (returns JSON)
*/

// Main page — renders the Cryptography Kit UI
Route::get('/', [CryptoController::class, 'index'])->name('crypto.index');

// API endpoint — processes cipher operations and returns JSON result
Route::post('/process', [CryptoController::class, 'process'])->name('crypto.process');
