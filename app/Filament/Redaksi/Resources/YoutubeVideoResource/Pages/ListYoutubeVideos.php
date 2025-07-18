<?php

namespace App\Filament\Redaksi\Resources\YoutubeVideoResource\Pages;

use App\Filament\Redaksi\Resources\YoutubeVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListYoutubeVideos extends ListRecords
{
    protected static string $resource = YoutubeVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
