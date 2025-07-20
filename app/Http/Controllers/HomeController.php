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
            ->limit(4)
            ->get();

        // Berita terbaru (non-featured)
        $latestNews = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->where('is_featured', false)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Berita populer berdasarkan views
        $popularNews = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->limit(5)
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
            ->limit(3)
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
            'videoCategories'
        ));
    }
}