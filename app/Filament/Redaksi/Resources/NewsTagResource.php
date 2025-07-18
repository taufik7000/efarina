<?php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\NewsTagResource\Pages;
use App\Models\NewsTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NewsTagResource extends Resource
{
    protected static ?string $model = NewsTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Tags Berita';

    protected static ?string $pluralModelLabel = 'Tags Berita';

    protected static ?string $modelLabel = 'Tag';

    protected static ?string $navigationGroup = 'News Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tag')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nama_tag')
                                    ->label('Nama Tag')
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
                                    ->readOnly()
                                    ->dehydrated(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\ColorPicker::make('color')
                                    ->label('Warna')
                                    ->default('#10b981'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label('Warna'),

                Tables\Columns\TextColumn::make('nama_tag')
                    ->label('Nama Tag')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->color),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable()
                    ->size('sm')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('news_count')
                    ->label('Digunakan')
                    ->getStateUsing(fn ($record) => $record->news()->count())
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\Filter::make('popular')
                    ->label('Tag Populer')
                    ->query(fn (Builder $query): Builder => 
                        $query->withCount('news')
                              ->having('news_count', '>', 0)
                              ->orderBy('news_count', 'desc')
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (NewsTag $record, Tables\Actions\DeleteAction $action) {
                        if (!$record->canBeDeleted()) {
                            $action->cancel();
                            $action->failureNotificationTitle('Tag tidak dapat dihapus karena masih digunakan.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('nama_tag');
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
            'index' => Pages\ListNewsTags::route('/'),
            'create' => Pages\CreateNewsTag::route('/create'),
            'view' => Pages\ViewNewsTag::route('/{record}'),
            'edit' => Pages\EditNewsTag::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['createdBy']);
    }
}