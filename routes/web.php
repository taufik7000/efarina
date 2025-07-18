<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\SecureFileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;

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



Route::get('/import-all-videos', function () {
    try {
        $youtubeService = new \App\Services\YouTubeService();
        $channelId = config('services.youtube.default_channel_id');
        
        if (!$channelId) {
            return response()->json(['error' => 'Channel ID not configured in .env']);
        }

        // Validate channel dulu
        if (!$youtubeService->validateChannelId($channelId)) {
            return response()->json(['error' => 'Invalid channel ID']);
        }

        // Get channel info
        $channelInfo = $youtubeService->getChannelInfo($channelId);
        $channelStats = $youtubeService->getChannelStats($channelId);
        
        echo "<h2>Channel Info:</h2>";
        echo "<p><strong>Name:</strong> {$channelInfo['title']}</p>";
        echo "<p><strong>Total Videos:</strong> {$channelStats['video_count']}</p>";
        echo "<p><strong>Subscribers:</strong> " . number_format($channelStats['subscriber_count']) . "</p>";
        echo "<hr>";
        
        echo "<h2>Starting Import ALL Videos...</h2>";
        echo "<p>This may take several minutes...</p>";
        echo "<div id='progress'></div>";
        
        // Flush output untuk real-time feedback
        if (ob_get_level()) {
            ob_end_flush();
        }
        ob_start();
        
        // Import ALL videos (maximum 500 untuk menghindari timeout)
        $maxVideos = min($channelStats['video_count'], 500);
        
        echo "<script>document.getElementById('progress').innerHTML = 'Importing up to {$maxVideos} videos...';</script>";
        flush();
        
        $startTime = microtime(true);
        $videos = $youtubeService->importAllChannelVideos($channelId, $maxVideos);
        $endTime = microtime(true);
        
        $duration = round($endTime - $startTime, 2);
        
        echo "<script>document.getElementById('progress').innerHTML = '';</script>";
        
        echo "<h2>Import Completed!</h2>";
        echo "<p><strong>Total Imported:</strong> " . count($videos) . " videos</p>";
        echo "<p><strong>Duration:</strong> {$duration} seconds</p>";
        echo "<hr>";
        
        // Show sample of imported videos
        echo "<h3>Sample of Imported Videos:</h3>";
        echo "<ul>";
        foreach (array_slice($videos, 0, 10) as $video) {
            echo "<li><strong>{$video->title}</strong> - {$video->formatted_view_count} views - {$video->age}</li>";
        }
        echo "</ul>";
        
        if (count($videos) > 10) {
            echo "<p>... and " . (count($videos) - 10) . " more videos</p>";
        }
        
        echo "<p><a href='/'>← Back to Home</a></p>";

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Import failed: ' . $e->getMessage()
        ], 500);
    }
});