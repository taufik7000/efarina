<?php

// app/Filament/Hrd/Resources/KpiTargetResource/Pages/ListKpiTargets.php
namespace App\Filament\Hrd\Resources\KpiTargetResource\Pages;

use App\Filament\Hrd\Resources\KpiTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListKpiTargets extends ListRecords
{
    protected static string $resource = KpiTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Targets'),
            
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(static::getResource()::getModel()::where('is_active', true)->count()),
            
            'global' => Tab::make('Global')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'global'))
                ->badge(static::getResource()::getModel()::where('target_type', 'global')->count()),
                
            'jabatan' => Tab::make('Position Based')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'jabatan'))
                ->badge(static::getResource()::getModel()::where('target_type', 'jabatan')->count()),
                
            'individual' => Tab::make('Individual')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'individual'))
                ->badge(static::getResource()::getModel()::where('target_type', 'individual')->count()),
                
            'invalid' => Tab::make('Invalid Weights')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereRaw('(attendance_weight + task_completion_weight + quality_weight) != 100')
                )
                ->badge(static::getResource()::getModel()::whereRaw('(attendance_weight + task_completion_weight + quality_weight) != 100')->count())
                ->badgeColor('danger'),
        ];
    }
}