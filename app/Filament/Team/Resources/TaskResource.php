<?php

namespace App\Filament\Team\Resources;

use App\Filament\Team\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Project Management';
    protected static ?string $navigationLabel = 'Tasks';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Untuk list/index page, filter berdasarkan user
        if (request()->routeIs('filament.team.resources.tasks.index')) {
            return parent::getEloquentQuery()
                ->where(function ($query) use ($user) {
                    $query->where('assigned_to', $user->id)
                        ->orWhere('created_by', $user->id)
                        ->orWhereHas('project', function ($projectQuery) use ($user) {
                            $projectQuery->where('project_manager_id', $user->id);
                        });
                });
        }

        // Untuk view/edit page, semua task bisa diakses
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Task')
                    ->schema([
Forms\Components\Select::make('project_id')
    ->label('Project')
    ->relationship('project', 'nama_project', function ($query) {
        $user = auth()->user();

        // Base filter: Hanya project dengan status yang diizinkan untuk create task
        $query->whereIn('status', ['in_progress', 'review']);

        // Redaksi bisa pilih semua project yang statusnya in_progress atau review
        if ($user->hasRole(['redaksi', 'admin'])) {
            return $query; // Sudah ada filter status di atas
        }

        // Team hanya bisa pilih project yang terkait dengan mereka
        if ($user->hasRole('team')) {
            return $query->where(function ($subQuery) use ($user) {
                $subQuery->where('project_manager_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereJsonContains('team_members', (string) $user->id);
            });
        }

        // Role lain tidak bisa akses form create
        return $query->whereRaw('1 = 0'); // Query kosong
    })
    ->searchable()
    ->preload()
    ->required()
    ->live()
    ->columnSpan(2)
    ->afterStateUpdated(function ($state, callable $set, callable $get) {
        // Validasi tambahan saat project dipilih
        if ($state) {
            $project = \App\Models\Project::find($state);
            $user = auth()->user();
            
            // Cek status project
            if ($project && !in_array($project->status, ['in_progress', 'review'])) {
                Notification::make()
                    ->title('Project Tidak Valid')
                    ->body('Task hanya bisa dibuat untuk project yang sedang berjalan (In Progress atau Review).')
                    ->danger()
                    ->send();
                $set('project_id', null);
                return;
            }
            
            // Cek permission user terhadap project
            if ($project && !$user->can('createForProject', [\App\Models\Task::class, $project])) {
                Notification::make()
                    ->title('Akses Ditolak')
                    ->body('Anda tidak memiliki izin untuk membuat task di project ini.')
                    ->danger()
                    ->send();
                $set('project_id', null);
                return;
            }
        }
    })
    ->helperText(function () {
        $user = auth()->user();
        
        if ($user->hasRole(['redaksi', 'admin'])) {
            return 'Pilih project yang sedang berjalan (In Progress atau Review).';
        }
        
        return 'Hanya project yang Anda kelola dan sedang berjalan yang dapat dipilih.';
    })
    ->placeholder('Pilih project yang sedang berjalan')
    ->getOptionLabelFromRecordUsing(function ($record) {
        // Tampilkan status project di option label
        return $record->nama_project . ' (' . ucfirst($record->status) . ')';
    })
    ->getSearchResultsUsing(function (string $search) {
        $user = auth()->user();
        
        $query = \App\Models\Project::where('nama_project', 'like', "%{$search}%")
            ->whereIn('status', ['in_progress', 'review']);
        
        if ($user->hasRole(['redaksi', 'admin'])) {
            // Redaksi bisa search semua project yang statusnya valid
            return $query->limit(50)->get();
        }
        
        if ($user->hasRole('team')) {
            // Team hanya bisa search project mereka
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('project_manager_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereJsonContains('team_members', (string) $user->id);
            });
            
            return $query->limit(50)->get();
        }
        
        return collect(); // Empty collection untuk role lain
    }),

                        Forms\Components\TextInput::make('nama_task')
                            ->label('Nama Task')
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
                                'todo' => 'To Do',
                                'in_progress' => 'In Progress',
                                'review' => 'Review',
                                'done' => 'Done',
                                'blocked' => 'Blocked',
                            ])
                            ->required()
                            ->default('todo'),

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

                Forms\Components\Section::make('Assignment & Timeline')
                    ->schema([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned To')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('parent_task_id')
                            ->label('Parent Task')
                            ->relationship('parentTask', 'nama_task')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->native(false),

                        Forms\Components\DatePicker::make('tanggal_deadline')
                            ->label('Deadline')
                            ->native(false),

                        Forms\Components\TextInput::make('estimated_hours')
                            ->label('Estimasi Jam')
                            ->numeric()
                            ->suffix('jam'),

                        Forms\Components\TextInput::make('progress_percentage')
                            ->label('Progress (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->suffix('%'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Info')
                    ->schema([
                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->separator(','),

                        Forms\Components\FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->directory('task-attachments')
                            ->preserveFilenames(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_task')
                    ->label('Task')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->project->nama_project),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'todo',
                        'primary' => 'in_progress',
                        'warning' => 'review',
                        'success' => 'done',
                        'danger' => 'blocked',
                    ]),

                Tables\Columns\BadgeColumn::make('prioritas')
                    ->label('Prioritas')
                    ->colors([
                        'secondary' => 'low',
                        'primary' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ]),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->placeholder('Unassigned'),

                Tables\Columns\ViewColumn::make('progress_percentage')
                    ->label('Progress')
                    ->view('filament.team.components.progress-bar'),

                Tables\Columns\TextColumn::make('tanggal_deadline')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null),

                Tables\Columns\TextColumn::make('comments_count')
                    ->label('Comments')
                    ->counts('comments')
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
                        'todo' => 'To Do',
                        'in_progress' => 'In Progress',
                        'review' => 'Review',
                        'done' => 'Done',
                        'blocked' => 'Blocked',
                    ]),

                Tables\Filters\SelectFilter::make('prioritas')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'nama_project'),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Assigned To')
                    ->relationship('assignedTo', 'name'),

                Tables\Filters\Filter::make('my_tasks')
                    ->label('My Tasks')
                    ->query(fn (Builder $query): Builder => $query->where('assigned_to', auth()->id())),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('tanggal_deadline', '<', now())
                              ->where('status', '!=', 'done')
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    // Visibilitas tombol ini sekarang diatur oleh policy 'update'
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'todo' => 'To Do',
                                'in_progress' => 'In Progress',
                                'review' => 'Review',
                                'done' => 'Done',
                                'blocked' => 'Blocked',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('note')
                            ->label('Progress Note')
                            ->required(),
                        Forms\Components\TextInput::make('hours_worked')
                            ->label('Hours Worked')
                            ->numeric()
                            ->suffix('jam'),
                    ])
                    ->action(function (Task $record, array $data): void {
                        $record->updateStatus($data['status'], $data['note']);
                        
                        if (isset($data['hours_worked'])) {
                            $record->progressUpdates()->create([
                                'user_id' => auth()->id(),
                                'progress_note' => $data['note'],
                                'progress_percentage' => $record->progress_percentage,
                                'status_change' => $data['status'],
                                'hours_worked' => $data['hours_worked'],
                            ]);
                        }
                    }),

                // Filament akan otomatis menggunakan TaskPolicy::view()
                Tables\Actions\ViewAction::make(),
                
                // Filament akan otomatis menggunakan TaskPolicy::update()
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Filament akan otomatis menggunakan TaskPolicy::deleteAny()
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}