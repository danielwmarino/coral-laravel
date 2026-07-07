<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Strategy extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id', 'status', 'content', 'generated_document',
        'created_by', 'reviewed_by', 'review_notes', 'archived',
    ];

    protected $casts = [
        'content' => 'array',
        'archived' => 'boolean',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function goals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class);
    }
}
