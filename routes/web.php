<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

// Web route untuk menampilkan dashboard:
Route::get('/', [SensorController::class, 'index']);
// Route untuk export data ke Excel:
Route::get('/export-excel', [SensorController::class, 'export']);