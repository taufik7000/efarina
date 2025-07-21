<?php

namespace App\Filament\Hrd\Resources\EmployeeDocumentResource\Pages;

use App\Filament\Hrd\Resources\EmployeeDocumentResource;
use App\Models\EmployeeDocument;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmployeeDocuments extends ListRecords
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Upload Dokumen')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Dokumen')
                ->badge(EmployeeDocument::count()),

            'pending' => Tab::make('Menunggu Verifikasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_verified', false))
                ->badge(EmployeeDocument::where('is_verified', false)->count())
                ->badgeColor('warning'),

            'verified' => Tab::make('Terverifikasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_verified', true))
                ->badge(EmployeeDocument::where('is_verified', true)->count())
                ->badgeColor('success'),

            'ktp' => Tab::make('KTP')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('document_type', 'ktp'))
                ->badge(EmployeeDocument::where('document_type', 'ktp')->count()),

            'cv' => Tab::make('CV/Resume')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('document_type', 'cv'))
                ->badge(EmployeeDocument::where('document_type', 'cv')->count()),

            'kontrak' => Tab::make('Kontrak')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('document_type', 'kontrak'))
                ->badge(EmployeeDocument::where('document_type', 'kontrak')->count()),

            'ijazah' => Tab::make('Ijazah')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('document_type', 'ijazah'))
                ->badge(EmployeeDocument::where('document_type', 'ijazah')->count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeDocumentResource\Widgets\DocumentStatsWidget::class,
        ];
    }
}