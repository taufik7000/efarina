<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class EmployeeProfileCompletionChart extends ChartWidget
{
    protected static ?string $heading = 'Kelengkapan Profile Karyawan';
    
    protected function getData(): array
    {
        $ranges = [
            '0-25%' => User::whereHas('employeeProfile', function($q) {
                // Logic untuk range 0-25%
            })->count(),
            '26-50%' => 0, // Logic similar
            '51-75%' => 0,
            '76-100%' => User::withCompleteProfile()->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Karyawan',
                    'data' => array_values($ranges),
                    'backgroundColor' => [
                        'rgb(239, 68, 68)', // Red
                        'rgb(245, 158, 11)', // Yellow
                        'rgb(59, 130, 246)', // Blue
                        'rgb(34, 197, 94)', // Green
                    ],
                ],
            ],
            'labels' => array_keys($ranges),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
