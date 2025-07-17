<?php
// app/Enums/ProjectStatus.php

namespace App\Enums;

enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case PLANNING = 'planning';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PLANNING => 'Planning',
            self::IN_PROGRESS => 'In Progress',
            self::REVIEW => 'Under Review',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'warning',
            self::PLANNING => 'secondary',
            self::IN_PROGRESS => 'primary',
            self::REVIEW => 'info',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DRAFT => 'heroicon-o-document',
            self::PLANNING => 'heroicon-o-calendar',
            self::IN_PROGRESS => 'heroicon-o-play',
            self::REVIEW => 'heroicon-o-eye',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }

    public function canCreateTask(): bool
    {
        return in_array($this, [self::IN_PROGRESS, self::REVIEW]);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($status) => [$status->value => $status->label()])
            ->toArray();
    }

    public static function activeStatuses(): array
    {
        return [self::IN_PROGRESS->value, self::REVIEW->value];
    }
}