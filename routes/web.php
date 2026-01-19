<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rute Halaman Utama (Saat buka IP:8000)
Route::get('/', [SensorController::class, 'index']);

// Rute Dashboard (PENTING: Tambahkan ini agar Filter Tanggal jalan)
Route::get('/dashboard', [SensorController::class, 'index']);

// Rute Download Excel
Route::get('/export-excel', [SensorController::class, 'export']);

// === Untuk update fan  ===
Route::post('/update-settings', [SensorController::class, 'updateSettings'])->name('update.settings');