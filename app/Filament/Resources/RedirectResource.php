<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Redirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'URL Redirects';
    protected static ?string $modelLabel = 'Redirect';
    protected static ?string $pluralModelLabel = 'Redirects';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('URL Information')
                    ->schema([
                        TextInput::make('old_url')
                            ->label('URL Lama')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->prefix(url('/'))
                            ->placeholder('berita/artikel-lama')
                            ->helperText('Masukkan path URL tanpa domain (contoh: berita/artikel-lama)')
                            ->rules(['regex:/^[a-zA-Z0-9\-_\/\.\?=&%]+$/'])
                            ->validationMessages([
                                'regex' => 'URL hanya boleh berisi huruf, angka, tanda hubung, garis miring, titik, dan parameter query.'
                            ]),
                            
                        TextInput::make('new_url')
                            ->label('URL Baru')
                            ->required()
                            ->placeholder('https://example.com/new-page atau /halaman-baru')
                            ->helperText('Masukkan URL lengkap (dengan http/https) atau path internal (/halaman-baru)'),
                    ])->columns(1),

                Forms\Components\Section::make('Redirect Settings')
                    ->schema([
                        Select::make('status_code')
                            ->label('Status Code')
                            ->required()
                            ->default(301)
                            ->options([
                                301 => '301 - Permanent Redirect',
                                302 => '302 - Temporary Redirect',
                                307 => '307 - Temporary Redirect (Method Preserved)',
                                308 => '308 - Permanent Redirect (Method Preserved)',
                            ])
                            ->helperText('301 untuk redirect permanen, 302 untuk sementara'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Nonaktifkan untuk menangguhkan redirect sementara'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Catatan tentang redirect ini...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('old_url')
                    ->label('URL Lama')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->formatStateUsing(fn (string $state): string => url($state))
                    ->color('gray'),

                TextColumn::make('new_url')
                    ->label('URL Baru')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                BadgeColumn::make('status_code')
                    ->label('Status')
                    ->colors([
                        'success' => 301,
                        'warning' => 302,
                        'info' => [307, 308],
                    ])
                    ->formatStateUsing(fn (int $state): string => match($state) {
                        301 => '301 Permanent',
                        302 => '302 Temporary', 
                        307 => '307 Temporary',
                        308 => '308 Permanent',
                        default => $state
                    }),

                BooleanColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable(),

                TextColumn::make('hit_count')
                    ->label('Hits')
                    ->sortable()
                    ->numeric()
                    ->badge()
                    ->color('success'),

                TextColumn::make('last_accessed_at')
                    ->label('Terakhir Diakses')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('Belum pernah'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
                    
                SelectFilter::make('status_code')
                    ->label('Status Code')
                    ->options([
                        301 => '301 - Permanent',
                        302 => '302 - Temporary',
                        307 => '307 - Temporary',
                        308 => '308 - Permanent',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Tables\Actions\Action::make('test_redirect')
                    ->label('Test')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Redirect $record): string => url($record->old_url))
                    ->openUrlInNewTab()
                    ->color('info'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'view' => Pages\ViewRedirect::route('/{record}'),
            'edit' => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}