<?php

namespace App\Console\Commands;

use App\Services\YouTubeService;
use App\Models\YoutubeVideo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportFromPlaylistResilient extends Command
{
    protected $signature = 'youtube:import-resilient 
                            {--channel= : YouTube Channel ID}
                            {--playlist= : Specific playlist ID (optional)}
                            {--max=5000 : Maximum videos to import}
                            {--batch=25 : Videos per batch (reduced for stability)}
                            {--delay=3 : Delay between batches in seconds}
                            {--retry=3 : Number of retries for failed batches}
                            {--timeout=60 : HTTP timeout in seconds}
                            {--resume : Resume from last successful batch}
                            {--force : Force reimport existing videos}';

    protected $description = 'Import ALL videos with retry mechanism and recovery';

    private YouTubeService $youtubeService;
    private array $failedBatches = [];
    private int $resumeFromBatch = 1;

    public function __construct(YouTubeService $youtubeService)
    {
        parent::__construct();
        $this->youtubeService = $youtubeService;
    }

    public function handle()
    {
        $channelId = $this->option('channel') ?: config('services.youtube.default_channel_id');
        $playlistId = $this->option('playlist');
        
        if (!$channelId && !$playlistId) {
            $this->error('Channel ID or Playlist ID is required');
            return 1;
        }

        // Resume functionality
        if ($this->option('resume')) {
            $this->resumeFromBatch = $this->getLastSuccessfulBatch();
            $this->info("Resuming from batch #{$this->resumeFromBatch}");
        }

        // Get uploads playlist ID
        if (!$playlistId) {
            $this->info("Getting uploads playlist for channel: {$channelId}");
            $playlistId = $this->getUploadsPlaylistId($channelId);
            
            if (!$playlistId) {
                $this->error('Could not find uploads playlist for this channel');
                return 1;
            }
        }

        $this->info("Using playlist ID: {$playlistId}");
        
        // Get playlist info
        $playlistInfo = $this->getPlaylistInfo($playlistId);
        if (!$playlistInfo) {
            $this->error('Could not get playlist information');
            return 1;
        }

        $this->info("Playlist: {$playlistInfo['title']}");
        $this->info("Total Videos: " . number_format($playlistInfo['video_count']));
        $this->line('');

        return $this->importPlaylistVideosResilient($playlistId, $playlistInfo['video_count']);
    }

    private function importPlaylistVideosResilient(string $playlistId, int $totalVideos): int
    {
        $maxVideos = $this->option('max');
        $batchSize = $this->option('batch'); // Reduced to 25 for stability
        $delay = $this->option('delay');
        $maxRetries = $this->option('retry');
        $timeout = $this->option('timeout');
        $force = $this->option('force');

        $this->info("Starting resilient import (max: {$maxVideos} videos)");
        $this->info("Batch size: {$batchSize}, Delay: {$delay}s, Timeout: {$timeout}s");
        $this->info("Max retries per batch: {$maxRetries}");
        $this->line('');

        $importedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $nextPageToken = null;
        $batchNumber = 1;

        // Skip to resume batch if needed
        if ($this->resumeFromBatch > 1) {
            $skipResult = $this->skipToResumePoint($playlistId, $batchSize);
            $nextPageToken = $skipResult['nextPageToken'];
            $batchNumber = $this->resumeFromBatch;
            $this->info("Skipped to batch #{$batchNumber}");
        }

        $progressBar = $this->output->createProgressBar(min($maxVideos, $totalVideos));
        $progressBar->start();

        try {
            do {
                $this->line('');
                $this->info("Processing batch #{$batchNumber}...");

                $batchSuccess = false;
                $retryCount = 0;

                // Retry mechanism untuk setiap batch
                while (!$batchSuccess && $retryCount < $maxRetries) {
                    try {
                        if ($retryCount > 0) {
                            $this->warn("Retry #{$retryCount} for batch #{$batchNumber}");
                            sleep($delay * $retryCount); // Exponential backoff
                        }

                        // Get video IDs dengan timeout yang lebih besar
                        $result = $this->getPlaylistVideoIdsWithRetry($playlistId, $batchSize, $nextPageToken, $timeout);
                        $videoIds = $result['videoIds'];
                        $nextPageTokenNew = $result['nextPageToken'];

                        if (empty($videoIds)) {
                            $this->warn("No more videos found in batch #{$batchNumber}");
                            $batchSuccess = true;
                            break;
                        }

                        $this->info("Found " . count($videoIds) . " videos in this batch");

                        // Filter existing videos
                        if (!$force) {
                            $existingVideoIds = YoutubeVideo::whereIn('video_id', $videoIds)->pluck('video_id')->toArray();
                            $newVideoIds = array_diff($videoIds, $existingVideoIds);
                            
                            if (!empty($existingVideoIds)) {
                                $skippedCount += count($existingVideoIds);
                                $this->info("Skipped " . count($existingVideoIds) . " existing videos");
                            }
                            
                            $videoIds = $newVideoIds;
                        }

                        if (!empty($videoIds)) {
                            // Get video details dengan retry
                            $videoDetails = $this->getVideoDetailsWithRetry($videoIds, $timeout, $maxRetries);
                            
                            // Save to database
                            foreach ($videoDetails as $videoData) {
                                try {
                                    $video = $this->saveVideoToDatabase($videoData, $force);
                                    if ($video) {
                                        $importedCount++;
                                        $progressBar->advance();
                                        
                                        // Show sample video titles
                                        if ($importedCount <= 5 || $importedCount % 100 == 0) {
                                            $this->line("  + {$video->title} ({$video->formatted_view_count} views)");
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $errorCount++;
                                    $this->error("Error saving video {$videoData['video_id']}: " . $e->getMessage());
                                }
                            }
                            
                            $this->info("✅ Imported " . count($videoDetails) . " new videos from batch #{$batchNumber}");
                        } else {
                            $this->info("No new videos to import in batch #{$batchNumber}");
                            $progressBar->advance(count($result['videoIds']));
                        }

                        // Update nextPageToken only if batch successful
                        $nextPageToken = $nextPageTokenNew;
                        $batchSuccess = true;

                        // Save progress
                        $this->saveProgress($batchNumber, $importedCount, $skippedCount, $errorCount);

                    } catch (\Exception $e) {
                        $retryCount++;
                        $this->error("Batch #{$batchNumber} failed (attempt {$retryCount}): " . $e->getMessage());
                        
                        if ($retryCount >= $maxRetries) {
                            $this->error("Max retries reached for batch #{$batchNumber}. Skipping...");
                            $this->failedBatches[] = $batchNumber;
                            $batchSuccess = true; // Skip this batch
                        }
                    }
                }

                // Check limits
                if ($importedCount >= $maxVideos) {
                    $this->info("Reached maximum limit of {$maxVideos} videos");
                    break;
                }

                // Delay antar batch
                if ($nextPageToken && $delay > 0) {
                    $this->info("Waiting {$delay} seconds before next batch...");
                    sleep($delay);
                }

                $batchNumber++;

                // Safety limit
                if ($batchNumber > 1000) {
                    $this->warn("Reached batch limit (1000 batches)");
                    break;
                }

            } while ($nextPageToken && ($importedCount + $skippedCount) < $maxVideos);

        } catch (\Exception $e) {
            $progressBar->finish();
            $this->error("Critical import failure: " . $e->getMessage());
            $this->saveProgress($batchNumber, $importedCount, $skippedCount, $errorCount);
            return 1;
        }

        $progressBar->finish();
        $this->line('');
        $this->line('');

        // Summary
        $this->info("=== IMPORT SUMMARY ===");
        $this->info("Total Imported: {$importedCount} videos");
        $this->info("Skipped (existing): {$skippedCount} videos");
        $this->info("Errors: {$errorCount} videos");
        $this->info("Batches processed: " . ($batchNumber - 1));
        
        if (!empty($this->failedBatches)) {
            $this->warn("Failed batches: " . implode(', ', $this->failedBatches));
            $this->info("You can retry failed batches manually or resume later");
        }
        
        if ($importedCount > 0) {
            $this->info("✅ Import completed successfully!");
            
            $totalInDb = YoutubeVideo::count();
            $this->info("📊 Total videos in database: {$totalInDb}");
        } else {
            $this->warn("⚠️  No new videos were imported");
        }

        // Clear progress file on successful completion
        $this->clearProgress();

        return 0;
    }

    private function getPlaylistVideoIdsWithRetry(string $playlistId, int $maxResults, ?string $pageToken, int $timeout): array
    {
        $params = [
            'part' => 'contentDetails',
            'playlistId' => $playlistId,
            'maxResults' => min($maxResults, 50),
            'key' => config('services.youtube.api_key'),
        ];

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $response = Http::timeout($timeout)
            ->retry(3, 1000) // 3 retries with 1 second delay
            ->get('https://www.googleapis.com/youtube/v3/playlistItems', $params);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch playlist items: " . $response->status() . " - " . $response->body());
        }

        $data = $response->json();
        
        $videoIds = [];
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['contentDetails']['videoId'])) {
                    $videoIds[] = $item['contentDetails']['videoId'];
                }
            }
        }

        return [
            'videoIds' => $videoIds,
            'nextPageToken' => $data['nextPageToken'] ?? null,
            'totalResults' => $data['pageInfo']['totalResults'] ?? 0,
        ];
    }

    private function getVideoDetailsWithRetry(array $videoIds, int $timeout, int $maxRetries): array
    {
        if (empty($videoIds)) {
            return [];
        }

        // Smaller chunks for better reliability
        $chunks = array_chunk($videoIds, 25);
        $allVideoDetails = [];

        foreach ($chunks as $chunkIndex => $chunk) {
            $videoIdsString = implode(',', $chunk);
            $retryCount = 0;
            $success = false;

            while (!$success && $retryCount < $maxRetries) {
                try {
                    if ($retryCount > 0) {
                        $this->info("  Retrying chunk " . ($chunkIndex + 1) . " (attempt " . ($retryCount + 1) . ")");
                        sleep($retryCount * 2); // Exponential backoff
                    }

                    $response = Http::timeout($timeout)
                        ->retry(2, 1000)
                        ->get('https://www.googleapis.com/youtube/v3/videos', [
                            'part' => 'snippet,statistics,contentDetails',
                            'id' => $videoIdsString,
                            'key' => config('services.youtube.api_key'),
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        
                        if (!empty($data['items'])) {
                            foreach ($data['items'] as $item) {
                                $allVideoDetails[] = $this->formatVideoData($item);
                            }
                        }
                        $success = true;
                    } else {
                        throw new \Exception("HTTP " . $response->status() . ": " . $response->body());
                    }

                } catch (\Exception $e) {
                    $retryCount++;
                    $this->warn("  Chunk " . ($chunkIndex + 1) . " failed: " . $e->getMessage());
                    
                    if ($retryCount >= $maxRetries) {
                        $this->error("  Skipping chunk " . ($chunkIndex + 1) . " after {$maxRetries} retries");
                    }
                }
            }

            // Small delay between chunks
            if (count($chunks) > 1 && $success) {
                sleep(1);
            }
        }

        return $allVideoDetails;
    }

    private function saveProgress(int $batchNumber, int $imported, int $skipped, int $errors): void
    {
        $progress = [
            'last_batch' => $batchNumber,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
            'timestamp' => now()->toDateTimeString(),
        ];

        file_put_contents(storage_path('youtube_import_progress.json'), json_encode($progress));
    }

    private function getLastSuccessfulBatch(): int
    {
        $progressFile = storage_path('youtube_import_progress.json');
        
        if (file_exists($progressFile)) {
            $progress = json_decode(file_get_contents($progressFile), true);
            return ($progress['last_batch'] ?? 0) + 1;
        }

        return 1;
    }

    private function clearProgress(): void
    {
        $progressFile = storage_path('youtube_import_progress.json');
        if (file_exists($progressFile)) {
            unlink($progressFile);
        }
    }

    private function skipToResumePoint(string $playlistId, int $batchSize): array
    {
        $nextPageToken = null;
        $targetBatch = $this->resumeFromBatch;
        
        // Skip batches until we reach the resume point
        for ($i = 1; $i < $targetBatch; $i++) {
            $result = $this->getPlaylistVideoIdsWithRetry($playlistId, $batchSize, $nextPageToken, 30);
            $nextPageToken = $result['nextPageToken'];
            
            if (!$nextPageToken) {
                break;
            }
        }

        return ['nextPageToken' => $nextPageToken];
    }

    // Copy other methods from previous version...
    private function getUploadsPlaylistId(string $channelId): ?string
    {
        try {
            $response = Http::timeout(30)->retry(3, 1000)->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'contentDetails',
                'id' => $channelId,
                'key' => config('services.youtube.api_key'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['items'])) {
                    return $data['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? null;
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->error("Error getting uploads playlist: " . $e->getMessage());
            return null;
        }
    }

    private function getPlaylistInfo(string $playlistId): ?array
    {
        try {
            $response = Http::timeout(30)->retry(3, 1000)->get('https://www.googleapis.com/youtube/v3/playlists', [
                'part' => 'snippet,contentDetails',
                'id' => $playlistId,
                'key' => config('services.youtube.api_key'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['items'])) {
                    $playlist = $data['items'][0];
                    return [
                        'title' => $playlist['snippet']['title'],
                        'video_count' => $playlist['contentDetails']['itemCount'] ?? 0,
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->error("Error getting playlist info: " . $e->getMessage());
            return null;
        }
    }

    private function formatVideoData(array $apiData): array
    {
        $snippet = $apiData['snippet'];
        $statistics = $apiData['statistics'] ?? [];
        $contentDetails = $apiData['contentDetails'] ?? [];

        $thumbnails = $snippet['thumbnails'];
        $thumbnailUrl = $thumbnails['maxres']['url'] ?? 
                       $thumbnails['high']['url'] ?? 
                       $thumbnails['medium']['url'] ?? 
                       $thumbnails['default']['url'] ?? null;

        return [
            'video_id' => $apiData['id'],
            'channel_id' => $snippet['channelId'],
            'channel_title' => $snippet['channelTitle'],
            'title' => $snippet['title'],
            'description' => $snippet['description'] ?? '',
            'thumbnail_url' => $thumbnailUrl,
            'published_at' => \Carbon\Carbon::parse($snippet['publishedAt']),
            'duration_iso' => $contentDetails['duration'] ?? null,
            'duration_seconds' => $this->convertDurationToSeconds($contentDetails['duration'] ?? 'PT0S'),
            'view_count' => (int) ($statistics['viewCount'] ?? 0),
            'like_count' => (int) ($statistics['likeCount'] ?? 0),
            'tags' => $snippet['tags'] ?? [],
        ];
    }

    private function saveVideoToDatabase(array $videoData, bool $force = false): ?YoutubeVideo
    {
        $existingVideo = YoutubeVideo::findByVideoId($videoData['video_id']);
        
        if ($existingVideo && !$force) {
            return $existingVideo;
        }

        if ($existingVideo && $force) {
            $existingVideo->updateFromApiData($videoData);
            return $existingVideo;
        }

        return YoutubeVideo::createFromApiData($videoData);
    }

    private function convertDurationToSeconds(string $duration): int
    {
        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches);
        
        $hours = (int) ($matches[1] ?? 0);
        $minutes = (int) ($matches[2] ?? 0);
        $seconds = (int) ($matches[3] ?? 0);
        
        return $hours * 3600 + $minutes * 60 + $seconds;
    }
}