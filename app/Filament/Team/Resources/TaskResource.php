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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Task')
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'nama_project')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->columnSpan(2),

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

                Forms\Components\Section::make('Todo Items')
                    ->schema([
                        Forms\Components\Repeater::make('todo_items')
                            ->label('Todo Checklist')
                            ->schema([
                                Forms\Components\TextInput::make('text')
                                    ->label('Todo Item')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Checkbox::make('completed')
                                    ->label('Completed')
                                    ->default(false),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Todo Item')
                            ->defaultItems(0)
                            ->collapsible()
                            ->reorderable()
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['id'] = time() + rand(1, 1000);
                                $data['created_at'] = now()->toISOString();
                                $data['completed_at'] = null;
                                $data['completed_by'] = null;
                                return $data;
                            }),
                    ])
                    ->collapsible(),

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
                    ])
                    ->collapsible(),
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

                // 👇 KOLOM BARU UNTUK TODO STATS
                Tables\Columns\ViewColumn::make('todo_stats')
                    ->label('Todo')
                    ->view('filament.team.components.todo-stats'),

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

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        
        return parent::getEloquentQuery()
            ->with(['project', 'assignedTo', 'createdBy'])
            ->whereHas('project', function ($query) use ($user) {
                $query->where('project_manager_id', $user->id)
                      ->orWhereJsonContains('team_members', $user->id);
            })
            ->orWhere('assigned_to', $user->id)
            ->orWhere('created_by', $user->id);
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