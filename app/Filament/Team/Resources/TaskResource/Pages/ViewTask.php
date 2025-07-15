<?php

namespace App\Filament\Team\Resources\TaskResource\Pages;

use App\Filament\Team\Resources\TaskResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Schema;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;
    
    // Gunakan custom view yang telah Anda buat
    protected static string $view = 'filament.team.pages.view-task';

    // Properti untuk forms yang di-binding dari view
    public $newComment = '';
    public $newTodoItem = '';

    /**
     * Helper method untuk memeriksa apakah pengguna saat ini dapat menambahkan komentar.
     * Method ini memanggil 'addComment' dari TaskPolicy.
     */
    public function canAddComment(): bool
    {
        // Menggunakan gate Laravel untuk memeriksa policy 'addComment' pada record (tugas) saat ini.
        return auth()->user()->can('addComment', $this->record);
    }

    /**
     * Helper method untuk memeriksa apakah pengguna dapat mengedit to-do item.
     * Logika ini bisa disesuaikan atau dipindahkan ke TaskPolicy jika diperlukan.
     */
    public function canEditTodos(): bool
    {
        // Menggunakan policy 'update' sebagai acuan, karena yang bisa mengedit tugas
        // seharusnya juga bisa mengedit to-do list di dalamnya.
        return auth()->user()->can('update', $this->record);
    }

    /**
     * Helper method untuk memeriksa apakah fitur to-do list (kolom 'todo_items')
     * sudah ada di tabel tasks.
     */
    public function hasTodoFeature(): bool
    {
        return Schema::hasColumn('tasks', 'todo_items');
    }

    /**
     * Mendefinisikan action yang muncul di header halaman.
     * Kita akan biarkan kosong agar semua aksi berada di dalam view (tombol, dll).
     */
    protected function getHeaderActions(): array
    {
        return [
            // Kosongkan array ini agar tidak ada tombol duplikat di header.
            // Sebagai alternatif, Anda bisa menaruh Actions\EditAction::make() di sini.
            // Actions\EditAction::make()->visible(fn() => auth()->user()->can('update', $this->record)),
        ];
    }

    // --- Livewire methods untuk To-do Management ---

    public function addTodoItemFromBlade()
    {
        if (!$this->canEditTodos()) {
            Notification::make()->title('Access Denied')->body('You are not authorized to add to-do items.')->danger()->send();
            return;
        }

        $this->validate(['newTodoItem' => 'required|string|max:255']);

        try {
            $this->record->addTodoItem($this->newTodoItem);
            $this->newTodoItem = ''; // Reset input field
            Notification::make()->title('To-do item added!')->success()->send();
            $this->record = $this->record->fresh(); // Refresh data
        } catch (\Exception $e) {
            Notification::make()->title('Error')->body('Failed to add to-do item: ' . $e->getMessage())->danger()->send();
        }
    }

    public function toggleTodoItem($itemId)
    {
        if (!$this->canEditTodos()) {
            Notification::make()->title('Access Denied')->body('You are not authorized to edit to-do items.')->danger()->send();
            return;
        }

        try {
            $todoItems = $this->record->todo_items ?? [];
            $itemIndex = array_search($itemId, array_column($todoItems, 'id'));
            
            if ($itemIndex !== false) {
                $currentStatus = $todoItems[$itemIndex]['completed'] ?? false;
                $this->record->updateTodoItem($itemId, !$currentStatus, 'To-do item ' . ($currentStatus ? 'unchecked' : 'checked'));
                Notification::make()->title('To-do item updated!')->success()->send();
                $this->record = $this->record->fresh();
            }
        } catch (\Exception $e) {
            Notification::make()->title('Error')->body('Failed to update to-do item: ' . $e->getMessage())->danger()->send();
        }
    }

    public function removeTodoItem($itemId)
    {
        if (!$this->canEditTodos()) {
            Notification::make()->title('Access Denied')->body('You are not authorized to remove to-do items.')->danger()->send();
            return;
        }

        try {
            $this->record->removeTodoItem($itemId);
            Notification::make()->title('To-do item removed!')->success()->send();
            $this->record = $this->record->fresh();
        } catch (\Exception $e) {
            Notification::make()->title('Error')->body('Failed to remove to-do item: ' . $e->getMessage())->danger()->send();
        }
    }

    // --- Livewire method untuk Add Comment dari Blade ---

    public function addCommentFromBlade()
    {
        // PENTING: Pengecekan izin sesuai policy yang telah kita buat.
        if (!$this->canAddComment()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You are not authorized to add comments to this task.')
                ->danger()
                ->send();
            return;
        }

        $this->validate([
            'newComment' => 'required|string|min:1',
        ]);

        try {
            $this->record->addComment($this->newComment);
            $this->newComment = ''; // Reset textarea

            Notification::make()
                ->title('Comment added successfully!')
                ->success()
                ->send();

            // Refresh data tugas beserta relasi comments dan user yang berkomentar.
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