<?php

namespace App\Filament\Resources\BudgetAllocationResource\Pages;

use App\Filament\Resources\BudgetAllocationResource;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification; 
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

    class ViewBudgetAllocation extends ViewRecord
{
    protected static string $resource = BudgetAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin', 'direktur', 'keuangan'])),
                
            Actions\Action::make('increase_budget')
                ->label('Tambah Budget')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->visible(fn () => auth()->user()->hasRole(['keuangan', 'direktur']))
                ->form([
                    Forms\Components\TextInput::make('additional_amount')
                        ->label('Tambahan Budget')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->rules(['min:1']),
                    Forms\Components\Textarea::make('reason')
                        ->label('Alasan Penambahan')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    $budgetPlan = $record->budgetPlan;
                    $additionalAmount = $data['additional_amount'];
                    
                    // Validation
                    if ($additionalAmount > $budgetPlan->remaining_budget) {
                        Notification::make()
                            ->title('Budget Plan Tidak Cukup')
                            ->body("Remaining budget: Rp " . number_format($budgetPlan->remaining_budget))
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    // Update allocation
                    $record->increment('allocated_amount', $additionalAmount);
                    $budgetPlan->updateTotals();
                    
                    Notification::make()
                        ->title('Budget Berhasil Ditambah')
                        ->body("Budget allocation ditambah Rp " . number_format($additionalAmount))
                        ->success()
                        ->send();
                }),
        ];
    }

    // Override untuk custom layout jika diperlukan
    public function getContentTabLabel(): ?string
    {
        return 'Detail Alokasi';
    }
}