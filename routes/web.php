<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\SecureFileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\SecureAttendanceController;
use App\Exports\AttendanceReportExport;
use App\Http\Middleware\HandleRedirects;


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

Route::post('/api/news/{news}/view', [NewsController::class, 'incrementView'])
    ->name('api.news.view')
    ->middleware('throttle:100,1');


Route::get('/video/{videoId}', [VideoController::class, 'show'])
    ->name('video.show')
    ->where('videoId', '[a-zA-Z0-9\-_]+');

// API routes for views counter
Route::post('/api/news/{news}/view', [NewsController::class, 'incrementView'])
    ->name('api.news.view')
    ->middleware('throttle:100,1'); // Limit 100 requests per minute



Route::prefix('video')->name('video.')->group(function () {
    Route::get('/', [VideoController::class, 'index'])->name('index');
    Route::get('/{videoId}', [VideoController::class, 'show'])->name('show');
});

// API routes untuk video (jika diperlukan AJAX)
Route::prefix('api/video')->name('api.video.')->group(function () {
    Route::get('/', [App\Http\Controllers\VideoController::class, 'apiIndex'])->name('index');
});


// Route GET untuk PDF export
Route::get('/hrd/export/attendance-pdf', function() {
    $bulan = request('bulan', now()->month);
    $tahun = request('tahun', now()->year);
    
    try {
        // Generate data
        $startDate = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $holidayService = new \App\Services\HolidayService();
        $holidays = $holidayService->getHolidays($tahun);

        $workingDays = $startDate->diffInDaysFiltered(function (\Carbon\Carbon $date) use ($holidays) {
            return !$date->isSunday() && !isset($holidays[$date->format('Y-m-d')]);
        }, $endDate);

        $users = \App\Models\User::with(['jabatan', 'kehadiran' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }])
        ->whereHas('roles', function ($query) {
            $query->whereNotIn('name', ['Direktur', 'Admin']);
        })
        ->get();

        $data = [];
        $totalSummary = array_fill_keys(['hadir', 'terlambat', 'cuti', 'sakit', 'izin', 'kompensasi', 'absen'], 0);

        foreach ($users as $user) {
            $statusCounts = $user->kehadiran->countBy('status')->all();

            $hadir = ($statusCounts['Tepat Waktu'] ?? 0) + ($statusCounts['Terlambat'] ?? 0);
            $terlambat = $statusCounts['Terlambat'] ?? 0;
            $cuti = $statusCounts['Cuti'] ?? 0;
            $sakit = $statusCounts['Sakit'] ?? 0;
            $izin = $statusCounts['Izin'] ?? 0;
            $kompensasi = $statusCounts['Kompensasi Libur'] ?? 0;

            $totalNonAbsen = $hadir + $cuti + $sakit + $izin + $kompensasi;
            $absen = max(0, $workingDays - $totalNonAbsen);
            
            $attendanceRate = $workingDays > 0 ? round(($hadir / $workingDays) * 100) : 0;

            // Clean data dari special characters
            $data[] = [
                'name' => preg_replace('/[^\p{L}\p{N}\s]/u', '', $user->name),
                'jabatan' => preg_replace('/[^\p{L}\p{N}\s]/u', '', $user->jabatan->nama_jabatan ?? 'N/A'),
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'cuti' => $cuti,
                'sakit' => $sakit,
                'izin' => $izin,
                'kompensasi' => $kompensasi,
                'absen' => $absen,
                'attendance_rate' => $attendanceRate,
            ];

            $totalSummary['hadir'] += $hadir;
            $totalSummary['terlambat'] += $terlambat;
            $totalSummary['cuti'] += $cuti;
            $totalSummary['sakit'] += $sakit;
            $totalSummary['izin'] += $izin;
            $totalSummary['kompensasi'] += $kompensasi;
            $totalSummary['absen'] += $absen;
        }

        // Set locale dan generate view
        \Carbon\Carbon::setLocale('en'); // Use English to avoid special chars
        
        $html = view('exports.attendance-report-pdf', [
            'reportData' => $data,
            'summary' => $totalSummary,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'periode' => \Carbon\Carbon::create($tahun, $bulan, 1)->format('F Y')
        ])->render();

        // Generate PDF dengan options yang aman
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => false,
                'isPhpEnabled' => false,
            ]);
            
        $fileName = 'Laporan_Kehadiran_' . \Carbon\Carbon::create($tahun, $bulan, 1)->format('F_Y') . '.pdf';
        
        return $pdf->download($fileName);
        
    } catch (\Exception $e) {
        // Jika ada error, return JSON error
        return response()->json([
            'error' => 'Failed to generate PDF: ' . $e->getMessage()
        ], 500);
    }
})->name('hrd.export.attendance.pdf')->middleware(['auth', 'role:hrd']);

