<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Audit extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id',
        'product_name',
        'product_url',
        'auditor_name',
        'product_type',
        'audit_mode',
        'audit_date',
        'status',
        'ux_score',
        'content_score',
        'overall_score',
        'crawled_pages',
        'custom_pages',
    ];

    protected $casts = [
        'audit_date'    => 'date',
        'crawled_pages' => 'array',
        'custom_pages'  => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AuditResponse::class);
    }
}
