<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name', 'key', 'description', 'type_id', 'color', 'status', 'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class, 'type_id');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(ProjectStatus::class)->orderBy('position');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ProjectCategory::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function openTasksCount(): int
    {
        return $this->tasks()
            ->whereHas('status', fn($q) => $q->where('is_done', false))
            ->count();
    }

    public function seedDefaultStatuses(): void
    {
        $defaults = [
            ['name' => 'Backlog',    'color' => '#64748b', 'position' => 0, 'is_default' => true],
            ['name' => 'To Do',      'color' => '#3b82f6', 'position' => 1],
            ['name' => 'In Progress','color' => '#f59e0b', 'position' => 2],
            ['name' => 'In Review',  'color' => '#8b5cf6', 'position' => 3],
            ['name' => 'Done',       'color' => '#22c55e', 'position' => 4, 'is_done' => true],
        ];

        foreach ($defaults as $status) {
            $this->statuses()->create($status);
        }
    }
}