// Route untuk Print (HTML view yang bisa di-print)
Route::get('/hrd/attendance-report/print', function() {
    $bulan = request('bulan', now()->month);
    $tahun = request('tahun', now()->year);
    
    // Generate data yang sama
    $startDate = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
    $endDate = $startDate->copy()->endOfMonth();
    $holidayService = new \App\Services\HolidayService();
    $holidays = $holidayService->getHolidays($tahun);

    $workingDays = $startDate->diffInDaysFiltered(function (\Carbon\Carbon $date) use ($holidays) {
        return !$date->isSunday() && !isset($holidays[$date->format('Y-m-d')]);
    }, $endDate);

    $users = \App\Models\User::with(['jabatan', 'kehadiran' => function ($query) use ($startDate, $endDate) {
        $query->whereBetween('tanggal', [$startDate, $endDate]);
    }])
    ->whereHas('roles', function ($query) {
        $query->whereNotIn('name', ['Direktur', 'Admin']);
    })
    ->get();

    $data = [];
    $totalSummary = array_fill_keys(['hadir', 'terlambat', 'cuti', 'sakit', 'izin', 'kompensasi', 'absen'], 0);

    foreach ($users as $user) {
        $statusCounts = $user->kehadiran->countBy('status')->all();

        $hadir = ($statusCounts['Tepat Waktu'] ?? 0) + ($statusCounts['Terlambat'] ?? 0);
        $terlambat = $statusCounts['Terlambat'] ?? 0;
        $cuti = $statusCounts['Cuti'] ?? 0;
        $sakit = $statusCounts['Sakit'] ?? 0;
        $izin = $statusCounts['Izin'] ?? 0;
        $kompensasi = $statusCounts['Kompensasi Libur'] ?? 0;

        $totalNonAbsen = $hadir + $cuti + $sakit + $izin + $kompensasi;
        $absen = max(0, $workingDays - $totalNonAbsen);
        
        $attendanceRate = $workingDays > 0 ? round(($hadir / $workingDays) * 100) : 0;

        $data[] = [
            'name' => $user->name,
            'jabatan' => $user->jabatan->nama_jabatan ?? 'N/A',
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'cuti' => $cuti,
            'sakit' => $sakit,
            'izin' => $izin,
            'kompensasi' => $kompensasi,
            'absen' => $absen,
            'attendance_rate' => $attendanceRate,
        ];

        $totalSummary['hadir'] += $hadir;
        $totalSummary['terlambat'] += $terlambat;
        $totalSummary['cuti'] += $cuti;
        $totalSummary['sakit'] += $sakit;
        $totalSummary['izin'] += $izin;
        $totalSummary['kompensasi'] += $kompensasi;
        $totalSummary['absen'] += $absen;
    }

    return view('exports.attendance-report-print', [
        'reportData' => $data,
        'summary' => $totalSummary,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'periode' => \Carbon\Carbon::create($tahun, $bulan, 1)->format('F Y')
    ]);
})->name('hrd.attendance-report.print')->middleware(['auth', 'role:hrd']);


Route::middleware([HandleRedirects::class])->group(function () {
    
    // Catch-all untuk semua URL lainnya (termasuk WordPress)
    Route::get('{path}', function ($path) {
        abort(404); // Jika middleware tidak redirect
    })->where('path', '.*'); // Accept any path
    
});


