<?php

namespace App\Filament\Team\Resources\TaskResource\Pages;

use App\Filament\Team\Resources\TaskResource;
use App\Models\TaskComment;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Forms;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('add_comment')
                ->label('Add Comment')
                ->icon('heroicon-o-chat-bubble-left')
                ->form([
                    Forms\Components\Textarea::make('comment')
                        ->label('Comment')
                        ->required()
                        ->rows(3),
                    Forms\Components\FileUpload::make('attachments')
                        ->label('Attachments')
                        ->multiple()
                        ->directory('comment-attachments'),
                ])
                ->action(function (array $data): void {
                    $this->record->addComment($data['comment'], $data['attachments'] ?? null);
                    
                    $this->refreshFormData([]);
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Task Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama_task')
                            ->label('Task Name'),
                        Infolists\Components\TextEntry::make('deskripsi')
                            ->label('Description'),
                        Infolists\Components\TextEntry::make('project.nama_project')
                            ->label('Project'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->status_color),
                        Infolists\Components\TextEntry::make('prioritas')
                            ->badge()
                            ->color(fn ($record) => $record->prioritas_color),
                        Infolists\Components\TextEntry::make('progress_percentage')
                            ->label('Progress')
                            ->suffix('%'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Assignment & Timeline')
                    ->schema([
                        Infolists\Components\TextEntry::make('assignedTo.name')
                            ->label('Assigned To'),
                        Infolists\Components\TextEntry::make('createdBy.name')
                            ->label('Created By'),
                        Infolists\Components\TextEntry::make('tanggal_mulai')
                            ->label('Start Date')
                            ->date(),
                        Infolists\Components\TextEntry::make('tanggal_deadline')
                            ->label('Deadline')
                            ->date(),
                        Infolists\Components\TextEntry::make('estimated_hours')
                            ->label('Estimated Hours')
                            ->suffix(' jam'),
                        Infolists\Components\TextEntry::make('actual_hours')
                            ->label('Actual Hours')
                            ->suffix(' jam'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Progress Updates')
                    ->schema([
                        Infolists\Components\ViewEntry::make('progressUpdates')
                            ->view('filament.team.components.task-progress-updates'),
                    ]),

                Infolists\Components\Section::make('Comments')
                    ->schema([
                        Infolists\Components\ViewEntry::make('comments')
                            ->view('filament.team.components.task-comments'),
                    ]),
            ]);
    }
}