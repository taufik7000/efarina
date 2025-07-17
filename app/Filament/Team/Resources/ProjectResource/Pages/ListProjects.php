<?php

namespace App\Filament\Team\Resources\ProjectResource\Pages;

use App\Filament\Team\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        
        // Dynamic label berdasarkan role
        $createLabel = $user->hasRole('team') 
            ? 'Ajukan Proposal Project' 
            : 'Buat Project Baru';

        return [
            Actions\CreateAction::make()
                ->label($createLabel)
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();
        
        $tabs = [
            'all' => Tab::make('Semua Project')
                ->badge($this->getTabBadgeCount('all')),
        ];

        // Tab untuk status
        $tabs['draft'] = Tab::make($user->hasRole('team') ? 'Proposal Pending' : 'Menunggu Approval')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
            ->badge($this->getTabBadgeCount('draft'))
            ->badgeColor('warning');

        $tabs['planning'] = Tab::make('Planning')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'planning'))
            ->badge($this->getTabBadgeCount('planning'))
            ->badgeColor('info');

        $tabs['in_progress'] = Tab::make('In Progress')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress'))
            ->badge($this->getTabBadgeCount('in_progress'))
            ->badgeColor('primary');

        $tabs['completed'] = Tab::make('Completed')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
            ->badge($this->getTabBadgeCount('completed'))
            ->badgeColor('success');

        // Tab khusus untuk team member
        if (!$user->hasRole(['admin', 'redaksi'])) {
            $tabs['my_projects'] = Tab::make('Project Saya')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('project_manager_id', $user->id)
                      ->orWhereJsonContains('team_members', $user->id);
                }))
                ->badge($this->getTabBadgeCount('my_projects'));
        }

        return $tabs;
    }

    private function getTabBadgeCount(string $tab): int
    {
        $user = auth()->user();
        $query = static::getResource()::getEloquentQuery();

        return match ($tab) {
            'all' => $query->count(),
            'draft' => $query->where('status', 'draft')->count(),
            'planning' => $query->where('status', 'planning')->count(),
            'in_progress' => $query->where('status', 'in_progress')->count(),
            'completed' => $query->where('status', 'completed')->count(),
            'my_projects' => $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('project_manager_id', $user->id)
                  ->orWhereJsonContains('team_members', $user->id);
            })->count(),
            default => 0,
        };
    }

    // Override page title based on role
    public function getTitle(): string
    {
        $user = auth()->user();
        return $user->hasRole('team') ? 'Project Proposals' : 'Manage Projects';
    }
}