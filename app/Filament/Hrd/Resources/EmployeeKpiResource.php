<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\EmployeeKpiResource\Pages;
use App\Models\EmployeeKpi;
use App\Models\User;
use App\Models\KpiTarget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class EmployeeKpiResource extends Resource
{
    protected static ?string $model = EmployeeKpi::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'KPI Management';
    protected static ?string $navigationLabel = 'Employee KPIs';
    protected static ?string $pluralModelLabel = 'Employee KPIs';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('KPI Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Employee')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\Select::make('period_year')
                            ->label('Year')
                            ->options(function () {
                                $currentYear = now()->year;
                                $years = [];
                                for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->required()
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\Select::make('period_month')
                            ->label('Month')
                            ->options([
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ])
                            ->default(now()->month)
                            ->required()
                            ->disabled(fn ($context) => $context === 'edit'),

                        Forms\Components\Select::make('kpi_target_id')
                            ->label('KPI Target Used')
                            ->relationship('kpiTarget', 'target_name')
                            ->searchable()
                            ->preload()
                            ->helperText('Target template used for this KPI calculation'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Calculated Scores')
                    ->description('Overall performance scores (0-100)')
                    ->schema([
                        Forms\Components\TextInput::make('attendance_score')
                            ->label('Attendance Score')
                            ->numeric()
                            ->suffix('/100')
                            ->minValue(0)
                            ->maxValue(100)
                            ->disabled(),

                        Forms\Components\TextInput::make('task_completion_score')
                            ->label('Task Completion Score')
                            ->numeric()
                            ->suffix('/100')
                            ->minValue(0)
                            ->maxValue(100)
                            ->disabled(),

                        Forms\Components\TextInput::make('quality_score')
                            ->label('Quality Score')
                            ->numeric()
                            ->suffix('/100')
                            ->minValue(0)
                            ->maxValue(100)
                            ->disabled(),

                        Forms\Components\TextInput::make('overall_score')
                            ->label('Overall Score')
                            ->numeric()
                            ->suffix('/100')
                            ->minValue(0)
                            ->maxValue(100)
                            ->disabled()
                            ->extraAttributes(['class' => 'font-bold']),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Review & Approval')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'calculated' => 'Calculated',
                                'reviewed' => 'Reviewed',
                                'approved' => 'Approved',
                                'disputed' => 'Disputed',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('comments')
                            ->label('HRD/Manager Comments')
                            ->placeholder('Add comments about this KPI...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('employee_notes')
                            ->label('Employee Notes')
                            ->placeholder('Employee feedback or dispute reason...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('reviewed_by')
                            ->label('Reviewed By')
                            ->relationship('reviewer', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('reviewed_at')
                            ->label('Reviewed At'),

                        Forms\Components\Select::make('approved_by')
                            ->label('Approved By')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Approved At'),

                        Forms\Components\Toggle::make('is_final')
                            ->label('Finalized')
                            ->helperText('Finalized KPIs cannot be modified'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Raw Metrics')
                    ->description('Detailed performance metrics used for calculation')
                    ->schema([
                        Forms\Components\Fieldset::make('Attendance Metrics')
                            ->schema([
                                Forms\Components\TextInput::make('total_working_days')
                                    ->label('Total Working Days')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('present_days')
                                    ->label('Present Days')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('late_days')
                                    ->label('Late Days')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('absent_days')
                                    ->label('Absent Days')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('attendance_rate')
                                    ->label('Attendance Rate')
                                    ->suffix('%')
                                    ->disabled(),
                            ])
                            ->columns(5),

                        Forms\Components\Fieldset::make('Task Performance Metrics')
                            ->schema([
                                Forms\Components\TextInput::make('total_tasks_assigned')
                                    ->label('Total Tasks')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('tasks_completed')
                                    ->label('Completed')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('tasks_overdue')
                                    ->label('Overdue')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('task_completion_rate')
                                    ->label('Completion Rate')
                                    ->suffix('%')
                                    ->disabled(),

                                Forms\Components\TextInput::make('average_task_completion_time')
                                    ->label('Avg Completion Time')
                                    ->suffix(' days')
                                    ->disabled(),
                            ])
                            ->columns(5),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.photo_url')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn (EmployeeKpi $record): string => 
                        'https://ui-avatars.com/api/?name=' . urlencode($record->user->name) . '&color=7F9CF5&background=EBF4FF'
                    )
                    ->size(40),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.jabatan.nama_jabatan')
                    ->label('Position')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('period_name')
                    ->label('Period')
                    ->searchable()
                    ->sortable(['period_year', 'period_month']),

                Tables\Columns\TextColumn::make('overall_score')
                    ->label('Overall Score')
                    ->formatStateUsing(fn ($state): string => number_format($state, 1) . '/100')
                    ->alignCenter()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('overall_grade')
                    ->label('Grade')
                    ->color(fn (EmployeeKpi $record): string => $record->overall_grade_color)
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('attendance_score')
                    ->label('Attendance')
                    ->formatStateUsing(fn ($state): string => number_format($state, 1))
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('task_completion_score')
                    ->label('Tasks')
                    ->formatStateUsing(fn ($state): string => number_format($state, 1))
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quality_score')
                    ->label('Quality')
                    ->formatStateUsing(fn ($state): string => number_format($state, 1))
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->color(fn (EmployeeKpi $record): string => $record->status_color)
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\IconColumn::make('is_final')
                    ->label('Final')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('calculated_at')
                    ->label('Calculated')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('period_year', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('period_year')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    })
                    ->default(now()->year),

                Tables\Filters\SelectFilter::make('period_month')
                    ->options([
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ])
                    ->default(now()->month),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Employee'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'calculated' => 'Calculated',
                        'reviewed' => 'Reviewed',
                        'approved' => 'Approved',
                        'disputed' => 'Disputed',
                    ]),

                Tables\Filters\SelectFilter::make('grade')
                    ->options([
                        'A' => 'A (90-100)',
                        'B' => 'B (80-89)',
                        'C' => 'C (70-79)',
                        'D' => 'D (60-69)',
                        'E' => 'E (<60)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $data['value'] ? $query->byGrade($data['value']) : $query;
                    }),

                Tables\Filters\Filter::make('needs_review')
                    ->label('Needs Review')
                    ->query(fn (Builder $query): Builder => $query->pendingReview()),

                Tables\Filters\Filter::make('low_performers')
                    ->label('Low Performers (<70)')
                    ->query(fn (Builder $query): Builder => $query->where('overall_score', '<', 70)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    
                    Tables\Actions\EditAction::make()
                        ->visible(fn (EmployeeKpi $record): bool => 
                            $record->canBeEditedBy(auth()->user())
                        ),

                    Tables\Actions\Action::make('review')
                        ->label('Review')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->visible(fn (EmployeeKpi $record): bool => 
                            in_array($record->status, ['calculated', 'disputed'])
                        )
                        ->form([
                            Forms\Components\Textarea::make('comments')
                                ->label('Review Comments')
                                ->required()
                                ->placeholder('Add your review comments...'),
                        ])
                        ->action(function (EmployeeKpi $record, array $data) {
                            $record->markAsReviewed(auth()->user(), $data['comments']);
                            
                            Notification::make()
                                ->title('KPI Reviewed')
                                ->body('KPI has been marked as reviewed.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (EmployeeKpi $record): bool => 
                            $record->canBeApprovedBy(auth()->user())
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Approve KPI')
                        ->modalDescription('Are you sure you want to approve this KPI? This action will finalize the KPI.')
                        ->form([
                            Forms\Components\Textarea::make('approval_comments')
                                ->label('Approval Comments')
                                ->placeholder('Optional approval comments...'),
                        ])
                        ->action(function (EmployeeKpi $record, array $data) {
                            $record->approve(auth()->user(), $data['approval_comments'] ?? null);
                            
                            Notification::make()
                                ->title('KPI Approved')
                                ->body('KPI has been approved and finalized.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('recalculate')
                        ->label('Recalculate')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->visible(fn (EmployeeKpi $record): bool => 
                            !$record->is_final && auth()->user()->hasRole(['admin', 'hrd'])
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Recalculate KPI')
                        ->modalDescription('This will recalculate the KPI based on current data. Continue?')
                        ->action(function (EmployeeKpi $record) {
                            try {
                                $newKpi = EmployeeKpi::calculateKpi(
                                    $record->user, 
                                    $record->period_year, 
                                    $record->period_month
                                );
                                
                                Notification::make()
                                    ->title('KPI Recalculated')
                                    ->body('KPI has been recalculated successfully.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Calculation Failed')
                                    ->body('Error: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('view_details')
                        ->label('View Details')
                        ->icon('heroicon-o-document-text')
                        ->color('gray')
                        ->modalHeading(fn (EmployeeKpi $record) => "KPI Details - {$record->user->name}")
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalContent(function (EmployeeKpi $record) {
                            return view('filament.hrd.modals.kpi-details', [
                                'kpi' => $record,
                                'breakdown' => $record->getScoreBreakdown(),
                                'comparison' => $record->getComparisonWithTarget(),
                            ]);
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn (EmployeeKpi $record): bool => 
                            !$record->is_final && auth()->user()->hasRole(['admin'])
                        ),
                ])
                ->button()
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_review')
                        ->label('Bulk Review')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->form([
                            Forms\Components\Textarea::make('bulk_comments')
                                ->label('Review Comments')
                                ->required()
                                ->placeholder('Comments for all selected KPIs...'),
                        ])
                        ->action(function ($records, array $data) {
                            $reviewed = 0;
                            foreach ($records as $record) {
                                if (in_array($record->status, ['calculated', 'disputed'])) {
                                    $record->markAsReviewed(auth()->user(), $data['bulk_comments']);
                                    $reviewed++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Bulk Review Complete')
                                ->body("{$reviewed} KPIs have been reviewed.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Bulk Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('bulk_approval_comments')
                                ->label('Approval Comments')
                                ->placeholder('Comments for all approved KPIs...'),
                        ])
                        ->action(function ($records, array $data) {
                            $approved = 0;
                            foreach ($records as $record) {
                                if ($record->canBeApprovedBy(auth()->user())) {
                                    $record->approve(auth()->user(), $data['bulk_approval_comments'] ?? null);
                                    $approved++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Bulk Approval Complete')
                                ->body("{$approved} KPIs have been approved.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('export_kpis')
                        ->label('Export KPIs')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function ($records) {
                            // Implementation for exporting KPIs
                            Notification::make()
                                ->title('Export Started')
                                ->body('KPI export is being processed.')
                                ->info()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            $canDelete = $records->filter(fn ($record) => 
                                !$record->is_final && auth()->user()->hasRole(['admin'])
                            );
                            
                            $canDelete->each->delete();
                            
                            $deleted = $canDelete->count();
                            $skipped = $records->count() - $deleted;
                            
                            $message = $deleted . ' KPIs deleted.';
                            if ($skipped > 0) {
                                $message .= " {$skipped} KPIs skipped (finalized or no permission).";
                            }
                            
                            Notification::make()
                                ->title('Bulk Delete Complete')
                                ->body($message)
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('calculate_monthly_kpis')
                    ->label('Calculate Monthly KPIs')
                    ->icon('heroicon-o-calculator')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('target_year')
                            ->label('Year')
                            ->options(function () {
                                $currentYear = now()->year;
                                $years = [];
                                for ($i = $currentYear - 1; $i <= $currentYear; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->required(),

                        Forms\Components\Select::make('target_month')
                            ->label('Month')
                            ->options([
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                            ])
                            ->default(now()->subMonth()->month)
                            ->required(),

                        Forms\Components\CheckboxList::make('user_ids')
                            ->label('Employees')
                            ->options(User::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Leave empty to calculate for all employees'),
                    ])
                    ->action(function (array $data) {
                        $year = $data['target_year'];
                        $month = $data['target_month'];
                        $userIds = $data['user_ids'] ?? [];
                        
                        $users = empty($userIds) ? User::all() : User::whereIn('id', $userIds)->get();
                        $calculated = 0;
                        $errors = 0;
                        
                        foreach ($users as $user) {
                            try {
                                EmployeeKpi::calculateKpi($user, $year, $month);
                                $calculated++;
                            } catch (\Exception $e) {
                                $errors++;
                                \Log::error('KPI Calculation failed', [
                                    'user_id' => $user->id,
                                    'year' => $year,
                                    'month' => $month,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                        
                        Notification::make()
                            ->title('Bulk KPI Calculation Complete')
                            ->body("Calculated: {$calculated}, Errors: {$errors}")
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No KPIs Found')
            ->emptyStateDescription('Start by calculating KPIs for your employees.')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Employee Information')
                    ->schema([
                        Components\TextEntry::make('user.name')
                            ->label('Employee Name'),
                        
                        Components\TextEntry::make('user.jabatan.nama_jabatan')
                            ->label('Position'),
                        
                        Components\TextEntry::make('period_name')
                            ->label('Period'),
                        
                        Components\TextEntry::make('kpiTarget.target_display_name')
                            ->label('Target Used'),
                    ])
                    ->columns(2),

                Components\Section::make('Performance Scores')
                    ->schema([
                        Components\TextEntry::make('overall_score')
                            ->label('Overall Score')
                            ->formatStateUsing(fn ($state): string => number_format($state, 1) . '/100')
                            ->badge()
                            ->color(fn (EmployeeKpi $record): string => $record->overall_grade_color),

                        Components\TextEntry::make('overall_grade')
                            ->label('Grade')
                            ->badge()
                            ->color(fn (EmployeeKpi $record): string => $record->overall_grade_color),

                        Components\TextEntry::make('attendance_score')
                            ->label('Attendance Score')
                            ->formatStateUsing(fn ($state): string => number_format($state, 1) . '/100'),

                        Components\TextEntry::make('task_completion_score')
                            ->label('Task Completion Score')
                            ->formatStateUsing(fn ($state): string => number_format($state, 1) . '/100'),

                        Components\TextEntry::make('quality_score')
                            ->label('Quality Score')
                            ->formatStateUsing(fn ($state): string => number_format($state, 1) . '/100'),
                    ])
                    ->columns(3),

                Components\Section::make('Detailed Metrics')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\Section::make('Attendance')
                                    ->schema([
                                        Components\TextEntry::make('attendance_rate')
                                            ->label('Attendance Rate')
                                            ->suffix('%'),
                                        Components\TextEntry::make('present_days')
                                            ->label('Present Days'),
                                        Components\TextEntry::make('late_days')
                                            ->label('Late Days'),
                                        Components\TextEntry::make('absent_days')
                                            ->label('Absent Days'),
                                    ]),

                                Components\Section::make('Task Performance')
                                    ->schema([
                                        Components\TextEntry::make('task_completion_rate')
                                            ->label('Completion Rate')
                                            ->suffix('%'),
                                        Components\TextEntry::make('tasks_completed')
                                            ->label('Tasks Completed'),
                                        Components\TextEntry::make('tasks_overdue')
                                            ->label('Overdue Tasks'),
                                        Components\TextEntry::make('average_task_completion_time')
                                            ->label('Avg Completion Time')
                                            ->suffix(' days'),
                                    ]),

                                Components\Section::make('Quality')
                                    ->schema([
                                        Components\TextEntry::make('average_task_rating')
                                            ->label('Avg Task Rating')
                                            ->suffix('/5'),
                                        Components\TextEntry::make('revision_rate')
                                            ->label('Revision Rate')
                                            ->suffix('%'),
                                        Components\TextEntry::make('client_satisfaction_avg')
                                            ->label('Client Satisfaction')
                                            ->suffix('/5'),
                                    ]),
                            ]),
                    ])
                    ->collapsible(),

                Components\Section::make('Review & Status')
                    ->schema([
                        Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (EmployeeKpi $record): string => $record->status_color),

                        Components\TextEntry::make('comments')
                            ->label('HRD Comments')
                            ->placeholder('No comments'),

                        Components\TextEntry::make('employee_notes')
                            ->label('Employee Notes')
                            ->placeholder('No employee notes'),

                        Components\TextEntry::make('reviewer.name')
                            ->label('Reviewed By')
                            ->placeholder('Not reviewed'),

                        Components\TextEntry::make('reviewed_at')
                            ->label('Reviewed At')
                            ->dateTime(),

                        Components\TextEntry::make('calculated_at')
                            ->label('Calculated At')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeKpis::route('/'),
            'create' => Pages\CreateEmployeeKpi::route('/create'),
            'edit' => Pages\EditEmployeeKpi::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = static::getModel()::pendingReview()->count();
        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}