<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\User;
use App\Models\EmployeeDocument;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentEmployeeUpdatesWidget extends BaseWidget
{
    protected static ?string $heading = 'Update Profile Terbaru';
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EmployeeDocument::query()
                    ->with(['user', 'verifier'])
                    ->latest('uploaded_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('document_type_name')
                    ->label('Jenis Dokumen')
                    ->badge(),

                Tables\Columns\TextColumn::make('uploaded_time_ago')
                    ->label('Waktu Upload'),

                Tables\Columns\BadgeColumn::make('is_verified')
                    ->label('Status')
                    ->formatStateUsing(fn (EmployeeDocument $record): string => 
                        $record->is_verified ? 'Terverifikasi' : 'Pending'
                    )
                    ->color(fn (EmployeeDocument $record): string => 
                        $record->is_verified ? 'success' : 'warning'
                    ),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('Belum diverifikasi'),
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EmployeeDocument $record): bool => !$record->is_verified)
                    ->action(function (EmployeeDocument $record) {
                        $record->verify(auth()->user());
                        $this->dispatch('refresh');
                    }),

                Tables\Actions\Action::make('view_profile')
                    ->icon('heroicon-o-user')
                    ->url(fn (EmployeeDocument $record): string => 
                        route('filament.hrd.resources.employee-profiles.view', $record->user)
                    ),
            ]);
    }
}