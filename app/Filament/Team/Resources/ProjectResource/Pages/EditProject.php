<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            
            Actions\DeleteAction::make()
                ->visible(fn () => 
                    auth()->user()->hasRole(['admin']) || 
                    ($this->record->created_by === auth()->id() && $this->record->status === 'draft')
                ),

            // Action untuk Redaksi: Approve Project
            Actions\Action::make('approve')
                ->label('Setujui Project')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => 
                    auth()->user()->hasRole(['redaksi', 'admin']) && 
                    $this->record->status === 'draft'
                )
                ->requiresConfirmation()
                ->modalHeading('Setujui Project')
                ->modalDescription('Project akan disetujui dan bisa dimulai.')
                ->action(function () {
                    $this->record->update(['status' => 'planning']);
                    
                    Notification::make()
                        ->title('Project Disetujui')
                        ->body("Project '{$this->record->nama_project}' telah disetujui.")
                        ->success()
                        ->send();
                }),

            // Action untuk Redaksi: Reject Project  
            Actions\Action::make('reject')
                ->label('Tolak Project')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => 
                    auth()->user()->hasRole(['redaksi', 'admin']) && 
                    $this->record->status === 'draft'
                )
                ->form([
                    \Filament\Forms\Components\Textarea::make('catatan')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'cancelled',
                        'catatan' => $data['catatan'],
                    ]);
                    
                    Notification::make()
                        ->title('Project Ditolak')
                        ->body("Project '{$this->record->nama_project}' ditolak.")
                        ->warning()
                        ->send();
                }),

            // Action untuk PM: Start Project
            Actions\Action::make('start_project')
                ->label('Mulai Project')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => 
                    $this->record->project_manager_id === auth()->id() && 
                    $this->record->status === 'planning'
                )
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'in_progress']);
                    
                    Notification::make()
                        ->title('Project Dimulai')
                        ->body("Project '{$this->record->nama_project}' telah dimulai.")
                        ->success()
                        ->send();
                }),

            // Action untuk PM: Complete Project
            Actions\Action::make('complete_project')
                ->label('Selesaikan Project')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => 
                    $this->record->project_manager_id === auth()->id() && 
                    in_array($this->record->status, ['in_progress', 'review'])
                )
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->markAsCompleted();
                    
                    Notification::make()
                        ->title('Project Selesai')
                        ->body("Project '{$this->record->nama_project}' telah selesai.")
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        
        // Admin dan Redaksi bisa edit semua
        if ($user->hasRole(['admin', 'redaksi'])) {
            return;
        }

        // Creator bisa edit project mereka yang masih draft
        if ($this->record->created_by === $user->id && $this->record->status === 'draft') {
            return;
        }

        // Project Manager bisa edit beberapa field project mereka (bukan status)
        if ($this->record->project_manager_id === $user->id) {
            return;
        }

        // Selain itu tidak boleh edit
        abort(403, 'Anda tidak memiliki akses untuk mengedit project ini.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        
        // Jika bukan redaksi/admin, tidak boleh ubah status
        if (!$user->hasRole(['admin', 'redaksi'])) {
            unset($data['status']);
            unset($data['catatan']); // Catatan hanya bisa diubah redaksi
        }

        // Set created_by jika belum ada
        if (!isset($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }

        return $data;
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->success()
            ->title('Project Updated')
            ->body('Project berhasil diupdate.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}