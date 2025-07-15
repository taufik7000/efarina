<?php

namespace App\Filament\Direktur\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\On;

abstract class BaseWidget extends Widget
{
    public array $filters = [];

    public function mount(array $filters = []): void
    {
        $this->filters = $filters;
    }

    #[On('filters-updated')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
        // Force refresh widget
        $this->dispatch('$refresh');
    }

    #[On('refresh-widgets')]
    public function refreshWidget(array $filters): void
    {
        $this->updateFilters($filters);
    }

    protected function getDateRange(): array
    {
        return [
            'from' => $this->filters['date_from'] ?? now()->startOfMonth(),
            'to' => $this->filters['date_to'] ?? now(),
        ];
    }

    protected function getBudgetPeriod(): ?int
    {
        return $this->filters['budget_period'] ?? null;
    }
}