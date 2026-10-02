<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PekerjaController;
use App\Http\Controllers\RekapController;
use Illuminate\Support\Facades\Route;

// Hanya untuk yang BELUM login
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'tampil'])->name('login');
    Route::post('/login', [LoginController::class, 'proses']);
});

// Hanya untuk yang SUDAH login
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [LoginController::class, 'keluar'])->name('logout');

    Route::resource('pekerja', PekerjaController::class);
    Route::resource('rekap', RekapController::class);
});