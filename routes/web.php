<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\Ciphers\CaesarController;
use App\Http\Controllers\Ciphers\PlayfairController;
use App\Http\Controllers\Ciphers\VigenereController;
use App\Http\Controllers\Ciphers\AutokeyController;
use App\Http\Controllers\Ciphers\VernamController;
use App\Http\Controllers\Ciphers\RailFenceController;
use App\Http\Controllers\Ciphers\RowTranspositionController;
use App\Http\Controllers\Ciphers\DesController;

/*
|--------------------------------------------------------------------------
| Cryptography Kit Routes
|--------------------------------------------------------------------------
| GET  /                          → Main UI
| POST /cipher/{name}/encrypt     → Encrypt using that cipher
| POST /cipher/{name}/decrypt     → Decrypt using that cipher
*/

// Main page
Route::get('/', [CryptoController::class, 'index'])->name('crypto.index');

// ── Caesar ──────────────────────────────────────────────
Route::prefix('cipher/caesar')->name('caesar.')->group(function () {
    Route::post('encrypt', [CaesarController::class, 'encrypt'])->name('encrypt');
    Route::post('decrypt', [CaesarController::class, 'decrypt'])->name('decrypt');
});

// ── Playfair ─────────────────────────────────────────────
Route::prefix('cipher/playfair')->name('playfair.')->group(function () {
    Route::post('encrypt', [PlayfairController::class, 'encrypt'])->name('encrypt');
    Route::post('decrypt', [PlayfairController::class, 'decrypt'])->name('decrypt');
});

// ── Vigenere ──────────────────────────────────────────────
Route::prefix('cipher/vigenere')->name('vigenere.')->group(function () {
    Route::post('encrypt', [VigenereController::class, 'encrypt'])->name('encrypt');
    Route::post('decrypt', [VigenereController::class, 'decrypt'])->name('decrypt');
});

// ── Autokey ───────────────────────────────────────────────
Route::prefix('cipher/autokey')->name('autokey.')->group(function () {
    Route::post('encrypt', [AutokeyController::class, 'encrypt'])->name('encrypt');
    Route::post('decrypt', [AutokeyController::class, 'decrypt'])->name('decrypt');
});

// ── Vernam ────────────────────────────────────────────────
Route::prefix('cipher/vernam')->name('vernam.')->group(function () {
    Route::post('encrypt', [VernamController::class, 'encrypt'])->name('encrypt');
    Route::post('decrypt', [VernamController::class, 'decrypt'])->name('decrypt');
});

// ── Rail Fence ────────────────────────────────────────────
Route::prefix('cipher/railfence')->name('railfence.')->group(function () {
    Route::post('encrypt', [RailFenceController::class, 'encrypt'])->name('encrypt');
    Route::post('decrypt', [RailFenceController::class, 'decrypt'])->name('decrypt');
});

// ── Row Transposition ─────────────────────────────────────
Route::prefix('cipher/rowtransposition')->name('rowtransposition.')->group(function () {
    Route::post('encrypt', [RowTranspositionController::class, 'encrypt'])->name('encrypt');
    Route::post('decrypt', [RowTranspositionController::class, 'decrypt'])->name('decrypt');
});

// ── DES ───────────────────────────────────────────────────
Route::prefix('cipher/des')->name('des.')->group(function () {
    Route::post('encrypt', [DesController::class, 'encrypt'])->name('encrypt');
    Route::post('decrypt', [DesController::class, 'decrypt'])->name('decrypt');
});
