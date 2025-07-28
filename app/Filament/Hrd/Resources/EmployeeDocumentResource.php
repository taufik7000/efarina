<?php

// app/Filament/Hrd/Resources/EmployeeDocumentResource.php
// UNTUK FILAMENT 3.x

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\EmployeeDocumentResource\Pages;
use App\Models\EmployeeDocument;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentResource extends Resource
{
    protected static ?string $model = EmployeeDocument::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Manajemen Organisasi';
    protected static ?string $navigationLabel = 'Dokumen Karyawan';
    protected static ?string $pluralModelLabel = 'Dokumen Karyawan';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Karyawan')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pilih Karyawan')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required(),
                            ]),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Detail Dokumen')
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->label('Jenis Dokumen')
                            ->options([
                                'ktp' => 'KTP',
                                'cv' => 'CV/Resume',
                                'ijazah' => 'Ijazah',
                                'sertifikat' => 'Sertifikat',
                                'foto' => 'Foto Profil',
                                'npwp' => 'NPWP',
                                'bpjs' => 'BPJS',
                                'kontrak' => 'Kontrak Kerja',
                                'other' => 'Lainnya'
                            ])
                            ->required()
                            ->native(false),

                        // FILAMENT 3.x FileUpload - Simple Version
                        Forms\Components\FileUpload::make('file_upload')
                            ->label('Upload Dokumen')
                            ->directory('employee-documents')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(5120) // 5MB
                            ->required()
                            ->helperText('Maksimal 5MB. Format: PDF, JPG, PNG, DOC, DOCX')
                            ->columnSpanFull()
                            // Filament 3.x way to accept file types
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->uploadingMessage('Mengupload file...')
                            ->previewable(false), // Disable preview to avoid issues

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->placeholder('Deskripsi atau catatan untuk dokumen ini...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Status Verifikasi')
                    ->schema([
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Sudah Diverifikasi')
                            ->helperText('Centang jika dokumen sudah diverifikasi')
                            ->default(false),

                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi')
                            ->placeholder('Catatan untuk verifikasi dokumen...')
                            ->rows(2)
                            ->visible(fn (Forms\Get $get): bool => $get('is_verified')),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.jabatan.nama_jabatan')
                    ->label('Jabatan')
                    ->placeholder('Tidak ada')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Jenis')
                    ->formatStateUsing(function (string $state): string {
                        $types = [
                            'ktp' => 'KTP',
                            'cv' => 'CV/Resume',
                            'ijazah' => 'Ijazah',
                            'sertifikat' => 'Sertifikat',
                            'foto' => 'Foto Profil',
                            'npwp' => 'NPWP',
                            'bpjs' => 'BPJS',
                            'kontrak' => 'Kontrak Kerja',
                            'other' => 'Lainnya'
                        ];
                        return $types[$state] ?? ucfirst($state);
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('File')
                    ->limit(30)
                    ->searchable()
                    ->tooltip(fn (EmployeeDocument $record): string => $record->file_name),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(function (?int $state): string {
                        if (!$state) return 'Unknown';
                        
                        if ($state >= 1048576) {
                            return number_format($state / 1048576, 2) . ' MB';
                        } elseif ($state >= 1024) {
                            return number_format($state / 1024, 2) . ' KB';
                        }
                        return $state . ' bytes';
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('uploaded_at')
                    ->label('Upload')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('Belum diverifikasi')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter Karyawan'),

                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options([
                        'ktp' => 'KTP',
                        'cv' => 'CV/Resume',
                        'ijazah' => 'Ijazah',
                        'sertifikat' => 'Sertifikat',
                        'foto' => 'Foto Profil',
                        'npwp' => 'NPWP',
                        'bpjs' => 'BPJS',
                        'kontrak' => 'Kontrak Kerja',
                        'other' => 'Lainnya'
                    ]),

                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Status Verifikasi')
                    ->placeholder('Semua')
                    ->trueLabel('Terverifikasi')
                    ->falseLabel('Belum Diverifikasi'),

                Tables\Filters\Filter::make('uploaded_today')
                    ->label('Upload Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('uploaded_at', today())),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (EmployeeDocument $record): string => Storage::disk('public')->url($record->file_path))
                    ->openUrlInNewTab()
                    ->visible(function (EmployeeDocument $record): bool {
                        $extension = strtolower(pathinfo($record->file_name, PATHINFO_EXTENSION));
                        return in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']);
                    }),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (EmployeeDocument $record) {
                        if (Storage::disk('public')->exists($record->file_path)) {
                            return Storage::disk('public')->download($record->file_path, $record->file_name);
                        }
                        
                        Notification::make()
                            ->title('File Tidak Ditemukan')
                            ->body('File dokumen tidak dapat ditemukan di server.')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->visible(fn (EmployeeDocument $record): bool => !$record->is_verified)
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Dokumen')
                    ->modalDescription('Apakah Anda yakin dokumen ini valid dan dapat diverifikasi?')
                    ->action(function (EmployeeDocument $record) {
                        $record->update([
                            'is_verified' => true,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Dokumen Terverifikasi')
                            ->body('Dokumen telah berhasil diverifikasi.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('unverify')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (EmployeeDocument $record): bool => $record->is_verified)
                    ->requiresConfirmation()
                    ->action(function (EmployeeDocument $record) {
                        $record->update([
                            'is_verified' => false,
                            'verified_by' => null,
                            'verified_at' => null,
                        ]);

                        Notification::make()
                            ->title('Verifikasi Dibatalkan')
                            ->body('Verifikasi dokumen telah dibatalkan.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->before(function (EmployeeDocument $record) {
                        // Hapus file dari storage
                        if (Storage::disk('public')->exists($record->file_path)) {
                            Storage::disk('public')->delete($record->file_path);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('verify_selected')
                        ->label('Verifikasi Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (!$record->is_verified) {
                                    $record->update([
                                        'is_verified' => true,
                                        'verified_by' => auth()->id(),
                                        'verified_at' => now(),
                                    ]);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title('Dokumen Berhasil Diverifikasi')
                                ->body($count . ' dokumen telah diverifikasi.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Hapus semua file dari storage
                            foreach ($records as $record) {
                                if (Storage::disk('public')->exists($record->file_path)) {
                                    Storage::disk('public')->delete($record->file_path);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->poll('30s'); // Auto refresh every 30 seconds
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeDocuments::route('/'),
            'create' => Pages\CreateEmployeeDocument::route('/create'),
            'edit' => Pages\EditEmployeeDocument::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_verified', false)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_verified', false)->count() > 0 ? 'warning' : 'success';
    }
}