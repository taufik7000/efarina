<?php

namespace App\Filament\Redaksi\Resources\NewsTagResource\Pages;

use App\Filament\Redaksi\Resources\NewsTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNewsTags extends ListRecords
{
    protected static string $resource = NewsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Tag Baru')
                ->icon('heroicon-o-plus')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();
                    return $data;
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn () => $this->getModel()::count()),

            'active' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getModel()::where('is_active', true)->count())
                ->badgeColor('success'),

            'popular' => Tab::make('Populer')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->withCount(['news' => function ($q) {
                        $q->where('status', 'published');
                    }])->having('news_count', '>', 0)->orderBy('news_count', 'desc')
                )
                ->badge(fn () => $this->getModel()::withCount('news')->having('news_count', '>', 0)->count())
                ->badgeColor('warning'),

            'inactive' => Tab::make('Tidak Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => $this->getModel()::where('is_active', false)->count())
                ->badgeColor('gray'),
        ];
    }
}