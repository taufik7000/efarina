<?php

namespace App\Filament\Hrd\Resources\EmployeeProfileResource\Pages;

use App\Filament\Hrd\Resources\EmployeeProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmployeeProfiles extends ListRecords
{
    protected static string $resource = EmployeeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_all')
                ->label('Export Semua Profile')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Logic export ke Excel
                    $this->notify('success', 'Export profile berhasil!');
                }),

            Actions\Action::make('bulk_reminder')
                ->label('Kirim Reminder Profile')
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->action(function () {
                    // Logic kirim reminder ke karyawan yang profile belum lengkap
                    $this->notify('info', 'Reminder telah dikirim!');
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn () => $this->getModel()::count()),

            'complete' => Tab::make('Profile Lengkap')
                ->modifyQueryUsing(fn (Builder $query) => $query->withCompleteProfile())
                ->badge(fn () => $this->getModel()::withCompleteProfile()->count())
                ->badgeColor('success'),

            'incomplete' => Tab::make('Profile Belum Lengkap')
                ->modifyQueryUsing(fn (Builder $query) => $query->withIncompleteProfile())
                ->badge(fn () => $this->getModel()::withIncompleteProfile()->count())
                ->badgeColor('warning'),

            'unverified_docs' => Tab::make('Dokumen Belum Diverifikasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->withUnverifiedDocuments())
                ->badge(fn () => $this->getModel()::withUnverifiedDocuments()->count())
                ->badgeColor('danger'),
        ];
    }
}