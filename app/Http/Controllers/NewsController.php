<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\YoutubeVideo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $query = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->latest();


        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('ringkasan', 'like', "%{$search}%")
                  ->orWhere('konten', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $news = $query->paginate(12);
        
        // Get categories for filter
        $categories = NewsCategory::active()
            ->ordered()
            ->withCount(['news' => function ($query) {
                $query->where('status', 'published');
            }])
            ->get();
        
        $featuredVideos = YoutubeVideo::active()
            ->featured()
            ->with('category')
            ->latest('published_at')
            ->limit(5)
            ->get();

        $popularNews = News::published()->orderBy('views_count', 'desc')->limit(5)->get();

        return view('news.index', compact('news', 'categories', 'popularNews', 'featuredVideos'));
    }

    public function show(News $news): View
    {
        // Check if news is published
        if ($news->status !== 'published') {
            abort(404);
        }

        // Load relationships
        $news->load(['category', 'tags', 'author']);

        // Get related news
        $relatedNews = News::with(['category', 'author'])
            ->where('status', 'published')
            ->where('id', '!=', $news->id)
            ->where('news_category_id', $news->news_category_id)
            ->latest()
            ->limit(4)
            ->get();

        // Get popular news
        $popularNews = News::with(['category', 'author'])
            ->where('status', 'published')
            ->where('id', '!=', $news->id)
            ->orderBy('views_count', 'desc')
            ->limit(10)
            ->get();
            
        $featuredVideos = YoutubeVideo::active()
            ->featured()
            ->with('category')
            ->latest('published_at')
            ->limit(5)
            ->get();
        
        $latestVideos = YoutubeVideo::active()
            ->with('category')
            ->latest('published_at')
            ->limit(6)
            ->get();

        return view('news.show', compact('news', 'relatedNews', 'popularNews', 'featuredVideos', 'latestVideos'));
    }

    public function apiRelatedNews(Request $request)
    {
        // Validasi request (category_id dihapus)
        $request->validate([
            'exclude_id' => 'required|integer|exists:news,id',
        ]);

        $relatedNews = News::with(['category:id,nama_kategori,slug,color', 'author:id,name'])
            ->select('id', 'slug', 'judul', 'thumbnail', 'excerpt', 'published_at', 'news_category_id', 'author_id')
            ->where('status', 'published')
            ->where('id', '!=', $request->exclude_id)
            // ->where('news_category_id', $request->category_id) // <-- HAPUS BARIS INI
            ->latest('published_at')
            ->paginate(4);

        if ($relatedNews->isEmpty()) {
            return response('');
        }

        return view('news.components.related-news-items', ['newsItems' => $relatedNews]);
    }

    public function category(NewsCategory $category): View
    {
        if (!$category->is_active) {
            abort(404);
        }

        $heroNews = News::published()
            ->with(['category', 'author'])
            ->where('news_category_id', $category->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        // Ambil ID dari berita yang sudah tampil di hero section
        $heroNewsIds = $heroNews->pluck('id');

        $news = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->where('news_category_id', $category->id)
            ->latest('published_at') // Pastikan diurutkan berdasarkan tanggal publish
            ->paginate(10); // Ambil 10 berita pertama

        $popularNews = News::published()
            ->where('id', '!=', $news->pluck('id')->first()) // Jangan tampilkan berita yang sudah ada
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();
            
        $featuredVideos = YoutubeVideo::active()
            ->featured()
            ->with('category')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('news.category', compact('news', 'category', 'heroNews', 'popularNews', 'featuredVideos'));
    }

    public function tag(NewsTag $tag): View
    {
        // Check if tag is active
        if (!$tag->is_active) {
            abort(404);
        }

        $news = $tag->news()
            ->with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->latest()
            ->paginate(12);

        return view('news.tag', compact('news', 'tag'));
    }

    public function incrementView(News $news): JsonResponse
    {
        // Only increment for published news
        if ($news->status === 'published') {
            $news->increment('views_count');
            return response()->json(['success' => true, 'views' => $news->views_count]);
        }

        return response()->json(['success' => false], 400);
    }
    
    public function apiLoadMoreIndex(Request $request)
    {
        // Validasi
        $request->validate([
            'page' => 'required|integer|min:1',
            'category' => 'nullable|string', // category slug
        ]);

        $query = News::published()->with(['category', 'author'])->latest('published_at');

        // Filter berdasarkan kategori jika ada (dan bukan 'all')
        if ($request->filled('category') && $request->category !== 'all') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        
        // Ambil data menggunakan paginate
        $newsItems = $query->paginate(10, ['*'], 'page', $request->page);

        // Jika tidak ada berita, kirim response kosong
        if ($newsItems->isEmpty()) {
            return response()->json(['html' => '', 'hasMorePages' => false]);
        }

        // Render komponen view partial
        $html = view('news.components.news-list-item', ['newsItems' => $newsItems])->render();

        // Kembalikan HTML dan status apakah masih ada halaman berikutnya
        return response()->json([
            'html' => $html,
            'hasMorePages' => $newsItems->hasMorePages()
        ]);
    }
}