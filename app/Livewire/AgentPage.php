<?php

namespace App\Livewire;

use App\Models\AgentConversation;
use App\Models\Client;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AgentPage extends Component
{
    public ?Client $client = null;
    public string $input = '';
    public array $messages = [];
    public bool $thinking = false;
    public ?string $conversationId = null;

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isClientUser()) {
            $this->client = $user->profile?->client;
        } else {
            $clientId = Session::get('active_client_id');
            $this->client = $clientId ? Client::find($clientId) : null;
        }

        // Load or create conversation
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

    public function send(): void
    {
        $text = trim($this->input);
        if (!$text || !$this->client) return;

        $this->messages[] = ['role' => 'user', 'content' => $text];
        $this->input = '';
        $this->thinking = true;

        // Build context
        $goals = \App\Models\Goal::where('client_id', $this->client->id)->where('archived', false)->get();
        $strategy = \App\Models\Strategy::where('client_id', $this->client->id)->where('status', 'approved')->latest()->first();

        $system = "You are a digital marketing strategist assistant for {$this->client->name}. "
            . "Be concise, actionable, and specific. Use markdown for formatting.\n\n";

        // Inject executive summary if available
        if ($this->client->executive_summary) {
            $system .= "CLIENT BRIEF:\n" . $this->client->executive_summary . "\n\n";
        }

        if ($strategy) {
            $system .= "APPROVED STRATEGY:\n" . mb_substr($strategy->generated_document ?? '', 0, 3000) . "\n\n";
        }
        if ($goals->isNotEmpty()) {
            $system .= "ACTIVE GOALS:\n" . $goals->map(fn($g) => "- {$g->title} ({$g->status})")->join("\n") . "\n\n";
        }

        // Inject up to 10 knowledge chunks (documents + website)
        $chunks = \App\Models\KnowledgeChunk::where('client_id', $this->client->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        if ($chunks->isNotEmpty()) {
            $system .= "KNOWLEDGE BASE:\n";
            foreach ($chunks as $chunk) {
                $system .= "[{$chunk->source_label}]: " . mb_substr($chunk->chunk_text, 0, 500) . "\n---\n";
            }
        }

        set_time_limit(120);
        $streamKey = 'agent_stream_' . auth()->id();
        cache()->forget($streamKey);

        try {
            $reply = '';
            $stream = app(\Anthropic\Client::class)->messages->stream(
                maxTokens: 1500,
                messages: collect($this->messages)->map(fn($m) => ['role' => $m['role'], 'content' => $m['content']])->toArray(),
                model: 'claude-sonnet-4-6',
                system: $system,
            );
            foreach ($stream as $response) {
                if ($response->type === 'content_block_delta' && isset($response->delta->text)) {
                    $reply .= $response->delta->text;
                    cache()->put($streamKey, $reply, 120);
                }
            }
            if (empty($reply)) {
                $reply = $stream->getResponse()->content[0]->text ?? 'Sorry, I could not respond.';
            }
        } catch (\Exception $e) {
            $reply = 'Error: ' . $e->getMessage();
        }

        cache()->forget($streamKey);
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

    public function getStreamChunk(): string
    {
        return cache()->get('agent_stream_' . auth()->id(), '');
    }

    public function clearChat(): void
    {
        $this->messages = [];
        $this->conversationId = null;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent');
    }
}
