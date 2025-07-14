<?php

// File: app/Filament/Team/Resources/TaskResource/Pages/ListTasks.php
namespace App\Filament\Team\Resources\TaskResource\Pages;

use App\Filament\Team\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}