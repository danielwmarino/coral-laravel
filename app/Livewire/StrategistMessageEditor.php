<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;

class StrategistMessageEditor extends Component
{
    public Client $client;

    public bool $editing = false;

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    #[\Livewire\Attributes\On('start-editing-message')]
    public function startEditing(): void
    {
        if (!auth()->user()->isAgency()) return;
        $this->editing = true;
    }

    public function saveHtml(string $html): void
    {
        if (!auth()->user()->isAgency()) return;

        $clean = strip_tags($html, '<p><br><div><span><strong><b><em><i><u><ul><ol><li><h1><h2><h3><blockquote>');
        $this->client->update(['strategist_message' => $clean]);
        $this->client->refresh();
        $this->editing = false;
        session()->flash('message', 'Strategist message saved.');
    }

    public function cancel(): void
    {
        $this->editing = false;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.strategist-message-editor');
    }
}
