<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use Carbon\Carbon;
use Exception; // <-- Pastikan class Exception di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KioskController extends Controller
{
    /**
     * Menampilkan halaman Kiosk utama.
     */
    public function tampilkanKiosk()
    {
        return view('kiosk.tampilan');
    }

    /**
     * Membuat QR code dinamis yang unik dan berbatas waktu.
     */
    public function buatQrCode()
    {
        try {
            $now = Carbon::now('Asia/Jakarta');
            $token = Str::random(40);
            $expiredAt = $now->copy()->addSeconds(20);

            Cache::put('qrcode_token', $token, $expiredAt);
            $qrCodeImage = QrCode::format('svg')->size(300)->generate($token);

            return response($qrCodeImage)->header('Content-Type', 'image/svg+xml');
        } catch (Exception $e) {
            Log::error('Gagal membuat QR code', ['error' => $e->getMessage()]);
            return response('Error', 500);
        }
    }

    /**
     * Memvalidasi semua data, melakukan geofencing, dan menyimpan catatan absensi.
     */
    public function validasiAbsensi(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'token'          => 'required|string',
                'foto'           => 'nullable|string',
                'lokasi'         => 'required|string',
                'info_perangkat' => 'required|string',
            ]);

            $tokenDariPonsel = $validatedData['token'];
            $tokenValidDiServer = Cache::get('qrcode_token');
            $now = Carbon::now('Asia/Jakarta');

            if (!$tokenValidDiServer || $tokenDariPonsel !== $tokenValidDiServer) {
                return response()->json(['pesan' => 'QR Code tidak valid atau kedaluwarsa.'], 422);
            }

            $pengguna = Auth::user();
            if (!$pengguna) {
                return response()->json(['pesan' => 'Sesi tidak ditemukan.'], 401);
            }
            Cache::forget('qrcode_token');
            
            // --- VALIDASI LOKASI (GEOFENCING) ---
            $koordinatKantor = [2.976016, 99.079039]; // <-- Menggunakan koordinat Anda
            list($latPengguna, $lonPengguna) = explode(',', $validatedData['lokasi']);
            
            $jarak = $this->hitungJarak($koordinatKantor[0], $koordinatKantor[1], $latPengguna, $lonPengguna);
            $jarakMaksimalMeter = 500; // Toleransi jarak 500 meter, bisa disesuaikan

            if ($jarak > $jarakMaksimalMeter) {
                return response()->json(['pesan' => 'Anda berada di luar area kantor yang diizinkan.'], 403);
            }
            // --- AKHIR VALIDASI LOKASI ---

            $fotoPath = null;
            if (!empty($validatedData['foto'])) {
                try {
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $validatedData['foto']));
                    $imageName = 'absensi/' . $pengguna->id . '_' . now()->timestamp . '.jpg';
                    Storage::disk('public')->put($imageName, $imageData);
                    $fotoPath = $imageName;
                } catch (Exception $e) {
                    Log::error('Gagal menyimpan foto absensi', ['error' => $e->getMessage()]);
                }
            }

            $hariIni = $now->toDateString();
            $kehadiranHariIni = Kehadiran::where('user_id', $pengguna->id)
                                         ->whereDate('tanggal', $hariIni)
                                         ->first();

            if (!$kehadiranHariIni) {
                Kehadiran::create([
                    'user_id'              => $pengguna->id,
                    'tanggal'              => $hariIni,
                    'jam_masuk'            => $now->toTimeString(),
                    'foto_masuk'           => $fotoPath,
                    'lokasi_masuk'         => $validatedData['lokasi'],
                    'info_perangkat_masuk' => $validatedData['info_perangkat'],
                    'status'               => $this->tentukanStatusKehadiran($now),
                    'metode_absen'         => 'qrcode',
                ]);
                return response()->json(['status' => 'success', 'pesan' => 'Absen Masuk Berhasil!']);
            }

            if (is_null($kehadiranHariIni->jam_pulang)) {
                $kehadiranHariIni->update([
                    'jam_pulang'            => $now->toTimeString(),
                    'foto_pulang'           => $fotoPath,
                    'lokasi_pulang'         => $validatedData['lokasi'],
                    'info_perangkat_pulang' => $validatedData['info_perangkat'],
                ]);
                return response()->json(['status' => 'success', 'pesan' => 'Absen Pulang Berhasil!']);
            }

            return response()->json(['pesan' => 'Anda sudah melakukan absen masuk dan pulang hari ini.'], 422);

        } catch (Exception $e) {
            Log::error('Kesalahan Fatal di validasiAbsensi', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['pesan' => 'Terjadi kesalahan pada server. Silakan hubungi administrator.'], 500);
        }
    }

    private function tentukanStatusKehadiran(Carbon $jamMasuk): string
    {
        $jamKerja = Carbon::createFromTime(8, 15, 0, 'Asia/Jakarta');
        return $jamMasuk->lte($jamKerja) ? 'Tepat Waktu' : 'Terlambat';
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    public function cekStatusAbsensi()
{
    try {
        $pengguna = Auth::user();
        if (!$pengguna) {
            return response()->json(['pesan' => 'Sesi tidak ditemukan.'], 401);
        }

        $hariIni = Carbon::now('Asia/Jakarta')->toDateString();
        $kehadiranHariIni = Kehadiran::where('user_id', $pengguna->id)
                                     ->whereDate('tanggal', $hariIni)
                                     ->first();

        $data = [
            'jam_masuk' => null,
            'jam_pulang' => null,
            'sudah_masuk' => false,
            'sudah_pulang' => false,
            'status' => null,
        ];

        if ($kehadiranHariIni) {
            $data['jam_masuk'] = $kehadiranHariIni->jam_masuk;
            $data['jam_pulang'] = $kehadiranHariIni->jam_pulang;
            $data['sudah_masuk'] = !is_null($kehadiranHariIni->jam_masuk);
            $data['sudah_pulang'] = !is_null($kehadiranHariIni->jam_pulang);
            $data['status'] = $kehadiranHariIni->status;
        }

        return response()->json($data);
    } catch (Exception $e) {
        Log::error('Error cek status absensi', ['error' => $e->getMessage()]);
        return response()->json(['pesan' => 'Terjadi kesalahan.'], 500);
    }
}
    
}