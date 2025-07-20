<?php

// METODE 1: Laravel Queue Job (RECOMMENDED)
// File: app/Jobs/SyncYouTubeVideoViewsJob.php

namespace App\Jobs;

use App\Models\YoutubeVideo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncYouTubeVideoViewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 menit timeout
    public $tries = 3; // Retry 3 kali jika gagal
    public $backoff = 60; // Delay 60 detik antar retry

    protected array $videoIds;
    protected string $batchName;

    public function __construct(array $videoIds, string $batchName = 'default')
    {
        $this->videoIds = $videoIds;
        $this->batchName = $batchName;
    }

    public function handle(): void
    {
        Log::info("Starting sync job for batch: {$this->batchName}", [
            'video_count' => count($this->videoIds)
        ]);

        try {
            // Process dalam chunk 25 video
            $chunks = array_chunk($this->videoIds, 25);
            $synced = 0;
            $errors = 0;

            foreach ($chunks as $chunkIndex => $chunk) {
                try {
                    $response = Http::timeout(30)
                        ->retry(2, 1000)
                        ->get('https://www.googleapis.com/youtube/v3/videos', [
                            'part' => 'statistics',
                            'id' => implode(',', $chunk),
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
                        $errors += count($chunk);
                        Log::warning("API request failed for chunk {$chunkIndex}", [
                            'response' => $response->body()
                        ]);
                    }

                    // Rate limiting
                    if (count($chunks) > 1) {
                        sleep(1);
                    }

                } catch (\Exception $e) {
                    $errors += count($chunk);
                    Log::error("Error processing chunk {$chunkIndex}: " . $e->getMessage());
                }
            }

            Log::info("Sync job completed for batch: {$this->batchName}", [
                'synced' => $synced,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error("Sync job failed for batch: {$this->batchName}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Sync job permanently failed for batch: {$this->batchName}", [
            'error' => $exception->getMessage()
        ]);
    }
}