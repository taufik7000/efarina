<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ViewEntry;

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
                // Gunakan Grid untuk membuat layout multi-kolom.
                // make(3) berarti kita membagi halaman menjadi 3 kolom virtual.
                Grid::make(3)->schema([
                    // KOLOM KIRI (Main Content)
                    // Gunakan Group untuk mengelompokkan semua section di kolom ini.
                    // columnSpan(2) berarti grup ini akan memakan 2 dari 3 kolom virtual.
                    Group::make()
                        ->schema([
                            Section::make('Project Overview')
                                ->schema([
                                    TextEntry::make('nama_project')
                                        ->label('Project Name'),
                                    TextEntry::make('deskripsi')
                                        ->label('Description')
                                        ->columnSpanFull(), // Agar deskripsi memakan lebar penuh di dalam section
                                    TextEntry::make('status')
                                        ->badge()
                                        ->color(fn ($record) => $record->status_color),
                                    TextEntry::make('prioritas')
                                        ->badge()
                                        ->color(fn ($record) => $record->prioritas_color),
                                ])
                                ->columns(2),

                            Section::make('Tasks Overview')
                                ->schema([
                                    ViewEntry::make('tasks')
                                        ->view('filament.team.components.project-tasks-overview'),
                                ]),
                        ])
                        ->columnSpan(2),

                    // KOLOM KANAN (Sidebar)
                    // Gunakan Group lagi untuk kolom kedua.
                    // columnSpan(1) berarti grup ini akan memakan 1 dari 3 kolom virtual.
                    Group::make()
                        ->schema([
                            Section::make('Timeline & Team')
                                ->schema([
                                    TextEntry::make('tanggal_mulai')
                                        ->label('Start Date')
                                        ->date(),
                                    TextEntry::make('tanggal_deadline')
                                        ->label('Deadline')
                                        ->date(),
                                    TextEntry::make('projectManager.name')
                                        ->label('Project Manager'),
                                    TextEntry::make('divisi.nama_divisi')
                                        ->label('Division'),
                                    TextEntry::make('progress_percentage')
                                        ->label('Progress')
                                        ->suffix('%'),
                                    TextEntry::make('budget')
                                        ->label('Budget')
                                        ->money('IDR'),
                                ]),

                            Section::make('Team Members')
                                ->schema([
                                    RepeatableEntry::make('team_members_users')
                                        ->label('')
                                        ->schema([
                                            TextEntry::make('name')
                                                ->label('Name'),
                                            TextEntry::make('jabatan.nama_jabatan')
                                                ->label('Position'),
                                        ])
                                        ->columns(2),
                                ]),
                        ])
                        ->columnSpan(1),
                ]),
            ]);
    }
}