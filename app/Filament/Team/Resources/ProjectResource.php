<?php

namespace App\Filament\Team\Resources;

use App\Filament\Team\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\ProjectProposal;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Notifications\Notification;
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
                            ->required()
                            ->rows(4)
                            ->helperText('Jelaskan secara detail tentang project yang akan dikerjakan')
                            ->columnSpan(2),

                        Forms\Components\Select::make('project_manager_id')
                            ->label('Project Manager')
                            ->relationship('projectManager', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn() => auth()->user()->hasRole(['admin', 'redaksi'])) // Hanya visible untuk redaksi/admin
                            ->disabled(fn($context) => $context === 'edit' && auth()->user()->hasRole('team')),

                        Forms\Components\Hidden::make('project_manager_id')
                            ->default(fn() => auth()->user()->hasRole('team') ? auth()->id() : null)
                            ->visible(fn() => auth()->user()->hasRole('team')),

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

                Forms\Components\Section::make('Tim Project')
                    ->schema([
                        Forms\Components\Select::make('team_members')
                            ->label('Anggota Tim')
                            ->options(User::pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih anggota tim yang akan terlibat dalam project ini')
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Target & Tujuan')
                    ->schema([
                        Forms\Components\Textarea::make('tujuan_utama')
                            ->label('Tujuan Utama')
                            ->rows(3)
                            ->helperText('Jelaskan tujuan utama dari project ini')
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('target_audience')
                            ->label('Target Audience')
                            ->rows(2)
                            ->helperText('Siapa target audience dari project ini')
                            ->columnSpan(2),

                Forms\Components\Section::make('Target Metrics')
                    ->description('Definisikan metrics yang dapat diukur untuk mengevaluasi kesuksesan project')
                    ->schema([
                        Forms\Components\Repeater::make('target_metrics')
                            ->label('Target Metrics')
                            ->schema([
                                Forms\Components\TextInput::make('metric')
                                    ->label('Metric')
                                    ->placeholder('contoh: Total Views, Engagement Rate, Downloads')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('target')
                                    ->label('Target')
                                    ->placeholder('contoh: 100K, 5%, 1000')
                                    ->required()
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('timeframe')
                                    ->label('Timeframe')
                                    ->placeholder('contoh: 3 bulan, 1 minggu')
                                    ->required()
                                    ->maxLength(100),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Metric')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Deliverables')
                    ->description('Daftar output konkret yang akan dihasilkan dari project ini')
                    ->schema([
                        Forms\Components\Repeater::make('deliverables')
                            ->label('Deliverables')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Jenis')
                                    ->options([
                                        'article' => '📝 Artikel',
                                        'video' => '🎥 Video',
                                        'podcast' => '🎙️ Podcast',
                                        'infographic' => '📊 Infografis',
                                        'report' => '📋 Report',
                                        'ebook' => '📚 E-book',
                                        'webinar' => '💻 Webinar',
                                        'campaign' => '📢 Campaign',
                                        'other' => '❓ Lainnya',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('title')
                                    ->label('Judul/Deskripsi')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->placeholder('contoh: 5 artikel, 1 video')
                                    ->required()
                                    ->maxLength(100),
                                Forms\Components\Textarea::make('description')
                                    ->label('Detail')
                                    ->placeholder('Jelaskan spesifikasi lebih detail')
                                    ->rows(2)
                                    ->maxLength(500),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Deliverable')
                            ->columnSpanFull(),
                    ]),

                        Forms\Components\Textarea::make('expected_outcomes')
                            ->label('Expected Outcomes')
                            ->rows(3)
                            ->helperText('Hasil yang diharapkan dari project ini')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Timeline')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->afterOrEqual('tanggal_mulai'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Approval')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'planning' => 'Planning',
                                'in_progress' => 'In Progress',
                                'review' => 'Review',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default(fn() => auth()->user()->hasRole('team') ? 'draft' : 'planning')
                            ->visible(fn() => auth()->user()->hasRole(['admin', 'redaksi']))
                            ->dehydrated(fn() => auth()->user()->hasRole(['admin', 'redaksi'])),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->helperText('Catatan tambahan atau alasan penolakan')
                            ->visible(fn () => auth()->user()->hasRole(['admin', 'redaksi']))
                            ->columnSpan(2),
                    ])
                    ->columns(1)
                    ->visible(fn () => auth()->user()->hasRole(['admin', 'redaksi']) || request()->routeIs('*.view')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_project')
                    ->label('Nama Project')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('projectManager.name')
                    ->label('Project Manager')
                    ->sortable(),

                Tables\Columns\TextColumn::make('team_members')
                    ->label('Tim')
                    ->formatStateUsing(fn ($record) => $record->getTeamMemberNames())
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('prioritas')
                    ->label('Prioritas')
                    ->colors([
                        'secondary' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'danger' => 'urgent',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'draft',
                        'secondary' => 'planning',
                        'primary' => 'in_progress',
                        'info' => 'review',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'planning' => 'Planning',
                        'in_progress' => 'In Progress',
                        'review' => 'Review',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('prioritas')
                    ->label('Prioritas')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => 
                        auth()->user()->hasRole(['admin', 'redaksi']) ||
                        ($record->created_by === auth()->id() && $record->status === 'draft') ||
                        $record->project_manager_id === auth()->id()
                    ),
                
                // Action untuk Redaksi: Approve Project
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => 
                        auth()->user()->hasRole(['redaksi', 'admin']) && 
                        $record->status === 'draft'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Project')
                    ->modalDescription('Project akan disetujui dan bisa dimulai.')
                    ->action(function ($record) {
                        $record->update(['status' => 'planning']);
                        
                        Notification::make()
                            ->title('Project Disetujui')
                            ->body("Project '{$record->nama_project}' telah disetujui.")
                            ->success()
                            ->send();
                    }),

                // Action untuk Redaksi: Reject Project  
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => 
                        auth()->user()->hasRole(['redaksi', 'admin']) && 
                        $record->status === 'draft'
                    )
                    ->form([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'cancelled',
                            'catatan' => $data['catatan'],
                        ]);
                        
                        Notification::make()
                            ->title('Project Ditolak')
                            ->body("Project '{$record->nama_project}' ditolak.")
                            ->warning()
                            ->send();
                    }),

                // Action untuk PM: Start Project (hanya visible di panel yang tepat)
                Tables\Actions\Action::make('start_project')
                    ->label('Mulai')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(
                        fn($record) =>
                        auth()->user()->hasRole(['redaksi', 'admin']) &&
                        $record->status === 'planning'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mulai Project')
                    ->modalDescription('Project akan diubah status menjadi in progress.')
                    ->action(function ($record) {
                        $record->update(['status' => 'in_progress']);

                        Notification::make()
                            ->title('Project Dimulai')
                            ->body("Project '{$record->nama_project}' telah dimulai.")
                            ->success()
                            ->send();
                    }),

                // Action untuk PM: Complete Project
                Tables\Actions\Action::make('complete_project')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->project_manager_id === auth()->id() && 
                        in_array($record->status, ['in_progress', 'review'])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Selesaikan Project')
                    ->modalDescription('Project akan ditandai sebagai selesai.')
                    ->action(function ($record) {
                        $record->markAsCompleted();
                        
                        Notification::make()
                            ->title('Project Selesai')
                            ->body("Project '{$record->nama_project}' telah selesai.")
                            ->success()
                            ->send();
                    }),
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

public static function getEloquentQuery(): Builder
{
    $user = auth()->user();

    if (!$user) {
        return parent::getEloquentQuery()->whereRaw('1 = 0');
    }

    if ($user->hasRole(['admin', 'redaksi'])) {
        return parent::getEloquentQuery();
    }

    return parent::getEloquentQuery()
        ->where(function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('project_manager_id', $user->id)
                ->orWhereJsonContains('team_members', (string) $user->id); // Cast ke string
        });
}

    public static function getNavigationBadge(): ?string
    {
        if (auth()->check() && auth()->user()->hasRole(['redaksi', 'admin'])) {
            return static::getModel()::where('status', 'draft')->count();
        }
        
        return null;
    }
}