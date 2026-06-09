<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = ['subject_type', 'subject_id', 'causer_id', 'description', 'properties'];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(Model $subject, string $description, array $properties = [], ?User $causer = null): self
    {
        return static::create([
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->getKey(),
            'causer_id'    => $causer ? $causer->id : auth()->id(),
            'description'  => $description,
            'properties'   => $properties,
        ]);
    }
}
