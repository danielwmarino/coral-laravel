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

        $this->selectedId = null;
        $strategies = $this->strategies();
        if ($strategies->isNotEmpty()) {
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

    private function scopedStrategy(string $id): ?Strategy
    {
        if (!$this->client) return null;
        return Strategy::where('id', $id)
            ->where('client_id', $this->client->id)
            ->first();
    }

    public function selectStrategy(string $id): void
    {
        $strategy = $this->scopedStrategy($id);
        if (!$strategy) return;
        $this->selectedId = $id;
        $this->docText = $strategy->generated_document ?? '';
        $this->reviewNotes = $strategy->review_notes ?? '';
        $this->editing = false;
    }

    public function saveDocument(): void
    {
        $strategy = $this->scopedStrategy($this->selectedId ?? '');
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
        abort_unless(auth()->user()->isAgency(), 403);
        $strategy = $this->scopedStrategy($this->selectedId ?? '');
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
        abort_unless(auth()->user()->isAgency(), 403);
        if (!trim($this->reviewNotes)) {
            session()->flash('error', 'Add review notes before requesting changes');
            return;
        }
        $strategy = $this->scopedStrategy($this->selectedId ?? '');
        if (!$strategy) return;
        $strategy->update(['status' => 'changes_requested', 'review_notes' => $this->reviewNotes]);
        session()->flash('toast', 'Changes requested');
    }

    public function archiveStrategy(string $id): void
    {
        $this->scopedStrategy($id)?->update(['archived' => true]);
        session()->flash('toast', 'Strategy archived');
    }

    public function unarchiveStrategy(string $id): void
    {
        $this->scopedStrategy($id)?->update(['archived' => false]);
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
        $strategy = $this->scopedStrategy($this->deleteTargetId);
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
        $selected = $this->selectedId ? $this->scopedStrategy($this->selectedId) : null;
        $active = $strategies->where('archived', false)->values();
        $archived = $strategies->where('archived', true)->values();
        $isAgency = auth()->user()->isAgency();

        return view('livewire.strategy', compact('strategies', 'selected', 'active', 'archived', 'isAgency'));
    }
}
