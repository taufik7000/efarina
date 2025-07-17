<?php

// File: app/Filament/Redaksi/Resources/ProjectProposalResource/Pages/EditProjectProposal.php

namespace App\Filament\Redaksi\Resources\ProjectProposalResource\Pages;

use App\Filament\Redaksi\Resources\ProjectProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProjectProposal extends EditRecord
{
    protected static string $resource = ProjectProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Quick Approve Action
            Actions\Action::make('quick_approve')
                ->label('Setujui Proposal')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->size('lg')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Setujui Proposal')
                ->modalDescription(fn ($record) => "Apakah Anda yakin ingin menyetujui proposal '{$record->judul_proposal}'? Project akan otomatis dibuat setelah approval.")
                ->modalSubmitActionLabel('Ya, Setujui')
                ->modalIcon('heroicon-o-check-circle')
                ->form([
                    \Filament\Forms\Components\Textarea::make('approval_notes')
                        ->label('Catatan Approval (Opsional)')
                        ->placeholder('Tambahkan catatan untuk pengaju proposal...')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->action(function ($record, array $data) {
                    // Approve proposal
                    $record->approve(auth()->id(), $data['approval_notes'] ?? 'Proposal disetujui dari halaman edit.');
                    
                    // Success notification
                    Notification::make()
                        ->title('✅ Proposal Berhasil Disetujui!')
                        ->body("Proposal '{$record->judul_proposal}' telah disetujui dan project sedang dibuat. Notifikasi telah dikirim ke pengaju.")
                        ->success()
                        ->duration(8000)
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view_projects')
                                ->label('Lihat Projects')
                                ->url(route('filament.redaksi.resources.projects.index'))
                        ])
                        ->send();
                        
                    // Redirect to proposals list
                    return redirect()->route('filament.redaksi.resources.project-proposals.index');
                }),

            // Reject Action with Modal
            Actions\Action::make('reject_proposal')
                ->label('Tolak Proposal')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->size('lg')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Tolak Proposal')
                ->modalDescription(fn ($record) => "Berikan alasan penolakan yang jelas untuk proposal '{$record->judul_proposal}' agar pengaju dapat memperbaiki proposalnya.")
                ->modalSubmitActionLabel('Tolak Proposal')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->form([
                    \Filament\Forms\Components\Section::make('Alasan Penolakan')
                        ->description('Jelaskan secara detail mengapa proposal ini ditolak dan berikan saran perbaikan.')
                        ->schema([
                            \Filament\Forms\Components\Select::make('rejection_category')
                                ->label('Kategori Penolakan')
                                ->options([
                                    'scope' => '📋 Scope tidak jelas',
                                    'budget' => '💰 Estimasi budget tidak realistis',
                                    'timeline' => '⏰ Timeline tidak feasible',
                                    'priority' => '🎯 Prioritas tidak sesuai',
                                    'resources' => '👥 Resources tidak tersedia',
                                    'strategy' => '🎲 Tidak sesuai strategi perusahaan',
                                    'duplicate' => '🔄 Project serupa sudah ada',
                                    'incomplete' => '📝 Informasi tidak lengkap',
                                    'other' => '❓ Alasan lainnya',
                                ])
                                ->required()
                                ->native(false),
                                
                            \Filament\Forms\Components\Textarea::make('rejection_reason')
                                ->label('Alasan Detail')
                                ->placeholder('Jelaskan secara spesifik mengapa proposal ditolak dan apa yang perlu diperbaiki...')
                                ->required()
                                ->rows(4)
                                ->minLength(20)
                                ->maxLength(1000)
                                ->columnSpanFull(),
                                
                            \Filament\Forms\Components\Textarea::make('improvement_suggestions')
                                ->label('Saran Perbaikan')
                                ->placeholder('Berikan saran konstruktif untuk memperbaiki proposal ini...')
                                ->rows(3)
                                ->maxLength(500)
                                ->columnSpanFull(),
                                
                            \Filament\Forms\Components\Checkbox::make('allow_resubmission')
                                ->label('Izinkan pengajuan ulang')
                                ->helperText('Centang jika pengaju diperbolehkan mengajukan proposal serupa setelah perbaikan')
                                ->default(true),
                        ])
                        ->columns(1),
                ])
                ->action(function ($record, array $data) {
                    // Format rejection notes
                    $rejectionNotes = "**Kategori:** " . $data['rejection_category'] . "\n\n";
                    $rejectionNotes .= "**Alasan Penolakan:**\n" . $data['rejection_reason'] . "\n\n";
                    
                    if (!empty($data['improvement_suggestions'])) {
                        $rejectionNotes .= "**Saran Perbaikan:**\n" . $data['improvement_suggestions'] . "\n\n";
                    }
                    
                    $rejectionNotes .= "**Resubmission:** " . ($data['allow_resubmission'] ? "Diizinkan" : "Tidak diizinkan") . "\n";
                    $rejectionNotes .= "**Reviewer:** " . auth()->user()->name . "\n";
                    $rejectionNotes .= "**Tanggal:** " . now()->format('d F Y, H:i');
                    
                    // Reject proposal
                    $record->reject(auth()->id(), $rejectionNotes);
                    
                    // Success notification
                    Notification::make()
                        ->title('❌ Proposal Ditolak')
                        ->body("Proposal '{$record->judul_proposal}' telah ditolak. Feedback detail telah dikirim ke pengaju untuk perbaikan.")
                        ->warning()
                        ->duration(6000)
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('view_proposals')
                                ->label('Lihat Proposals')
                                ->url(route('filament.redaksi.resources.project-proposals.index'))
                        ])
                        ->send();
                        
                    // Redirect to proposals list
                    return redirect()->route('filament.redaksi.resources.project-proposals.index');
                }),

            // View Action
            Actions\ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-o-eye'),

            // Delete Action (for pending only)
            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Hapus Proposal')
                ->modalDescription('Proposal yang dihapus tidak dapat dipulihkan. Pastikan Anda yakin dengan keputusan ini.')
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->title('Proposal Updated')
            ->body('Perubahan pada proposal telah disimpan.')
            ->success();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Add timestamp for last edit
        $data['updated_at'] = now();
        
        return $data;
    }
}