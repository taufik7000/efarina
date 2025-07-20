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
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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
                                    ->label('Terakhir Sinkron')
                                    ->disabled(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Thumbnail')
                    ->size(80, 60)
                    ->extraAttributes(['style' => 'border-radius: 6px;']),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Video')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('category.nama_kategori')
                    ->label('Kategori')
                    ->sortable()
                    ->badge()
                    ->placeholder('Belum dikategorikan'),

                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => number_format($state)),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Tanggal Publish')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('video_category_id')
                    ->label('Kategori')
                    ->options(function () {
                        return VideoCategory::active()
                            ->withCount(['videos' => function ($query) {
                                $query->where('is_active', true);
                            }])
                            ->get()
                            ->mapWithKeys(function ($category) {
                                return [$category->id => $category->nama_kategori . ' (' . $category->videos_count . ')'];
                            })
                            ->toArray();
                    })
                    ->placeholder('Semua Kategori'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non-aktif'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Video Unggulan')
                    ->placeholder('Semua Video')
                    ->trueLabel('Unggulan')
                    ->falseLabel('Biasa'),

                Tables\Filters\Filter::make('uncategorized')
                    ->label('Belum Dikategorikan')
                    ->query(function (Builder $query): Builder {
                        return $query->whereNull('video_category_id');
                    })
                    ->toggle(),

                Tables\Filters\Filter::make('recent')
                    ->label('Video Terbaru (30 hari)')
                    ->query(function (Builder $query): Builder {
                        return $query->where('published_at', '>=', now()->subDays(30));
                    })
                    ->toggle(),

                Tables\Filters\Filter::make('popular')
                    ->label('Video Populer (>1000 views)')
                    ->query(function (Builder $query): Builder {
                        return $query->where('view_count', '>', 1000);
                    })
                    ->toggle(),

                Tables\Filters\SelectFilter::make('duration_range')
                    ->label('Durasi Video')
                    ->options([
                        'short' => 'Pendek (< 5 menit)',
                        'medium' => 'Sedang (5-20 menit)', 
                        'long' => 'Panjang (> 20 menit)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, string $duration) {
                                switch ($duration) {
                                    case 'short':
                                        return $query->where('duration_seconds', '<', 300); // < 5 menit
                                    case 'medium':
                                        return $query->whereBetween('duration_seconds', [300, 1200]); // 5-20 menit
                                    case 'long':
                                        return $query->where('duration_seconds', '>', 1200); // > 20 menit
                                }
                            }
                        );
                    }),

                Tables\Filters\SelectFilter::make('view_range')
                    ->label('Range Views')
                    ->options([
                        'low' => 'Rendah (< 1K)',
                        'medium' => 'Sedang (1K - 10K)',
                        'high' => 'Tinggi (10K - 100K)',
                        'viral' => 'Viral (> 100K)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, string $range) {
                                switch ($range) {
                                    case 'low':
                                        return $query->where('view_count', '<', 1000);
                                    case 'medium':
                                        return $query->whereBetween('view_count', [1000, 10000]);
                                    case 'high':
                                        return $query->whereBetween('view_count', [10000, 100000]);
                                    case 'viral':
                                        return $query->where('view_count', '>', 100000);
                                }
                            }
                        );
                    }),
            ])
            ->headerActions([
                Action::make('sync_all_views')
                    ->label('🔄 Sync Views Semua Video')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function () {
                        try {
                            // Set execution time limit untuk proses ini
                            set_time_limit(300); // 5 menit
                            
                            $youtubeService = app(YouTubeService::class);
                            
                            // Ambil video dalam batch kecil untuk avoid timeout
                            $videos = YoutubeVideo::active()
                                ->select(['id', 'video_id', 'title'])
                                ->limit(100) // Batasi hanya 100 video pertama
                                ->get();
                            
                            if ($videos->isEmpty()) {
                                Notification::make()
                                    ->title('Tidak ada video untuk disync')
                                    ->body('Tidak ditemukan video aktif di database.')
                                    ->info()
                                    ->send();
                                return;
                            }
                            
                            $videoIds = $videos->pluck('video_id')->toArray();
                            $synced = 0;
                            $errors = 0;
                            
                            // Process dalam batch lebih kecil (25 video per batch)
                            $batches = array_chunk($videoIds, 25);
                            
                            foreach ($batches as $batchIndex => $batch) {
                                try {
                                    // Fetch data dari YouTube API untuk batch ini
                                    $response = Http::timeout(15)->retry(2, 1000)->get('https://www.googleapis.com/youtube/v3/videos', [
                                        'part' => 'statistics',
                                        'id' => implode(',', $batch),
                                        'key' => config('services.youtube.api_key'),
                                    ]);
                                    
                                    if ($response->successful()) {
                                        $data = $response->json();
                                        
                                        if (!empty($data['items'])) {
                                            foreach ($data['items'] as $videoData) {
                                                $video = YoutubeVideo::where('video_id', $videoData['id'])->first();
                                                
                                                if ($video) {
                                                    $statistics = $videoData['statistics'] ?? [];
                                                    $video->update([
                                                        'view_count' => (int) ($statistics['viewCount'] ?? 0),
                                                        'like_count' => (int) ($statistics['likeCount'] ?? 0),
                                                        'last_sync_at' => now(),
                                                    ]);
                                                    $synced++;
                                                }
                                            }
                                        }
                                    } else {
                                        $errors += count($batch);
                                    }
                                    
                                    // Progress feedback untuk batch besar
                                    if (count($batches) > 2 && ($batchIndex + 1) % 2 == 0) {
                                        // Flush output untuk prevent timeout di browser
                                        if (function_exists('fastcgi_finish_request')) {
                                            fastcgi_finish_request();
                                        }
                                    }
                                    
                                    // Rate limiting - pause lebih singkat
                                    if (count($batches) > 1) {
                                        usleep(500000); // 0.5 detik
                                    }
                                    
                                } catch (\Exception $e) {
                                    $errors += count($batch);
                                    Log::error("Error syncing batch: " . $e->getMessage());
                                }
                            }
                            
                            if ($synced > 0) {
                                Notification::make()
                                    ->title('Sync views berhasil!')
                                    ->body("✅ {$synced} video berhasil disync. ❌ {$errors} video error. (Max 100 video per sekali sync)")
                                    ->success()
                                    ->duration(5000)
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Sync views gagal')
                                    ->body('Tidak ada video yang berhasil disync. Periksa koneksi API.')
                                    ->warning()
                                    ->send();
                            }
                            
                        } catch (\Exception $e) {
                            Log::error("Error in sync all views: " . $e->getMessage());
                            
                            Notification::make()
                                ->title('Gagal sync views')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Sync Views Semua Video')
                    ->modalDescription('Ini akan menyinkronkan data views dan likes untuk maksimal 100 video aktif terbaru. Untuk dataset besar, ulangi proses ini beberapa kali.')
                    ->modalSubmitActionLabel('Ya, Sync Views (Max 100)'),

                Action::make('import_recent_videos')
                    ->label('Import Video Terbaru')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->color('success')
                    ->action(function () {
                        try {
                            $youtubeService = app(YouTubeService::class);
                            $channelId = config('services.youtube.default_channel_id');
                            
                            if (!$channelId) {
                                Notification::make()
                                    ->title('Channel ID tidak dikonfigurasi')
                                    ->body('Silakan set YOUTUBE_DEFAULT_CHANNEL_ID di file .env')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            // Import 15 video terbaru
                            $importedVideos = $youtubeService->importRecentVideos($channelId, 15);
                            
                            if (empty($importedVideos)) {
                                Notification::make()
                                    ->title('Tidak ada video baru')
                                    ->body('Semua video terbaru sudah ada di database.')
                                    ->info()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Import berhasil!')
                                    ->body(count($importedVideos) . ' video baru telah diimport.')
                                    ->success()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Log::error("Error importing recent videos: " . $e->getMessage());
                            
                            Notification::make()
                                ->title('Gagal import video')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Import Video Terbaru')
                    ->modalDescription('Ini akan mengimport maksimal 15 video terbaru dari channel YouTube.')
                    ->modalSubmitActionLabel('Ya, Import Sekarang'),

                Action::make('create_categories_from_playlists')
                    ->label('Buat Kategori dari Playlist')
                    ->icon('heroicon-o-folder-plus')
                    ->color('warning')
                    ->action(function () {
                        try {
                            $youtubeService = app(YouTubeService::class);
                            $channelId = config('services.youtube.default_channel_id');
                            
                            if (!$channelId) {
                                Notification::make()
                                    ->title('Channel ID tidak dikonfigurasi')
                                    ->body('Silakan set YOUTUBE_DEFAULT_CHANNEL_ID di file .env')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            // Fetch playlists dari channel
                            $playlists = $youtubeService->fetchChannelPlaylists($channelId);
                            
                            if (empty($playlists)) {
                                Notification::make()
                                    ->title('Tidak ada playlist ditemukan')
                                    ->body('Channel tidak memiliki playlist publik.')
                                    ->info()
                                    ->send();
                                return;
                            }
                            
                            $created = 0;
                            $updated = 0;
                            
                            foreach ($playlists as $playlist) {
                                // Check apakah kategori sudah ada
                                $existingCategory = VideoCategory::where('nama_kategori', $playlist['title'])
                                                                ->first();
                                
                                if ($existingCategory) {
                                    $updated++;
                                    continue;
                                }
                                
                                // Buat kategori baru
                                VideoCategory::create([
                                    'nama_kategori' => $playlist['title'],
                                    'deskripsi' => "Kategori berdasarkan playlist: " . $playlist['title'],
                                    'color' => self::generateRandomColor(),
                                    'is_active' => true,
                                    'sort_order' => VideoCategory::max('sort_order') + 1,
                                ]);
                                
                                $created++;
                            }
                            
                            if ($created > 0) {
                                Notification::make()
                                    ->title('Kategori berhasil dibuat!')
                                    ->body("{$created} kategori baru dibuat dari playlist. {$updated} kategori sudah ada.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Semua kategori sudah ada')
                                    ->body('Semua playlist sudah memiliki kategori yang sesuai.')
                                    ->info()
                                    ->send();
                            }
                            
                        } catch (\Exception $e) {
                            Log::error("Error creating categories from playlists: " . $e->getMessage());
                            
                            Notification::make()
                                ->title('Gagal membuat kategori')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Buat Kategori dari Playlist')
                    ->modalDescription('Ini akan membuat kategori video berdasarkan playlist yang ada di channel YouTube.')
                    ->modalSubmitActionLabel('Ya, Buat Kategori'),

                Action::make('assign_categories_auto')
                    ->label('Auto Assign Kategori')
                    ->icon('heroicon-o-tag')
                    ->color('info')
                    ->action(function () {
                        try {
                            // Cari video yang belum dikategorikan
                            $uncategorizedVideos = YoutubeVideo::whereNull('video_category_id')
                                                              ->where('is_active', true)
                                                              ->get();
                            
                            if ($uncategorizedVideos->isEmpty()) {
                                Notification::make()
                                    ->title('Semua video sudah dikategorikan')
                                    ->body('Tidak ada video yang perlu dikategorikan.')
                                    ->info()
                                    ->send();
                                return;
                            }
                            
                            $assigned = 0;
                            $categories = VideoCategory::active()->get();
                            
                            foreach ($uncategorizedVideos as $video) {
                                // Coba cari kategori berdasarkan title video
                                foreach ($categories as $category) {
                                    $categoryWords = explode(' ', strtolower($category->nama_kategori));
                                    $videoTitle = strtolower($video->title);
                                    
                                    // Jika ada kata dari kategori yang cocok dengan title video
                                    foreach ($categoryWords as $word) {
                                        if (strlen($word) > 3 && strpos($videoTitle, $word) !== false) {
                                            $video->update(['video_category_id' => $category->id]);
                                            $assigned++;
                                            break 2; // Break dari kedua loop
                                        }
                                    }
                                }
                            }
                            
                            if ($assigned > 0) {
                                Notification::make()
                                    ->title('Auto assign berhasil!')
                                    ->body("{$assigned} video berhasil dikategorikan secara otomatis.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Tidak ada yang cocok')
                                    ->body('Tidak ada video yang bisa dikategorikan secara otomatis.')
                                    ->info()
                                    ->send();
                            }
                            
                        } catch (\Exception $e) {
                            Log::error("Error auto assigning categories: " . $e->getMessage());
                            
                            Notification::make()
                                ->title('Gagal auto assign')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Auto Assign Kategori')
                    ->modalDescription('Ini akan mencoba mengkategorikan video secara otomatis berdasarkan kecocokan kata dalam judul.')
                    ->modalSubmitActionLabel('Ya, Auto Assign'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('mark_active')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_active' => true])))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('mark_inactive')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_active' => false])))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('sync_selected_views')
                        ->label('Sync Views Terpilih')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(function ($records) {
                            // Set execution time limit
                            set_time_limit(120); // 2 menit
                            
                            $videoIds = $records->pluck('video_id')->toArray();
                            $synced = 0;
                            $errors = 0;
                            
                            try {
                                // Process dalam batch kecil (20 video)
                                $batches = array_chunk($videoIds, 20);
                                
                                foreach ($batches as $batch) {
                                    $response = Http::timeout(10)->retry(2, 500)->get('https://www.googleapis.com/youtube/v3/videos', [
                                        'part' => 'statistics',
                                        'id' => implode(',', $batch),
                                        'key' => config('services.youtube.api_key'),
                                    ]);
                                    
                                    if ($response->successful()) {
                                        $data = $response->json();
                                        
                                        if (!empty($data['items'])) {
                                            foreach ($data['items'] as $videoData) {
                                                $video = YoutubeVideo::where('video_id', $videoData['id'])->first();
                                                
                                                if ($video) {
                                                    $statistics = $videoData['statistics'] ?? [];
                                                    $video->update([
                                                        'view_count' => (int) ($statistics['viewCount'] ?? 0),
                                                        'like_count' => (int) ($statistics['likeCount'] ?? 0),
                                                        'last_sync_at' => now(),
                                                    ]);
                                                    $synced++;
                                                }
                                            }
                                        }
                                    } else {
                                        $errors += count($batch);
                                    }
                                    
                                    // Rate limiting lebih singkat
                                    if (count($batches) > 1) {
                                        usleep(300000); // 0.3 detik
                                    }
                                }
                                
                                Notification::make()
                                    ->title('Sync views terpilih selesai')
                                    ->body("✅ {$synced} video berhasil, ❌ {$errors} video error.")
                                    ->success()
                                    ->send();
                                    
                            } catch (\Exception $e) {
                                Log::error("Error in bulk sync views: " . $e->getMessage());
                                
                                Notification::make()
                                    ->title('Gagal sync views')
                                    ->body('Terjadi kesalahan: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading('Sync Views Video Terpilih')
                        ->modalDescription('Ini akan menyinkronkan views dan likes untuk video yang dipilih. Maksimal 50 video per sekali proses.')
                        ->modalSubmitActionLabel('Ya, Sync Views'),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
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

    /**
     * Generate random color untuk kategori baru
     */
    private static function generateRandomColor(): string
    {
        $colors = [
            '#3b82f6', // blue
            '#ef4444', // red
            '#10b981', // green
            '#f59e0b', // yellow
            '#8b5cf6', // purple
            '#06b6d4', // cyan
            '#f97316', // orange
            '#84cc16', // lime
            '#ec4899', // pink
            '#6b7280', // gray
        ];
        
        return $colors[array_rand($colors)];
    }

    /**
     * Get video statistics untuk ditampilkan
     */
    private static function getVideoStatistics(): array
    {
        $total = YoutubeVideo::count();
        $active = YoutubeVideo::where('is_active', true)->count();
        $featured = YoutubeVideo::where('is_featured', true)->count();
        $categorized = YoutubeVideo::whereNotNull('video_category_id')->count();
        $uncategorized = YoutubeVideo::whereNull('video_category_id')->count();
        
        $totalViews = YoutubeVideo::sum('view_count');
        $totalDurationSeconds = YoutubeVideo::sum('duration_seconds');
        
        // Format total views - tampilkan angka lengkap dengan separator
        $formattedViews = number_format($totalViews);
        
        // Format total duration
        $hours = floor($totalDurationSeconds / 3600);
        $minutes = floor(($totalDurationSeconds % 3600) / 60);
        $formattedDuration = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
        
        return [
            'total' => number_format($total),
            'active' => number_format($active),
            'featured' => number_format($featured),
            'categorized' => number_format($categorized),
            'uncategorized' => number_format($uncategorized),
            'total_views' => $formattedViews,
            'total_duration' => $formattedDuration,
        ];
    }
}