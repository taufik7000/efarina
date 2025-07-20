<?php

namespace App\Services;

use App\Models\Holiday; // <-- Gunakan model Holiday kita
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class HolidayService
{
    /**
     * Mendapatkan semua hari libur untuk tahun tertentu dari database.
     *
     * @param int $year
     * @return array
     */
    public function getHolidays(int $year): array
    {
        // Buat kunci cache yang unik
        $cacheKey = 'holidays_db_' . $year;

        // Cache data selama 1 hari untuk performa
        return Cache::remember($cacheKey, 86400, function () use ($year) {
            return Holiday::whereYear('date', $year)
                ->pluck('name', 'date') // Ambil kolom 'name' sebagai value dan 'date' sebagai key
                ->all();
        });
    }

    /**
     * Mengecek apakah sebuah tanggal adalah hari libur.
     * (Tidak perlu diubah)
     */
    public function isHoliday(Carbon $date): bool
    {
        $holidays = $this->getHolidays($date->year);
        return isset($holidays[$date->format('Y-m-d')]);
    }

    /**
     * Mendapatkan nama hari libur pada tanggal tertentu.
     * (Tidak perlu diubah)
     */
    public function getHolidayName(Carbon $date): ?string
    {
        $holidays = $this->getHolidays($date->year);
        return $holidays[$date->format('Y-m-d')] ?? null;
    }
}