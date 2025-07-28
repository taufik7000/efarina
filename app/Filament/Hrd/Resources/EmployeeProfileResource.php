<?php

// app/Filament/Hrd/Resources/EmployeeProfileResource.php

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
                            ->label('NIK (KTP)')
                            ->maxLength(16)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('no_telepon')  // PERBAIKAN: gunakan no_telepon
                            ->label('No. Telepon')
                            ->tel()
                            ->maxLength(15),

                        Forms\Components\DatePicker::make('tanggal_lahir')  // PERBAIKAN: gunakan tanggal_lahir
                            ->label('Tanggal Lahir'),

                        Forms\Components\TextInput::make('tempat_lahir')  // PERBAIKAN: gunakan tempat_lahir
                            ->label('Tempat Lahir')
                            ->maxLength(100),

                        Forms\Components\Select::make('jenis_kelamin')  // PERBAIKAN: gunakan jenis_kelamin
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ]),

                        Forms\Components\Select::make('agama')
                            ->label('Agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Katolik' => 'Katolik',
                                'Protestan' => 'Protestan',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                            ]),

                        Forms\Components\Select::make('status_nikah')  // PERBAIKAN: gunakan status_nikah
                            ->label('Status Pernikahan')
                            ->options([
                                'Belum Menikah' => 'Belum Menikah',
                                'Menikah' => 'Menikah',
                                'Cerai' => 'Cerai',
                                'Janda/Duda' => 'Janda/Duda',
                            ]),

                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kontak Darurat')
                    ->relationship('employeeProfile')
                    ->schema([
                        Forms\Components\TextInput::make('kontak_darurat_nama')
                            ->label('Nama Kontak Darurat')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('kontak_darurat_telp')
                            ->label('No. Telepon Darurat')
                            ->tel()
                            ->maxLength(15),

                        Forms\Components\TextInput::make('kontak_darurat_hubungan')
                            ->label('Hubungan')
                            ->maxLength(100),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jabatan.nama_jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Belum ditentukan'),

                Tables\Columns\TextColumn::make('jabatan.divisi.nama_divisi')
                    ->label('Divisi')
                    ->searchable()
                    ->placeholder('Belum ditentukan'),

                Tables\Columns\TextColumn::make('employeeProfile.no_telepon')
                    ->label('No. Telepon')
                    ->placeholder('Belum diisi'),

                Tables\Columns\BadgeColumn::make('profile_completion')
                    ->label('Kelengkapan Profile')
                    ->formatStateUsing(fn (User $record): string => $record->getProfileCompletionPercentage() . '%')
                    ->color(fn (User $record): string => match (true) {
                        $record->getProfileCompletionPercentage() >= 80 => 'success',
                        $record->getProfileCompletionPercentage() >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\IconColumn::make('has_complete_profile')
                    ->label('Profile')
                    ->boolean()
                    ->state(fn (User $record): bool => $record->hasCompleteProfile())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

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
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail'),
                    
                Tables\Actions\EditAction::make()
                    ->label('Edit Profile'),
            ]);
    }

public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            // SECTION 1: Informasi Dasar dengan Foto
            Components\Section::make('👤 Informasi Dasar')
                ->schema([
                    Components\Split::make([
                        // Left side - Photo
                        Components\ImageEntry::make('photo_url')
                            ->label('')
                            ->state(function (User $record): ?string {
                                $photo = $record->getDocument('foto');
                                return $photo ? Storage::disk('public')->url($photo->file_path) : null;
                            })
                            ->defaultImageUrl(function (User $record): string {
                                return 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . 
                                       '&color=7F9CF5&background=EBF4FF&size=300';
                            })
                            ->circular()
                            ->size(150)
                            ->grow(false),

                        // Right side - Basic Info
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('name')
                                    ->label('Nama Lengkap')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('primary'),

                                Components\TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),

                                Components\TextEntry::make('jabatan.nama_jabatan')
                                    ->label('Jabatan')
                                    ->icon('heroicon-o-briefcase')
                                    ->placeholder('Belum diatur')
                                    ->badge()
                                    ->color('info'),

                                Components\TextEntry::make('jabatan.divisi.nama_divisi')
                                    ->label('Divisi')
                                    ->icon('heroicon-o-building-office')
                                    ->placeholder('Belum diatur')
                                    ->badge()
                                    ->color('success'),

                                Components\TextEntry::make('employment_start_date')
                                    ->label('Tanggal Bergabung')
                                    ->icon('heroicon-o-calendar')
                                    ->date('d F Y')
                                    ->badge()
                                    ->color('warning'),

                                Components\TextEntry::make('work_duration')
                                    ->label('Masa Kerja')
                                    ->state(function (User $record): string {
                                        if (!$record->employment_start_date) return 'Tidak diketahui';
                                        
                                        $start = $record->employment_start_date;
                                        $now = now();
                                        
                                        $years = $start->diffInYears($now);
                                        $months = $start->copy()->addYears($years)->diffInMonths($now);
                                        
                                        if ($years > 0) {
                                            return $years . ' tahun ' . $months . ' bulan';
                                        } else {
                                            return $months . ' bulan';
                                        }
                                    })
                                    ->icon('heroicon-o-clock')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ]),
                ])
                ->columns(1),

            // SECTION 2: Data Personal
            Components\Section::make('📋 Data Personal')
                ->schema([
                    Components\TextEntry::make('employeeProfile.nik_ktp')
                        ->label('NIK (KTP)')
                        ->placeholder('Belum diisi')
                        ->copyable(),

                    Components\TextEntry::make('employeeProfile.no_telepon')
                        ->label('No. Telepon')
                        ->placeholder('Belum diisi')
                        ->copyable(),

                    Components\TextEntry::make('employeeProfile.birth_info')
                        ->label('Tempat, Tanggal Lahir')
                        ->state(function (User $record): string {
                            $profile = $record->employeeProfile;
                            if (!$profile) return 'Belum diisi';
                            
                            $place = $profile->tempat_lahir ?? '';
                            $date = $profile->tanggal_lahir ? $profile->tanggal_lahir->format('d F Y') : '';
                            
                            if ($place && $date) {
                                return $place . ', ' . $date;
                            } elseif ($date) {
                                return $date;
                            } elseif ($place) {
                                return $place;
                            }
                            
                            return 'Belum diisi';
                        })
                        ->placeholder('Belum diisi'),

                    Components\TextEntry::make('employeeProfile.age')
                        ->label('Usia')
                        ->state(function (User $record): string {
                            $profile = $record->employeeProfile;
                            if (!$profile || !$profile->tanggal_lahir) return 'Belum diisi';
                            
                            return $profile->tanggal_lahir->age . ' tahun';
                        })
                        ->placeholder('Belum diisi'),

                    Components\TextEntry::make('employeeProfile.jenis_kelamin')
                        ->label('Jenis Kelamin')
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                            default => 'Belum diisi',
                        })
                        ->badge()
                        ->color(fn (?string $state): string => match ($state) {
                            'L' => 'blue',
                            'P' => 'pink',
                            default => 'gray',
                        }),

                    Components\TextEntry::make('employeeProfile.agama')
                        ->label('Agama')
                        ->placeholder('Belum diisi'),

                    Components\TextEntry::make('employeeProfile.status_nikah')
                        ->label('Status Pernikahan')
                        ->placeholder('Belum diisi')
                        ->badge()
                        ->color('info'),

                    Components\TextEntry::make('employeeProfile.alamat')
                        ->label('Alamat')
                        ->placeholder('Belum diisi')
                        ->columnSpanFull(),
                ])
                ->columns(3),

            // SECTION 3: Kontak Darurat
            Components\Section::make('🚨 Kontak Darurat')
                ->schema([
                    Components\TextEntry::make('employeeProfile.kontak_darurat_nama')
                        ->label('Nama')
                        ->placeholder('Belum diisi'),

                    Components\TextEntry::make('employeeProfile.kontak_darurat_telp')
                        ->label('No. Telepon')
                        ->placeholder('Belum diisi')
                        ->copyable(),

                    Components\TextEntry::make('employeeProfile.kontak_darurat_hubungan')
                        ->label('Hubungan')
                        ->placeholder('Belum diisi')
                        ->badge()
                        ->color('warning'),
                ])
                ->columns(3),

            // SECTION 4: Informasi Keuangan
            Components\Section::make('💰 Informasi Keuangan')
                ->schema([
                    Components\TextEntry::make('employeeProfile.formatted_gaji')
                        ->label('Gaji Pokok')
                        ->placeholder('Belum diatur')
                        ->badge()
                        ->color('success'),

                    Components\TextEntry::make('employeeProfile.masked_rekening')
                        ->label('No. Rekening')
                        ->placeholder('Belum diisi'),

                    Components\TextEntry::make('employeeProfile.masked_npwp')
                        ->label('NPWP')
                        ->placeholder('Belum diisi'),
                ])
                ->columns(3)
                ->collapsible(),

            // SECTION 5: Status Profile & Overview
            Components\Section::make('📊 Status Profile')
                ->schema([
                    Components\Grid::make(4)
                        ->schema([
                            Components\TextEntry::make('profile_completion_percentage')
                                ->label('Kelengkapan Profile')
                                ->state(fn (User $record): string => $record->getProfileCompletionPercentage() . '%')
                                ->badge()
                                ->size('xl')
                                ->color(fn (User $record): string => match (true) {
                                    $record->getProfileCompletionPercentage() >= 80 => 'success',
                                    $record->getProfileCompletionPercentage() >= 50 => 'warning',
                                    default => 'danger',
                                }),

                            Components\TextEntry::make('total_documents')
                                ->label('Total Dokumen')
                                ->state(fn (User $record): string => $record->employeeDocuments->count())
                                ->badge()
                                ->size('xl')
                                ->color('info'),

                            Components\TextEntry::make('verified_documents')
                                ->label('Terverifikasi')
                                ->state(fn (User $record): string => $record->getVerifiedDocumentsCount())
                                ->badge()
                                ->size('xl')
                                ->color('success'),

                            Components\TextEntry::make('pending_documents')
                                ->label('Menunggu Verifikasi')
                                ->state(fn (User $record): string => $record->getUnverifiedDocumentsCount())
                                ->badge()
                                ->size('xl')
                                ->color(fn (User $record): string => 
                                    $record->getUnverifiedDocumentsCount() > 0 ? 'warning' : 'success'
                                ),
                        ]),
                ])
                ->columns(1),

            // SECTION 6: DOKUMEN KARYAWAN (MAIN FEATURE)
            Components\Section::make('📁 Dokumen Karyawan')
                ->description('Kelola dokumen karyawan dalam satu tempat')
                ->headerActions([
                    \Filament\Infolists\Components\Actions\Action::make('upload_document_modal')
                        ->label('Upload Dokumen')
                        ->icon('heroicon-o-plus')
                        ->color('success')
                        ->form([
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
                                    'other' => '📎 Lainnya'
                                ])
                                ->required()
                                ->native(false),

                            Forms\Components\FileUpload::make('file_upload')
                                ->label('File Dokumen')
                                ->directory('employee-documents')
                                ->disk('public')
                                ->maxSize(5120)
                                ->required()
                                ->helperText('Maksimal 5MB'),

                            Forms\Components\Textarea::make('description')
                                ->label('Keterangan')
                                ->rows(2),

                            Forms\Components\Toggle::make('is_verified')
                                ->label('Langsung Verifikasi')
                                ->default(false),
                        ])
                        ->action(function (array $data, User $record) {
                            try {
                                $filePath = $data['file_upload'];
                                $fullPath = Storage::disk('public')->path($filePath);
                                $fileName = basename($filePath);
                                
                                \App\Models\EmployeeDocument::create([
                                    'user_id' => $record->id,
                                    'document_type' => $data['document_type'],
                                    'file_path' => $filePath,
                                    'file_name' => $fileName,
                                    'file_size' => file_exists($fullPath) ? filesize($fullPath) : null,
                                    'mime_type' => file_exists($fullPath) ? mime_content_type($fullPath) : null,
                                    'description' => $data['description'] ?? null,
                                    'uploaded_at' => now(),
                                    'is_verified' => $data['is_verified'] ?? false,
                                    'verified_by' => ($data['is_verified'] ?? false) ? auth()->id() : null,
                                    'verified_at' => ($data['is_verified'] ?? false) ? now() : null,
                                ]);

                                Notification::make()
                                    ->title('Dokumen Berhasil Diupload')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Upload Gagal')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->modalHeading('Upload Dokumen Baru')
                        ->modalWidth('xl'),
                ])
                ->schema([
                    Components\RepeatableEntry::make('employeeDocuments')
                        ->label('')
                        ->schema([
                            Components\Grid::make(7)
                                ->schema([
                                    // Document Type & Icon
                                    Components\TextEntry::make('document_type')
                                        ->label('Jenis')
                                        ->formatStateUsing(function (string $state): string {
                                            $types = [
                                                'ktp' => '🆔 KTP',
                                                'cv' => '📄 CV/Resume',
                                                'ijazah' => '🎓 Ijazah',
                                                'sertifikat' => '📜 Sertifikat',
                                                'foto' => '📸 Foto Profil',
                                                'npwp' => '🏦 NPWP',
                                                'bpjs' => '🏥 BPJS',
                                                'kontrak' => '📋 Kontrak Kerja',
                                                'skck' => '👮 SKCK',
                                                'surat_sehat' => '⚕️ Surat Sehat',
                                                'referensi' => '📝 Surat Referensi',
                                                'other' => '📎 Lainnya'
                                            ];
                                            return $types[$state] ?? ('📄 ' . ucfirst($state));
                                        })
                                        ->badge()
                                        ->color('info'),

                                    // File Name
                                    Components\TextEntry::make('file_name')
                                        ->label('File')
                                        ->limit(25)
                                        ->tooltip(fn ($record): string => $record->file_name ?? ''),

                                    // File Size
                                    Components\TextEntry::make('file_size')
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
                                        ->badge()
                                        ->color('gray'),

                                    // Upload Date
                                    Components\TextEntry::make('uploaded_at')
                                        ->label('Upload')
                                        ->since(),

                                    // Verification Status
                                    Components\TextEntry::make('is_verified')
                                        ->label('Status')
                                        ->formatStateUsing(function (bool $state, $record): string {
                                            if ($state) {
                                                return "✅ Terverifikasi";
                                            }
                                            return "⏳ Pending";
                                        })
                                        ->badge()
                                        ->color(fn (bool $state): string => $state ? 'success' : 'warning'),

                                    // Description
                                    Components\TextEntry::make('description')
                                        ->label('Keterangan')
                                        ->limit(20)
                                        ->placeholder('Tidak ada'),

                                    // Actions menggunakan Filament Actions
                                    Components\Actions::make([
                                        // Preview Action
                                        \Filament\Infolists\Components\Actions\Action::make('preview')
                                            ->label('Preview')
                                            ->icon('heroicon-o-eye')
                                            ->color('info')
                                            ->url(function ($record): ?string {
                                                if (!Storage::disk('public')->exists($record->file_path)) {
                                                    return null;
                                                }
                                                $extension = strtolower(pathinfo($record->file_name, PATHINFO_EXTENSION));
                                                if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
                                                    return Storage::disk('public')->url($record->file_path);
                                                }
                                                return null;
                                            })
                                            ->openUrlInNewTab()
                                            ->visible(function ($record): bool {
                                                if (!Storage::disk('public')->exists($record->file_path)) {
                                                    return false;
                                                }
                                                $extension = strtolower(pathinfo($record->file_name, PATHINFO_EXTENSION));
                                                return in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']);
                                            }),

                                        // Download Action
                                        \Filament\Infolists\Components\Actions\Action::make('download')
                                            ->label('Download')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->color('success')
                                            ->action(function ($record) {
                                                if (Storage::disk('public')->exists($record->file_path)) {
                                                    return Storage::disk('public')->download($record->file_path, $record->file_name);
                                                }
                                                
                                                Notification::make()
                                                    ->title('File tidak ditemukan')
                                                    ->danger()
                                                    ->send();
                                            })
                                            ->visible(fn ($record): bool => Storage::disk('public')->exists($record->file_path)),

                                        // Verify Action
                                        \Filament\Infolists\Components\Actions\Action::make('verify')
                                            ->label('Verifikasi')
                                            ->icon('heroicon-o-check-circle')
                                            ->color('success')
                                            ->requiresConfirmation()
                                            ->modalHeading('Verifikasi Dokumen')
                                            ->modalDescription('Apakah Anda yakin dokumen ini valid dan dapat diverifikasi?')
                                            ->action(function ($record) {
                                                $record->update([
                                                    'is_verified' => true,
                                                    'verified_by' => auth()->id(),
                                                    'verified_at' => now(),
                                                ]);

                                                Notification::make()
                                                    ->title('Dokumen Terverifikasi')
                                                    ->success()
                                                    ->send();
                                            })
                                            ->visible(fn ($record): bool => !$record->is_verified),

                                        // Unverify Action
                                        \Filament\Infolists\Components\Actions\Action::make('unverify')
                                            ->label('Batal')
                                            ->icon('heroicon-o-x-circle')
                                            ->color('warning')
                                            ->requiresConfirmation()
                                            ->modalHeading('Batalkan Verifikasi')
                                            ->modalDescription('Apakah Anda yakin ingin membatalkan verifikasi dokumen ini?')
                                            ->action(function ($record) {
                                                $record->update([
                                                    'is_verified' => false,
                                                    'verified_by' => null,
                                                    'verified_at' => null,
                                                ]);

                                                Notification::make()
                                                    ->title('Verifikasi Dibatalkan')
                                                    ->success()
                                                    ->send();
                                            })
                                            ->visible(fn ($record): bool => $record->is_verified),

                                        // Delete Action
                                        \Filament\Infolists\Components\Actions\Action::make('delete')
                                            ->label('Hapus')
                                            ->icon('heroicon-o-trash')
                                            ->color('danger')
                                            ->requiresConfirmation()
                                            ->modalHeading('Hapus Dokumen')
                                            ->modalDescription('Apakah Anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.')
                                            ->action(function ($record) {
                                                // Hapus file dari storage
                                                if (Storage::disk('public')->exists($record->file_path)) {
                                                    Storage::disk('public')->delete($record->file_path);
                                                }
                                                
                                                // Hapus record dari database
                                                $record->delete();

                                                Notification::make()
                                                    ->title('Dokumen Dihapus')
                                                    ->success()
                                                    ->send();
                                            }),
                                    ]),
                                ]),
                        ])
                        ->contained(false),
                ])
                ->collapsed(false),

            // SECTION 7: Catatan HRD
            Components\Section::make('📝 Catatan HRD')
                ->schema([
                    Components\TextEntry::make('employeeProfile.notes_hrd')
                        ->label('')
                        ->placeholder('Tidak ada catatan khusus')
                        ->markdown()
                        ->columnSpanFull(),
                ])
                ->visible(fn (User $record): bool => 
                    $record->employeeProfile && !empty($record->employeeProfile->notes_hrd)
                )
                ->collapsible(),
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