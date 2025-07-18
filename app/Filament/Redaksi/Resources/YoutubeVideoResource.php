<?php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\YoutubeVideoResource\Pages;
use App\Models\YoutubeVideo;
use App\Models\VideoCategory;
use App\Services\YouTubeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class YoutubeVideoResource extends Resource
{
    protected static ?string $model = YoutubeVideo::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationLabel = 'Video YouTube';

    protected static ?string $pluralModelLabel = 'Video YouTube';

    protected static ?string $modelLabel = 'Video YouTube';

    protected static ?string $navigationGroup = 'Video Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Video')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Judul Video')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi Asli')
                                    ->rows(3)
                                    ->disabled()
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('custom_description')
                                    ->label('Deskripsi Custom')
                                    ->rows(3)
                                    ->helperText('Deskripsi custom untuk website (opsional)')
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('video_id')
                                            ->label('Video ID')
                                            ->required()
                                            ->disabled()
                                            ->helperText('YouTube Video ID'),

                                        Forms\Components\Select::make('video_category_id')
                                            ->label('Kategori')
                                            ->options(VideoCategory::active()->ordered()->pluck('nama_kategori', 'id'))
                                            ->searchable()
                                            ->placeholder('Pilih kategori video'),
                                    ]),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Aktif')
                                            ->default(true),

                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Unggulan'),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Urutan')
                                            ->numeric()
                                            ->default(0),
                                    ]),
                            ]),

                        Forms\Components\Section::make('Informasi Channel')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('channel_id')
                                            ->label('Channel ID')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('channel_title')
                                            ->label('Nama Channel')
                                            ->disabled(),
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Thumbnail')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail_url')
                                    ->label('Thumbnail')
                                    ->image()
                                    ->disabled()
                                    ->helperText('Thumbnail dari YouTube'),
                            ]),

                        Forms\Components\Section::make('Statistik')
                            ->schema([
                                Forms\Components\TextInput::make('view_count')
                                    ->label('Jumlah Views')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('like_count')
                                    ->label('Jumlah Likes')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('duration_seconds')
                                    ->label('Durasi (detik)')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Tanggal Publish')
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('last_sync_at')
                                    ->label('Last Sync')
                                    ->disabled(),
                            ]),

                        Forms\Components\Section::make('Tags')
                            ->schema([
                                Forms\Components\TagsInput::make('tags')
                                    ->label('YouTube Tags')
                                    ->disabled(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_hq')
                    ->label('Thumbnail')
                    ->size(80)
                    ->getStateUsing(fn ($record) => $record->thumbnail_url ?: $record->thumbnail_hq),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('channel_title')
                    ->label('Channel')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.nama_kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ? 
                        \Filament\Support\Colors\Color::hex($record->category->color) : 
                        'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Durasi')
                    ->alignCenter()
                    ->sortable('duration_seconds'),


                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publish')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_sync_at')
                    ->label('Last Sync')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('video_category_id')
                    ->label('Kategori')
                    ->options(VideoCategory::active()->ordered()->pluck('nama_kategori', 'id'))
                    ->placeholder('Semua Kategori'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Unggulan')
                    ->placeholder('Semua')
                    ->trueLabel('Unggulan')
                    ->falseLabel('Biasa'),

                Tables\Filters\Filter::make('uncategorized')
                    ->label('Belum Dikategorikan')
                    ->query(fn (Builder $query): Builder => $query->whereNull('video_category_id'))
                    ->toggle(),

                Tables\Filters\Filter::make('recent')
                    ->label('Video Terbaru (7 hari)')
                    ->query(fn (Builder $query): Builder => $query->where('published_at', '>=', now()->subDays(7)))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('watch')
                    ->label('Tonton')
                    ->icon('heroicon-o-play')
                    ->url(fn (YoutubeVideo $record) => $record->watch_url)
                    ->openUrlInNewTab()
                    ->color('success'),

                Tables\Actions\Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (YoutubeVideo $record) {
                        try {
                            $youtubeService = app(YouTubeService::class);
                            $videoDetails = $youtubeService->fetchVideoDetails($record->video_id);
                            
                            if ($videoDetails) {
                                $record->updateFromApiData($videoDetails);
                                Notification::make()
                                    ->title('Video berhasil disinkronkan')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Gagal mengambil data video')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->color('info'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('import_videos')
                    ->label('Import Video')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->form([
                        Forms\Components\TextInput::make('video_url')
                            ->label('Video URL atau ID')
                            ->required()
                            ->placeholder('https://youtube.com/watch?v=... atau video_id'),
                        
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori (Opsional)')
                            ->options(VideoCategory::active()->ordered()->pluck('nama_kategori', 'id'))
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        try {
                            $videoId = YoutubeVideo::extractVideoId($data['video_url']);
                            
                            if (!$videoId) {
                                Notification::make()
                                    ->title('URL video tidak valid')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $youtubeService = app(YouTubeService::class);
                            $video = $youtubeService->importVideo($videoId);
                            
                            if ($video && isset($data['category_id'])) {
                                $video->update(['video_category_id' => $data['category_id']]);
                            }
                            
                            if ($video) {
                                Notification::make()
                                    ->title('Video berhasil diimpor: ' . $video->title)
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Gagal mengimpor video')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('categorize')
                        ->label('Set Kategori')
                        ->icon('heroicon-o-folder')
                        ->form([
                            Forms\Components\Select::make('category_id')
                                ->label('Kategori')
                                ->options(VideoCategory::active()->ordered()->pluck('nama_kategori', 'id'))
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                $record->update(['video_category_id' => $data['category_id']]);
                            }
                            
                            Notification::make()
                                ->title('Kategori berhasil diupdate untuk ' . count($records) . ' video')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('toggle_featured')
                        ->label('Toggle Unggulan')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_featured' => !$record->is_featured]);
                            }
                            
                            Notification::make()
                                ->title('Status unggulan berhasil diupdate')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('sync_selected')
                        ->label('Sync Data Terpilih')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function ($records) {
                            $youtubeService = app(YouTubeService::class);
                            $synced = 0;
                            
                            foreach ($records as $record) {
                                try {
                                    $videoDetails = $youtubeService->fetchVideoDetails($record->video_id);
                                    if ($videoDetails) {
                                        $record->updateFromApiData($videoDetails);
                                        $synced++;
                                    }
                                } catch (\Exception $e) {
                                    // Continue to next video
                                }
                            }
                            
                            Notification::make()
                                ->title("{$synced} video berhasil disinkronkan")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListYoutubeVideos::route('/'),
            'create' => Pages\CreateYoutubeVideo::route('/create'),
            'edit' => Pages\EditYoutubeVideo::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('video_category_id', null)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('video_category_id', null)->count() > 0 ? 'warning' : null;
    }
}