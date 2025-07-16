<?php

namespace App\Filament\Team\Resources;

use App\Filament\Team\Resources\ProjectProposalResource\Pages;
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

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $navigationLabel = 'Project Proposals';
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Proposal')
                    ->schema([
                        Forms\Components\TextInput::make('judul_proposal')
                            ->label('Judul Proposal')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Select::make('kategori')
                            ->label('Kategori')
                            ->options([
                                'content' => 'Content',
                                'event' => 'Event',
                                'campaign' => 'Campaign',
                                'research' => 'Research',
                                'other' => 'Other',
                            ])
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

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Project')
                            ->required()
                            ->rows(4)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('tujuan_project')
                            ->label('Tujuan & Target')
                            ->required()
                            ->rows(3)
                            ->helperText('Jelaskan tujuan dan target yang ingin dicapai')
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estimasi')
                    ->schema([
                        Forms\Components\TextInput::make('estimasi_durasi_hari')
                            ->label('Estimasi Durasi (Hari)')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('hari')
                            ->helperText('Perkiraan berapa hari project ini akan selesai'),

                        Forms\Components\TextInput::make('estimasi_budget')
                            ->label('Estimasi Budget')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Kosongkan jika tidak memerlukan budget khusus'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Review Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('catatan_review')
                            ->label('Catatan Review')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record && $record->catatan_review),

                        Forms\Components\Placeholder::make('reviewed_info')
                            ->label('Info Review')
                            ->content(function ($record) {
                                if (!$record || !$record->reviewed_by) {
                                    return 'Belum direview';
                                }
                                
                                $reviewer = $record->reviewedBy->name;
                                $date = $record->reviewed_at->format('d/m/Y H:i');
                                return "Direview oleh: {$reviewer} pada {$date}";
                            }),
                    ])
                    ->columns(1)
                    ->visible(fn ($record) => $record !== null),
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
                    ->limit(50),

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

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimasi_durasi_hari')
                    ->label('Durasi')
                    ->suffix(' hari')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('estimasi_budget')
                    ->label('Budget')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('project_id')
                    ->label('Project')
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
                    ]),

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
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),
                // Hapus semua tombol approve, reject, dan create_project dari team panel
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete_proposals')),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Tim hanya bisa lihat proposal mereka sendiri
        return parent::getEloquentQuery()
            ->where('created_by', auth()->id())
            ->latest();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectProposals::route('/'),
            'create' => Pages\CreateProjectProposal::route('/create'),
            'view' => Pages\ViewProjectProposal::route('/{record}'),
            'edit' => Pages\EditProjectProposal::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('created_by', auth()->id())
            ->where('status', 'pending')
            ->count();
    }
}