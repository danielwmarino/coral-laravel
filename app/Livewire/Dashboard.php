<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Goal;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Dashboard extends Component
{
    public ?Client $client = null;

    public function mount(): void
    {
        $this->loadClient();
    }

    public function loadClient(): void
    {
        $user = auth()->user();

        if ($user->isClientUser()) {
            $this->client = $user->profile?->client;
        } else {
            $clientId = Session::get('active_client_id');
            $this->client = $clientId ? Client::find($clientId) : Client::orderBy('name')->first();
        }
    }

    public function regenerateSummary(): void
    {
        if (!auth()->user()->isAgency() || !$this->client) return;

        set_time_limit(120);

        $goals = Goal::where('client_id', $this->client->id)->where('archived', false)->get();
        $strategy = \App\Models\Strategy::where('client_id', $this->client->id)
            ->where('status', 'approved')->latest()->first();

        $goalsText = $goals->isNotEmpty()
            ? $goals->map(fn($g) => "- {$g->title}: status={$g->status}, progress={$g->progressPercent()}%, target={$g->target_value}, current={$g->current_value}")->join("\n")
            : '(no goals set)';

        $strategyText = $strategy
            ? mb_substr($strategy->generated_document ?? json_encode($strategy->content), 0, 2000)
            : '(no approved strategy)';

        $prompt = "You are a senior marketing strategist writing a performance executive summary for a client dashboard.\n\n"
            . "CLIENT: {$this->client->name}\n\n"
            . "STRATEGY SNAPSHOT:\n{$strategyText}\n\n"
            . "CURRENT GOALS & PROGRESS:\n{$goalsText}\n\n"
            . "Write 2–3 short paragraphs (no bullet points, flowing prose) that:\n"
            . "1. Honestly assess current performance — what's working, what's not, what the numbers show\n"
            . "2. Call out wins, risks, or gaps that need attention right now\n"
            . "3. Give a clear sense of momentum — are things on track, ahead, or behind?\n\n"
            . "Be direct and specific. Reference actual goal names and numbers. Avoid generic marketing language. "
            . "Write as if briefing a CEO before a board meeting. Return only the summary text, no headers.";

        try {
            $response = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 600,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: 'claude-haiku-4-5-20251001',
            );
            $summary = $response->content[0]->text ?? '';
            $this->client->update([
                'executive_summary' => $summary,
                'executive_summary_updated_at' => now(),
            ]);
            $this->client->refresh();
        } catch (\Exception $e) {
            session()->flash('message', 'Failed to generate summary: ' . $e->getMessage());
        }
    }

    public function render(): \Illuminate\View\View
    {
        $goals = collect();
        $stats = ['total' => 0, 'in_progress' => 0, 'completed' => 0, 'at_risk' => 0];

        if ($this->client) {
            $allActive = Goal::where('client_id', $this->client->id)
                ->where('archived', false)
                ->get();

            $stats['total']       = $allActive->count();
            $stats['in_progress'] = $allActive->where('status', 'in_progress')->count();
            $stats['completed']   = $allActive->where('status', 'completed')->count();
            $stats['at_risk']     = $allActive->where('status', 'at_risk')->count();

            // Up to 6 active non-archived goals for the grid
            $goals = $allActive->take(6);
        }

        return view('livewire.dashboard', [
            'client' => $this->client,
            'goals'  => $goals,
            'stats'  => $stats,
            'isAgency' => auth()->user()->isAgency(),
        ]);
    }
}
