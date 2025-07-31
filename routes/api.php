<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KioskController; // <-- PENTING: Import controller kita

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sinilah Anda dapat mendaftarkan rute API untuk aplikasi Anda. Rute-rute
| ini dimuat oleh RouteServiceProvider dan semuanya akan diberi awalan '/api'.
| Buatlah sesuatu yang hebat!
|
*/

// Rute default Laravel, dilindungi oleh otentikasi Sanctum.
// Ini berguna untuk mengambil data pengguna yang sedang login.
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




// -----------------------------------------------------------------------------
// RUTE UNTUK FITUR ABSENSI KITA
// -----------------------------------------------------------------------------

// Endpoint yang akan menerima data dari ponsel karyawan saat memindai QR code.
// - 'auth:sanctum' memastikan bahwa hanya pengguna yang sudah login di aplikasi
//   (dalam hal ini, di panel Filament) yang bisa mengakses rute ini.
Route::post('/absensi/validasi', [KioskController::class, 'validasiAbsensi'])->middleware('auth:sanctum');

Route::get('/waktu-server', [KioskController::class, 'cekWaktuServer']);
Route::get('/kiosk/kehadiran', [App\Http\Controllers\KioskController::class, 'getKehadiranJson']);
