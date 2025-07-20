<?php

namespace App\Console\Commands;

use App\Jobs\SyncYouTubeVideoViewsJob;
use App\Models\YoutubeVideo;
use Illuminate\Console\Command;

class AutoSyncYouTubeViews extends Command
{
    protected $signature = 'youtube:auto-sync-views
                            {--priority : Sync only featured/popular videos}
                            {--all : Sync all videos}
                            {--recent : Sync only recent videos (30 days)}';

    protected $description = 'Automatically sync YouTube video views using queue jobs';

    public function handle()
    {
        $this->info('🚀 Starting auto sync YouTube views...');

        $query = YoutubeVideo::active();

        // Apply filters based on options
        if ($this->option('priority')) {
            $query->where(function ($q) {
                $q->where('is_featured', true)
                  ->orWhere('view_count', '>', 10000);
            });
            $this->info('📊 Mode: Priority videos only (featured + popular)');
        } elseif ($this->option('recent')) {
            $query->where('published_at', '>=', now()->subDays(30));
            $this->info('📅 Mode: Recent videos (30 days)');
        } else {
            $this->info('🎯 Mode: All active videos');
        }

        $videoIds = $query->pluck('video_id')->toArray();

        if (empty($videoIds)) {
            $this->warn('No videos found to sync.');
            return Command::SUCCESS;
        }

        // Split into batches dan dispatch jobs
        $batches = array_chunk($videoIds, 100);
        $jobCount = 0;

        foreach ($batches as $index => $batch) {
            SyncYouTubeVideoViewsJob::dispatch($batch, "auto_sync_" . date('Y_m_d_H_i') . "_" . ($index + 1))
                ->delay(now()->addMinutes($index * 2)); // 2 menit delay antar batch

            $jobCount++;
        }

        $this->info("✅ Dispatched {$jobCount} sync jobs for " . count($videoIds) . " videos.");
        $this->info("⏱️  Jobs will process over the next " . ($jobCount * 2) . " minutes.");

        return Command::SUCCESS;
    }
}
