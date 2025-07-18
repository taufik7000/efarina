<?php

namespace App\Console\Commands;

use App\Services\YouTubeService;
use App\Models\YoutubeVideo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportFromPlaylist extends Command
{
    protected $signature = 'youtube:import-playlist 
                            {--channel= : YouTube Channel ID}
                            {--playlist= : Specific playlist ID (optional)}
                            {--max=5000 : Maximum videos to import}
                            {--batch=50 : Videos per batch}
                            {--delay=2 : Delay between batches in seconds}
                            {--force : Force reimport existing videos}';

    protected $description = 'Import ALL videos from channel uploads playlist';

    private YouTubeService $youtubeService;

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

        // Get uploads playlist ID jika tidak disediakan
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

        return $this->importPlaylistVideos($playlistId, $playlistInfo['video_count']);
    }

    private function getUploadsPlaylistId(string $channelId): ?string
    {
        try {
            $response = Http::timeout(10)->get('https://www.googleapis.com/youtube/v3/channels', [
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
            $response = Http::timeout(10)->get('https://www.googleapis.com/youtube/v3/playlists', [
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

    private function importPlaylistVideos(string $playlistId, int $totalVideos): int
    {
        $maxVideos = $this->option('max');
        $batchSize = $this->option('batch');
        $delay = $this->option('delay');
        $force = $this->option('force');

        $this->info("Starting playlist import (max: {$maxVideos} videos)");
        $this->info("Batch size: {$batchSize}, Delay: {$delay}s");
        $this->line('');

        $importedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $nextPageToken = null;
        $batchNumber = 1;

        $progressBar = $this->output->createProgressBar(min($maxVideos, $totalVideos));
        $progressBar->start();

        try {
            do {
                $this->line('');
                $this->info("Processing batch #{$batchNumber}...");

                // Get video IDs dari playlist
                $result = $this->getPlaylistVideoIds($playlistId, $batchSize, $nextPageToken);
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
                            $video = $this->saveVideoToDatabase($videoData, $force);
                            if ($video) {
                                $importedCount++;
                                $progressBar->advance();
                                
                                // Show sample video titles
                                if ($importedCount <= 10) {
                                    $this->line("  + {$video->title} ({$video->formatted_view_count} views)");
                                }
                            }
                        } catch (\Exception $e) {
                            $errorCount++;
                            $this->error("Error saving video {$videoData['video_id']}: " . $e->getMessage());
                        }
                    }
                    
                    $this->info("Imported " . count($videoDetails) . " new videos from batch #{$batchNumber}");
                } else {
                    $this->info("No new videos to import in batch #{$batchNumber}");
                    // Skip progress advance untuk existing videos
                    $progressBar->advance(count($result['videoIds']));
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
                if ($batchNumber > 500) {
                    $this->warn("Reached batch limit (500 batches)");
                    break;
                }

            } while ($nextPageToken && ($importedCount + $skippedCount) < $maxVideos);

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
            
            // Show database stats
            $totalInDb = YoutubeVideo::count();
            $this->info("📊 Total videos in database: {$totalInDb}");
        } else {
            $this->warn("⚠️  No new videos were imported");
        }

        return 0;
    }

    private function getPlaylistVideoIds(string $playlistId, int $maxResults, ?string $pageToken = null): array
    {
        $params = [
            'part' => 'contentDetails',
            'playlistId' => $playlistId,
            'maxResults' => min($maxResults, 50), // API limit
            'key' => config('services.youtube.api_key'),
        ];

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $response = Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/playlistItems', $params);

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

    private function getVideoDetails(array $videoIds): array
    {
        if (empty($videoIds)) {
            return [];
        }

        $chunks = array_chunk($videoIds, 50);
        $allVideoDetails = [];

        foreach ($chunks as $chunk) {
            $videoIdsString = implode(',', $chunk);

            $response = Http::timeout(30)->get('https://www.googleapis.com/youtube/v3/videos', [
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
                sleep(1);
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