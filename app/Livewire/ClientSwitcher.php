<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class ClientSwitcher extends Component
{
    public ?string $selectedClientId = null;
    public string $selectedClientName = 'Select Client';
    public string $pageUrl = '';

    public function mount(): void
    {
        $this->pageUrl = url()->current();
        $user = auth()->user();

        if ($user->isClientUser()) {
            // Client users are locked to their own client
            $client = $user->profile?->client;
            if ($client) {
                $this->selectedClientId = $client->id;
                $this->selectedClientName = $client->name;
                Session::put('active_client_id', $client->id);
            }
        } else {
            // Agency staff — restore from session or default to first client
            $sessionId = Session::get('active_client_id');
            $sessionClient = $sessionId ? Client::find($sessionId) : null;
            if ($sessionClient) {
                $this->selectedClientId = $sessionId;
                $this->selectedClientName = $sessionClient->name;
            } else {
                $first = Client::orderBy('name')->first();
                if ($first) {
                    $this->selectedClientId = $first->id;
                    $this->selectedClientName = $first->name;
                    Session::put('active_client_id', $first->id);
                }
            }
        }
    }

    public function switchClient(string $clientId): void
    {
        $user = auth()->user();

        // Client users cannot switch
        if ($user->isClientUser()) {
            return;
        }

        $client = Client::find($clientId);
        if (!$client) return;

        $this->selectedClientId = $client->id;
        $this->selectedClientName = $client->name;
        Session::put('active_client_id', $client->id);

        $this->redirect($this->pageUrl ?: route('dashboard'));
    }

    public function render(): \Illuminate\View\View
    {
        $user = auth()->user();

        $clients = $user->isClientUser()
            ? collect() // client users don't see the switcher list
            : Client::orderBy('name')->get();

        return view('livewire.client-switcher', [
            'clients' => $clients,
            'isAgency' => $user->isAgency(),
        ]);
    }
}
