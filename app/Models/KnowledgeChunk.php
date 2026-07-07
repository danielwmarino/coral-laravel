<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'client_id', 'source_type', 'source_label', 'chunk_text', 'embedding',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
