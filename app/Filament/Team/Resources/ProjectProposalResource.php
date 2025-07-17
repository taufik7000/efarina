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
                Forms\Components\Section::make('Informasi Dasar')
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
                            ->columnSpan(2)
                            ->helperText('Jelaskan secara detail tentang project yang akan dikerjakan'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tujuan & Target')
                    ->schema([
                        Forms\Components\Textarea::make('tujuan_utama')
                            ->label('Tujuan Utama')
                            ->required()
                            ->rows(3)
                            ->helperText('Apa tujuan utama dari project ini?')
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('target_audience')
                            ->label('Target Audience')
                            ->required()
                            ->rows(2)
                            ->helperText('Siapa target audience dari project ini?')
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('tujuan_project')
                            ->label('Tujuan & Outcome yang Diharapkan')
                            ->required()
                            ->rows(3)
                            ->helperText('Jelaskan outcome dan impact yang diharapkan')
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Target Metrics')
                    ->description('Definisikan metrics yang dapat diukur untuk mengevaluasi kesuksesan project')
                    ->schema([
                        Forms\Components\Repeater::make('target_metrics')
                            ->label('Target Metrics')
                            ->schema([
                                Forms\Components\TextInput::make('metric')
                                    ->label('Metric')
                                    ->placeholder('contoh: Total Views, Engagement Rate, Downloads')
                                    ->required(),
                                Forms\Components\TextInput::make('target')
                                    ->label('Target')
                                    ->placeholder('contoh: 100K, 5%, 1000')
                                    ->required(),
                                Forms\Components\TextInput::make('timeframe')
                                    ->label('Timeframe')
                                    ->placeholder('contoh: 3 bulan, 1 minggu')
                                    ->required(),
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
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->placeholder('contoh: 5 artikel, 1 video')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Detail')
                                    ->placeholder('Jelaskan spesifikasi lebih detail')
                                    ->rows(2),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Deliverable')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Timeline & Budget')
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
                            ->helperText('Total estimasi budget yang dibutuhkan'),

                        Forms\Components\Repeater::make('budget_breakdown')
                            ->label('Rincian Budget')
                            ->schema([
                                Forms\Components\TextInput::make('item')
                                    ->label('Item')
                                    ->placeholder('contoh: Transport, Equipment, Talent Fee')
                                    ->required(),
                                Forms\Components\TextInput::make('amount')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Keterangan')
                                    ->placeholder('Detail penggunaan budget')
                                    ->rows(1),
                            ])
                            ->columns(3)
                            ->addActionLabel('Tambah Item Budget')
                            ->columnSpanFull()
                            ->collapsible(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Metodologi')
                    ->schema([
                        Forms\Components\Textarea::make('metodologi')
                            ->label('Metodologi & Pendekatan')
                            ->rows(4)
                            ->helperText('Jelaskan metode dan pendekatan yang akan digunakan')
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('resiko_dan_mitigasi')
                            ->label('Resiko & Mitigasi')
                            ->rows(3)
                            ->helperText('Identifikasi resiko potensial dan cara mengatasinya')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Dokumen Pendukung')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Lampiran Dokumen')
                            ->multiple()
                            ->acceptedFileTypes(['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'])
                            ->maxSize(10240) // 10MB
                            ->helperText('Upload dokumen pendukung seperti proposal detail, referensi, mockup, dll. (Max 10MB per file)')
                            ->columnSpanFull(),
                    ])
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