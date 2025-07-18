<?php

namespace App\Console\Commands;

use App\Services\YouTubeService;
use App\Models\YoutubeVideo;
use Illuminate\Console\Command;

class YouTubeMaintenance extends Command
{
    protected $signature = 'youtube:maintenance 
                            {--sync-existing : Update existing videos data}
                            {--import-recent=10 : Import recent videos}
                            {--cleanup : Clean up old/invalid videos}
                            {--stats : Show statistics}';

    protected $description = 'YouTube videos maintenance tasks';

    private YouTubeService $youtubeService;

    public function __construct(YouTubeService $youtubeService)
    {
        parent::__construct();
        $this->youtubeService = $youtubeService;
    }

    public function handle()
    {
        $this->info('🔧 Starting YouTube Maintenance...');
        $this->line('');

        if ($this->option('stats')) {
            $this->showStats();
        }

        if ($this->option('sync-existing')) {
            $this->syncExistingVideos();
        }

        if ($this->option('import-recent')) {
            $this->importRecentVideos();
        }

        if ($this->option('cleanup')) {
            $this->cleanupVideos();
        }

        $this->info('✅ Maintenance completed!');
    }

    private function showStats(): void
    {
        $this->info('📊 YouTube Videos Statistics:');
        
        $total = YoutubeVideo::count();
        $active = YoutubeVideo::where('is_active', true)->count();
        $featured = YoutubeVideo::where('is_featured', true)->count();
        $uncategorized = YoutubeVideo::whereNull('video_category_id')->count();
        $needsSync = YoutubeVideo::where(function ($q) {
            $q->whereNull('last_sync_at')
              ->orWhere('last_sync_at', '<', now()->subHours(24));
        })->count();

        $this->table(['Metric', 'Count'], [
            ['Total Videos', number_format($total)],
            ['Active Videos', number_format($active)],
            ['Featured Videos', number_format($featured)],
            ['Uncategorized', number_format($uncategorized)],
            ['Needs Sync', number_format($needsSync)],
        ]);
        
        $this->line('');
    }

    private function syncExistingVideos(): void
    {
        $this->info('🔄 Syncing existing videos...');
        
        $videos = YoutubeVideo::active()
            ->where(function ($query) {
                $query->whereNull('last_sync_at')
                      ->orWhere('last_sync_at', '<', now()->subHours(24));
            })
            ->limit(50)
            ->get();

        if ($videos->isEmpty()) {
            $this->info('No videos need syncing.');
            return;
        }

        $synced = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($videos->count());
        $progressBar->start();

        foreach ($videos as $video) {
            try {
                $videoDetails = $this->youtubeService->fetchVideoDetails($video->video_id);
                if ($videoDetails) {
                    $video->updateFromApiData($videoDetails);
                    $synced++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error syncing {$video->video_id}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
        $this->info("✅ Synced: {$synced}, Errors: {$errors}");
        $this->line('');
    }

    private function importRecentVideos(): void
    {
        $maxRecent = $this->option('import-recent');
        $channelId = config('services.youtube.default_channel_id');
        
        if (!$channelId) {
            $this->error('No default channel configured');
            return;
        }

        $this->info("📥 Importing {$maxRecent} recent videos...");
        
        try {
            $videos = $this->youtubeService->importRecentVideos($channelId, $maxRecent);
            
            if ($videos->isEmpty()) {
                $this->info('No new videos found.');
            } else {
                $this->info("✅ Imported " . $videos->count() . " new videos:");
                foreach ($videos->take(5) as $video) {
                    $this->line("  - {$video->title}");
                }
                
                if ($videos->count() > 5) {
                    $this->line("  ... and " . ($videos->count() - 5) . " more");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
        }
        
        $this->line('');
    }

    private function cleanupVideos(): void
    {
        $this->info('🧹 Cleaning up videos...');
        
        // Find videos that might be deleted from YouTube
        $oldVideos = YoutubeVideo::where('last_sync_at', '<', now()->subDays(30))
            ->where('view_count', 0)
            ->get();

        if ($oldVideos->isEmpty()) {
            $this->info('No videos need cleanup.');
            return;
        }

        $this->warn("Found {$oldVideos->count()} potentially deleted videos.");
        
        if ($this->confirm('Mark these videos as inactive?')) {
            foreach ($oldVideos as $video) {
                $video->update(['is_active' => false]);
            }
            $this->info("✅ Marked {$oldVideos->count()} videos as inactive.");
        }
        
        $this->line('');
    }
}