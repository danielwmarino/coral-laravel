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
        if (!auth()->user()->isAgency() || !$this->client) {
            return;
        }

        // Placeholder — AI generation job will go here
        session()->flash('message', 'Summary regeneration queued.');
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
