<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\JobVacancyResource\Pages;
use App\Models\JobVacancy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class JobVacancyResource extends Resource
{
    protected static ?string $model = JobVacancy::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Rekrutmen';

    protected static ?string $modelLabel = 'Lowongan Pekerjaan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Posisi')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(JobVacancy::class, 'slug', ignoreRecord: true),
                        
                        Forms\Components\Select::make('job_type')
                            ->label('Jenis Pekerjaan')
                            ->options([
                                'Full-time' => 'Full-time',
                                'Part-time' => 'Part-time',
                                'Contract' => 'Kontrak',
                                'Internship' => 'Magang',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('application_deadline')
                            ->label('Batas Akhir Lamaran')
                            ->required(),

                        Forms\Components\TextInput::make('salary_range')
                            ->label('Rentang Gaji (Opsional)')
                            ->maxLength(255)
                            ->placeholder('Contoh: Rp 5.000.000 - Rp 7.000.000'),
                    ]),
                
                Forms\Components\Section::make('Detail Pekerjaan')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi Pekerjaan')
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('requirements')
                            ->label('Persyaratan')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Status Publikasi')
                    ->schema([
                        Forms\Components\Toggle::make('status')
                            ->label('Status Dibuka')
                            ->default(true)
                            ->helperText('Aktifkan jika lowongan ini dibuka untuk pelamar.')
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false)
                            ->required()
                            ->formatStateUsing(fn (string $state): bool => $state === 'open')
                            ->dehydrateStateUsing(fn (bool $state): string => $state ? 'open' : 'closed'),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->helperText('Atur tanggal di masa depan untuk menjadwalkan lowongan.')
                            ->default(now()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Posisi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('job_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Full-time' => 'primary',
                        'Part-time' => 'info',
                        'Contract' => 'warning',
                        'Internship' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->getStateUsing(fn (JobVacancy $record): bool => $record->status === 'open'),
                Tables\Columns\TextColumn::make('application_deadline')
                    ->label('Batas Akhir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Dipublikasikan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Dibuka',
                        'closed' => 'Ditutup',
                    ]),
                Tables\Filters\SelectFilter::make('job_type')
                    ->label('Jenis Pekerjaan')
                    ->options([
                        'Full-time' => 'Full-time',
                        'Part-time' => 'Part-time',
                        'Contract' => 'Kontrak',
                        'Internship' => 'Magang',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            // Relasi akan kita tambahkan nanti, misalnya untuk melihat daftar pelamar
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobVacancies::route('/'),
            'create' => Pages\CreateJobVacancy::route('/create'),
            'edit' => Pages\EditJobVacancy::route('/{record}/edit'),
        ];
    }    
}