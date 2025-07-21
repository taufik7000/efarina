<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\EmployeeProfileResource\Pages;
use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\EmployeeDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class EmployeeProfileResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Manajemen Organisasi';
    protected static ?string $navigationLabel = 'Profile Karyawan';
    protected static ?string $pluralModelLabel = 'Profile Karyawan';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('jabatan_id')
                            ->relationship('jabatan', 'nama_jabatan')
                            ->searchable()
                            ->preload()
                            ->label('Jabatan')
                            ->required(),

                        Forms\Components\DatePicker::make('employment_start_date')
                            ->label('Tanggal Bergabung')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Data Personal')
                    ->relationship('employeeProfile')
                    ->schema([
                        Forms\Components\TextInput::make('nik_ktp')
                            ->label('NIK KTP')
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->placeholder('1234567890123456'),

                        Forms\Components\TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir'),

                        Forms\Components\TextInput::make('no_telepon')
                            ->label('No. Telepon')
                            ->tel()
                            ->maxLength(15),

                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ]),

                        Forms\Components\Select::make('agama')
                            ->label('Agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Kristen' => 'Kristen',
                                'Katolik' => 'Katolik',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                            ]),

                        Forms\Components\Select::make('status_nikah')
                            ->label('Status Pernikahan')
                            ->options([
                                'belum_menikah' => 'Belum Menikah',
                                'menikah' => 'Menikah',
                                'cerai' => 'Cerai',
                            ]),

                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('no_telepon')
                            ->label('No. Telepon')
                            ->tel()
                            ->maxLength(15),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Informasi Keuangan')
                    ->relationship('employeeProfile')
                    ->schema([
                        Forms\Components\TextInput::make('gaji_pokok')
                            ->label('Gaji Pokok')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('5000000'),

                        Forms\Components\TextInput::make('no_rekening')
                            ->label('No. Rekening')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(20)
                            ->placeholder('12.345.678.9-012.345'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Catatan HRD')
                    ->relationship('employeeProfile')
                    ->schema([
                        Forms\Components\Textarea::make('notes_hrd')
                            ->label('Catatan Khusus')
                            ->placeholder('Catatan khusus dari HRD...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('Foto')
                    ->circular()
                    ->state(function (User $record): ?string {
                        $photo = $record->getDocument('foto');
                        return $photo ? Storage::url($photo->file_path) : null;
                    })
                    ->defaultImageUrl(function (): string {
                        return 'https://ui-avatars.com/api/?name=N+A&color=7F9CF5&background=EBF4FF';
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jabatan.nama_jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->placeholder('Belum diatur'),

                Tables\Columns\TextColumn::make('jabatan.divisi.nama_divisi')
                    ->label('Divisi')
                    ->searchable()
                    ->placeholder('Belum diatur'),

                Tables\Columns\TextColumn::make('profile_completion')
                    ->label('Kelengkapan Profile')
                    ->state(fn (User $record): string => $record->getProfileCompletionPercentage() . '%')
                    ->badge()
                    ->color(fn (User $record): string => match (true) {
                        $record->getProfileCompletionPercentage() >= 80 => 'success',
                        $record->getProfileCompletionPercentage() >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\IconColumn::make('has_complete_profile')
                    ->label('Profile Lengkap')
                    ->boolean()
                    ->state(fn (User $record): bool => $record->hasCompleteProfile())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('documents_status')
                    ->label('Dokumen')
                    ->state(function (User $record): string {
                        $verified = $record->getVerifiedDocumentsCount();
                        $total = $record->employeeDocuments->count();
                        return "{$verified}/{$total}";
                    })
                    ->badge()
                    ->color(function (User $record): string {
                        $verified = $record->getVerifiedDocumentsCount();
                        $total = $record->employeeDocuments->count();
                        if ($total === 0) return 'gray';
                        return $verified === $total ? 'success' : 'warning';
                    }),

                Tables\Columns\TextColumn::make('employment_start_date')
                    ->label('Bergabung')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jabatan')
                    ->relationship('jabatan', 'nama_jabatan')
                    ->searchable()
                    ->preload()
                    ->label('Filter Jabatan'),

                Tables\Filters\SelectFilter::make('divisi')
                    ->relationship('jabatan.divisi', 'nama_divisi')
                    ->searchable()
                    ->preload()
                    ->label('Filter Divisi'),

                Tables\Filters\Filter::make('profile_complete')
                    ->label('Profile Lengkap')
                    ->query(fn (Builder $query): Builder => $query->withCompleteProfile()),

                Tables\Filters\Filter::make('profile_incomplete')
                    ->label('Profile Belum Lengkap')
                    ->query(fn (Builder $query): Builder => $query->withIncompleteProfile()),

                Tables\Filters\Filter::make('has_unverified_docs')
                    ->label('Ada Dokumen Belum Diverifikasi')
                    ->query(fn (Builder $query): Builder => $query->withUnverifiedDocuments()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail'),
                    
                Tables\Actions\EditAction::make()
                    ->label('Edit Profile'),

                Tables\Actions\Action::make('manage_documents')
                    ->label('Kelola Dokumen')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->url(fn (User $record): string => 
                        EmployeeDocumentResource::getUrl('index', [
                            'tableFilters' => [
                                'user' => [
                                    'value' => $record->id,
                                ],
                            ],
                        ])
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\Action::make('export_profiles')
                        ->label('Export Profile')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            // Logic export ke Excel/PDF
                            Notification::make()
                                ->title('Export Berhasil')
                                ->body('Profile karyawan telah diekspor.')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi Dasar')
                    ->schema([
                        Components\ImageEntry::make('photo_url')
                            ->label('Foto Profile')
                            ->state(function (User $record): ?string {
                                $photo = $record->getDocument('foto');
                                return $photo ? Storage::url($photo->file_path) : null;
                            })
                            ->defaultImageUrl(function (User $record): string {
                                return 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF';
                            })
                            ->circular()
                            ->size(120),

                        Components\TextEntry::make('name')
                            ->label('Nama Lengkap'),

                        Components\TextEntry::make('email')
                            ->label('Email'),

                        Components\TextEntry::make('jabatan.nama_jabatan')
                            ->label('Jabatan')
                            ->placeholder('Belum diatur'),

                        Components\TextEntry::make('jabatan.divisi.nama_divisi')
                            ->label('Divisi')
                            ->placeholder('Belum diatur'),

                        Components\TextEntry::make('employment_start_date')
                            ->label('Tanggal Bergabung')
                            ->date('d F Y'),
                    ])
                    ->columns(3),

                Components\Section::make('Data Personal')
                    ->schema([
                        Components\TextEntry::make('employeeProfile.nik_ktp')
                            ->label('NIK KTP')
                            ->placeholder('Belum diisi'),

                        Components\TextEntry::make('employeeProfile.birth_place_full')
                            ->label('Tempat, Tanggal Lahir')
                            ->placeholder('Belum diisi'),

                        Components\TextEntry::make('employeeProfile.age')
                            ->label('Usia')
                            ->suffix(' tahun')
                            ->placeholder('-'),

                        Components\TextEntry::make('employeeProfile.jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->formatStateUsing(fn (?string $state): string => match($state) {
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                                default => 'Belum diisi'
                            })
                            ->placeholder('Belum diisi'),

                        Components\TextEntry::make('employeeProfile.agama')
                            ->label('Agama')
                            ->placeholder('Belum diisi'),

                        Components\TextEntry::make('employeeProfile.status_nikah')
                            ->label('Status Pernikahan')
                            ->formatStateUsing(fn (?string $state): string => match($state) {
                                'belum_menikah' => 'Belum Menikah',
                                'menikah' => 'Menikah',
                                'cerai' => 'Cerai',
                                default => 'Belum diisi'
                            })
                            ->placeholder('Belum diisi'),

                        Components\TextEntry::make('employeeProfile.alamat')
                            ->label('Alamat')
                            ->placeholder('Belum diisi')
                            ->columnSpanFull(),

                        Components\TextEntry::make('employeeProfile.no_telepon')
                            ->label('No. Telepon')
                            ->placeholder('Belum diisi'),

                        Components\TextEntry::make('emergency_contact_info')
                            ->label('Kontak Darurat')
                            ->state(function (User $record): string {
                                $profile = $record->employeeProfile;
                                if (!$profile || !$profile->kontak_darurat_nama) {
                                    return 'Belum diisi';
                                }
                                
                                $contact = $profile->kontak_darurat_nama;
                                if ($profile->kontak_darurat_telp) {
                                    $contact .= ' (' . $profile->kontak_darurat_telp . ')';
                                }
                                if ($profile->kontak_darurat_hubungan) {
                                    $contact .= ' - ' . $profile->kontak_darurat_hubungan;
                                }
                                
                                return $contact;
                            })
                            ->placeholder('Belum diisi')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Components\Section::make('Informasi Keuangan')
                    ->schema([
                        Components\TextEntry::make('employeeProfile.formatted_gaji')
                            ->label('Gaji Pokok')
                            ->placeholder('Belum diatur'),

                        Components\TextEntry::make('bank_account_info')
                            ->label('Rekening Bank')
                            ->state(function (User $record): string {
                                $profile = $record->employeeProfile;
                                if (!$profile || !$profile->no_rekening) {
                                    return 'Belum diisi';
                                }
                                
                                return $profile->masked_rekening;
                            })
                            ->placeholder('Belum diisi'),

                        Components\TextEntry::make('employeeProfile.masked_npwp')
                            ->label('NPWP')
                            ->placeholder('Belum diisi'),
                    ])
                    ->columns(3),

                Components\Section::make('Status Profile & Dokumen')
                    ->schema([
                        Components\TextEntry::make('profile_completion')
                            ->label('Kelengkapan Profile')
                            ->state(fn (User $record): string => $record->getProfileCompletionPercentage() . '%')
                            ->badge()
                            ->color(fn (User $record): string => match (true) {
                                $record->getProfileCompletionPercentage() >= 80 => 'success',
                                $record->getProfileCompletionPercentage() >= 50 => 'warning',
                                default => 'danger',
                            }),

                        Components\TextEntry::make('documents_summary')
                            ->label('Status Dokumen')
                            ->state(function (User $record): string {
                                $verified = $record->getVerifiedDocumentsCount();
                                $pending = $record->getUnverifiedDocumentsCount();
                                $total = $verified + $pending;
                                return "Total: {$total} | Terverifikasi: {$verified} | Pending: {$pending}";
                            }),
                    ])
                    ->columns(2),

                Components\Section::make('Catatan HRD')
                    ->schema([
                        Components\TextEntry::make('employeeProfile.notes_hrd')
                            ->label('Catatan')
                            ->placeholder('Tidak ada catatan khusus')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (User $record): bool => $record->employeeProfile?->notes_hrd !== null),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeProfiles::route('/'),
            'create' => Pages\CreateEmployeeProfile::route('/create'),
            'view' => Pages\ViewEmployeeProfile::route('/{record}'),
            'edit' => Pages\EditEmployeeProfile::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Profile dibuat otomatis saat create user
    }
}