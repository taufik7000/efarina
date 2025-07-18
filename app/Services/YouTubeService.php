<?php

namespace App\Services;

use App\Models\YoutubeVideo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class YouTubeService
{
    private string $apiKey;
    private string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
        
        if (!$this->apiKey) {
            throw new \Exception('YouTube API key not configured. Please set YOUTUBE_API_KEY in .env file.');
        }
    }

    /**
     * Import SEMUA video dari channel (dengan pagination)
     */
    public function importAllChannelVideos(string $channelId, int $maxTotal = 500): array
    {
        try {
            Log::info("Starting FULL import from channel: {$channelId} (max: {$maxTotal})");
            
            $allVideos = [];
            $nextPageToken = null;
            $totalImported = 0;
            $batchSize = 50; // Maximum per request
            
            do {
                // Step 1: Get video IDs dengan pagination
                $result = $this->getChannelVideoIdsPaginated($channelId, $batchSize, $nextPageToken);
                $videoIds = $result['videoIds'];
                $nextPageToken = $result['nextPageToken'];
                
                if (empty($videoIds)) {
                    Log::info("No more videos found. Stopping import.");
                    break;
                }

                Log::info("Processing batch of " . count($videoIds) . " videos");

                // Step 2: Get details untuk batch ini
                $videoDetails = $this->getVideoDetails($videoIds);
                
                // Step 3: Simpan ke database
                foreach ($videoDetails as $videoData) {
                    $video = $this->saveVideoToDatabase($videoData, $channelId);
                    if ($video) {
                        $allVideos[] = $video;
                        $totalImported++;
                    }
                }

                // Check limit
                if ($totalImported >= $maxTotal) {
                    Log::info("Reached maximum limit of {$maxTotal} videos. Stopping.");
                    break;
                }

                // Rate limiting - pause 1 detik antar batch
                sleep(1);

            } while ($nextPageToken && $totalImported < $maxTotal);

            Log::info("FULL import completed. Total imported: {$totalImported} videos from channel: {$channelId}");
            return $allVideos;

        } catch (\Exception $e) {
            Log::error("Error in full import from channel {$channelId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Import video terbatas (untuk update rutin)
     */
    public function importRecentVideos(string $channelId, int $maxResults = 10): array
    {
        try {
            Log::info("Starting recent import from channel: {$channelId}");
            
            // Get video IDs terbaru
            $result = $this->getChannelVideoIdsPaginated($channelId, $maxResults);
            $videoIds = $result['videoIds'];
            
            if (empty($videoIds)) {
                Log::warning("No recent videos found for channel: {$channelId}");
                return [];
            }

            // Get detail dan simpan
            $videoDetails = $this->getVideoDetails($videoIds);
            
            $importedVideos = [];
            foreach ($videoDetails as $videoData) {
                $video = $this->saveVideoToDatabase($videoData, $channelId);
                if ($video) {
                    $importedVideos[] = $video;
                }
            }

            Log::info("Recent import completed. Imported " . count($importedVideos) . " videos from channel: {$channelId}");
            return $importedVideos;

        } catch (\Exception $e) {
            Log::error("Error importing recent videos from channel {$channelId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get video IDs dengan pagination support
     */
    private function getChannelVideoIdsPaginated(string $channelId, int $maxResults = 50, ?string $pageToken = null): array
    {
        $params = [
            'part' => 'id',
            'channelId' => $channelId,
            'type' => 'video',
            'order' => 'date',
            'maxResults' => min($maxResults, 50), // API limit 50
            'key' => $this->apiKey,
        ];

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $response = Http::timeout(30)->get("{$this->baseUrl}/search", $params);

        if (!$response->successful()) {
            Log::error("YouTube API error: " . $response->body());
            throw new \Exception("Failed to fetch videos from channel: " . $response->status());
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

    /**
     * Get channel statistics
     */
    public function getChannelStats(string $channelId): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/channels", [
                'part' => 'statistics',
                'id' => $channelId,
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['items'])) {
                    $stats = $data['items'][0]['statistics'];
                    return [
                        'subscriber_count' => (int) ($stats['subscriberCount'] ?? 0),
                        'video_count' => (int) ($stats['videoCount'] ?? 0),
                        'view_count' => (int) ($stats['viewCount'] ?? 0),
                    ];
                }
            }

            return ['subscriber_count' => 0, 'video_count' => 0, 'view_count' => 0];
        } catch (\Exception $e) {
            Log::error("Error getting channel stats {$channelId}: " . $e->getMessage());
            return ['subscriber_count' => 0, 'video_count' => 0, 'view_count' => 0];
        }
    }

    /**
     * Sync existing video untuk update view count, dll
     */
    public function syncExistingVideos(int $limit = 50): int
    {
        $videos = YoutubeVideo::active()
            ->where(function ($query) {
                $query->whereNull('last_sync_at')
                      ->orWhere('last_sync_at', '<', now()->subHours(24));
            })
            ->limit($limit)
            ->get();

        $synced = 0;
        $videoIds = $videos->pluck('video_id')->toArray();

        if (!empty($videoIds)) {
            try {
                $videoDetails = $this->getVideoDetails($videoIds);
                
                foreach ($videoDetails as $videoData) {
                    $video = $videos->firstWhere('video_id', $videoData['video_id']);
                    if ($video) {
                        $video->updateFromApiData($videoData);
                        $synced++;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error syncing videos: " . $e->getMessage());
            }
        }

        Log::info("Synced {$synced} existing videos");
        return $synced;
    }

    /**
     * Get detail video berdasarkan video IDs
     */
    private function getVideoDetails(array $videoIds): array
    {
        if (empty($videoIds)) {
            return [];
        }

        // YouTube API bisa handle maksimal 50 video IDs per request
        $chunks = array_chunk($videoIds, 50);
        $allVideoDetails = [];

        foreach ($chunks as $chunk) {
            $videoIdsString = implode(',', $chunk);

            $response = Http::timeout(30)->get("{$this->baseUrl}/videos", [
                'part' => 'snippet,statistics,contentDetails',
                'id' => $videoIdsString,
                'key' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                Log::error("YouTube API error: " . $response->body());
                continue; // Skip chunk ini, lanjut ke berikutnya
            }

            $data = $response->json();
            
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $allVideoDetails[] = $this->formatVideoData($item);
                }
            }

            // Rate limiting
            if (count($chunks) > 1) {
                sleep(1);
            }
        }

        return $allVideoDetails;
    }

    /**
     * Format data video dari API response
     */
    private function formatVideoData(array $apiData): array
    {
        $snippet = $apiData['snippet'];
        $statistics = $apiData['statistics'] ?? [];
        $contentDetails = $apiData['contentDetails'] ?? [];

        // Get thumbnail dengan prioritas resolusi tertinggi
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
            'published_at' => Carbon::parse($snippet['publishedAt']),
            'duration_iso' => $contentDetails['duration'] ?? null,
            'duration_seconds' => $this->convertDurationToSeconds($contentDetails['duration'] ?? 'PT0S'),
            'view_count' => (int) ($statistics['viewCount'] ?? 0),
            'like_count' => (int) ($statistics['likeCount'] ?? 0),
            'tags' => $snippet['tags'] ?? [],
        ];
    }

    /**
     * Simpan video ke database
     */
    private function saveVideoToDatabase(array $videoData, string $channelId): ?YoutubeVideo
    {
        try {
            // Cek apakah video sudah ada
            $existingVideo = YoutubeVideo::findByVideoId($videoData['video_id']);
            
            if ($existingVideo) {
                // Update existing video
                $existingVideo->updateFromApiData($videoData);
                Log::debug("Updated existing video: {$videoData['title']}");
                return $existingVideo;
            } else {
                // Create new video
                $video = YoutubeVideo::createFromApiData($videoData);
                Log::info("Created new video: {$videoData['title']}");
                return $video;
            }

        } catch (\Exception $e) {
            Log::error("Error saving video {$videoData['video_id']}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Convert ISO 8601 duration ke seconds
     */
    private function convertDurationToSeconds(string $duration): int
    {
        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches);
        
        $hours = (int) ($matches[1] ?? 0);
        $minutes = (int) ($matches[2] ?? 0);
        $seconds = (int) ($matches[3] ?? 0);
        
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    /**
     * Validate channel ID
     */
    public function validateChannelId(string $channelId): bool
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/channels", [
                'part' => 'id',
                'id' => $channelId,
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return !empty($data['items']);
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Error validating channel {$channelId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get channel info
     */
    public function getChannelInfo(string $channelId): ?array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/channels", [
                'part' => 'snippet,statistics',
                'id' => $channelId,
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['items'])) {
                    $channel = $data['items'][0];
                    return [
                        'id' => $channel['id'],
                        'title' => $channel['snippet']['title'],
                        'description' => $channel['snippet']['description'] ?? '',
                        'thumbnail' => $channel['snippet']['thumbnails']['high']['url'] ?? null,
                        'subscriber_count' => (int) ($channel['statistics']['subscriberCount'] ?? 0),
                        'video_count' => (int) ($channel['statistics']['videoCount'] ?? 0),
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error getting channel info {$channelId}: " . $e->getMessage());
            return null;
        }
    }


    public function importVideo(string $videoId): ?YoutubeVideo
{
    try {
        $videoDetails = $this->fetchVideoDetails($videoId);
        
        if (!$videoDetails) {
            return null;
        }

        return $this->saveVideoToDatabase($videoDetails, $videoDetails['channel_id']);

    } catch (\Exception $e) {
        Log::error("Error importing single video {$videoId}: " . $e->getMessage());
        return null;
    }
}

/**
 * Fetch video details from YouTube API (single video)
 */
public function fetchVideoDetails(string $videoId): ?array
{
    try {
        $response = Http::timeout(30)->get("{$this->baseUrl}/videos", [
            'part' => 'snippet,statistics,contentDetails',
            'id' => $videoId,
            'key' => $this->apiKey,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            if (empty($data['items'])) {
                return null;
            }

            $video = $data['items'][0];
            return $this->formatVideoData($video);
        }

        Log::error('YouTube API error: ' . $response->body());
        return null;

    } catch (\Exception $e) {
        Log::error('YouTube API exception: ' . $e->getMessage());
        return null;
    }
}
}