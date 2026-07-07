<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AnalyticsConnection extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'client_id', 'platform', 'oauth_token', 'config', 'connected_at', 'last_synced_at',
    ];

    protected $casts = [
        'config' => 'array',
        'connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = ['oauth_token'];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function goalLinks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GoalAnalyticsLink::class);
    }
}
