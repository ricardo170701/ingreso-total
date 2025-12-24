<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrToolController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Módulo básico: generar un QR con un "código" (texto) para pruebas rápidas.
Route::get('/qr', [QrToolController::class, 'index'])->name('qr.tool');
Route::post('/qr', [QrToolController::class, 'generate'])->name('qr.tool.generate');
