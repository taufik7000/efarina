<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Berita unggulan (featured)
        $featuredNews = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Berita terbaru
        $latestNews = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Berita populer berdasarkan views
        $popularNews = News::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        // Kategori aktif
        $categories = NewsCategory::active()
            ->ordered()
            ->withCount(['news' => function ($query) {
                $query->where('status', 'published');
            }])
            ->get();

        return view('home', compact('featuredNews', 'latestNews', 'popularNews', 'categories'));
    }
}