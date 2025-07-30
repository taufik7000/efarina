<?php

namespace App\Filament\Redaksi\Resources;

use App\Filament\Redaksi\Resources\NewsResource\Pages;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Berita';

    protected static ?string $pluralModelLabel = 'Berita';

    protected static ?string $modelLabel = 'Berita';

    protected static ?string $navigationGroup = 'News Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // KOLOM KIRI - Konten Utama & SEO
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Utama')
                            ->schema([
                                Forms\Components\TextInput::make('judul')
                                    ->label('Judul Berita')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        // Hanya auto-generate slug jika slug masih kosong atau sama dengan slug auto-generated sebelumnya
                                        $currentSlug = $get('slug');

                                        if (empty($currentSlug) || $currentSlug === Str::slug($get('original_title') ?? '')) {
                                            $newSlug = self::truncateSlug($state, 100);
                                            $set('slug', $newSlug);
                                        }

                                        // Simpan judul asli untuk referensi
                                        $set('original_title', $state);
                                    })
                                    ->validationMessages([
                                        'required' => 'Judul berita harus diisi.',
                                    ]),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug URL')
                                    ->required()
                                    ->maxLength(80)
                                    ->unique(ignoreRecord: true)
                                    ->live(onBlur: true)
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('regenerate_slug')
                                            ->icon('heroicon-m-arrow-path')
                                            ->tooltip('Generate ulang slug dari judul')
                                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                                $judul = $get('judul');
                                                if ($judul) {
                                                    $newSlug = self::truncateSlug($judul, 50);
                                                    $set('slug', $newSlug);
                                                }
                                            })
                                    )
                                    ->afterStateUpdated(function ($state, Forms\Set $set, $component) {
                                        // Validasi dan format slug yang diinput manual
                                        if ($state) {
                                            $cleanSlug = self::cleanSlug($state, 50);
                                            if ($cleanSlug !== $state) {
                                                $set('slug', $cleanSlug);
                                            }
                                        }
                                    })
                                    ->helperText('URL berita (max 50 karakter). Klik ⟳ untuk generate ulang dari judul.')
                                    ->placeholder('slug-url-berita')
                                    ->validationMessages([
                                        'required' => 'Slug URL harus diisi.',
                                        'unique' => 'Slug URL sudah digunakan.',
                                        'max' => 'Slug URL maksimal 50 karakter.',
                                    ]),

                                Forms\Components\Textarea::make('excerpt')
                                    ->label('Ringkasan/Excerpt')
                                    ->required()
                                    ->maxLength(300)
                                    ->rows(3)
                                    ->hint('Ringkasan singkat yang akan ditampilkan di preview'),
                            ]),

                        Forms\Components\Section::make('Konten Berita')
                            ->schema([
                                Forms\Components\RichEditor::make('konten')
                                    ->label('Konten Lengkap')
                                    ->required()
                                    ->fileAttachmentsDirectory('news-attachments')
                                    ->toolbarButtons([
                                        'attachFiles',
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'codeBlock',
                                        'h2',
                                        'h3',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'undo',
                                    ]),
                            ]),

                        // SEO SECTION - DI KOLOM KIRI
                        Forms\Components\Section::make('SEO & Meta Data')
                            ->schema([
                                Forms\Components\TextInput::make('seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(120)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if (empty($state) && $get('judul')) {
                                            $set('seo_title', $get('judul'));
                                        }
                                    })
                                    ->helperText(function ($state) {
                                        $length = strlen($state ?? '');
                                        return "Karakter: {$length}/120 (Optimal: 80-120)";
                                    }),

                                Forms\Components\Textarea::make('seo_description')
                                    ->label('Meta Description')
                                    ->maxLength(250)
                                    ->rows(3)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if (empty($state) && $get('excerpt')) {
                                            $set('seo_description', $get('excerpt'));
                                        }
                                    })
                                    ->helperText(function ($state) {
                                        $length = strlen($state ?? '');
                                        return "Karakter: {$length}/250 (Optimal: 150-250)";
                                    }),

                                Forms\Components\TextInput::make('focus_keyword')
                                    ->label('Focus Keyword')
                                    ->helperText('Kata kunci utama untuk SEO')
                                    ->live(onBlur: true),

                                Forms\Components\TagsInput::make('seo_keywords')
                                    ->label('SEO Keywords')
                                    ->placeholder('Tambah keyword dan tekan Enter')
                                    ->helperText('Pisahkan keyword dengan Enter. Max 10 keywords.'),

                                Forms\Components\TextInput::make('canonical_url')
                                    ->label('Canonical URL')
                                    ->url()
                                    ->prefix('https://')
                                    ->helperText('URL kanonik untuk mencegah duplicate content'),

                                // SEO Preview
                                Forms\Components\Placeholder::make('seo_preview')
                                    ->label('Google Search Preview')
                                    ->content(function (Forms\Get $get) {
                                        $title = $get('seo_title') ?: $get('judul') ?: 'Judul berita Anda...';
                                        $description = $get('seo_description') ?: $get('excerpt') ?: 'Deskripsi berita akan tampil di sini...';
                                        $url = 'https://yoursite.com/berita/' . Str::slug($get('judul') ?: 'judul-berita');
                                        
                                        return new \Illuminate\Support\HtmlString("
                                            <div class='bg-white border rounded-lg p-4 max-w-2xl'>
                                                <div class='text-sm text-gray-600 mb-1'>{$url}</div>
                                                <div class='text-blue-800 text-lg hover:underline cursor-pointer mb-1'>{$title}</div>
                                                <div class='text-gray-700 text-sm leading-5'>{$description}</div>
                                            </div>
                                        ");
                                    }),

                                // Open Graph - Section with collapsible
                                Forms\Components\Section::make('Open Graph')
                                    ->schema([
                                        Forms\Components\TextInput::make('og_title')
                                            ->label('OG Title')
                                            ->maxLength(120)
                                            ->helperText('Judul untuk social media sharing'),

                                        Forms\Components\Textarea::make('og_description')
                                            ->label('OG Description')
                                            ->maxLength(300)
                                            ->rows(2)
                                            ->helperText('Deskripsi untuk social media sharing'),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),

                                // Twitter Card - Section with collapsible
                                Forms\Components\Section::make('Twitter Card')
                                    ->schema([
                                        Forms\Components\Select::make('twitter_card_type')
                                            ->label('Card Type')
                                            ->options([
                                                'summary' => 'Summary',
                                                'summary_large_image' => 'Summary Large Image',
                                            ])
                                            ->default('summary_large_image'),

                                        Forms\Components\TextInput::make('twitter_title')
                                            ->label('Twitter Title')
                                            ->maxLength(70),

                                        Forms\Components\Textarea::make('twitter_description')
                                            ->label('Twitter Description')
                                            ->maxLength(200)
                                            ->rows(2),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),

                // KOLOM KANAN - Media & Settings
                Forms\Components\Group::make()
                    ->schema([
                        // MEDIA & GAMBAR - DI KOLOM KANAN
                        Forms\Components\Section::make('Media & Gambar')
                            ->schema([
                                Forms\Components\FileUpload::make('thumbnail')
                                    ->label('Thumbnail Utama')
                                    ->image()
                                    ->directory('news-thumbnails')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->maxSize(2048)
                                    ->helperText('Gambar utama berita (max 2MB)'),

                                Forms\Components\FileUpload::make('gallery')
                                    ->label('Galeri Gambar')
                                    ->image()
                                    ->multiple()
                                    ->directory('news-gallery')
                                    ->imageEditor()
                                    ->maxFiles(10)
                                    ->maxSize(2048)
                                    ->reorderable()
                                    ->helperText('Gambar tambahan (max 10 files, 2MB each)'),

                                Forms\Components\FileUpload::make('og_image')
                                    ->label('Social Media Image')
                                    ->image()
                                    ->directory('seo-og-images')
                                    ->maxSize(1024)
                                    ->helperText('Gambar untuk social sharing (1200x630px optimal)')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['1.91:1']),

                                Forms\Components\FileUpload::make('twitter_image')
                                    ->label('Twitter Card Image')
                                    ->image()
                                    ->directory('seo-twitter-images')
                                    ->maxSize(1024)
                                    ->helperText('Gambar untuk Twitter (1200x600px optimal)')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['2:1']),
                            ]),

                        Forms\Components\Section::make('Publikasi')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'archived' => 'Archived',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->live(),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Tanggal Publikasi')
                                    ->seconds(false)
                                    ->visible(fn (Forms\Get $get) => $get('status') === 'published')
                                    ->default(now()),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Berita Unggulan')
                                    ->default(false)
                                    ->helperText('Tampilkan di halaman utama'),
                            ]),

                        Forms\Components\Section::make('Kategorisasi')
                            ->schema([
                                Forms\Components\Select::make('news_category_id')
                                    ->label('Kategori')
                                    ->options(NewsCategory::active()->ordered()->pluck('nama_kategori', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Pilih kategori utama berita'),

                                Forms\Components\Select::make('tags')
                                    ->label('Tags')
                                    ->multiple()
                                    ->relationship('tags', 'nama_tag')
                                    ->options(NewsTag::active()->pluck('nama_tag', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Pilih atau buat tag baru')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama_tag')
                                            ->label('Nama Tag')
                                            ->required(),
                                        Forms\Components\ColorPicker::make('color')
                                            ->label('Warna')
                                            ->default('#10b981'),
                                        Forms\Components\Hidden::make('created_by')
                                            ->default(auth()->id()),
                                    ]),
                            ]),

                        Forms\Components\Section::make('Informasi Sistem')
                            ->schema([
                                Forms\Components\Placeholder::make('author_info')
                                    ->label('Penulis')
                                    ->content(fn ($record) => $record?->author?->name ?? auth()->user()->name),

                                Forms\Components\Placeholder::make('reading_time')
                                    ->label('Waktu Baca')
                                    ->content(fn ($record) => $record?->reading_time ?? 'Belum dihitung')
                                    ->visible(fn ($context) => $context === 'edit'),

                                Forms\Components\Placeholder::make('views_count')
                                    ->label('Jumlah Views')
                                    ->content(fn ($record) => number_format($record?->views_count ?? 0))
                                    ->visible(fn ($context) => $context === 'edit'),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Dibuat')
                                    ->content(fn ($record) => $record?->created_at?->format('d F Y, H:i') ?? 'Belum disimpan'),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label('Terakhir Diubah')
                                    ->content(fn ($record) => $record?->updated_at?->format('d F Y, H:i') ?? 'Belum disimpan'),

                                Forms\Components\Hidden::make('author_id')
                                    ->default(auth()->id()),
                            ])
                            ->visible(fn ($context) => $context === 'edit'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->size(60),

                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),


                Tables\Columns\TextColumn::make('category.nama_kategori')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_color),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('seo_score')
                    ->label('SEO Score')
                    ->badge()
                    ->color(fn ($record) => $record->seo_score_color)
                    ->formatStateUsing(fn ($state) => $state . '/100')
                    ->sortable(),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('Penulis')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Dipublikasi')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('Belum dipublikasi'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('news_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'nama_kategori')
                    ->preload(),

                Tables\Filters\Filter::make('is_featured')
                    ->label('Berita Unggulan')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true)),

                Tables\Filters\SelectFilter::make('author_id')
                    ->label('Penulis')
                    ->relationship('author', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->action(function (News $record) {
                        $record->update([
                            'status' => 'published',
                            'published_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (News $record) => $record->status === 'draft'),

                Tables\Actions\Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->action(fn (News $record) => $record->archive())
                    ->requiresConfirmation()
                    ->visible(fn (News $record) => $record->status === 'published'),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->canBePublished()) {
                                    $record->publish();
                                }
                            });
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'view' => Pages\ViewNews::route('/{record}'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'tags', 'author']);
    }

    private static function truncateSlug(string $text, int $maxLength = 80): string
{
    $slug = Str::slug($text);
    
    if (strlen($slug) <= $maxLength) {
        return $slug;
    }
    
    // Potong pada batas kata terdekat
    $truncated = substr($slug, 0, $maxLength);
    $lastHyphen = strrpos($truncated, '-');
    
    if ($lastHyphen !== false && $lastHyphen > 20) {
        $truncated = substr($truncated, 0, $lastHyphen);
    }
    
    return rtrim($truncated, '-');
}

private static function cleanSlug(string $slug, int $maxLength = 50): string
{
    // Bersihkan dan format slug yang diinput manual
    $cleaned = Str::slug($slug);
    
    if (strlen($cleaned) > $maxLength) {
        $cleaned = self::truncateSlug($cleaned, $maxLength);
    }
    
    return $cleaned;
}
}