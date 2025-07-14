<?php

namespace App\Filament\Team\Resources\TaskResource\Pages;

use App\Filament\Team\Resources\TaskResource;
use App\Models\TaskComment;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Notifications\Notification;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;
    
    // Gunakan custom view
    protected static string $view = 'filament.team.pages.view-task';

    // Property untuk form comment
    public $newComment = '';

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
                ])
                ->action(function (array $data): void {
                    // Add comment
                    $this->record->addComment($data['comment']);
                    
                    // Show success notification
                    Notification::make()
                        ->title('Comment added successfully!')
                        ->success()
                        ->send();
                    
                    // Refresh data
                    $this->record = $this->record->fresh(['comments.user']);
                }),
        ];
    }

    // Livewire method untuk add comment dari blade
    public function addCommentFromBlade()
    {
        $this->validate([
            'newComment' => 'required|string|min:1',
        ]);

        // Add comment
        $this->record->addComment($this->newComment);

        // Clear form
        $this->newComment = '';

        // Show success notification
        Notification::make()
            ->title('Comment added successfully!')
            ->success()
            ->send();

        // Refresh data
        $this->record = $this->record->fresh(['comments.user']);
    }
}