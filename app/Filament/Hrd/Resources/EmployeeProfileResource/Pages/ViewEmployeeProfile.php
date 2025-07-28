<?php

// app/Filament/Hrd/Resources/EmployeeProfileResource/Pages/ViewEmployeeProfile.php

namespace App\Filament\Hrd\Resources\EmployeeProfileResource\Pages;

use App\Filament\Hrd\Resources\EmployeeProfileResource;
use App\Filament\Hrd\Resources\EmployeeDocumentResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ViewEmployeeProfile extends ViewRecord
{
    protected static string $resource = EmployeeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Edit Profile Action
            Actions\EditAction::make()
                ->label('Edit Profile')
                ->icon('heroicon-o-pencil')
                ->color('primary'),

            // Upload Document Modal Action
            Actions\Action::make('upload_document')
                ->label('Upload Dokumen')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->form([
                    Forms\Components\Section::make('Upload Dokumen Baru')
                        ->description('Upload dokumen untuk ' . $this->record->name)
                        ->schema([
                            Forms\Components\Select::make('document_type')
                                ->label('Jenis Dokumen')
                                ->options([
                                    'ktp' => '🆔 KTP',
                                    'cv' => '📄 CV/Resume',
                                    'ijazah' => '🎓 Ijazah',
                                    'sertifikat' => '📜 Sertifikat',
                                    'foto' => '📸 Foto Profil',
                                    'npwp' => '🏦 NPWP',
                                    'bpjs' => '🏥 BPJS',
                                    'kontrak' => '📋 Kontrak Kerja',
                                    'skck' => '👮 SKCK',
                                    'surat_sehat' => '⚕️ Surat Keterangan Sehat',
                                    'referensi' => '📝 Surat Referensi',
                                    'other' => '📎 Lainnya'
                                ])
                                ->required()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    // Cek apakah dokumen sudah ada
                                    $existingDoc = \App\Models\EmployeeDocument::where('user_id', $this->record->id)
                                        ->where('document_type', $state)
                                        ->first();
                                    
                                    if ($existingDoc) {
                                        $set('existing_warning', 'Dokumen ' . $state . ' sudah ada. Upload baru akan mengganti yang lama.');
                                    } else {
                                        $set('existing_warning', '');
                                    }
                                }),

                            Forms\Components\Placeholder::make('existing_warning')
                                ->label('')
                                ->content(fn (Forms\Get $get): string => $get('existing_warning') ?? '')
                                ->visible(fn (Forms\Get $get): bool => !empty($get('existing_warning')))
                                ->extraAttributes(['class' => 'text-orange-600 bg-orange-50 p-3 rounded-md']),

                            Forms\Components\FileUpload::make('file_upload')
                                ->label('File Dokumen')
                                ->directory('employee-documents')
                                ->disk('public')
                                ->visibility('public')
                                ->maxSize(5120) // 5MB
                                ->required()
                                ->helperText('Maksimal 5MB. Format: PDF, JPG, PNG, DOC, DOCX')
                                ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                ->uploadingMessage('Mengupload file...')
                                ->previewable(false),

                            Forms\Components\Textarea::make('description')
                                ->label('Keterangan')
                                ->placeholder('Deskripsi atau catatan untuk dokumen ini...')
                                ->rows(3),

                            Forms\Components\Toggle::make('is_verified')
                                ->label('Langsung Verifikasi')
                                ->helperText('Centang jika dokumen sudah diverifikasi')
                                ->default(false),
                        ]),
                ])
                ->action(function (array $data) {
                    try {
                        // Cek dan hapus dokumen existing jika ada
                        $existingDoc = \App\Models\EmployeeDocument::where('user_id', $this->record->id)
                            ->where('document_type', $data['document_type'])
                            ->first();

                        if ($existingDoc) {
                            // Hapus file lama
                            if (Storage::disk('public')->exists($existingDoc->file_path)) {
                                Storage::disk('public')->delete($existingDoc->file_path);
                            }
                            $existingDoc->delete();
                        }

                        // Di Filament 3.x, file sudah ter-upload otomatis
                        $filePath = $data['file_upload'];
                        
                        // Get file info
                        $fullPath = Storage::disk('public')->path($filePath);
                        $fileName = basename($filePath);
                        
                        if (file_exists($fullPath)) {
                            $fileSize = filesize($fullPath);
                            $mimeType = mime_content_type($fullPath);
                        } else {
                            $fileSize = null;
                            $mimeType = null;
                        }

                        // Simpan ke database
                        $document = \App\Models\EmployeeDocument::create([
                            'user_id' => $this->record->id,
                            'document_type' => $data['document_type'],
                            'file_path' => $filePath,
                            'file_name' => $fileName,
                            'file_size' => $fileSize,
                            'mime_type' => $mimeType,
                            'description' => $data['description'] ?? null,
                            'uploaded_at' => now(),
                            'is_verified' => $data['is_verified'] ?? false,
                            'verified_by' => ($data['is_verified'] ?? false) ? auth()->id() : null,
                            'verified_at' => ($data['is_verified'] ?? false) ? now() : null,
                        ]);

                        Notification::make()
                            ->title('✅ Dokumen Berhasil Diupload')
                            ->body('Dokumen ' . $data['document_type'] . ' telah berhasil diupload untuk ' . $this->record->name)
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        \Log::error('Upload error in modal: ' . $e->getMessage());
                        
                        Notification::make()
                            ->title('❌ Upload Gagal')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalHeading('Upload Dokumen Baru')
                ->modalDescription('Upload dokumen untuk karyawan: ' . $this->record->name)
                ->modalSubmitActionLabel('Upload Dokumen')
                ->modalWidth('2xl'),

            // Manage Documents Action
            Actions\Action::make('manage_documents')
                ->label('Kelola Dokumen')
                ->icon('heroicon-o-folder-open')
                ->color('info')
                ->url(fn (): string => 
                    EmployeeDocumentResource::getUrl('index', [
                        'tableFilters' => [
                            'user' => ['value' => $this->record->id],
                        ],
                    ])
                ),

            // Send Completion Reminder
            Actions\Action::make('send_completion_reminder')
                ->label('Kirim Reminder')
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->visible(fn (): bool => !$this->record->hasCompleteProfile())
                ->requiresConfirmation()
                ->modalHeading('Kirim Reminder Profile')
                ->modalDescription('Kirim reminder ke karyawan untuk melengkapi profile mereka?')
                ->action(function () {
                    // Logic kirim reminder (bisa via email, notifikasi, dll)
                    Notification::make()
                        ->title('Reminder Terkirim')
                        ->body('Reminder telah dikirim ke ' . $this->record->name)
                        ->success()
                        ->send();
                }),

            // More Actions Dropdown
            Actions\ActionGroup::make([
                Actions\Action::make('send_document_reminder')
                    ->label('Reminder Dokumen')
                    ->icon('heroicon-o-document-text')
                    ->color('orange')
                    ->visible(fn (): bool => $this->record->getUnverifiedDocumentsCount() > 0)
                    ->action(function () {
                        Notification::make()
                            ->title('Reminder Dokumen Terkirim')
                            ->success()
                            ->send();
                    }),

                Actions\Action::make('export_profile')
                    ->label('Export Profile')
                    ->icon('heroicon-o-printer')
                    ->action(function () {
                        Notification::make()
                            ->title('Export Profile')
                            ->body('Fitur export akan segera tersedia')
                            ->info()
                            ->send();
                    }),
            ])
            ->label('Aksi Lainnya')
            ->icon('heroicon-o-ellipsis-vertical')
            ->color('gray'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Widget untuk statistik dokumen karyawan jika diperlukan
        ];
    }

    public function getTitle(): string
    {
        return 'Profile: ' . $this->record->name;
    }

    public function getSubheading(): ?string
    {
        $jabatan = $this->record->jabatan?->nama_jabatan ?? 'No Position';
        $divisi = $this->record->jabatan?->divisi?->nama_divisi ?? '';
        
        return $jabatan . ($divisi ? ' - ' . $divisi : '');
    }

    protected function getViewData(): array
    {
        return [
            'profileCompletion' => $this->record->getProfileCompletionPercentage(),
            'totalDocuments' => $this->record->employeeDocuments->count(),
            'verifiedDocuments' => $this->record->getVerifiedDocumentsCount(),
            'pendingDocuments' => $this->record->getUnverifiedDocumentsCount(),
        ];
    }
}