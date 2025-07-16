<?php

// File: app/Filament/Redaksi/Resources/ProjectProposalResource.php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\ProjectProposalResource\Pages;
use App\Models\ProjectProposal;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class ProjectProposalResource extends Resource
{
    protected static ?string $model = ProjectProposal::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $navigationLabel = 'Review Proposals';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Proposal')
                    ->schema([
                        Forms\Components\TextInput::make('judul_proposal')
                            ->label('Judul Proposal')
                            ->disabled(),

                        Forms\Components\Select::make('kategori')
                            ->label('Kategori')
                            ->options([
                                'content' => 'Content',
                                'event' => 'Event',
                                'campaign' => 'Campaign',
                                'research' => 'Research',
                                'other' => 'Other',
                            ])
                            ->disabled(),

                        Forms\Components\Select::make('prioritas')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->disabled(),

                        Forms\Components\Placeholder::make('created_info')
                            ->label('Dibuat Oleh')
                            ->content(fn ($record) => $record?->createdBy?->name . ' pada ' . $record?->created_at?->format('d/m/Y H:i')),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Project')
                            ->disabled()
                            ->rows(4)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('tujuan_project')
                            ->label('Tujuan & Target')
                            ->disabled()
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estimasi')
                    ->schema([
                        Forms\Components\TextInput::make('estimasi_durasi_hari')
                            ->label('Estimasi Durasi')
                            ->suffix('hari')
                            ->disabled(),

                        Forms\Components\TextInput::make('estimasi_budget')
                            ->label('Estimasi Budget')
                            ->prefix('Rp')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Review & Approval')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state !== 'pending') {
                                    $set('reviewed_by', auth()->id());
                                    $set('reviewed_at', now());
                                }
                            }),

                        Forms\Components\Hidden::make('reviewed_by'),
                        Forms\Components\Hidden::make('reviewed_at'),

                        Forms\Components\Textarea::make('catatan_review')
                            ->label('Catatan Review')
                            ->rows(3)
                            ->required(fn ($get) => $get('status') === 'rejected')
                            ->helperText('Wajib diisi jika menolak proposal'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul_proposal')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Pengaju')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('kategori')
                    ->label('Kategori')
                    ->colors([
                        'info' => 'content',
                        'warning' => 'event',
                        'success' => 'campaign',
                        'primary' => 'research',
                        'gray' => 'other',
                    ]),

                Tables\Columns\BadgeColumn::make('prioritas')
                    ->label('Prioritas')
                    ->colors([
                        'gray' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'primary' => 'urgent',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('estimasi_durasi_hari')
                    ->label('Durasi')
                    ->suffix(' hari')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('estimasi_budget')
                    ->label('Budget')
                    ->money('IDR')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('project_id')
                    ->label('Project Created')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),

                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'content' => 'Content',
                        'event' => 'Event',
                        'campaign' => 'Campaign',
                        'research' => 'Research',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('prioritas')
                    ->options([
                        'urgent' => 'Urgent',
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Review'),

                Tables\Actions\Action::make('quick_approve')
                    ->label('Quick Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->approve(auth()->id(), 'Approved by quick action');
                        
                        Notification::make()
                            ->title('Proposal Disetujui & Project Dibuat')
                            ->body("Project '{$record->judul_proposal}' telah dibuat otomatis.")
                            ->success()
                            ->duration(8000)
                            ->send();
                    }),

                Tables\Actions\Action::make('create_project_now')
                    ->label('Buat Project')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('primary')
                    ->visible(fn ($record) => $record->isApproved() && !$record->hasProject())
                    ->form([
                        Forms\Components\Select::make('project_manager_id')
                            ->label('Project Manager')
                            ->options(User::all()->pluck('name', 'id'))
                            ->required(),
                            
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Target Selesai')
                            ->required()
                            ->after('tanggal_mulai'),
                    ])
                    ->action(function ($record, array $data) {
                        $project = $record->createProject([
                            'project_manager_id' => $data['project_manager_id'],
                            'tanggal_mulai' => $data['tanggal_mulai'],
                            'tanggal_selesai' => $data['tanggal_selesai'],
                        ]);
                        
                        Notification::make()
                            ->title('Project Berhasil Dibuat!')
                            ->body("Project '{$project->nama_project}' telah dibuat dan siap dikerjakan.")
                            ->success()
                            ->duration(8000)
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        // Redaksi bisa lihat semua proposal
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectProposals::route('/'),
            'edit' => Pages\EditProjectProposal::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
}