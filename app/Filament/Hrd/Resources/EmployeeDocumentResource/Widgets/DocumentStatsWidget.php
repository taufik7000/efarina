<?php

namespace App\Filament\Hrd\Resources\EmployeeDocumentResource\Widgets;

use App\Models\EmployeeDocument;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DocumentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDocuments = EmployeeDocument::count();
        $verifiedDocuments = EmployeeDocument::where('is_verified', true)->count();
        $pendingDocuments = EmployeeDocument::where('is_verified', false)->count();
        $totalEmployees = User::count();
        $employeesWithDocs = User::whereHas('employeeDocuments')->count();
        
        $verificationRate = $totalDocuments > 0 ? round(($verifiedDocuments / $totalDocuments) * 100, 1) : 0;
        $employeeCoverage = $totalEmployees > 0 ? round(($employeesWithDocs / $totalEmployees) * 100, 1) : 0;

        return [
            Stat::make('Total Dokumen', $totalDocuments)
                ->description('Semua dokumen karyawan')
                ->descriptionIcon('heroicon-o-document-duplicate')
                ->color('primary')
                ->chart([7, 12, 8, 15, 9, 18, $totalDocuments]),

            Stat::make('Terverifikasi', $verifiedDocuments)
                ->description("Rate: {$verificationRate}%")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart([3, 8, 5, 12, 7, 15, $verifiedDocuments]),

            Stat::make('Menunggu Verifikasi', $pendingDocuments)
                ->description($pendingDocuments > 0 ? 'Perlu ditindaklanjuti' : 'Semua terverifikasi')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingDocuments > 0 ? 'warning' : 'success')
                ->chart([4, 3, 6, 2, 8, 5, $pendingDocuments]),

            Stat::make('Coverage Karyawan', $employeesWithDocs)
                ->description("Dari {$totalEmployees} karyawan ({$employeeCoverage}%)")
                ->descriptionIcon('heroicon-o-users')
                ->color($employeeCoverage >= 80 ? 'success' : ($employeeCoverage >= 50 ? 'warning' : 'danger'))
                ->chart([2, 5, 8, 12, 15, 18, $employeesWithDocs]),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }

        public function getDisplayName(): string
        {
            return 'Statistik Dokumen Karyawan';
        }
    }