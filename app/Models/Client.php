<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'name', 'slug', 'logo_url', 'strategist_message',
        'executive_summary', 'executive_summary_updated_at',
    ];

    protected $casts = [
        'executive_summary_updated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function userProfiles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserProfile::class);
    }

    public function strategies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Strategy::class);
    }

    public function goals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function insights(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Insight::class);
    }

    public function agentConversations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AgentConversation::class);
    }

    public function analyticsConnections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnalyticsConnection::class);
    }

    public function knowledgeMeta(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ClientKnowledgeMeta::class);
    }

    public function knowledgeChunks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(KnowledgeChunk::class);
    }

    public function aiPreference(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ClientAiPreference::class);
    }
}
