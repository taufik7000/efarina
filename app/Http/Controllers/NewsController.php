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

        return view('news.index', compact('news', 'categories'));
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

        return view('news.show', compact('news', 'relatedNews', 'popularNews', 'featuredVideos'));
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
        // Check if category is active
        if (!$category->is_active) {
            abort(404);
        }

        $news = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->where('news_category_id', $category->id)
            ->latest()
            ->paginate(12);

        return view('news.category', compact('news', 'category'));
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
}