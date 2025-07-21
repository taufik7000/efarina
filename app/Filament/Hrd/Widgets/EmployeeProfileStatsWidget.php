<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\User;
use App\Models\EmployeeDocument;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeProfileStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalEmployees = User::count();
        $completeProfiles = User::withCompleteProfile()->count();
        $incompleteProfiles = User::withIncompleteProfile()->count();
        $unverifiedDocs = EmployeeDocument::unverified()->count();

        $completionRate = $totalEmployees > 0 ? round(($completeProfiles / $totalEmployees) * 100) : 0;

        return [
            Stat::make('Total Karyawan', $totalEmployees)
                ->description('Total karyawan aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Profile Lengkap', $completeProfiles)
                ->description("{$completionRate}% dari total karyawan")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Sample chart data

            Stat::make('Profile Belum Lengkap', $incompleteProfiles)
                ->description('Perlu tindak lanjut')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Dokumen Belum Diverifikasi', $unverifiedDocs)
                ->description('Menunggu verifikasi HRD')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}