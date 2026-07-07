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
            . "Return a JSON array of exactly 6 recommendations sorted by priority (high first). Each object: {title, body, why, category (SEO|Paid|Content|Social|Email|Analytics|CRO), priority (high|medium|low), effort (low|medium|high), impact (low|medium|high)}. "
            . "'body' is the actionable recommendation (2-4 sentences). 'why' explains the strategic reasoning and what happens if ignored (1-3 sentences). Return ONLY the JSON array.";

        try {
            $response = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 2000,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: 'claude-sonnet-4-6',
            );
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
                        'why'    => $item['why'] ?? '',
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
        Insight::where('id', $id)->where('client_id', $this->client?->id)->update(['saved' => true]);
        session()->flash('toast', 'Recommendation saved');
    }

    public function unsaveRec(string $id): void
    {
        Insight::where('id', $id)->where('client_id', $this->client?->id)->update(['saved' => false]);
    }

    public function dismissRec(string $id): void
    {
        Insight::where('id', $id)->where('client_id', $this->client?->id)->update(['dismissed' => true]);
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

        $client = $this->client;
        $isAgency = auth()->user()->isAgency();
        return view('livewire.recommendations', compact('recs', 'isAgency', 'client'));
    }
}
