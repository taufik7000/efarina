<?php

// 1. Namespace harus sesuai dengan lokasi file
namespace App\Filament\Team\Pages; 

use Filament\Pages\Page;

class AbsenQr extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationGroup = 'Absensi';

    protected static ?string $title = 'Pindai QR Absensi';

    // 2. Properti $view harus mencerminkan path folder:
    //    filament -> team -> pages -> absen-qr
    protected static string $view = 'filament.team.pages.absen-qr';
    
    protected static ?int $navigationSort = 1;
}