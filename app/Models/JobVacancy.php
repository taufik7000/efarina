<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobVacancy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'job_type',
        'location',
        'description',
        'requirements',
        'salary_range',
        'application_deadline',
        'status',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'application_deadline' => 'date',
        'published_at' => 'datetime',
    ];

    /**
     * Get the job applications for the job vacancy.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}