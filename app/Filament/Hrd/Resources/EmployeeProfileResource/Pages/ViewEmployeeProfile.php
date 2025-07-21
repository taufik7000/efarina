<?php

namespace App\Filament\Hrd\Resources\EmployeeProfileResource\Pages;

use App\Filament\Hrd\Resources\EmployeeProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeProfile extends ViewRecord
{
    protected static string $resource = EmployeeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Profile'),

            Actions\Action::make('manage_documents')
                ->label('Kelola Dokumen')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->url(fn () => static::getResource()::getUrl('documents', ['record' => $this->getRecord()])),

            Actions\Action::make('print_profile')
                ->label('Print Profile')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(function () {
                    // Logic untuk print/PDF
                    $this->notify('info', 'Fitur print akan segera tersedia');
                }),

            Actions\Action::make('send_completion_reminder')
                ->label('Kirim Reminder')
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->visible(fn () => !$this->getRecord()->hasCompleteProfile())
                ->action(function () {
                    // Logic kirim email/notif reminder
                    $this->notify('success', 'Reminder telah dikirim ke karyawan');
                }),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Bisa tambahkan widget untuk statistik
        ];
    }
}