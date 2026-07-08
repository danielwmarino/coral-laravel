<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditResponse extends Model
{
    use HasUuids;

    protected $fillable = [
        'audit_id',
        'section',
        'category',
        'item_key',
        'response',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }
}
