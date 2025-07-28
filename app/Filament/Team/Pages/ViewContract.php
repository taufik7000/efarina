<?php

namespace App\Filament\Team\Pages;

use App\Models\EmployeeDocument;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ViewContract extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    
    // Kelompokkan di bawah menu "Profile Saya"
    protected static ?string $navigationGroup = 'Profile Saya';
    
    protected static ?string $navigationLabel = 'Kontrak Kerja';
    
    protected static ?string $title = 'Kontrak Kerja';

    protected static string $view = 'filament.team.pages.view-contract';

    public ?EmployeeDocument $contractDocument;

    public function mount(): void
    {
        // Cari dokumen dengan jenis 'kontrak_kerja' milik user yang login
        $this->contractDocument = EmployeeDocument::where('user_id', Auth::id())
            ->where('document_type', 'kontrak_kerja')
            ->first();
    }
}