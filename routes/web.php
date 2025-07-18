<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\SecureFileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\VideoController;

Route::get('/', [HomeController::class, 'index'])->name('home');

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

// Secure file routes - harus login
Route::middleware(['auth'])->group(function () {
    // View/preview file
    Route::get('/secure-files/bukti-realisasi/{projectId}/{pengajuanId}/{filename}', 
        [SecureFileController::class, 'serveBuktiRealisasi'])
        ->name('secure.bukti-realisasi')
        ->where(['projectId' => '[0-9]+', 'pengajuanId' => '[0-9]+']);
    
    // Download file
    Route::get('/secure-files/download/bukti-realisasi/{projectId}/{pengajuanId}/{filename}', 
        [SecureFileController::class, 'downloadBuktiRealisasi'])
        ->name('secure.download.bukti-realisasi')
        ->where(['projectId' => '[0-9]+', 'pengajuanId' => '[0-9]+']);
});

// News routes
Route::prefix('berita')->name('news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/kategori/{category:slug}', [NewsController::class, 'category'])->name('category');
    Route::get('/tag/{tag:slug}', [NewsController::class, 'tag'])->name('tag');
    Route::get('/{news:slug}', [NewsController::class, 'show'])->name('show');
});

// API routes for views counter
Route::post('/api/news/{news}/view', [NewsController::class, 'incrementView'])
    ->name('api.news.view')
    ->middleware('throttle:10,1'); // Limit 10 requests per minute



Route::prefix('video')->name('video.')->group(function () {
    Route::get('/', [VideoController::class, 'index'])->name('index');
    Route::get('/{videoId}', [VideoController::class, 'show'])->name('show');
});

// API routes untuk video (jika diperlukan AJAX)
Route::prefix('api/video')->name('api.video.')->group(function () {
    Route::get('/', [App\Http\Controllers\VideoController::class, 'apiIndex'])->name('index');
});