<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Insight;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class InsightsPage extends Component
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

        $prompt = "You are a senior digital marketing analyst. Generate 6 external market insights relevant to {$this->client->name}.\n\n"
            . "Each insight should be an observation about industry trends, competitive landscape, or market opportunities — not internal recommendations.\n\n"
            . "Return a JSON array of 6 insights, each: {title, body, category (SEO|Paid|Content|Social|Email|Analytics|Industry), priority (high|medium|low)}. Return ONLY the JSON array.";

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
                    'type'      => 'external',
                    'title'     => $item['title'] ?? 'Insight',
                    'priority'  => $item['priority'] ?? 'medium',
                    'category'  => $item['category'] ?? null,
                    'content'   => ['body' => $item['body'] ?? ''],
                ]);
            }
            session()->flash('toast', 'Insights generated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed: ' . $e->getMessage());
        }

        $this->generating = false;
    }

    public function saveInsight(string $id): void { Insight::find($id)?->update(['saved' => true]); }
    public function dismissInsight(string $id): void { Insight::find($id)?->update(['dismissed' => true]); }

    public function render(): \Illuminate\View\View
    {
        $insights = $this->client
            ? Insight::where('client_id', $this->client->id)
                ->where('type', 'external')
                ->where('dismissed', false)
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();
        $isAgency = auth()->user()->isAgency();
        return view('livewire.insights', compact('insights', 'isAgency'));
    }
}
