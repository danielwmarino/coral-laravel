<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ClientKnowledgeMeta extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id', 'website_url', 'last_crawled_at', 'crawl_status',
        'crawl_page_count', 'summary_text', 'summary_updated_at',
    ];

    protected $casts = [
        'last_crawled_at' => 'datetime',
        'summary_updated_at' => 'datetime',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
