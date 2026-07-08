<?php

namespace App\Livewire;

use App\Models\AgentConversation;
use App\Models\Client;
use App\Models\Goal;
use App\Models\KnowledgeChunk;
use App\Models\Strategy;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AgentPage extends Component
{
    public ?Client $client = null;
    public string $input = '';
    public array $messages = [];
    public bool $thinking = false;
    #[\Livewire\Attributes\Locked]
    public ?string $conversationId = null;
    public bool $historyOpen = false;

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isClientUser()) {
            $this->client = $user->profile?->client;
        } else {
            $clientId = Session::get('active_client_id');
            $this->client = $clientId ? Client::find($clientId) : null;
        }

        // Load most recent conversation
        if ($this->client) {
            $conv = AgentConversation::where('client_id', $this->client->id)
                ->where('user_id', auth()->id())
                ->latest()
                ->first();
            if ($conv) {
                $this->conversationId = $conv->id;
                $this->messages = $conv->messages ?? [];
            }
        }
    }

    public function newChat(): void
    {
        $this->messages = [];
        $this->conversationId = null;
        $this->input = '';
        $this->historyOpen = false;
    }

    public function loadConversation(string $id): void
    {
        $conv = AgentConversation::where('id', $id)
            ->where('client_id', $this->client?->id)
            ->where('user_id', auth()->id())
            ->first();
        if ($conv) {
            $this->conversationId = $conv->id;
            $this->messages = $conv->messages ?? [];
            $this->input = '';
        }
        $this->historyOpen = false;
    }

    public function conversations()
    {
        if (!$this->client) return collect();
        return AgentConversation::where('client_id', $this->client->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(20)
            ->get(['id', 'title', 'created_at']);
    }

    public function send(): void
    {
        $text = trim($this->input);
        if (!$text || !$this->client) return;

        $this->messages[] = ['role' => 'user', 'content' => $text];
        $this->input = '';
        $this->thinking = true;

        // Build context
        $goals = Goal::where('client_id', $this->client->id)->where('archived', false)->get();
        $strategy = Strategy::where('client_id', $this->client->id)->where('status', 'approved')->latest()->first();

        $system = "You are a digital marketing strategist assistant for {$this->client->name}. "
            . "Be concise, actionable, and specific. Use markdown for formatting.\n\n";

        // Inject client brief (who they are) if available
        if ($this->client->client_brief) {
            $system .= "CLIENT BRIEF:\n" . $this->client->client_brief . "\n\n";
        }

        if ($strategy) {
            $system .= "APPROVED STRATEGY:\n" . mb_substr($strategy->generated_document ?? '', 0, 3000) . "\n\n";
        }
        if ($goals->isNotEmpty()) {
            $system .= "ACTIVE GOALS:\n" . $goals->map(fn($g) => "- {$g->title} ({$g->status})")->join("\n") . "\n\n";
        }

        // Inject knowledge chunks — up to 8 document chunks + up to 8 website chunks
        $docChunks  = KnowledgeChunk::where('client_id', $this->client->id)
            ->where('source_type', 'document')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
        $webChunks  = KnowledgeChunk::where('client_id', $this->client->id)
            ->where('source_type', 'website')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
        $chunks = $docChunks->concat($webChunks);
        if ($chunks->isNotEmpty()) {
            $system .= "KNOWLEDGE BASE:\n";
            foreach ($chunks as $chunk) {
                $system .= "[{$chunk->source_label}]: " . mb_substr($chunk->chunk_text, 0, 500) . "\n---\n";
            }
        }

        set_time_limit(120);

        try {
            $response = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 1500,
                messages: collect($this->messages)->map(fn($m) => ['role' => $m['role'], 'content' => $m['content']])->toArray(),
                model: 'claude-sonnet-4-6',
                system: $system,
            );
            $reply = $response->content[0]->text ?? 'Sorry, I could not respond.';
        } catch (\Exception $e) {
            $reply = 'Error: ' . $e->getMessage();
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $reply];
        $this->thinking = false;

        // Persist conversation
        if ($this->conversationId) {
            AgentConversation::find($this->conversationId)?->update(['messages' => $this->messages]);
        } else {
            $conv = AgentConversation::create([
                'client_id' => $this->client->id,
                'user_id'   => auth()->id(),
                'messages'  => $this->messages,
                'title'     => substr($text, 0, 60),
            ]);
            $this->conversationId = $conv->id;
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent', [
            'conversations' => $this->conversations(),
        ]);
    }
}
