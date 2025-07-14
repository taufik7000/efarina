<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KioskController;

Route::get('/', function () {
    return redirect()->route('login'); // Arahkan halaman utama ke login
});

// Rute untuk Login Kustom
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirect admin login ke login terpusat
Route::get('/admin/login', function() {
    return redirect('/login');
});

// Redirect semua panel login ke login terpusat
Route::get('/direktur/login', function() {
    return redirect('/login');
});

Route::get('/hrd/login', function() {
    return redirect('/login');
});

Route::get('/keuangan/login', function() {
    return redirect('/login');
});

Route::get('/bisnis/login', function() {
    return redirect('/login');
});

Route::get('/redaksi/login', function() {
    return redirect('/login');
});


Route::get('/team/login', function() {
    return redirect('/login');
});

// Endpoint untuk Kiosk me-refresh daftar kehadiran (tidak perlu otentikasi)
Route::get('/kiosk/kehadiran', [KioskController::class, 'getKehadiranJson']);


// Rute untuk menampilkan halaman Kiosk
Route::get('/absensi', [KioskController::class, 'tampilkanKiosk'])->name('kiosk.tampilan');

// Rute API untuk membuat gambar QR code baru
Route::get('/api/kiosk/qrcode', [KioskController::class, 'buatQrCode'])->name('kiosk.qrcode');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/absensi/validasi', [KioskController::class, 'validasiAbsensi']);
    Route::get('/absensi/status', [KioskController::class, 'cekStatusAbsensi']);
});

// Atau jika menggunakan web middleware (untuk Filament):
Route::middleware(['web', 'auth'])->prefix('api')->group(function () {
    Route::post('/absensi/validasi', [KioskController::class, 'validasiAbsensi']);
    Route::get('/absensi/status', [KioskController::class, 'cekStatusAbsensi']);
});