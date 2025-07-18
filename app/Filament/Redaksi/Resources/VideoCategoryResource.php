<?php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\VideoCategoryResource\Pages;
use App\Models\VideoCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class VideoCategoryResource extends Resource
{
    protected static ?string $model = VideoCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'Kategori Video';

    protected static ?string $pluralModelLabel = 'Kategori Video';

    protected static ?string $modelLabel = 'Kategori Video';

    protected static ?string $navigationGroup = 'Video Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nama_kategori')
                                    ->label('Nama Kategori')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                        $set('slug', Str::slug($state))
                                    ),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('URL-friendly version of the name'),
                            ]),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\ColorPicker::make('color')
                                    ->label('Warna Kategori')
                                    ->default('#3b82f6'),

                                Forms\Components\TextInput::make('icon')
                                    ->label('Icon Class')
                                    ->placeholder('fas fa-video')
                                    ->helperText('Font Awesome icon class'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Urutan tampilan (0 = pertama)'),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label('Warna'),

                Tables\Columns\TextColumn::make('nama_kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('sm'),

                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon')
                    ->formatStateUsing(fn (string $state) => new \Illuminate\Support\HtmlString(
                        "<i class='{$state}'></i> {$state}"
                    )),

                Tables\Columns\TextColumn::make('videos_count')
                    ->label('Jumlah Video')
                    ->counts('videos')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (VideoCategory $record, Tables\Actions\DeleteAction $action) {
                        if (!$record->canBeDeleted()) {
                            $action->cancel();
                            $action->failureNotificationTitle('Kategori tidak dapat dihapus karena masih memiliki video.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideoCategories::route('/'),
            'create' => Pages\CreateVideoCategory::route('/create'),
            'edit' => Pages\EditVideoCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('videos');
    }
}