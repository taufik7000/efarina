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
                            ->required()
                            ->default('medium'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('draft')
                            ->visible(fn ($record) => $record !== null), // Hanya tampil saat edit/view

                        Forms\Components\TextInput::make('progress_percentage')
                            ->label('Progress (%)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn ($record) => $record !== null), // Hanya tampil saat edit/view
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
                            ->label('Pengajuan Anggaran')
                            ->options(function () {
                                return \App\Models\PengajuanAnggaran::approved()
                                    ->available()
                                    ->get()
                                    ->pluck('display_name', 'id');
                            })
                            ->searchable()
                            ->helperText('Pilih pengajuan anggaran yang sudah disetujui')
                            ->nullable(),

                        Forms\Components\Placeholder::make('anggaran_info')
                            ->label('Informasi Anggaran')
                            ->content(function ($get) {
                                $pengajuanId = $get('pengajuan_anggaran_id');
                                if (!$pengajuanId) {
                                    return 'Pilih pengajuan anggaran untuk melihat detail.';
                                }
                                
                                $pengajuan = \App\Models\PengajuanAnggaran::find($pengajuanId);
                                if (!$pengajuan) {
                                    return 'Pengajuan anggaran tidak ditemukan.';
                                }
                                
                                $content = "Nomor: {$pengajuan->nomor_pengajuan}\n";
                                $content .= "Total: Rp " . number_format($pengajuan->total_anggaran, 0, ',', '.') . "\n";
                                $content .= "Status: " . ucfirst($pengajuan->status) . "\n";
                                $content .= "Redaksi: " . ucfirst($pengajuan->redaksi_approval_status) . "\n";
                                $content .= "Keuangan: " . ucfirst($pengajuan->keuangan_approval_status);
                                
                                return $content;
                            })
                            ->visible(fn ($get) => $get('pengajuan_anggaran_id')),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Status Approval')
                    ->schema([
                        Forms\Components\Select::make('redaksi_approval_status')
                            ->label('Status Redaksi')
                            ->options([
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('keuangan_approval_status')
                            ->label('Status Keuangan')
                            ->options([
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('redaksi_notes')
                            ->label('Catatan Redaksi')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->visible(fn ($record) => $record && $record->redaksi_notes),

                        Forms\Components\Textarea::make('keuangan_notes')
                            ->label('Catatan Keuangan')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(3)
                            ->visible(fn ($record) => $record && $record->keuangan_notes),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record !== null) // Hanya tampil saat edit/view
                    ->collapsible(),

                Forms\Components\Section::make('Budget Tracking')
                    ->schema([
                        Forms\Components\TextInput::make('budget_allocated')
                            ->label('Budget Allocated')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('budget_used')
                            ->label('Budget Used')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
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
                    ->label('Manager')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'planning' => 'warning',
                        'active' => 'success',
                        'on_hold' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('prioritas')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'urgent' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('proposal_budget')
                    ->label('Anggaran Proposal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('redaksi_approval_status')
                    ->label('Status Redaksi')
                    ->badge()
                    ->color(fn ($record) => $record->redaksi_status_color)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    }),

                Tables\Columns\TextColumn::make('keuangan_approval_status')
                    ->label('Status Keuangan')
                    ->badge()
                    ->color(fn ($record) => $record->keuangan_status_color)
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    }),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 75 ? 'success' : ($state >= 50 ? 'warning' : 'danger')),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
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

                Tables\Filters\SelectFilter::make('redaksi_approval_status')
                    ->label('Status Redaksi')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\SelectFilter::make('keuangan_approval_status')
                    ->label('Status Keuangan')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\Filter::make('my_projects')
                    ->label('My Projects')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('project_manager_id', auth()->id())
                              ->orWhere('created_by', auth()->id())
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
}