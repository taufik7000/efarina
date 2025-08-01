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
            ->orderBy('videos_count', 'desc')
            ->orderBy('nama_kategori')
            ->get();

        // Featured videos for hero section - optimized query
        $featuredVideos = YoutubeVideo::active()
            ->featured()
            ->select(['id', 'video_id', 'title', 'description', 'thumbnail_url', 'published_at', 'view_count', 'duration_seconds', 'video_category_id'])
            ->with(['category:id,nama_kategori,color'])
            ->orderBy('published_at', 'desc')
            ->limit(5)
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

        if ($request->has('exclude')) {
            $query->where('video_id', '!=', $request->exclude);
        }
         
        $videos = $query->paginate(12);

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

        $featuredVideos = YoutubeVideo::active()
            ->featured()
            ->with('category')
            ->latest('published_at')
            ->limit(5)
            ->get();

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

        return view('videos.show', compact('video', 'relatedVideos', 'featuredVideos'));
    }

    public function live()
    {
        $videos = YoutubeVideo::active()
            ->with('category')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        // KOREKSI DI SINI: Ubah nama key agar sesuai dengan yang diharapkan komponen
        $paginationInfo = [
            'from'  => $videos->firstItem(), // Sebelumnya 'firstItem'
            'to'    => $videos->lastItem(),  // Sebelumnya 'lastItem'
            'total' => $videos->total(),
        ];

        $featuredVideos = YoutubeVideo::active()
            ->featured()
            ->with('category')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('videos.live', [
            'videos'          => $videos,
            'featuredVideos'  => $featuredVideos,
            'paginationInfo'  => $paginationInfo,
        ]);
    }

    /**
     * API endpoint for video data (if needed for AJAX)
     */
    public function apiIndex(Request $request)
    {
        $query = YoutubeVideo::active()->with('category')->latest('published_at');

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


    public function apiRenderedGrid(Request $request)
    {
        try {
            $query = YoutubeVideo::active()->with('category')->latest('published_at');

            // Terapkan filter kategori jika ada
            if ($request->filled('category') && $request->category !== 'all') {
                $categorySlug = $request->category;
                // Menggunakan whereHas, cara yang lebih aman dan standar
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            }
            
            // Terapkan filter pencarian jika ada
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('title', 'like', "%{$search}%");
            }

            $videos = $query->paginate(12)->withQueryString();

            // Siapkan info pagination
            $paginationInfo = [
                'from'  => $videos->firstItem(),
                'to'    => $videos->lastItem(),
                'total' => $videos->total(),
            ];

            // Render komponen menjadi HTML
            $html = view('videos.components.video-grid', ['videos' => $videos])->render();
            $paginationHtml = view('videos.components.pagination', ['videos' => $videos, 'paginationInfo' => $paginationInfo])->render();

            // Kembalikan JSON yang berisi HTML
            return response()->json([
                'html' => $html,
                'pagination_html' => $paginationHtml,
            ]);

        } catch (\Exception $e) {
            // Jika terjadi error, kirim respons error 500 dengan pesan
            // Ini akan lebih mudah di-debug di console browser
            return response()->json([
                'error' => 'Terjadi kesalahan pada server.',
                'message' => $e->getMessage() // Pesan error sesungguhnya
            ], 500);
        }
    }
}