<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'task_number', 'title', 'description', 'type',
        'status_id', 'priority', 'assignee_id', 'reporter_id',
        'due_date', 'position', 'resolved_at', 'needs_discussion', 'needs_action',
    ];

    protected function casts(): array
    {
        return [
            'due_date'         => 'date',
            'resolved_at'      => 'datetime',
            'needs_discussion' => 'bool',
            'needs_action'     => 'bool',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProjectCategory::class, 'task_categories');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && is_null($this->resolved_at);
    }

    public static function typeIcon(string $type): string
    {
        return match($type) {
            'bug'         => 'bug',
            'feature'     => 'sparkles',
            'improvement' => 'arrow-trending-up',
            'chore'       => 'wrench',
            'question'    => 'question-mark-circle',
            default       => 'squares-plus',
        };
    }

    public static function typeColor(string $type): string
    {
        return match($type) {
            'bug'         => 'text-red-400',
            'feature'     => 'text-violet-400',
            'improvement' => 'text-blue-400',
            'chore'       => 'text-gray-400',
            'question'    => 'text-yellow-400',
            default       => 'text-gray-400',
        };
    }

    public static function priorityColor(string $priority): string
    {
        return match($priority) {
            'critical' => 'text-red-500',
            'high'     => 'text-orange-400',
            'medium'   => 'text-yellow-400',
            'low'      => 'text-gray-400',
            default    => 'text-gray-400',
        };
    }

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            $project = Project::find($task->project_id);
            $count = static::where('project_id', $task->project_id)->count() + 1;
            $task->task_number = $project->key . '-' . $count;
        });
    }
}
