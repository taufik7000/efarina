<?php

namespace App\Http\Controllers;

use App\Models\YoutubeVideo;
use App\Models\VideoCategory;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    /**
     * Display video index page at /video
     */
    public function index(Request $request)
    {
        $query = YoutubeVideo::active()
            ->with('category')
            ->select(['id', 'video_id', 'title', 'description', 'custom_description', 'thumbnail_url', 'published_at', 'view_count', 'duration_seconds', 'video_category_id', 'is_featured'])
            ->orderBy('published_at', 'desc'); // Video terbaru dahulu berdasarkan tanggal publish

        // Sort options
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('view_count', 'desc')->orderBy('published_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('published_at', 'asc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('published_at', 'desc');
                break;
        }

        // Filter by category if specified
        if ($request->has('category') && $request->category) {
            $category = VideoCategory::where('slug', $request->category)->first();
            if ($category) {
                $query->where('video_category_id', $category->id);
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('custom_description', 'like', "%{$search}%");
            });
        }

        // Optimized pagination with custom per page
        $perPage = $request->get('per_page', 12);
        $perPage = in_array($perPage, [6, 12, 24, 48]) ? $perPage : 12;
        
        $videos = $query->paginate($perPage)->withQueryString();
        
        // Get categories for filter - optimized query
        $categories = VideoCategory::active()
            ->select(['id', 'nama_kategori', 'slug', 'color'])
            ->withCount(['videos' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->orderBy('nama_kategori')
            ->get();

        // Featured videos for hero section - optimized query
        $featuredVideos = YoutubeVideo::active()
            ->featured()
            ->select(['id', 'video_id', 'title', 'description', 'custom_description', 'thumbnail_url', 'published_at', 'view_count', 'duration_seconds', 'video_category_id'])
            ->with(['category:id,nama_kategori,color'])
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        // Pagination info
        $paginationInfo = [
            'current_page' => $videos->currentPage(),
            'last_page' => $videos->lastPage(),
            'per_page' => $videos->perPage(),
            'total' => $videos->total(),
            'from' => $videos->firstItem(),
            'to' => $videos->lastItem(),
        ];

        return view('videos.index', compact('videos', 'categories', 'featuredVideos', 'paginationInfo', 'sort', 'perPage'));
    }

    /**
     * Display single video at /video/{video_id}
     */
    public function show($videoId)
    {
        $video = YoutubeVideo::active()
            ->with('category')
            ->where('video_id', $videoId)
            ->firstOrFail();

        // Related videos from same category
        $relatedVideos = YoutubeVideo::active()
            ->where('video_id', '!=', $video->video_id)
            ->when($video->category, function($query) use ($video) {
                return $query->where('video_category_id', $video->video_category_id);
            })
            ->latest()
            ->limit(8)
            ->get();

        // Latest videos if no related videos
        if ($relatedVideos->isEmpty()) {
            $relatedVideos = YoutubeVideo::active()
                ->where('video_id', '!=', $video->video_id)
                ->latest()
                ->limit(8)
                ->get();
        }

        return view('videos.show', compact('video', 'relatedVideos'));
    }

    /**
     * API endpoint for video data (if needed for AJAX)
     */
    public function apiIndex(Request $request)
    {
        $query = YoutubeVideo::active()->with('category');

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $videos = $query->latest()->paginate(12);

        return response()->json($videos);
    }
}