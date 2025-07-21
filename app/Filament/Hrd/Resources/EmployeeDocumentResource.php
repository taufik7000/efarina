<?php

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
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => 
                                "{$record->name} - {$record->jabatan?->nama_jabatan}"
                            )
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('document_type', null)),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Detail Dokumen')
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->label('Jenis Dokumen')
                            ->options(EmployeeDocument::getDocumentTypeOptions())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $userId = $get('user_id');
                                if ($userId && $state) {
                                    // Cek apakah dokumen sudah ada
                                    $existingDoc = EmployeeDocument::where('user_id', $userId)
                                        ->where('document_type', $state)
                                        ->first();
                                    
                                    if ($existingDoc) {
                                        Notification::make()
                                            ->title('Dokumen Sudah Ada')
                                            ->body("Karyawan sudah memiliki dokumen {$state}. Upload baru akan mengganti yang lama.")
                                            ->warning()
                                            ->send();
                                    }
                                }
                            }),

                        Forms\Components\FileUpload::make('file_upload')
                            ->label('Upload Dokumen')
                            ->directory('employee-documents')
                            ->acceptedFileTypes(['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'])
                            ->maxSize(5120) // 5MB
                            ->required()
                            ->helperText('Format: PDF, JPG, PNG, DOC, DOCX. Maksimal 5MB.')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi/Keterangan')
                            ->placeholder('Deskripsi atau catatan untuk dokumen ini...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Verifikasi')
                    ->schema([
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Langsung Verifikasi')
                            ->helperText('Centang jika dokumen sudah diverifikasi saat upload')
                            ->live(),

                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi')
                            ->placeholder('Catatan untuk verifikasi dokumen...')
                            ->visible(fn (Forms\Get $get): bool => $get('is_verified'))
                            ->rows(2),
                    ])
                    ->columns(1)
                    ->visible(fn (?EmployeeDocument $record): bool => $record === null), // Hanya tampil saat create
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.photo_url')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn (EmployeeDocument $record): string => 
                        'https://ui-avatars.com/api/?name=' . urlencode($record->user->name) . '&color=7F9CF5&background=EBF4FF'
                    )
                    ->size(40),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.jabatan.nama_jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->placeholder('Belum diatur'),

                Tables\Columns\TextColumn::make('document_type_name')
                    ->label('Jenis Dokumen')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nama File')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn (EmployeeDocument $record): string => $record->file_name),

                Tables\Columns\IconColumn::make('file_type_icon')
                    ->label('Type')
                    ->icon(fn (EmployeeDocument $record): string => $record->file_type_icon)
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label('Ukuran')
                    ->alignCenter(),

                Tables\Columns\BadgeColumn::make('is_verified')
                    ->label('Status')
                    ->formatStateUsing(fn (EmployeeDocument $record): string => $record->status_badge['label'])
                    ->color(fn (EmployeeDocument $record): string => $record->status_badge['color'])
                    ->icon(fn (EmployeeDocument $record): string => $record->status_badge['icon']),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('Belum diverifikasi')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('uploaded_time_ago')
                    ->label('Diupload')
                    ->alignCenter()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('uploaded_at', $direction);
                    }),

                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Tgl Verifikasi')
                    ->dateTime('d M Y')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter Karyawan'),

                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options(EmployeeDocument::getDocumentTypeOptions()),

                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Status Verifikasi')
                    ->trueLabel('Terverifikasi')
                    ->falseLabel('Belum Verifikasi')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_verified', true),
                        false: fn (Builder $query) => $query->where('is_verified', false),
                    ),

                Tables\Filters\SelectFilter::make('jabatan')
                    ->relationship('user.jabatan', 'nama_jabatan')
                    ->searchable()
                    ->preload()
                    ->label('Filter Jabatan'),

                Tables\Filters\SelectFilter::make('divisi')
                    ->relationship('user.jabatan.divisi', 'nama_divisi')
                    ->searchable()
                    ->preload()
                    ->label('Filter Divisi'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view')
                        ->label('Lihat File')
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
                        ->form([
                            Forms\Components\Textarea::make('verification_notes')
                                ->label('Catatan Verifikasi')
                                ->placeholder('Tambahkan catatan verifikasi...')
                                ->required(),
                        ])
                        ->action(function (EmployeeDocument $record, array $data) {
                            $record->verify(auth()->user(), $data['verification_notes']);

                            Notification::make()
                                ->title('Dokumen Diverifikasi')
                                ->body("Dokumen {$record->document_type_name} untuk {$record->user->name} telah diverifikasi.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('unverify')
                        ->label('Batalkan Verifikasi')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn (EmployeeDocument $record): bool => $record->is_verified)
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Verifikasi')
                        ->modalDescription(fn (EmployeeDocument $record): string => 
                            "Apakah Anda yakin ingin membatalkan verifikasi dokumen {$record->document_type_name}?"
                        )
                        ->action(function (EmployeeDocument $record) {
                            $record->unverify();

                            Notification::make()
                                ->title('Verifikasi Dibatalkan')
                                ->body("Verifikasi dokumen {$record->document_type_name} telah dibatalkan.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\EditAction::make()
                        ->label('Edit'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->before(function (EmployeeDocument $record) {
                            $record->deleteFile();
                        }),
                ])
                ->icon('heroicon-o-ellipsis-vertical')
                ->button()
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\Action::make('verify_selected')
                        ->label('Verifikasi Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Textarea::make('verification_notes')
                                ->label('Catatan Verifikasi')
                                ->placeholder('Catatan untuk semua dokumen yang dipilih...')
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (!$record->is_verified) {
                                    $record->verify(auth()->user(), $data['verification_notes']);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title('Verifikasi Berhasil')
                                ->body("{$count} dokumen telah diverifikasi.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                $record->deleteFile();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Dokumen')
            ->emptyStateDescription('Upload dokumen pertama untuk karyawan.')
            ->emptyStateIcon('heroicon-o-document-plus');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeDocuments::route('/'),
            'create' => Pages\CreateEmployeeDocument::route('/create'),
            'edit' => Pages\EditEmployeeDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user.jabatan.divisi', 'verifier']);
    }

    public static function getNavigationBadge(): ?string
    {
        $unverifiedCount = static::getModel()::where('is_verified', false)->count();
        return $unverifiedCount > 0 ? (string) $unverifiedCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}