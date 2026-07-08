<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id', 'strategy_id', 'title', 'description', 'smart_details',
        'status', 'target_value', 'current_value', 'metric_type', 'due_date',
        'strategist_notes', 'archived',
    ];

    protected $casts = [
        'smart_details' => 'array',
        'archived' => 'boolean',
        'due_date' => 'date',
        'target_value' => 'decimal:4',
        'current_value' => 'decimal:4',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function strategy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function analyticsLinks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GoalAnalyticsLink::class);
    }

    public function progressPercent(): float
    {
        if (!$this->target_value) {
            return 0;
        }
        return min(100, round(($this->current_value / $this->target_value) * 100, 1));
    }
}
