<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'client_id', 'type', 'title', 'content', 'priority',
        'category', 'dismissed', 'saved',
    ];

    protected $casts = [
        'content' => 'array',
        'dismissed' => 'boolean',
        'saved' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
