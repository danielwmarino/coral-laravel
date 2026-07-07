<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Strategy;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class StrategyPage extends Component
{
    public ?string $selectedId = null;
    public ?Client $client = null;

    // Editor state
    public string $docText = '';
    public string $reviewNotes = '';
    public bool $editing = false;
    public bool $saving = false;
    public bool $reviewing = false;
    public bool $copied = false;

    // Delete confirm
    public ?string $deleteTargetId = null;
    public bool $deleting = false;

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
            $this->client = $clientId ? Client::find($clientId) : null;
        }

        $strategies = $this->strategies();
        if ($strategies->isNotEmpty() && !$this->selectedId) {
            $first = $strategies->first();
            $this->selectedId = $first->id;
            $this->docText = $first->generated_document ?? '';
            $this->reviewNotes = $first->review_notes ?? '';
        }
    }

    public function strategies()
    {
        if (!$this->client) return collect();
        return Strategy::where('client_id', $this->client->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function selectStrategy(string $id): void
    {
        $strategy = Strategy::find($id);
        if (!$strategy) return;
        $this->selectedId = $id;
        $this->docText = $strategy->generated_document ?? '';
        $this->reviewNotes = $strategy->review_notes ?? '';
        $this->editing = false;
    }

    public function saveDocument(): void
    {
        $strategy = Strategy::find($this->selectedId);
        if (!$strategy) return;

        $updates = ['generated_document' => $this->docText];
        $resubmitting = ($strategy->status === 'approved' && $this->editing)
            || $strategy->status === 'changes_requested';

        if ($resubmitting) {
            $updates['status'] = 'in_review';
            $updates['review_notes'] = null;
        }

        $strategy->update($updates);
        $this->editing = false;
        session()->flash('toast', $resubmitting ? 'Strategy resubmitted for review' : 'Strategy saved');
    }

    public function approve(): void
    {
        $strategy = Strategy::find($this->selectedId);
        if (!$strategy) return;
        $strategy->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'review_notes' => $this->reviewNotes ?: null,
        ]);
        session()->flash('toast', 'Strategy approved');
    }

    public function requestChanges(): void
    {
        if (!trim($this->reviewNotes)) {
            session()->flash('error', 'Add review notes before requesting changes');
            return;
        }
        $strategy = Strategy::find($this->selectedId);
        if (!$strategy) return;
        $strategy->update(['status' => 'changes_requested', 'review_notes' => $this->reviewNotes]);
        session()->flash('toast', 'Changes requested');
    }

    public function archiveStrategy(string $id): void
    {
        Strategy::find($id)?->update(['archived' => true]);
        session()->flash('toast', 'Strategy archived');
    }

    public function unarchiveStrategy(string $id): void
    {
        Strategy::find($id)?->update(['archived' => false]);
        session()->flash('toast', 'Strategy restored');
    }

    public function confirmDelete(string $id): void
    {
        $this->deleteTargetId = $id;
    }

    public function deleteStrategy(): void
    {
        if (!$this->deleteTargetId) return;
        $this->deleting = true;
        $strategy = Strategy::find($this->deleteTargetId);
        if ($strategy) {
            if ($this->selectedId === $strategy->id) {
                $this->selectedId = null;
                $this->docText = '';
            }
            $strategy->delete();
        }
        $this->deleteTargetId = null;
        $this->deleting = false;
        session()->flash('toast', 'Strategy deleted');
    }

    public function render(): \Illuminate\View\View
    {
        $strategies = $this->strategies();
        $selected = $this->selectedId ? Strategy::find($this->selectedId) : null;
        $active = $strategies->where('archived', false)->values();
        $archived = $strategies->where('archived', true)->values();
        $isAgency = auth()->user()->isAgency();

        return view('livewire.strategy', compact('strategies', 'selected', 'active', 'archived', 'isAgency'));
    }
}
