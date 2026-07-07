<?php

namespace App\Livewire;

use App\Models\Goal;
use App\Models\Task;
use Livewire\Component;

class GoalDetail extends Component
{
    public string $goalId;
    public ?Goal $goal = null;

    // Edit form
    public bool $editOpen = false;
    public string $editTitle = '';
    public string $editDescription = '';
    public string $editStatus = 'not_started';
    public string $editMetricType = 'number';
    public string $editCurrentValue = '';
    public string $editTargetValue = '';
    public string $editDueDate = '';
    public string $editStrategistNotes = '';

    // Delete
    public bool $deleteOpen = false;
    public bool $deleting = false;

    public function mount(string $goalId): void
    {
        $this->goalId = $goalId;
        $this->goal = Goal::with('tasks')->find($goalId);
        if ($this->goal) $this->fillEdit();
    }

    private function fillEdit(): void
    {
        $this->editTitle           = $this->goal->title;
        $this->editDescription     = $this->goal->description ?? '';
        $this->editStatus          = $this->goal->status;
        $this->editMetricType      = $this->goal->metric_type ?? 'number';
        $this->editCurrentValue    = (string) ($this->goal->current_value ?? '');
        $this->editTargetValue     = (string) ($this->goal->target_value ?? '');
        $this->editDueDate         = $this->goal->due_date?->format('Y-m-d') ?? '';
        $this->editStrategistNotes = $this->goal->strategist_notes ?? '';
    }

    public function saveEdit(): void
    {
        $this->validate(['editTitle' => 'required|string|max:255']);
        $this->goal->update([
            'title'            => $this->editTitle,
            'description'      => $this->editDescription ?: null,
            'status'           => $this->editStatus,
            'metric_type'      => $this->editMetricType,
            'current_value'    => $this->editCurrentValue ?: 0,
            'target_value'     => $this->editTargetValue ?: null,
            'due_date'         => $this->editDueDate ?: null,
            'strategist_notes' => $this->editStrategistNotes ?: null,
        ]);
        $this->goal->refresh();
        $this->editOpen = false;
        session()->flash('toast', 'Goal updated');
    }

    public function toggleTask(string $taskId): void
    {
        $task = Task::find($taskId);
        if ($task) $task->update(['completed' => !$task->completed]);
        $this->goal->load('tasks');
    }

    public function toggleArchive(): void
    {
        $this->goal->update(['archived' => !$this->goal->archived]);
        $this->goal->refresh();
        session()->flash('toast', $this->goal->archived ? 'Goal archived' : 'Goal restored');
    }

    public function deleteGoal(): void
    {
        $this->deleting = true;
        $this->goal->delete();
        session()->flash('toast', 'Goal deleted');
        $this->redirect(route('goals.index'));
    }

    public function render(): \Illuminate\View\View
    {
        $isAgency = auth()->user()->isAgency();
        return view('livewire.goal-detail', ['isAgency' => $isAgency]);
    }
}
