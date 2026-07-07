<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiProviderConnection extends Model
{
    use HasUuids;

    protected $fillable = ['provider', 'label', 'encrypted_key', 'key_preview', 'added_by'];

    protected $hidden = ['encrypted_key'];

    public function setRawKey(string $key): void
    {
        $this->encrypted_key = Crypt::encryptString($key);
        $this->key_preview = substr($key, 0, 3) . '...' . substr($key, -4);
    }

    public function getRawKey(): string
    {
        return Crypt::decryptString($this->encrypted_key);
    }

    public function addedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
