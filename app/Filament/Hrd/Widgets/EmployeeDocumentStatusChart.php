<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\EmployeeDocument;
use Filament\Widgets\ChartWidget;

class EmployeeDocumentStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Dokumen Karyawan';
    
    protected function getData(): array
    {
        $documentTypes = EmployeeDocument::DOCUMENT_TYPES;
        $verifiedCounts = [];
        $unverifiedCounts = [];

        foreach ($documentTypes as $type => $label) {
            $verifiedCounts[] = EmployeeDocument::where('document_type', $type)
                ->verified()
                ->count();
            
            $unverifiedCounts[] = EmployeeDocument::where('document_type', $type)
                ->unverified()
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Terverifikasi',
                    'data' => $verifiedCounts,
                    'backgroundColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Belum Diverifikasi',
                    'data' => $unverifiedCounts,
                    'backgroundColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => array_values($documentTypes),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}