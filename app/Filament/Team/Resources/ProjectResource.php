<?php

namespace App\Filament\Team\Resources;

use App\Filament\Team\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $navigationLabel = 'Projects';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Project')
                    ->schema([
                        Forms\Components\TextInput::make('nama_project')
                            ->label('Nama Project')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpan(2),

                        Forms\Components\Select::make('project_manager_id')
                            ->label('Project Manager')
                            ->relationship('projectManager', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('prioritas')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Timeline')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->after('tanggal_mulai'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Anggaran Project')
                    ->schema([
                        Forms\Components\Select::make('pengajuan_anggaran_id')
                            ->label('Pengajuan Anggaran (Opsional)')
                            ->options(function () {
                                return \App\Models\PengajuanAnggaran::approved()
                                    ->available()
                                    ->get()
                                    ->pluck('display_name', 'id');
                            })
                            ->searchable()
                            ->helperText('Pilih pengajuan anggaran yang sudah disetujui untuk project ini')
                            ->live()
                            ->nullable(),

                        Forms\Components\Placeholder::make('anggaran_info')
                            ->label('Informasi Anggaran')
                            ->content(function ($get) {
                                $pengajuanId = $get('pengajuan_anggaran_id');
                                if (!$pengajuanId) {
                                    return 'Project akan berjalan tanpa anggaran khusus.';
                                }
                                
                                $pengajuan = \App\Models\PengajuanAnggaran::find($pengajuanId);
                                if (!$pengajuan) {
                                    return 'Pengajuan anggaran tidak ditemukan.';
                                }
                                
                                $content = "📋 {$pengajuan->nomor_pengajuan}\n";
                                $content .= "💰 Rp " . number_format($pengajuan->total_anggaran, 0, ',', '.') . "\n";
                                $content .= "✅ Status: Disetujui dan siap digunakan";
                                
                                return $content;
                            }),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Status Project')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'planning' => 'Planning',
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('progress_percentage')
                            ->label('Progress (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_project')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('projectManager.name')
                    ->label('Project Manager')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'planning',
                        'primary' => 'active',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\BadgeColumn::make('prioritas')
                    ->label('Prioritas')
                    ->colors([
                        'gray' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'primary' => 'urgent',
                    ]),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pengajuanAnggaran.total_anggaran')
                    ->label('Budget')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('Tanpa Budget'),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('prioritas')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\SelectFilter::make('project_manager_id')
                    ->label('Project Manager')
                    ->relationship('projectManager', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['redaksi', 'admin'])),
                    
                Tables\Actions\Action::make('mark_active')
                    ->label('Mulai Project')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(fn ($record) => 
                        $record->status === 'planning' && 
                        auth()->user()->hasRole(['redaksi', 'admin'])
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->markAsActive();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Project Dimulai')
                            ->body("Project '{$record->nama_project}' sekarang dalam status aktif.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_completed')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === 'active' && 
                        auth()->user()->hasRole(['redaksi', 'admin'])
                    )
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->markAsCompleted();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Project Selesai')
                            ->body("Project '{$record->nama_project}' telah diselesaikan.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['redaksi', 'admin'])),
                ]),
            ]);
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        // Menggunakan Spatie Permission
        return auth()->user()->hasRole(['redaksi', 'admin']);
    }

    public static function canEdit($record): bool
    {
        // Menggunakan Spatie Permission
        return auth()->user()->hasRole(['redaksi', 'admin']);
    }

    public static function canDelete($record): bool
    {
        // Menggunakan Spatie Permission
        return auth()->user()->hasRole(['redaksi', 'admin']);
    }
}