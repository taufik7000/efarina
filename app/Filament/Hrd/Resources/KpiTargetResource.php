<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\KpiTargetResource\Pages;
use App\Models\KpiTarget;
use App\Models\Jabatan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class KpiTargetResource extends Resource
{
    protected static ?string $model = KpiTarget::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'KPI Management';
    protected static ?string $navigationLabel = 'KPI Targets';
    protected static ?string $pluralModelLabel = 'KPI Targets';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Target Scope')
                    ->description('Define who this target applies to')
                    ->schema([
                        Forms\Components\Select::make('target_type')
                            ->label('Target Scope')
                            ->options([
                                'global' => 'Global (All Employees)',
                                'jabatan' => 'Specific Position',
                                'individual' => 'Individual Employee',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('target_id', null)),

                        Forms\Components\Select::make('target_id')
                            ->label('Specific Target')
                            ->options(function (Forms\Get $get) {
                                return match ($get('target_type')) {
                                    'jabatan' => Jabatan::pluck('nama_jabatan', 'id')->toArray(),
                                    'individual' => User::pluck('name', 'id')->toArray(),
                                    default => [],
                                };
                            })
                            ->searchable()
                            ->visible(fn (Forms\Get $get): bool => in_array($get('target_type'), ['jabatan', 'individual']))
                            ->required(fn (Forms\Get $get): bool => in_array($get('target_type'), ['jabatan', 'individual'])),

                        Forms\Components\TextInput::make('target_name')
                            ->label('Target Name')
                            ->placeholder('e.g., Q1 2024 Targets, Senior Developer Goals')
                            ->maxLength(255),

                        Forms\Components\Select::make('period_type')
                            ->label('Period Type')
                            ->options([
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'yearly' => 'Yearly',
                            ])
                            ->default('monthly')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Effective Period')
                    ->description('When this target is active')
                    ->schema([
                        Forms\Components\DatePicker::make('effective_from')
                            ->label('Effective From')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('effective_until')
                            ->label('Effective Until')
                            ->helperText('Leave empty for ongoing target')
                            ->after('effective_from'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Attendance Targets')
                    ->description('Set attendance performance expectations')
                    ->schema([
                        Forms\Components\TextInput::make('min_attendance_rate')
                            ->label('Minimum Attendance Rate (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(95)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),

                        Forms\Components\TextInput::make('max_late_days')
                            ->label('Maximum Late Days per Period')
                            ->numeric()
                            ->default(2)
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('max_absent_days')
                            ->label('Maximum Absent Days per Period')
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Task Performance Targets')
                    ->description('Set task completion expectations')
                    ->schema([
                        Forms\Components\TextInput::make('min_tasks_per_month')
                            ->label('Minimum Tasks per Month')
                            ->numeric()
                            ->default(10)
                            ->minValue(1)
                            ->required(),

                        Forms\Components\TextInput::make('min_completion_rate')
                            ->label('Minimum Completion Rate (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(90)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),

                        Forms\Components\TextInput::make('max_overdue_tasks')
                            ->label('Maximum Overdue Tasks')
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('target_avg_completion_days')
                            ->label('Target Avg Completion (Days)')
                            ->numeric()
                            ->default(3)
                            ->minValue(0.1)
                            ->step(0.1)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Quality Targets')
                    ->description('Set quality performance expectations')
                    ->schema([
                        Forms\Components\TextInput::make('min_quality_score')
                            ->label('Minimum Quality Score')
                            ->numeric()
                            ->default(80)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),

                        Forms\Components\TextInput::make('target_client_satisfaction')
                            ->label('Target Client Satisfaction')
                            ->numeric()
                            ->default(4.0)
                            ->minValue(1)
                            ->maxValue(5)
                            ->step(0.1)
                            ->required(),

                        Forms\Components\TextInput::make('max_revision_rate')
                            ->label('Maximum Revision Rate (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(20)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Scoring Weights')
                    ->description('Define how different areas contribute to overall score (must total 100%)')
                    ->schema([
                        Forms\Components\TextInput::make('attendance_weight')
                            ->label('Attendance Weight (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(30)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $total = ($get('attendance_weight') ?? 0) + 
                                        ($get('task_completion_weight') ?? 0) + 
                                        ($get('quality_weight') ?? 0);
                                
                                if ($total > 100) {
                                    Notification::make()
                                        ->title('Warning')
                                        ->body('Total weights exceed 100%. Please adjust.')
                                        ->warning()
                                        ->send();
                                }
                            }),

                        Forms\Components\TextInput::make('task_completion_weight')
                            ->label('Task Completion Weight (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(40)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('quality_weight')
                            ->label('Quality Weight (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(30)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->live(),

                        Forms\Components\Placeholder::make('total_weight')
                            ->label('Total Weight')
                            ->content(function (Forms\Get $get): string {
                                $total = ($get('attendance_weight') ?? 0) + 
                                        ($get('task_completion_weight') ?? 0) + 
                                        ($get('quality_weight') ?? 0);
                                
                                $color = $total === 100 ? 'success' : ($total > 100 ? 'danger' : 'warning');
                                return "<span class='text-{$color}-600 font-bold'>{$total}%</span>";
                            }),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Optional description for this target...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive targets will not be used for KPI calculations'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('target_name')
                    ->label('Target Name')
                    ->searchable(['target_name', 'target_type'])
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn (KpiTarget $record): string => $record->target_display_name),

                Tables\Columns\BadgeColumn::make('target_type')
                    ->label('Scope')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'global' => 'Global',
                        'jabatan' => 'Position',
                        'individual' => 'Individual',
                        default => ucfirst($state)
                    })
                    ->color(fn (string $state): string => match($state) {
                        'global' => 'primary',
                        'jabatan' => 'info',
                        'individual' => 'warning',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('effective_from')
                    ->label('Effective Period')
                    ->formatStateUsing(fn (KpiTarget $record): string => $record->period_display)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('min_attendance_rate')
                    ->label('Attendance Target')
                    ->formatStateUsing(fn ($state): string => $state . '%')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('min_tasks_per_month')
                    ->label('Min Tasks')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('min_completion_rate')
                    ->label('Completion Rate')
                    ->formatStateUsing(fn ($state): string => $state . '%')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('weights_summary')
                    ->label('Weights (A/T/Q)')
                    ->state(function (KpiTarget $record): string {
                        return "{$record->attendance_weight}%/{$record->task_completion_weight}%/{$record->quality_weight}%";
                    })
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('attendance_weight')
                    ->label('Valid Weights')
                    ->state(fn (KpiTarget $record): bool => $record->is_valid_weights)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('target_type')
                    ->options([
                        'global' => 'Global',
                        'jabatan' => 'Position',
                        'individual' => 'Individual',
                    ])
                    ->label('Target Scope'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_active', true),
                        false: fn (Builder $query) => $query->where('is_active', false),
                    ),

                Tables\Filters\Filter::make('effective_now')
                    ->label('Currently Effective')
                    ->query(fn (Builder $query): Builder => $query->effective()),

                Tables\Filters\Filter::make('invalid_weights')
                    ->label('Invalid Weights')
                    ->query(function (Builder $query): Builder {
                        return $query->whereRaw('(attendance_weight + task_completion_weight + quality_weight) != 100');
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('new_name')
                                ->label('New Target Name')
                                ->required()
                                ->placeholder('Copy of original target'),
                                
                            Forms\Components\DatePicker::make('new_effective_from')
                                ->label('New Effective From')
                                ->required()
                                ->default(now()),
                        ])
                        ->action(function (KpiTarget $record, array $data) {
                            $newTarget = $record->replicate();
                            $newTarget->target_name = $data['new_name'];
                            $newTarget->effective_from = $data['new_effective_from'];
                            $newTarget->effective_until = null;
                            $newTarget->created_by = auth()->id();
                            $newTarget->save();

                            Notification::make()
                                ->title('Target Duplicated')
                                ->body('New target created successfully.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('preview_impact')
                        ->label('Preview Impact')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->modalHeading('Target Impact Preview')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalContent(function (KpiTarget $record) {
                            // Calculate how many employees this would affect
                            $affectedCount = match($record->target_type) {
                                'global' => User::count(),
                                'jabatan' => User::where('jabatan_id', $record->target_id)->count(),
                                'individual' => 1,
                                default => 0
                            };

                            return view('filament.hrd.modals.target-impact-preview', [
                                'target' => $record,
                                'affected_count' => $affectedCount,
                            ]);
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn (KpiTarget $record): bool => 
                            $record->canBeDeletedBy(auth()->user())
                        ),
                ])
                ->button()
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                            
                            Notification::make()
                                ->title('Targets Activated')
                                ->body(count($records) . ' targets have been activated.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title('Targets Deactivated')
                                ->body(count($records) . ' targets have been deactivated.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            $canDelete = $records->filter(fn ($record) => 
                                $record->canBeDeletedBy(auth()->user())
                            );
                            
                            $canDelete->each->delete();
                            
                            $deleted = $canDelete->count();
                            $skipped = $records->count() - $deleted;
                            
                            $message = $deleted . ' targets deleted.';
                            if ($skipped > 0) {
                                $message .= " {$skipped} targets skipped (in use).";
                            }
                            
                            Notification::make()
                                ->title('Bulk Delete Complete')
                                ->body($message)
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('No KPI Targets')
            ->emptyStateDescription('Create your first KPI target to get started.')
            ->emptyStateIcon('heroicon-o-user');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKpiTargets::route('/'),
            'create' => Pages\CreateKpiTarget::route('/create'),
            'edit' => Pages\EditKpiTarget::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $invalidCount = static::getModel()::whereRaw('(attendance_weight + task_completion_weight + quality_weight) != 100')
                                        ->where('is_active', true)
                                        ->count();
        
        return $invalidCount > 0 ? (string) $invalidCount : null;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!isset($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        $data['updated_by'] = auth()->id();
        return $data;
    }
}