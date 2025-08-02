<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\YoutubeVideo;
use App\Models\VideoCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Berita unggulan (featured) - ambil 4 untuk main featured + 3 secondary
        $featuredNews = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Berita terbaru (non-featured)
        $latestNews = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->where('is_featured', false)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        $excludedIds = $featuredNews->pluck('id')->merge($latestNews->pluck('id'));

        // 2. Ambil 5 berita lainnya, kecuali yang sudah ditampilkan
        $otherNews = News::with('category')->where('status', 'published')
            ->whereNotIn('id', $excludedIds)
            ->latest('published_at')
            ->take(6) // Ambil 5 berita
            ->get();

        // Berita populer berdasarkan views
        $popularNews = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->limit(8)
            ->get();

        // Video terbaru
        $latestVideos = YoutubeVideo::active()
            ->with('category')
            ->latest('published_at')
            ->limit(6)
            ->get();

        // Video unggulan
        $featuredVideos = YoutubeVideo::active()
            ->featured()
            ->with('category')
            ->latest('published_at')
            ->limit(5)
            ->get();

        // Kategori berita aktif
        $newsCategories = NewsCategory::active()
            ->ordered()
            ->withCount(['news' => function ($query) {
                $query->where('status', 'published');
            }])
            ->get();

        // Kategori video aktif
        $videoCategories = VideoCategory::active()
            ->ordered()
            ->withCount(['activeVideos'])
            ->get();

        return view('home', compact(
            'featuredNews', 
            'latestNews', 
            'popularNews', 
            'latestVideos', 
            'featuredVideos',
            'newsCategories', 
            'videoCategories',
            'otherNews',
        ));
    }


    public function loadMoreNews(Request $request)
    {
        // Validasi halaman yang diminta
        $request->validate(['page' => 'required|integer|min:2']);

        $page = $request->page;

        // Ambil ID berita yang sudah ditampilkan di section lain
        $featuredNews = News::published()->featured()->latest()->take(4)->get();
        $latestNews = News::published()->where('is_featured', false)->latest()->take(6)->get();
        $excludedIds = $featuredNews->pluck('id')->merge($latestNews->pluck('id'));

        // Query untuk "Berita Lainnya" dengan pagination
        $otherNews = News::published()
            ->whereNotIn('id', $excludedIds)
            ->latest('published_at')
            ->paginate(5, ['*'], 'page', $page); // <-- Gunakan paginate di sini

        // Jika tidak ada berita lagi, kirim respons kosong
        if ($otherNews->isEmpty()) {
            return response()->json(['html' => '']);
        }

        // Render komponen Blade terpisah yang berisi HTML untuk setiap item berita
        $html = view('components.news-list-item', ['newsItems' => $otherNews])->render();
        
        return response()->json(['html' => $html]);
    }
}