<?php

namespace App\Filament\Redaksi\Resources\YoutubeVideoResource\Pages;

use App\Filament\Redaksi\Resources\YoutubeVideoResource;
use App\Filament\Redaksi\Widgets\YoutubeVideoStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListYoutubeVideos extends ListRecords
{
    protected static string $resource = YoutubeVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol tambah manual dihilangkan
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            YoutubeVideoStatsWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Video YouTube';
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}