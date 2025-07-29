<?php

namespace App\Filament\Resources\RedirectResource\Pages;

use App\Filament\Resources\RedirectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;

class ViewRedirect extends ViewRecord
{
    protected static string $resource = RedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('test_redirect')
                ->label('Test Redirect')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => url($this->record->old_url))
                ->openUrlInNewTab()
                ->color('info'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('URL Information')
                    ->schema([
                        TextEntry::make('old_url')
                            ->label('URL Lama')
                            ->copyable()
                            ->formatStateUsing(fn (string $state): string => url($state)),
                        TextEntry::make('new_url')
                            ->label('URL Baru')
                            ->copyable(),
                    ])->columns(1),

                Section::make('Redirect Settings')
                    ->schema([
                        TextEntry::make('status_code')
                            ->label('Status Code')
                            ->badge()
                            ->formatStateUsing(fn (int $state): string => match($state) {
                                301 => '301 - Permanent Redirect',
                                302 => '302 - Temporary Redirect',
                                307 => '307 - Temporary Redirect (Method Preserved)',
                                308 => '308 - Permanent Redirect (Method Preserved)',
                                default => $state
                            }),
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    ])->columns(2),

                Section::make('Statistics')
                    ->schema([
                        TextEntry::make('hit_count')
                            ->label('Total Hits')
                            ->numeric()
                            ->badge()
                            ->color('success'),
                        TextEntry::make('last_accessed_at')
                            ->label('Terakhir Diakses')
                            ->dateTime('d F Y, H:i:s')
                            ->placeholder('Belum pernah diakses'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d F Y, H:i:s'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime('d F Y, H:i:s'),
                    ])->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Tidak ada deskripsi')
                            ->columnSpanFull(),
                    ])->collapsible(),
            ]);
    }
}