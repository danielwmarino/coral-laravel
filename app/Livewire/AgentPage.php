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

        if ($strategy) {
            $system .= "APPROVED STRATEGY:\n" . ($strategy->generated_document ?? '') . "\n\n";
        }
        if ($goals->isNotEmpty()) {
            $system .= "ACTIVE GOALS:\n" . $goals->map(fn($g) => "- {$g->title} ({$g->status})")->join("\n");
        }

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
