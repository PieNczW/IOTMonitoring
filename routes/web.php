<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Halaman Depan diarahkan ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Grup Route yang WAJIB LOGIN (Middleware 'auth')
Route::middleware(['auth'])->group(function () {
    
    // Semua user (Admin & User) bisa akses dashboard
    Route::get('/dashboard', [SensorController::class, 'index'])->name('dashboard');

    // 3. Khusus ADMIN (Update Setting & Export Excel)
    // Kita cek role manual di Controller nanti, atau bisa pakai middleware custom
    Route::post('/update-settings', [SensorController::class, 'updateSettings'])->name('update.settings');
    Route::get('/export-excel', [SensorController::class, 'export'])->name('export');
});

// 4. Profil User
Route::resource('users', UserController::class);

// Load route bawaan Breeze (Login, Register, dll)
require __DIR__.'/auth.php';

// 5. Profil User
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

