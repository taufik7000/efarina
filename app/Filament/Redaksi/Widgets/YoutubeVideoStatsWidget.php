<?php

namespace App\Filament\Redaksi\Widgets;

use App\Models\YoutubeVideo;
use App\Models\VideoCategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class YoutubeVideoStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $totalVideos = YoutubeVideo::count();
        $activeVideos = YoutubeVideo::where('is_active', true)->count();
        $featuredVideos = YoutubeVideo::where('is_featured', true)->count();
        $uncategorizedVideos = YoutubeVideo::whereNull('video_category_id')->count();
        $totalCategories = VideoCategory::active()->count();
        
        // Total views dalam format lengkap (tanpa K/M)
        $totalViews = YoutubeVideo::sum('view_count');
        
        // Video terbaru (30 hari terakhir)
        $recentVideos = YoutubeVideo::where('published_at', '>=', now()->subDays(30))
                                   ->where('is_active', true)
                                   ->count();
        
        // Video populer (> 10K views)
        $popularVideos = YoutubeVideo::where('view_count', '>', 10000)
                                    ->where('is_active', true)
                                    ->count();

        return [
            Stat::make('Total Video', number_format($totalVideos))
                ->description($activeVideos . ' aktif, ' . ($totalVideos - $activeVideos) . ' non-aktif')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('primary')
                ->chart([7, 12, 8, 15, 9, 14, $totalVideos]),
                
            Stat::make('Video Unggulan', number_format($featuredVideos))
                ->description(round(($featuredVideos / max($totalVideos, 1)) * 100, 1) . '% dari total video')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning')
                ->chart([2, 4, 3, 6, 4, 7, $featuredVideos]),
                
            Stat::make('Total Views', number_format($totalViews))
                ->description($popularVideos . ' video populer (>10K views)')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success')
                ->chart([100, 200, 150, 300, 250, 400, 350]),
                
            Stat::make('Kategori', number_format($totalCategories))
                ->description($uncategorizedVideos . ' video belum dikategorikan')
                ->descriptionIcon('heroicon-m-folder')
                ->color($uncategorizedVideos > 0 ? 'danger' : 'success')
                ->chart([1, 2, 3, 4, 5, 6, $totalCategories]),
                
            Stat::make('Video Terbaru', number_format($recentVideos))
                ->description('Dalam 30 hari terakhir')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->chart([1, 3, 2, 5, 4, 6, $recentVideos]),
        ];
    }
}