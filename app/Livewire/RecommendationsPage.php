<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Insight;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class RecommendationsPage extends Component
{
    public ?Client $client = null;
    public bool $generating = false;

    public function mount(): void { $this->loadClient(); }

    public function loadClient(): void
    {
        $user = auth()->user();
        if ($user->isClientUser()) {
            $this->client = $user->profile?->client;
        } else {
            $clientId = Session::get('active_client_id');
            $this->client = $clientId ? Client::find($clientId) : null;
        }
    }

    public function generate(): void
    {
        if (!$this->client) return;
        $this->generating = true;

        $goals = \App\Models\Goal::where('client_id', $this->client->id)->where('archived', false)->get();
        $goalsText = $goals->isNotEmpty()
            ? $goals->map(fn($g) => "- {$g->title} (status: {$g->status}, progress: {$g->progressPercent()}%)")->join("\n")
            : '(no goals set)';

        $prompt = "You are a digital marketing strategist. Based on the following client goals, generate 6 actionable marketing recommendations.\n\nCLIENT: {$this->client->name}\nGOALS:\n{$goalsText}\n\n"
            . "Return a JSON array of exactly 6 recommendations, each: {title, body, category (SEO|Paid|Content|Social|Email|Analytics|CRO), priority (high|medium|low), effort (low|medium|high), impact (low|medium|high)}. Return ONLY the JSON array.";

        try {
            $response = app(\Anthropic\Client::class)->messages->create([
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 2000,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);
            $raw = $response->content[0]->text ?? '[]';
            $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
            $raw = preg_replace('/\s*```$/i', '', $raw);
            $items = json_decode($raw, true) ?? [];

            foreach ($items as $item) {
                Insight::create([
                    'client_id' => $this->client->id,
                    'type'      => 'recommendation',
                    'title'     => $item['title'] ?? 'Recommendation',
                    'priority'  => $item['priority'] ?? 'medium',
                    'category'  => $item['category'] ?? null,
                    'content'   => [
                        'body'   => $item['body'] ?? '',
                        'effort' => $item['effort'] ?? 'medium',
                        'impact' => $item['impact'] ?? 'medium',
                    ],
                ]);
            }
            session()->flash('toast', 'Recommendations generated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate: ' . $e->getMessage());
        }

        $this->generating = false;
    }

    public function saveRec(string $id): void
    {
        Insight::find($id)?->update(['saved' => true]);
    }

    public function dismissRec(string $id): void
    {
        Insight::find($id)?->update(['dismissed' => true]);
    }

    public function render(): \Illuminate\View\View
    {
        $recs = $this->client
            ? Insight::where('client_id', $this->client->id)
                ->where('type', 'recommendation')
                ->where('dismissed', false)
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        $isAgency = auth()->user()->isAgency();
        return view('livewire.recommendations', compact('recs', 'isAgency'));
    }
}
