<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;

class StrategistMessageEditor extends Component
{
    public Client $client;

    public string $message = '';
    public bool $editing = false;

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->message = $client->strategist_message ?? '';
    }

    public function startEditing(): void
    {
        if (!auth()->user()->isAgency()) return;
        $this->editing = true;
    }

    public function save(): void
    {
        if (!auth()->user()->isAgency()) return;

        $this->validate(['message' => 'nullable|string|max:5000']);

        $this->client->update(['strategist_message' => $this->message]);
        $this->editing = false;
        session()->flash('message', 'Strategist message saved.');
    }

    public function cancel(): void
    {
        $this->message = $this->client->strategist_message ?? '';
        $this->editing = false;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.strategist-message-editor');
    }
}
