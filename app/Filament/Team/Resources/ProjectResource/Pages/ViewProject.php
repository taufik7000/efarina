<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Project Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama_project')
                            ->label('Project Name'),
                        Infolists\Components\TextEntry::make('deskripsi')
                            ->label('Description'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->status_color),
                        Infolists\Components\TextEntry::make('prioritas')
                            ->badge()
                            ->color(fn ($record) => $record->prioritas_color),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Timeline & Team')
                    ->schema([
                        Infolists\Components\TextEntry::make('tanggal_mulai')
                            ->label('Start Date')
                            ->date(),
                        Infolists\Components\TextEntry::make('tanggal_deadline')
                            ->label('Deadline')
                            ->date(),
                        Infolists\Components\TextEntry::make('projectManager.name')
                            ->label('Project Manager'),
                        Infolists\Components\TextEntry::make('divisi.nama_divisi')
                            ->label('Division'),
                        Infolists\Components\TextEntry::make('progress_percentage')
                            ->label('Progress')
                            ->suffix('%'),
                        Infolists\Components\TextEntry::make('budget')
                            ->label('Budget')
                            ->money('IDR'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Team Members')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('team_members_users')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Name'),
                                Infolists\Components\TextEntry::make('jabatan.nama_jabatan')
                                    ->label('Position'),
                            ])
                            ->columns(2),
                    ]),

                Infolists\Components\Section::make('Tasks Overview')
                    ->schema([
                        Infolists\Components\ViewEntry::make('tasks')
                            ->view('filament.team.components.project-tasks-overview'),
                    ]),
            ]);
    }
}