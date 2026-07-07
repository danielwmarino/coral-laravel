<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['goal_id', 'client_id', 'title', 'completed'];

    protected $casts = [
        'completed' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function goal(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
