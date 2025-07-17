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
                    auth()->user()->hasRole(['redaksi', 'admin']) || 
                    ($this->record->created_by === auth()->id() && $this->record->status === 'draft') ||
                    $this->record->project_manager_id === auth()->id()
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
                    Forms\Components\Textarea::make('catatan')
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
                ->modalHeading('Mulai Project')
                ->modalDescription('Project akan diubah status menjadi in progress.')
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
                ->modalHeading('Selesaikan Project')
                ->modalDescription('Project akan ditandai sebagai selesai.')
                ->action(function () {
                    $this->record->markAsCompleted();
                    
                    Notification::make()
                        ->title('Project Selesai')
                        ->body("Project '{$this->record->nama_project}' telah selesai.")
                        ->success()
                        ->send();
                }),

            // Action khusus untuk Redaksi: Change Status
            Actions\Action::make('change_status')
                ->label('Ubah Status')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => auth()->user()->hasRole(['redaksi', 'admin']))
                ->form([
                    Forms\Components\Select::make('status')
                        ->label('Status Baru')
                        ->options([
                            'draft' => 'Draft',
                            'planning' => 'Planning', 
                            'in_progress' => 'In Progress',
                            'review' => 'Review',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default($this->record->status)
                        ->required(),
                    Forms\Components\Textarea::make('catatan')
                        ->label('Catatan')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => $data['status'],
                        'catatan' => $data['catatan'] ?? $this->record->catatan,
                    ]);
                    
                    Notification::make()
                        ->title('Status Diubah')
                        ->body("Status project diubah menjadi {$data['status']}.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Project')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama_project')
                            ->label('Nama Project')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                            
                        Infolists\Components\TextEntry::make('deskripsi')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                            
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($record) => $record->status_color),
                            
                        Infolists\Components\TextEntry::make('prioritas')
                            ->label('Prioritas')
                            ->badge()
                            ->color(fn ($record) => $record->prioritas_color),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Tim & Management')
                    ->schema([
                        Infolists\Components\TextEntry::make('projectManager.name')
                            ->label('Project Manager'),
                            
                        Infolists\Components\TextEntry::make('createdBy.name')
                            ->label('Dibuat Oleh'),
                            
                        Infolists\Components\TextEntry::make('team_members')
                            ->label('Tim')
                            ->formatStateUsing(fn ($record) => $record->getTeamMemberNames() ?: 'Belum ada tim'),
                            
                        Infolists\Components\TextEntry::make('progress_percentage')
                            ->label('Progress')
                            ->suffix('%'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Timeline')
                    ->schema([
                        Infolists\Components\TextEntry::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->date(),
                            
                        Infolists\Components\TextEntry::make('tanggal_deadline')
                            ->label('Deadline')
                            ->date(),
                            
                        Infolists\Components\TextEntry::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->date()
                            ->placeholder('Belum selesai'),
                            
                        Infolists\Components\TextEntry::make('days_remaining')
                            ->label('Sisa Hari')
                            ->suffix(' hari')
                            ->color(fn ($record) => $record->days_remaining < 7 ? 'danger' : 'primary'),
                    ])
                    ->columns(2),
            ]);
    }
}