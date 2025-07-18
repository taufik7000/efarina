<?php

namespace App\Console\Commands;

use App\Services\YouTubeService;
use App\Models\YoutubeVideo;
use Illuminate\Console\Command;

class ImportYouTubeVideos extends Command
{
    protected $signature = 'youtube:import 
                            {--channel= : YouTube Channel ID to import from}
                            {--all : Import all videos from channel}
                            {--recent=10 : Import recent videos (default: 10)}
                            {--batch=50 : Videos per batch (default: 50)}
                            {--delay=2 : Delay between batches in seconds (default: 2)}
                            {--max=1000 : Maximum videos to import (default: 1000)}
                            {--force : Force reimport existing videos}';

    protected $description = 'Import YouTube videos from specified channel';

    private YouTubeService $youtubeService;

    public function __construct(YouTubeService $youtubeService)
    {
        parent::__construct();
        $this->youtubeService = $youtubeService;
    }

    public function handle()
    {
        $channelId = $this->option('channel') ?: config('services.youtube.default_channel_id');
        
        if (!$channelId) {
            $this->error('Channel ID is required. Use --channel option or set YOUTUBE_CHANNEL_ID in .env');
            return 1;
        }

        // Validate channel
        $this->info("Validating channel: {$channelId}");
        if (!$this->youtubeService->validateChannelId($channelId)) {
            $this->error('Invalid channel ID');
            return 1;
        }

        // Get channel info
        $channelInfo = $this->youtubeService->getChannelInfo($channelId);
        $channelStats = $this->youtubeService->getChannelStats($channelId);
        
        $this->info("Channel: {$channelInfo['title']}");
        $this->info("Total Videos: " . number_format($channelStats['video_count']));
        $this->info("Subscribers: " . number_format($channelStats['subscriber_count']));
        $this->line('');

        if ($this->option('all')) {
            return $this->importAllVideos($channelId, $channelStats['video_count']);
        } else {
            return $this->importRecentVideos($channelId);
        }
    }

    private function importAllVideos(string $channelId, int $totalVideos): int
    {
        $maxVideos = $this->option('max');
        $batchSize = $this->option('batch');
        $delay = $this->option('delay');
        $force = $this->option('force');

        $this->info("Starting FULL import (max: {$maxVideos} videos)");
        $this->info("Batch size: {$batchSize}, Delay: {$delay}s");
        $this->line('');

        $importedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $nextPageToken = null;
        $batchNumber = 1;

        $progressBar = $this->output->createProgressBar($maxVideos);
        $progressBar->start();

        try {
            do {
                $this->line('');
                $this->info("Processing batch #{$batchNumber}...");

                // Get video IDs untuk batch ini
                $result = $this->getChannelVideoIdsPaginated($channelId, $batchSize, $nextPageToken);
                $videoIds = $result['videoIds'];
                $nextPageToken = $result['nextPageToken'];

                if (empty($videoIds)) {
                    $this->warn("No more videos found in batch #{$batchNumber}");
                    break;
                }

                $this->info("Found " . count($videoIds) . " videos in this batch");

                // Filter existing videos jika tidak force
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
                    // Get video details
                    $videoDetails = $this->getVideoDetails($videoIds);
                    
                    // Save to database
                    foreach ($videoDetails as $videoData) {
                        try {
                            $video = $this->saveVideoToDatabase($videoData, $channelId, $force);
                            if ($video) {
                                $importedCount++;
                                $progressBar->advance();
                            }
                        } catch (\Exception $e) {
                            $errorCount++;
                            $this->error("Error saving video {$videoData['video_id']}: " . $e->getMessage());
                        }
                    }
                    
                    $this->info("Imported " . count($videoDetails) . " videos from batch #{$batchNumber}");
                } else {
                    $this->info("No new videos to import in batch #{$batchNumber}");
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

                // Safety limit - maksimal 100 batch
                if ($batchNumber > 100) {
                    $this->warn("Reached batch limit (100 batches). Consider increasing batch size.");
                    break;
                }

            } while ($nextPageToken && $importedCount < $maxVideos);

        } catch (\Exception $e) {
            $progressBar->finish();
            $this->error("Import failed: " . $e->getMessage());
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
        
        if ($importedCount > 0) {
            $this->info("✅ Import completed successfully!");
        } else {
            $this->warn("⚠️  No new videos were imported");
        }

        return 0;
    }

    private function importRecentVideos(string $channelId): int
    {
        $maxRecent = $this->option('recent');
        
        $this->info("Importing {$maxRecent} recent videos...");
        
        try {
            $videos = $this->youtubeService->importRecentVideos($channelId, $maxRecent);
            
            $this->info("Successfully imported " . count($videos) . " recent videos");
            
            foreach ($videos as $video) {
                $this->line("- {$video->title} ({$video->formatted_view_count} views)");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Recent import failed: " . $e->getMessage());
            return 1;
        }
    }

    private function getChannelVideoIdsPaginated(string $channelId, int $maxResults, ?string $pageToken = null): array
    {
        $params = [
            'part' => 'id',
            'channelId' => $channelId,
            'type' => 'video',
            'order' => 'date',
            'maxResults' => min($maxResults, 50),
            'key' => config('services.youtube.api_key'),
        ];

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $response = \Illuminate\Support\Facades\Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/search', $params);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch videos: " . $response->status() . " - " . $response->body());
        }

        $data = $response->json();
        
        $videoIds = [];
        if (!empty($data['items'])) {
            $videoIds = collect($data['items'])->pluck('id.videoId')->toArray();
        }

        return [
            'videoIds' => $videoIds,
            'nextPageToken' => $data['nextPageToken'] ?? null,
            'totalResults' => $data['pageInfo']['totalResults'] ?? 0,
        ];
    }

    private function getVideoDetails(array $videoIds): array
    {
        if (empty($videoIds)) {
            return [];
        }

        $chunks = array_chunk($videoIds, 50);
        $allVideoDetails = [];

        foreach ($chunks as $chunk) {
            $videoIdsString = implode(',', $chunk);

            $response = \Illuminate\Support\Facades\Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/videos', [
                'part' => 'snippet,statistics,contentDetails',
                'id' => $videoIdsString,
                'key' => config('services.youtube.api_key'),
            ]);

            if (!$response->successful()) {
                $this->warn("Failed to get details for chunk: " . $response->status());
                continue;
            }

            $data = $response->json();
            
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $allVideoDetails[] = $this->formatVideoData($item);
                }
            }

            if (count($chunks) > 1) {
                sleep(1); // Rate limiting
            }
        }

        return $allVideoDetails;
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

    private function saveVideoToDatabase(array $videoData, string $channelId, bool $force = false): ?YoutubeVideo
    {
        $existingVideo = YoutubeVideo::findByVideoId($videoData['video_id']);
        
        if ($existingVideo && !$force) {
            return $existingVideo; // Skip jika sudah ada dan tidak force
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