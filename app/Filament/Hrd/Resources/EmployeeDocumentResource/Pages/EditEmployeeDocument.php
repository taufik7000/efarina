<?php

namespace App\Filament\Hrd\Resources\EmployeeDocumentResource\Pages;

use App\Filament\Hrd\Resources\EmployeeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class EditEmployeeDocument extends EditRecord
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_file')
                ->label('Lihat File')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn (): string => Storage::url($this->record->getAttribute('file_path')))
                ->openUrlInNewTab(),

            Actions\Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    if (!$this->record->fileExists()) {
                        Notification::make()
                            ->title('File Tidak Ditemukan')
                            ->body('File dokumen tidak ditemukan di server.')
                            ->danger()
                            ->send();
                        return;
                    }

                    return response()->download(
                        Storage::path($this->record->file_path),
                        $this->record->getAttribute('file_name')
                    );
                }),

            Actions\Action::make('replace_file')
                ->label('Ganti File')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Forms\Components\FileUpload::make('new_file')
                        ->label('File Baru')
                        ->directory('employee-documents')
                        ->acceptedFileTypes(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'])
                        ->maxSize(5120)
                        ->required(),

                    Forms\Components\Textarea::make('replacement_reason')
                        ->label('Alasan Penggantian')
                        ->placeholder('Alasan mengganti dokumen...')
                        ->required(),
                ])
                ->action(function (array $data) {
                    // Hapus file lama
                    $this->record->deleteFile();

                    // Upload file baru
                    $newFile = $data['new_file'];
                    $newPath = $newFile->store('employee-documents', 'public');

                    // Update record
                    $this->record->update([
                        'file_path' => $newPath,
                        'file_name' => $newFile->getClientOriginalName(),
                        'file_size' => $newFile->getSize(),
                        'mime_type' => $newFile->getMimeType(),
                        'description' => $data['replacement_reason'],
                        'is_verified' => false, // Reset verification
                        'verified_at' => null,
                        'verified_by' => null,
                        'verification_notes' => null,
                        'uploaded_at' => now(),
                    ]);

                    Notification::make()
                        ->title('File Berhasil Diganti')
                        ->body('Dokumen telah diganti dengan file baru.')
                        ->success()
                        ->send();

                    return redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Actions\Action::make('toggle_verification')
                ->label(fn (): string => $this->record->is_verified ? 'Batalkan Verifikasi' : 'Verifikasi')
                ->icon(fn (): string => $this->record->is_verified ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn (): string => $this->record->is_verified ? 'warning' : 'success')
                ->form(function (): array {
                    if ($this->record->is_verified) {
                        return [];
                    }

                    return [
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi')
                            ->placeholder('Tambahkan catatan verifikasi...')
                            ->required(),
                    ];
                })
                ->action(function (array $data) {
                    if ($this->record->is_verified) {
                        $this->record->unverify();
                        $message = 'Verifikasi dokumen telah dibatalkan.';
                    } else {
                        $this->record->verify(auth()->user(), $data['verification_notes'] ?? null);
                        $message = 'Dokumen telah diverifikasi.';
                    }

                    Notification::make()
                        ->title('Status Verifikasi Diupdate')
                        ->body($message)
                        ->success()
                        ->send();

                    return redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Actions\DeleteAction::make()
                ->before(function () {
                    $this->record->deleteFile();
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Dokumen Berhasil Diperbarui')
            ->body('Data dokumen telah disimpan.')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view_list')
                    ->label('Lihat Daftar')
                    ->url($this->getResource()::getUrl('index')),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Remove file fields yang tidak perlu di edit form
        unset($data['file_upload']);
        
        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Informasi Karyawan')
                ->schema([
                    Forms\Components\TextInput::make('user.name')
                        ->label('Nama Karyawan')
                        ->disabled(),

                    Forms\Components\TextInput::make('user.jabatan.nama_jabatan')
                        ->label('Jabatan')
                        ->disabled(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Detail Dokumen')
                ->schema([
                    Forms\Components\Select::make('document_type')
                        ->label('Jenis Dokumen')
                        ->options(\App\Models\EmployeeDocument::getDocumentTypeOptions())
                        ->disabled(), // Tidak bisa ubah jenis dokumen

                    Forms\Components\TextInput::make('file_name')
                        ->label('Nama File')
                        ->disabled(),

                    Forms\Components\TextInput::make('file_size_formatted')
                        ->label('Ukuran File')
                        ->disabled(),

                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi/Keterangan')
                        ->placeholder('Deskripsi atau catatan untuk dokumen ini...')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(3),

            Forms\Components\Section::make('Status & Verifikasi')
                ->schema([
                    Forms\Components\Toggle::make('is_verified')
                        ->label('Status Verifikasi')
                        ->disabled(),

                    Forms\Components\Textarea::make('verification_notes')
                        ->label('Catatan Verifikasi')
                        ->disabled()
                        ->visible(fn (): bool => !empty($this->record->verification_notes)),

                    Forms\Components\TextInput::make('verifier.name')
                        ->label('Diverifikasi Oleh')
                        ->disabled()
                        ->visible(fn (): bool => $this->record->is_verified),

                    Forms\Components\DateTimePicker::make('verified_at')
                        ->label('Tanggal Verifikasi')
                        ->disabled()
                        ->visible(fn (): bool => $this->record->verified_at),
                ])
                ->columns(2),
        ];
    }
}