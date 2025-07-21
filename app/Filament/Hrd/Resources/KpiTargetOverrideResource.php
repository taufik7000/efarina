<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\KpiTargetOverrideResource\Pages;
use App\Models\KpiTargetOverride;
use App\Models\KpiTarget;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class KpiTargetOverrideResource extends Resource
{
    protected static ?string $model = KpiTargetOverride::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'KPI Management';
    protected static ?string $navigationLabel = 'Target Overrides';
    protected static ?string $pluralModelLabel = 'Target Overrides';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Override Details')
                    ->description('Create individual target overrides for specific employees')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Employee')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => 
                                "{$record->name} - {$record->jabatan?->nama_jabatan}"
                            ),

                        Forms\Components\Select::make('kpi_target_id')
                            ->label('Base KPI Target')
                            ->relationship('kpiTarget', 'target_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('field_name', null)),

                        Forms\Components\Select::make('field_name')
                            ->label('Field to Override')
                            ->options([
                                'min_attendance_rate' => 'Minimum Attendance Rate (%)',
                                'max_late_days' => 'Maximum Late Days',
                                'max_absent_days' => 'Maximum Absent Days',
                                'min_tasks_per_month' => 'Minimum Tasks per Month',
                                'min_completion_rate' => 'Minimum Completion Rate (%)',
                                'max_overdue_tasks' => 'Maximum Overdue Tasks',
                                'target_avg_completion_days' => 'Target Average Completion Days',
                                'min_quality_score' => 'Minimum Quality Score',
                                'target_client_satisfaction' => 'Target Client Satisfaction (1-5)',
                                'max_revision_rate' => 'Maximum Revision Rate (%)',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                $targetId = $get('kpi_target_id');
                                if ($targetId && $state) {
                                    $target = KpiTarget::find($targetId);
                                    if ($target) {
                                        $originalValue = $target->getAttribute($state);
                                        $set('original_value', $originalValue);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('original_value')
                            ->label('Current Value')
                            ->disabled()
                            ->helperText('Current value from the base target'),

                        Forms\Components\TextInput::make('override_value')
                            ->label('New Value')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                $originalValue = $get('original_value');
                                if ($originalValue !== null && $state !== null) {
                                    $overrideType = $state > $originalValue ? 'increase' : 
                                                   ($state < $originalValue ? 'decrease' : 'custom');
                                    $set('override_type', $overrideType);
                                }
                            }),

                        Forms\Components\Select::make('override_type')
                            ->label('Override Type')
                            ->options([
                                'increase' => 'Increase Target',
                                'decrease' => 'Decrease Target',
                                'custom' => 'Custom Value',
                            ])
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Justification & Period')
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for Override')
                            ->required()
                            ->placeholder('Explain why this override is necessary (e.g., "Part-time employee", "Medical condition", "Senior role expectations")')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('effective_from')
                            ->label('Effective From')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('effective_until')
                            ->label('Effective Until')
                            ->helperText('Leave empty for permanent override')
                            ->after('effective_from'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending Approval',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'expired' => 'Expired',
                            ])
                            ->default('pending')
                            ->disabled(fn ($context) => $context === 'create'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->disabled(fn ($context) => $context === 'create'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Approval Information')
                    ->schema([
                        Forms\Components\Select::make('approved_by')
                            ->label('Approved By')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Approved At')
                            ->disabled(),

                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Approval Notes')
                            ->placeholder('Notes from the approver...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn ($context) => $context !== 'create'),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\KeyValue::make('additional_data')
                            ->label('Additional Data')
                            ->keyLabel('Property')
                            ->valueLabel('Value')
                            ->addActionLabel('Add property')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.jabatan.nama_jabatan')
                    ->label('Position')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('kpiTarget.target_display_name')
                    ->label('Base Target')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('field_display_name')
                    ->label('Field')
                    ->searchable(['field_name'])
                    ->wrap(),

                Tables\Columns\TextColumn::make('override_display')
                    ->label('Override')
                    ->searchable(false)
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('override_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (KpiTargetOverride $record): string => $record->override_type_color),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (KpiTargetOverride $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('period_display')
                    ->label('Period')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('remaining_days')
                    ->label('Days Left')
                    ->state(function (KpiTargetOverride $record): string {
                        $remaining = $record->getRemainingDays();
                        if ($remaining === null) {
                            return 'Permanent';
                        }
                        if ($remaining <= 0) {
                            return 'Expired';
                        }
                        return $remaining . ' days';
                    })
                    ->color(function (KpiTargetOverride $record): string {
                        $remaining = $record->getRemainingDays();
                        if ($remaining === null) return 'info';
                        if ($remaining <= 0) return 'danger';
                        if ($remaining <= 7) return 'warning';
                        return 'success';
                    })
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requested By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Employee'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ]),

                Tables\Filters\SelectFilter::make('override_type')
                    ->options([
                        'increase' => 'Increase',
                        'decrease' => 'Decrease',
                        'custom' => 'Custom',
                    ]),

                Tables\Filters\SelectFilter::make('field_name')
                    ->options([
                        'min_attendance_rate' => 'Attendance Rate',
                        'max_late_days' => 'Late Days',
                        'max_absent_days' => 'Absent Days',
                        'min_tasks_per_month' => 'Tasks per Month',
                        'min_completion_rate' => 'Completion Rate',
                        'max_overdue_tasks' => 'Overdue Tasks',
                        'target_avg_completion_days' => 'Completion Days',
                        'min_quality_score' => 'Quality Score',
                        'target_client_satisfaction' => 'Client Satisfaction',
                        'max_revision_rate' => 'Revision Rate',
                    ])
                    ->label('Field'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Expiring Soon (30 days)')
                    ->query(fn (Builder $query): Builder => $query->expiringSoon(30)),

                Tables\Filters\Filter::make('effective_now')
                    ->label('Currently Effective')
                    ->query(fn (Builder $query): Builder => $query->effective()),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    
                    Tables\Actions\EditAction::make()
                        ->visible(fn (KpiTargetOverride $record): bool => 
                            $record->canBeEditedBy(auth()->user())
                        ),

                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (KpiTargetOverride $record): bool => 
                            $record->canBeApprovedBy(auth()->user())
                        )
                        ->form([
                            Forms\Components\Textarea::make('approval_notes')
                                ->label('Approval Notes')
                                ->placeholder('Optional notes for approval...')
                                ->rows(3),
                        ])
                        ->action(function (KpiTargetOverride $record, array $data) {
                            $record->approve(auth()->user(), $data['approval_notes'] ?? null);
                            
                            Notification::make()
                                ->title('Override Approved')
                                ->body('Target override has been approved.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (KpiTargetOverride $record): bool => 
                            $record->canBeApprovedBy(auth()->user())
                        )
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->placeholder('Explain why this override is rejected...')
                                ->rows(3),
                        ])
                        ->action(function (KpiTargetOverride $record, array $data) {
                            $record->reject(auth()->user(), $data['rejection_reason']);
                            
                            Notification::make()
                                ->title('Override Rejected')
                                ->body('Target override has been rejected.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('extend')
                        ->label('Extend Period')
                        ->icon('heroicon-o-calendar-days')
                        ->color('info')
                        ->visible(fn (KpiTargetOverride $record): bool => 
                            $record->status === 'approved' && 
                            $record->effective_until && 
                            auth()->user()->hasRole(['admin', 'hrd'])
                        )
                        ->form([
                            Forms\Components\DatePicker::make('new_effective_until')
                                ->label('New End Date')
                                ->required()
                                ->after('today'),
                                
                            Forms\Components\Textarea::make('extension_reason')
                                ->label('Extension Reason')
                                ->required()
                                ->placeholder('Reason for extending the override period...')
                                ->rows(2),
                        ])
                        ->action(function (KpiTargetOverride $record, array $data) {
                            $record->update([
                                'effective_until' => $data['new_effective_until'],
                                'approval_notes' => ($record->approval_notes ?? '') . 
                                    "\n\nExtended until {$data['new_effective_until']}: {$data['extension_reason']}",
                            ]);
                            
                            Notification::make()
                                ->title('Override Extended')
                                ->body('Override period has been extended.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('view_impact')
                        ->label('View Impact')
                        ->icon('heroicon-o-chart-bar')
                        ->color('warning')
                        ->modalHeading('Override Impact')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalContent(function (KpiTargetOverride $record) {
                            return view('filament.hrd.modals.override-impact', [
                                'override' => $record,
                                'impact_description' => $record->getImpactDescription(),
                            ]);
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn (KpiTargetOverride $record): bool => 
                            $record->canBeDeletedBy(auth()->user())
                        ),
                ])
                ->button()
                ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Bulk Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Textarea::make('bulk_approval_notes')
                                ->label('Approval Notes')
                                ->placeholder('Notes for all approved overrides...')
                                ->rows(3),
                        ])
                        ->action(function ($records, array $data) {
                            $approved = 0;
                            foreach ($records as $record) {
                                if ($record->canBeApprovedBy(auth()->user())) {
                                    $record->approve(auth()->user(), $data['bulk_approval_notes'] ?? null);
                                    $approved++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Bulk Approval Complete')
                                ->body("{$approved} overrides have been approved.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_reject')
                        ->label('Bulk Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('bulk_rejection_reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->placeholder('Reason for rejecting all selected overrides...')
                                ->rows(3),
                        ])
                        ->action(function ($records, array $data) {
                            $rejected = 0;
                            foreach ($records as $record) {
                                if ($record->canBeApprovedBy(auth()->user())) {
                                    $record->reject(auth()->user(), $data['bulk_rejection_reason']);
                                    $rejected++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Bulk Rejection Complete')
                                ->body("{$rejected} overrides have been rejected.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('bulk_extend')
                        ->label('Bulk Extend Period')
                        ->icon('heroicon-o-calendar-days')
                        ->color('info')
                        ->form([
                            Forms\Components\DatePicker::make('new_effective_until')
                                ->label('New End Date')
                                ->required()
                                ->after('today'),
                                
                            Forms\Components\Textarea::make('extension_reason')
                                ->label('Extension Reason')
                                ->required()
                                ->placeholder('Reason for extending all selected overrides...')
                                ->rows(2),
                        ])
                        ->action(function ($records, array $data) {
                            $extended = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'approved' && $record->effective_until) {
                                    $record->update([
                                        'effective_until' => $data['new_effective_until'],
                                        'approval_notes' => ($record->approval_notes ?? '') . 
                                            "\n\nBulk extended until {$data['new_effective_until']}: {$data['extension_reason']}",
                                    ]);
                                    $extended++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Bulk Extension Complete')
                                ->body("{$extended} overrides have been extended.")
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
                            
                            $message = $deleted . ' overrides deleted.';
                            if ($skipped > 0) {
                                $message .= " {$skipped} overrides skipped (no permission).";
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
                Tables\Actions\Action::make('bulk_create_overrides')
                    ->label('Bulk Create Overrides')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('target_id')
                            ->label('Base KPI Target')
                            ->options(KpiTarget::active()->get()->mapWithKeys(function ($target) {
                                return [$target->id => $target->target_display_name];
                            }))
                            ->required()
                            ->searchable(),

                        Forms\Components\CheckboxList::make('user_ids')
                            ->label('Employees')
                            ->options(User::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->bulkToggleable()
                            ->required()
                            ->columns(3),

                        Forms\Components\Repeater::make('overrides')
                            ->label('Field Overrides')
                            ->schema([
                                Forms\Components\Select::make('field_name')
                                    ->label('Field')
                                    ->options([
                                        'min_attendance_rate' => 'Minimum Attendance Rate (%)',
                                        'max_late_days' => 'Maximum Late Days',
                                        'max_absent_days' => 'Maximum Absent Days',
                                        'min_tasks_per_month' => 'Minimum Tasks per Month',
                                        'min_completion_rate' => 'Minimum Completion Rate (%)',
                                        'max_overdue_tasks' => 'Maximum Overdue Tasks',
                                        'target_avg_completion_days' => 'Target Average Completion Days',
                                        'min_quality_score' => 'Minimum Quality Score',
                                        'target_client_satisfaction' => 'Target Client Satisfaction (1-5)',
                                        'max_revision_rate' => 'Maximum Revision Rate (%)',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('new_value')
                                    ->label('New Value')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->addActionLabel('Add Override')
                            ->reorderableWithButtons()
                            ->collapsible(),

                        Forms\Components\Textarea::make('bulk_reason')
                            ->label('Reason for Override')
                            ->required()
                            ->placeholder('Explain why these overrides are necessary...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('effective_from')
                            ->label('Effective From')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('effective_until')
                            ->label('Effective Until')
                            ->helperText('Leave empty for permanent override')
                            ->after('effective_from'),
                    ])
                    ->action(function (array $data) {
                        $target = KpiTarget::find($data['target_id']);
                        $userIds = $data['user_ids'];
                        $overrides = [];
                        
                        foreach ($data['overrides'] as $override) {
                            $overrides[$override['field_name']] = $override['new_value'];
                        }
                        
                        $created = KpiTargetOverride::bulkCreateOverrides(
                            $userIds,
                            $target,
                            $overrides,
                            $data['bulk_reason'],
                            auth()->user()
                        );
                        
                        // Update effective dates if provided
                        if (isset($data['effective_from']) || isset($data['effective_until'])) {
                            foreach ($created as $override) {
                                $override->update([
                                    'effective_from' => $data['effective_from'] ?? $override->effective_from,
                                    'effective_until' => $data['effective_until'] ?? $override->effective_until,
                                ]);
                            }
                        }
                        
                        Notification::make()
                            ->title('Bulk Overrides Created')
                            ->body(count($created) . ' target overrides have been created.')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No Target Overrides')
            ->emptyStateDescription('Create target overrides for specific employees who need different KPI targets.')
            ->emptyStateIcon('heroicon-o-adjustments-horizontal');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKpiTargetOverrides::route('/'),
            'create' => Pages\CreateKpiTargetOverride::route('/create'),

            'edit' => Pages\EditKpiTargetOverride::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = static::getModel()::pending()->count();
        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}