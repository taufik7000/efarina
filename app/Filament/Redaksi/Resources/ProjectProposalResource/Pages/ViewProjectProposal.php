<?php

// File: app/Filament/Redaksi/Resources/ProjectProposalResource/Pages/ViewProjectProposal.php

namespace App\Filament\Redaksi\Resources\ProjectProposalResource\Pages;

use App\Filament\Redaksi\Resources\ProjectProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewProjectProposal extends ViewRecord
{
    protected static string $resource = ProjectProposalResource::class;
    
    protected static string $view = 'filament.redaksi.pages.view-project-proposal';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Setujui Proposal')
                ->modalDescription('Proposal akan disetujui dan project otomatis dibuat.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Catatan Approval (Optional)')
                        ->rows(3)
                        ->placeholder('Tulis catatan untuk pengaju proposal...'),
                ])
                ->action(function ($record, array $data) {
                    $record->approve(auth()->id(), $data['notes'] ?? null);
                    
                    Notification::make()
                        ->title('Proposal Disetujui')
                        ->body("Proposal '{$record->judul_proposal}' berhasil disetujui dan project telah dibuat.")
                        ->success()
                        ->duration(8000)
                        ->send();
                        
                    return redirect()->route('filament.redaksi.resources.project-proposals.index');
                }),

            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Tolak Proposal')
                ->modalDescription('Berikan alasan penolakan yang jelas untuk membantu pengaju memperbaiki proposal.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->rows(4)
                        ->placeholder('Jelaskan alasan penolakan dan saran perbaikan...'),
                ])
                ->action(function ($record, array $data) {
                    $record->reject(auth()->id(), $data['notes']);
                    
                    Notification::make()
                        ->title('Proposal Ditolak')
                        ->body("Proposal '{$record->judul_proposal}' telah ditolak dan feedback dikirim ke pengaju.")
                        ->success()
                        ->duration(6000)
                        ->send();
                        
                    return redirect()->route('filament.redaksi.resources.project-proposals.index');
                }),

            Actions\EditAction::make()
                ->label('Edit Review')
                ->visible(fn ($record) => $record->status === 'pending'),

            Actions\Action::make('create_project')
                ->label('Buat Project Manual')
                ->icon('heroicon-o-rocket-launch')
                ->color('primary')
                ->visible(fn ($record) => $record->isApproved() && !$record->hasProject())
                ->form([
                    \Filament\Forms\Components\Select::make('project_manager_id')
                        ->label('Project Manager')
                        ->options(\App\Models\User::all()->pluck('name', 'id'))
                        ->required(),
                        
                    \Filament\Forms\Components\DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->default(now()),
                        
                    \Filament\Forms\Components\DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Target Selesai')
                        ->required()
                        ->after('tanggal_mulai'),
                ])
                ->action(function ($record, array $data) {
                    $project = $record->createProject([
                        'project_manager_id' => $data['project_manager_id'],
                        'tanggal_mulai' => $data['tanggal_mulai'],
                        'tanggal_selesai' => $data['tanggal_selesai'],
                    ]);
                    
                    Notification::make()
                        ->title('Project Berhasil Dibuat!')
                        ->body("Project '{$project->nama_project}' telah dibuat dan siap dikerjakan.")
                        ->success()
                        ->duration(8000)
                        ->send();
                }),
        ];
    }
}