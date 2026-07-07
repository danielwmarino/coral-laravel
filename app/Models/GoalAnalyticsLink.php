<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GoalAnalyticsLink extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'goal_id', 'analytics_connection_id', 'metric_key',
        'target_value', 'current_value', 'last_updated',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
        'target_value' => 'decimal:4',
        'current_value' => 'decimal:4',
    ];

    public function goal(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function analyticsConnection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AnalyticsConnection::class);
    }
}
