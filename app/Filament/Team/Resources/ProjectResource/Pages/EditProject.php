<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            
            // Delete action hanya untuk yang punya permission
            Actions\DeleteAction::make()
                ->visible(fn () => ProjectResource::canDelete($this->record)),
        ];
    }

    /**
     * Method untuk mengecek apakah user bisa mengakses halaman edit
     */
    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Cek permission
        if (!ProjectResource::canEdit($this->record)) {
            // Redirect ke view jika tidak ada permission edit
            $this->redirect(ProjectResource::getUrl('view', ['record' => $this->record]));
            return;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}