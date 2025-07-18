<?php

namespace App\Filament\Redaksi\Resources\NewsResource\Pages;

use App\Filament\Redaksi\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set author_id to current user
        $data['author_id'] = auth()->id();
        
        // Auto set published_at if status is published
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Ensure meta_data has default structure
        if (empty($data['meta_data']) || !is_array($data['meta_data'])) {
            $data['meta_data'] = [
                'meta_title' => $data['judul'] ?? '',
                'meta_description' => $data['excerpt'] ?? '',
                'meta_keywords' => '',
            ];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $news = $this->getRecord();
        
        $statusMessage = match($news->status) {
            'published' => 'dipublikasi dan dapat dilihat publik',
            'draft' => 'disimpan sebagai draft',
            'archived' => 'diarsipkan',
            default => 'dibuat'
        };
        
        Notification::make()
            ->title('Berita Berhasil Dibuat')
            ->body("Berita '{$news->judul}' telah {$statusMessage}.")
            ->success()
            ->duration(5000)
            ->send();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return null; // Disable default notification since we have custom one
    }

    // Custom CSS untuk mobile responsiveness
    public function getContentMaxWidth(): ?string
    {
        return '7xl'; // Lebih lebar di desktop
    }
}