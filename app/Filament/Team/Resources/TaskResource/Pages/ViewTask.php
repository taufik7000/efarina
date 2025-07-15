<?php

namespace App\Filament\Team\Resources\TaskResource\Pages;

use App\Filament\Team\Resources\TaskResource;
use App\Models\TaskComment;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;
    
    // Gunakan custom view
    protected static string $view = 'filament.team.pages.view-task';

    // Properties untuk forms
    public $newComment = '';
    public $newTodoItem = '';

    // Check if current user can edit todos
    public function canEditTodos(): bool
    {
        return $this->record->assigned_to === auth()->id() || 
               $this->record->created_by === auth()->id() ||
               $this->record->project->project_manager_id === auth()->id();
    }

    // Check if todo items feature is available
    public function hasTodoFeature(): bool
    {
        return Schema::hasColumn('tasks', 'todo_items');
    }

    protected function getHeaderActions(): array
    {
        $actions = [
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
                    $this->record->addComment($data['comment']);
                    
                    Notification::make()
                        ->title('Comment added successfully!')
                        ->success()
                        ->send();
                    
                    $this->record = $this->record->fresh(['comments.user']);
                }),
        ];

        // Only add todo action if feature is available
        if ($this->hasTodoFeature() && $this->canEditTodos()) {
            $actions[] = Actions\Action::make('add_todo_item')
                ->label('Add Todo Item')
                ->icon('heroicon-o-plus-circle')
                ->form([
                    Forms\Components\TextInput::make('text')
                        ->label('Todo Item')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    $this->record->addTodoItem($data['text']);
                    
                    Notification::make()
                        ->title('Todo item added successfully!')
                        ->success()
                        ->send();
                    
                    $this->record = $this->record->fresh();
                });
        }

        return $actions;
    }

    // Livewire methods untuk todo management
    public function toggleTodoItem($itemId)
    {
        if (!$this->hasTodoFeature()) {
            Notification::make()
                ->title('Feature Not Available')
                ->body('Todo items feature is not available yet.')
                ->warning()
                ->send();
            return;
        }

        if (!$this->canEditTodos()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You are not authorized to edit todo items for this task.')
                ->danger()
                ->send();
            return;
        }

        try {
            $todoItems = $this->record->todo_items ?? [];
            $itemIndex = array_search($itemId, array_column($todoItems, 'id'));
            
            if ($itemIndex !== false) {
                $currentStatus = $todoItems[$itemIndex]['completed'] ?? false;
                $this->record->updateTodoItem($itemId, !$currentStatus, 'Todo item ' . ($currentStatus ? 'unchecked' : 'checked'));
                
                Notification::make()
                    ->title('Todo item updated!')
                    ->success()
                    ->send();
                
                $this->record = $this->record->fresh();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to update todo item: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function removeTodoItem($itemId)
    {
        if (!$this->hasTodoFeature()) {
            Notification::make()
                ->title('Feature Not Available')
                ->body('Todo items feature is not available yet.')
                ->warning()
                ->send();
            return;
        }

        if (!$this->canEditTodos()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You are not authorized to remove todo items for this task.')
                ->danger()
                ->send();
            return;
        }

        try {
            $this->record->removeTodoItem($itemId);
            
            Notification::make()
                ->title('Todo item removed!')
                ->success()
                ->send();
            
            $this->record = $this->record->fresh();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to remove todo item: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function addTodoItemFromBlade()
    {
        if (!$this->hasTodoFeature()) {
            Notification::make()
                ->title('Feature Not Available')
                ->body('Todo items feature is not available yet.')
                ->warning()
                ->send();
            return;
        }

        if (!$this->canEditTodos()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You are not authorized to add todo items to this task.')
                ->danger()
                ->send();
            return;
        }

        $this->validate([
            'newTodoItem' => 'required|string|max:255',
        ]);

        try {
            $this->record->addTodoItem($this->newTodoItem);
            $this->newTodoItem = '';

            Notification::make()
                ->title('Todo item added!')
                ->success()
                ->send();

            $this->record = $this->record->fresh();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to add todo item: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Livewire method untuk add comment dari blade
    public function addCommentFromBlade()
    {
        $this->validate([
            'newComment' => 'required|string|min:1',
        ]);

        try {
            $this->record->addComment($this->newComment);
            $this->newComment = '';

            Notification::make()
                ->title('Comment added successfully!')
                ->success()
                ->send();

            $this->record = $this->record->fresh(['comments.user']);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to add comment: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}