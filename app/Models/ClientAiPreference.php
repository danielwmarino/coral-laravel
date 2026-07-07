<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientAiPreference extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'client_id';
    public $timestamps = false;

    protected $fillable = ['client_id', 'preferred_provider'];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function provider(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AiProviderConnection::class, 'preferred_provider', 'provider');
    }
}
