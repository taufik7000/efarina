<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Tombol Create Project - Hanya untuk Redaksi
        if (auth()->user()->hasRole('redaksi') || auth()->user()->hasRole('admin')) {
            $actions[] = Actions\CreateAction::make()
                ->label('Buat Project Baru')
                ->icon('heroicon-o-plus')
                ->color('primary');
        }

        // Tombol Create Proposal - Untuk Tim
        if (auth()->user()->hasRole('tim') || !auth()->user()->hasRole('redaksi')) {
            $actions[] = Actions\Action::make('create_proposal')
                ->label('Buat Proposal Project')
                ->icon('heroicon-o-light-bulb')
                ->color('warning')
                ->url(route('filament.team.resources.project-proposals.create'));
        }

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Bisa tambahkan widget statistik project di sini
        ];
    }
}