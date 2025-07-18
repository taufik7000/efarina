<?php

namespace App\Filament\Redaksi\Resources\NewsResource\Pages;

use App\Filament\Redaksi\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            
            Actions\Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-eye')
                ->color('success')
                ->action(function () {
                    $this->record->update([
                        'status' => 'published',
                        'published_at' => now(),
                        'editor_id' => auth()->id(),
                        'edited_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('Berita Dipublikasi')
                        ->body('Berita berhasil dipublikasi dan dapat dilihat publik.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'draft'),

            Actions\Action::make('unpublish')
                ->label('Unpublish')
                ->icon('heroicon-o-eye-slash')
                ->color('warning')
                ->action(function () {
                    $this->record->update([
                        'status' => 'draft',
                        'published_at' => null,
                    ]);
                    
                    Notification::make()
                        ->title('Berita Di-unpublish')
                        ->body('Berita dikembalikan ke status draft.')
                        ->warning()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'published'),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update editor info when saving
        $data['editor_id'] = auth()->id();
        $data['edited_at'] = now();
        
        // Auto set published_at if status changed to published
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        
        // Clear published_at if status is not published
        if ($data['status'] !== 'published') {
            $data['published_at'] = null;
        }

        // Update meta_data if empty
        if (empty($data['meta_data']) || !is_array($data['meta_data'])) {
            $data['meta_data'] = [
                'meta_title' => $data['judul'] ?? '',
                'meta_description' => $data['excerpt'] ?? '',
                'meta_keywords' => '',
            ];
        }

        return $data;
    }

    public function getContentMaxWidth(): ?string
    {
        return '7xl'; // Lebih lebar di desktop
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Berita berhasil diperbarui';
    }
}