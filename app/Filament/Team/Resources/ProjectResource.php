<?php

namespace App\Filament\Team\Resources;

use App\Filament\Team\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\User;
use App\Models\Divisi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $navigationLabel = 'Projects';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Project')
                    ->schema([
                        Forms\Components\TextInput::make('nama_project')
                            ->label('Nama Project')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpan(2),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'on_hold' => 'On Hold',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\Select::make('prioritas')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->required()
                            ->default('medium'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Timeline & Assignment')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->native(false),

                        Forms\Components\DatePicker::make('tanggal_deadline')
                            ->label('Deadline')
                            ->native(false),

                        Forms\Components\Select::make('project_manager_id')
                            ->label('Project Manager')
                            ->relationship('projectManager', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('divisi_id')
                            ->label('Divisi')
                            ->relationship('divisi', 'nama_divisi')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('team_members')
                            ->label('Team Members')
                            ->multiple()
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Budget & Progress')
                    ->schema([
                        Forms\Components\TextInput::make('budget')
                            ->label('Budget')
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('progress_percentage')
                            ->label('Progress (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->suffix('%'),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_project')
                    ->label('Nama Project')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'primary' => 'active',
                        'warning' => 'on_hold',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\BadgeColumn::make('prioritas')
                    ->label('Prioritas')
                    ->colors([
                        'secondary' => 'low',
                        'primary' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ]),

                Tables\Columns\TextColumn::make('projectManager.name')
                    ->label('Project Manager')
                    ->searchable(),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_deadline')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->tanggal_deadline && $record->tanggal_deadline->isPast() && $record->status !== 'completed' ? 'danger' : null),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->label('Tasks')
                    ->counts('tasks')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('prioritas')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\SelectFilter::make('project_manager_id')
                    ->label('Project Manager')
                    ->relationship('projectManager', 'name'),

                Tables\Filters\SelectFilter::make('divisi_id')
                    ->label('Divisi')
                    ->relationship('divisi', 'nama_divisi'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                // Edit action hanya untuk project manager
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => self::canEdit($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole(['admin', 'super-admin'])), // Hanya admin yang bisa bulk delete
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // HAPUS METHOD getEloquentQuery() AGAR SEMUA USER BISA MELIHAT SEMUA PROJECT
    // public static function getEloquentQuery(): Builder
    // {
    //     // Method ini dihapus agar semua user bisa melihat semua project
    // }

    /**
     * Method untuk mengecek apakah user bisa mengedit project
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();
        
        // Admin dan super-admin bisa edit semua
        if ($user->hasRole(['admin', 'super-admin'])) {
            return true;
        }
        
        // Project manager bisa edit project mereka
        if ($record->project_manager_id === $user->id) {
            return true;
        }
        
        // User yang membuat project bisa edit
        if ($record->created_by === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Method untuk mengecek apakah user bisa membuat project baru
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();
        
        // Admin, super-admin, dan role tertentu bisa membuat project
        return $user->hasRole(['admin', 'super-admin', 'direktur', 'team']);
    }

    /**
     * Method untuk mengecek apakah user bisa menghapus project
     */
    public static function canDelete($record): bool
    {
        $user = auth()->user();
        
        // Hanya admin, super-admin, dan project manager yang bisa hapus
        return $user->hasRole(['admin', 'super-admin']) || 
               $record->project_manager_id === $user->id ||
               $record->created_by === $user->id;
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}