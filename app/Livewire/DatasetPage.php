<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class DatasetPage extends Component
{
    public ?Client $client = null;

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isClientUser()) {
            $this->client = $user->profile?->client;
        } else {
            $clientId = Session::get('active_client_id');
            $this->client = $clientId ? Client::find($clientId) : null;
        }
    }

    public function render(): \Illuminate\View\View
    {
        $connections = $this->client
            ? \App\Models\AnalyticsConnection::where('client_id', $this->client->id)->get()
            : collect();
        $isAgency = auth()->user()->isAgency();
        return view('livewire.dataset', compact('connections', 'isAgency'));
    }
}
