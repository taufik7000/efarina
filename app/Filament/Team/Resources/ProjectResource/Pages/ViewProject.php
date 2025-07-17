<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use App\Models\Project;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.team.pages.view-project';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => 
                    auth()->check() && auth()->user()->hasRole(['redaksi', 'admin']) || 
                    ($this->record->created_by === auth()->id() && $this->record->status === 'draft') ||
                    $this->record->project_manager_id === auth()->id()
                ),
        ];
    }

    // Method untuk actions yang dipanggil dari blade
    public function approveAction()
    {
        $user = auth()->user();
        
        if (!$user || !$user->hasRole(['redaksi', 'admin']) || $this->record->status !== 'draft') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk menyetujui project ini.')
                ->danger()
                ->send();
            return;
        }

        $this->record->update(['status' => 'planning']);
        
        Notification::make()
            ->title('Project Disetujui')
            ->body("Project '{$this->record->nama_project}' telah disetujui.")
            ->success()
            ->send();
    }

    public function rejectAction()
    {
        $user = auth()->user();
        
        if (!$user || !$user->hasRole(['redaksi', 'admin']) || $this->record->status !== 'draft') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk menolak project ini.')
                ->danger()
                ->send();
            return;
        }

        // Redirect ke form untuk input alasan penolakan
        $this->redirect(route('filament.team.resources.projects.reject', ['record' => $this->record]));
    }

    public function startProjectAction()
    {
        $user = auth()->user();

        if (!$user || !$user->hasRole(['redaksi', 'admin']) || $this->record->status !== 'planning') {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Hanya redaksi yang dapat memulai project.')
                ->danger()
                ->send();
            return;
        }

        $this->record->update(['status' => 'in_progress']);

        Notification::make()
            ->title('Project Dimulai')
            ->body("Project '{$this->record->nama_project}' telah dimulai.")
            ->success()
            ->send();
    }

    public function completeProjectAction()
    {
        $user = auth()->user();
        
        if (!$user || $this->record->project_manager_id !== $user->id || 
            !in_array($this->record->status, ['in_progress', 'review'])) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk menyelesaikan project ini.')
                ->danger()
                ->send();
            return;
        }

        // Jika Project model punya method markAsCompleted()
        if (method_exists($this->record, 'markAsCompleted')) {
            $this->record->markAsCompleted();
        } else {
            $this->record->update(['status' => 'completed']);
        }
        
        Notification::make()
            ->title('Project Selesai')
            ->body("Project '{$this->record->nama_project}' telah selesai.")
            ->success()
            ->send();
    }

    // Helper methods untuk blade template
    public function canApprove(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(['redaksi', 'admin']) && $this->record->status === 'draft';
    }

    public function canReject(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(['redaksi', 'admin']) && $this->record->status === 'draft';
    }

    public function canStartProject(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(['redaksi', 'admin']) && $this->record->status === 'planning';
    }

    public function canCompleteProject(): bool
    {
        $user = auth()->user();
        return $user && $this->record->project_manager_id === $user->id && 
               in_array($this->record->status, ['in_progress', 'review']);
    }

    public function canEdit(): bool
    {
        $user = auth()->user();
        return $user && (
            $user->hasRole(['admin', 'redaksi']) ||
            ($this->record->created_by === $user->id && $this->record->status === 'draft') ||
            $this->record->project_manager_id === $user->id
        );
    }
}