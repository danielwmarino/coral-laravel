<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Goal;
use App\Models\Strategy;
use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class GoalsPage extends Component
{
    public ?Client $client = null;
    public bool $generating = false;

    // Review dialog
    public bool $reviewOpen = false;
    public array $reviewItems = [];
    public array $reviewChecked = [];

    // Add goal dialog
    public bool $addOpen = false;
    public string $addTitle = '';
    public string $addDescription = '';
    public string $addStatus = 'not_started';
    public string $addMetricType = 'number';
    public string $addTargetValue = '';
    public string $addDueDate = '';
    public bool $addSaving = false;

    // Manage dialog
    public bool $manageOpen = false;
    public array $manageSelected = [];
    public ?string $confirmAction = null;

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
    }

    public function goals()
    {
        if (!$this->client) return collect();
        return Goal::where('client_id', $this->client->id)->orderBy('created_at', 'desc')->get();
    }

    public function approvedStrategy(): ?Strategy
    {
        if (!$this->client) return null;
        return Strategy::where('client_id', $this->client->id)
            ->where('status', 'approved')
            ->latest()
            ->first();
    }

    public function generateGoals(): void
    {
        $strategy = $this->approvedStrategy();
        if (!$strategy || !$this->client) return;

        $this->generating = true;

        $existingGoals = Goal::where('client_id', $this->client->id)->get();
        $existingText = $existingGoals->isNotEmpty()
            ? $existingGoals->map(fn($g) => "- {$g->title}" . ($g->description ? ": {$g->description}" : ''))->join("\n")
            : '(none)';

        $prompt = "Based on the following approved digital marketing strategy, suggest new SMART goals not already covered by the client's existing goals.\n\n"
            . "STRATEGY:\n" . ($strategy->generated_document ?? json_encode($strategy->content)) . "\n\n"
            . "EXISTING GOALS (do not duplicate):\n{$existingText}\n\n"
            . "Return a JSON array of new goals only. Each object: {title, description, smart_details:{specific,measurable,achievable,relevant,time_bound}, metric_type, target_value, due_date, tasks:[]}. "
            . "Return [] if existing goals already cover the strategy. Return ONLY the JSON array.";

        try {
            $response = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 3000,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: 'claude-sonnet-4-6',
            );

            $raw = $response->content[0]->text ?? '[]';
            $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
            $raw = preg_replace('/\s*```$/i', '', $raw);
            $suggestions = json_decode($raw, true) ?? [];
        } catch (\Exception $e) {
            $suggestions = [];
        }

        // Build review list: existing active goals + suggestions
        $items = [];
        foreach ($existingGoals->where('archived', false) as $g) {
            $items[] = ['title' => $g->title, 'description' => $g->description, 'goalId' => $g->id, 'isExisting' => true];
        }
        foreach ($suggestions as $s) {
            $items[] = ['title' => $s['title'], 'description' => $s['description'] ?? '', 'suggestion' => $s, 'isExisting' => false];
        }

        $this->reviewItems = $items;
        $this->reviewChecked = collect($items)->map(fn($item) => $item['isExisting'])->toArray();
        $this->reviewOpen = true;
        $this->generating = false;
    }

    public function applyReview(): void
    {
        if (!$this->client || !$this->approvedStrategy()) return;

        foreach ($this->reviewItems as $i => $item) {
            $checked = $this->reviewChecked[$i] ?? false;

            if ($item['isExisting'] && !$checked) {
                // Uncheck existing → archive
                Goal::find($item['goalId'])?->update(['archived' => true]);
            } elseif (!$item['isExisting'] && $checked) {
                // New suggestion checked → insert
                $s = $item['suggestion'];
                $goal = Goal::create([
                    'client_id'    => $this->client->id,
                    'strategy_id'  => $this->approvedStrategy()->id,
                    'title'        => $s['title'],
                    'description'  => $s['description'] ?? null,
                    'smart_details' => $s['smart_details'] ?? null,
                    'metric_type'  => $s['metric_type'] ?? 'number',
                    'target_value' => $s['target_value'] ?? 0,
                    'due_date'     => $s['due_date'] ?? null,
                    'status'       => 'not_started',
                ]);
                foreach (($s['tasks'] ?? []) as $taskTitle) {
                    Task::create(['goal_id' => $goal->id, 'title' => $taskTitle]);
                }
            }
        }

        $this->reviewOpen = false;
        $this->reviewItems = [];
        $this->reviewChecked = [];
        session()->flash('toast', 'Goals updated');
    }

    public function saveNewGoal(): void
    {
        $this->validate([
            'addTitle' => 'required|string|max:255',
        ]);
        if (!$this->client) return;
        $this->addSaving = true;
        Goal::create([
            'client_id'   => $this->client->id,
            'title'       => $this->addTitle,
            'description' => $this->addDescription ?: null,
            'status'      => $this->addStatus,
            'metric_type' => $this->addMetricType,
            'target_value' => $this->addTargetValue ?: 0,
            'due_date'    => $this->addDueDate ?: null,
        ]);
        $this->addOpen = false;
        $this->addTitle = $this->addDescription = $this->addTargetValue = $this->addDueDate = '';
        $this->addSaving = false;
        session()->flash('toast', 'Goal added');
    }

    // Manage dialog
    public function openManage(): void
    {
        $this->manageSelected = [];
        $this->confirmAction = null;
        $this->manageOpen = true;
    }

    public function executeBulk(): void
    {
        $ids = array_keys(array_filter($this->manageSelected));
        if (empty($ids)) return;

        if ($this->confirmAction === 'archive') {
            Goal::whereIn('id', $ids)->update(['archived' => true]);
        } elseif ($this->confirmAction === 'restore') {
            Goal::whereIn('id', $ids)->update(['archived' => false]);
        } elseif ($this->confirmAction === 'delete') {
            Goal::whereIn('id', $ids)->delete();
        }

        $this->confirmAction = null;
        $this->manageSelected = [];
        $this->manageOpen = false;
        session()->flash('toast', 'Goals updated');
    }

    public function render(): \Illuminate\View\View
    {
        $goals = $this->goals();
        $active = $goals->where('archived', false)->values();
        $archived = $goals->where('archived', true)->values();
        $allGoals = $goals;
        $isAgency = auth()->user()->isAgency();
        $hasStrategy = (bool) $this->approvedStrategy();

        return view('livewire.goals', compact('active', 'archived', 'allGoals', 'isAgency', 'hasStrategy'));
    }
}
