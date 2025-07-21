<?php

namespace App\Filament\Hrd\Resources\EmployeeProfileResource\Pages;

use App\Filament\Hrd\Resources\EmployeeProfileResource;
use App\Models\User;
use App\Models\EmployeeDocument;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\View\View;

class ManageEmployeeDocuments extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static string $resource = EmployeeProfileResource::class;
    protected static string $view = 'filament.hrd.pages.manage-employee-documents';
    
    public User $record;

    public function mount(int|string $record): void
    {
        $this->record = User::findOrFail($record);
    }

    public function getTitle(): string
    {
        return "Kelola Dokumen - {$this->record->name}";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(EmployeeDocument::query()->where('user_id', $this->record->id))
            ->columns([
                Tables\Columns\TextColumn::make('document_type_name')
                    ->label('Jenis Dokumen')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nama File')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (EmployeeDocument $record): string => $record->file_name),

                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label('Ukuran File')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('file_type_icon')
                    ->label('Type')
                    ->icon(fn (EmployeeDocument $record): string => $record->file_type_icon)
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('uploaded_time_ago')
                    ->label('Diupload')
                    ->alignCenter(),

                Tables\Columns\BadgeColumn::make('is_verified')
                    ->label('Status')
                    ->formatStateUsing(fn (EmployeeDocument $record): string => $record->status_badge['label'])
                    ->color(fn (EmployeeDocument $record): string => $record->status_badge['color'])
                    ->icon(fn (EmployeeDocument $record): string => $record->status_badge['icon']),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('Belum diverifikasi')
                    ->limit(20),
            ])
            ->headerActions([
                Tables\Actions\Action::make('upload_document')
                    ->label('Upload Dokumen Baru')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('document_type')
                            ->label('Jenis Dokumen')
                            ->options(EmployeeDocument::DOCUMENT_TYPES)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Auto-set description berdasarkan type
                                $descriptions = [
                                    'ktp' => 'Scan KTP yang jelas dan tidak blur',
                                    'cv' => 'CV/Resume terbaru dalam format PDF',
                                    'kontrak' => 'Kontrak kerja yang telah ditandatangani',
                                    'ijazah' => 'Scan ijazah pendidikan terakhir',
                                    'sertifikat' => 'Sertifikat keahlian atau pelatihan',
                                    'foto' => 'Foto formal 4x6 dengan background putih',
                                ];
                                $set('description', $descriptions[$state] ?? '');
                            }),

                        Forms\Components\FileUpload::make('file_upload')
                            ->label('File Dokumen')
                            ->directory('employee-documents')
                            ->acceptedFileTypes(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'])
                            ->maxSize(5120) // 5MB
                            ->required()
                            ->helperText('Format: PDF, JPG, PNG, DOC, DOCX. Maksimal 5MB.')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    // Auto-set file name dari uploaded file
                                    $fileName = $state->getClientOriginalName();
                                    $set('file_name_display', $fileName);
                                }
                            }),

                        Forms\Components\TextInput::make('file_name_display')
                            ->label('Nama File')
                            ->disabled()
                            ->placeholder('Akan otomatis terisi setelah upload'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi/Keterangan')
                            ->placeholder('Deskripsi dokumen ini...')
                            ->rows(2),
                    ])
                    ->action(function (array $data) {
                        // Cek apakah dokumen dengan type yang sama sudah ada
                        $existingDoc = EmployeeDocument::where('user_id', $this->record->id)
                            ->where('document_type', $data['document_type'])
                            ->first();

                        if ($existingDoc) {
                            // Hapus file lama
                            $existingDoc->deleteFile();
                            $existingDoc->delete();
                        }

                        // Upload file baru
                        $uploadedFile = $data['file_upload'];
                        $filePath = $uploadedFile->store('employee-documents', 'public');

                        // Simpan ke database
                        EmployeeDocument::create([
                            'user_id' => $this->record->id,
                            'document_type' => $data['document_type'],
                            'file_path' => $filePath,
                            'file_name' => $uploadedFile->getClientOriginalName(),
                            'file_size' => $uploadedFile->getSize(),
                            'mime_type' => $uploadedFile->getMimeType(),
                            'description' => $data['description'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Dokumen Berhasil Diupload')
                            ->body("Dokumen {$data['document_type']} untuk {$this->record->name} telah diupload.")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (EmployeeDocument $record): string => Storage::url($record->file_path))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (EmployeeDocument $record) {
                        if (!$record->fileExists()) {
                            Notification::make()
                                ->title('File Tidak Ditemukan')
                                ->body('File dokumen tidak ditemukan di server.')
                                ->danger()
                                ->send();
                            return;
                        }

                        return response()->download(
                            Storage::path($record->file_path),
                            $record->file_name
                        );
                    }),

                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EmployeeDocument $record): bool => !$record->is_verified)
                    ->requiresConfirmation()
                    ->modalHeading(fn (EmployeeDocument $record): string => "Verifikasi Dokumen {$record->document_type_name}")
                    ->modalDescription('Apakah Anda yakin dokumen ini sudah sesuai dan dapat diverifikasi?')
                    ->action(function (EmployeeDocument $record) {
                        $record->verify(auth()->user());

                        Notification::make()
                            ->title('Dokumen Diverifikasi')
                            ->body("Dokumen {$record->document_type_name} telah diverifikasi.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('unverify')
                    ->label('Batal Verifikasi')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (EmployeeDocument $record): bool => $record->is_verified)
                    ->requiresConfirmation()
                    ->action(function (EmployeeDocument $record) {
                        $record->unverify();

                        Notification::make()
                            ->title('Verifikasi Dibatalkan')
                            ->body("Verifikasi dokumen {$record->document_type_name} telah dibatalkan.")
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\Action::make('replace')
                    ->label('Ganti Dokumen')
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
                    ->action(function (EmployeeDocument $record, array $data) {
                        // Hapus file lama
                        $record->deleteFile();

                        // Upload file baru
                        $newFile = $data['new_file'];
                        $newPath = $newFile->store('employee-documents', 'public');

                        // Update record
                        $record->update([
                            'file_path' => $newPath,
                            'file_name' => $newFile->getClientOriginalName(),
                            'file_size' => $newFile->getSize(),
                            'mime_type' => $newFile->getMimeType(),
                            'description' => $data['replacement_reason'],
                            'is_verified' => false, // Reset verification
                            'verified_at' => null,
                            'verified_by' => null,
                            'uploaded_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Dokumen Berhasil Diganti')
                            ->body("Dokumen {$record->document_type_name} telah diganti dengan file baru.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->before(function (EmployeeDocument $record) {
                        $record->deleteFile(); // Hapus file sebelum hapus record
                    }),
            ])
            ->emptyStateHeading('Belum Ada Dokumen')
            ->emptyStateDescription('Upload dokumen pertama untuk karyawan ini.')
            ->emptyStateIcon('heroicon-o-document-plus');
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
            'completionPercentage' => $this->record->getProfileCompletionPercentage(),
            'verifiedCount' => $this->record->getVerifiedDocumentsCount(),
            'totalCount' => $this->record->employeeDocuments->count(),
        ];
    }
}