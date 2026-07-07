<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Goal;
use App\Models\Strategy;
use App\Models\Task;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class GoalsPage extends Component
{
    public ?Client $client = null;
    public bool $generating = false;
    public string $generateError = '';

    // Review dialog (generate flow)
    public bool $reviewOpen = false;
    public array $reviewItems = [];  // [{id, title, description, isExisting, suggestionIndex}]
    public array $reviewChecked = [];
    public string $suggestionsJson = '[]';

    // Add goal dialog
    public bool $addOpen = false;
    public string $addTitle = '';
    public string $addDescription = '';
    public string $addStatus = 'not_started';
    public string $addMetricType = 'number';
    public string $addTargetValue = '';
    public string $addDueDate = '';

    // Manage dialog
    public bool $manageOpen = false;
    public array $manageSelected = [];
    public ?string $confirmAction = null;

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

    public function goals()
    {
        if (!$this->client) return collect();
        return Goal::where('client_id', $this->client->id)->orderBy('created_at', 'desc')->get();
    }

    public function approvedStrategy(): ?Strategy
    {
        if (!$this->client) return null;
        return Strategy::where('client_id', $this->client->id)
            ->where('status', 'approved')->latest()->first();
    }

    public function generateGoals(): void
    {
        $strategy = $this->approvedStrategy();
        if (!$strategy || !$this->client) return;

        $this->generating = true;
        $this->generateError = '';
        set_time_limit(120);

        $existingGoals = Goal::where('client_id', $this->client->id)->where('archived', false)->get();
        $existingText = $existingGoals->isNotEmpty()
            ? $existingGoals->map(fn($g) => "- {$g->title}" . ($g->description ? ": {$g->description}" : ''))->join("\n")
            : '(none)';

        $strategyText = mb_substr($strategy->generated_document ?? json_encode($strategy->content), 0, 4000);

        $prompt = "Based on the following approved digital marketing strategy, suggest new SMART goals not already covered by the client's existing goals.\n\n"
            . "STRATEGY:\n{$strategyText}\n\n"
            . "EXISTING GOALS (do not duplicate):\n{$existingText}\n\n"
            . "Return a JSON array of at most 6 new goals. Each object: {title, description, smart_details:{specific,measurable,achievable,relevant,time_bound}, metric_type (one of: number|percentage|currency|rank), target_value (a single number), due_date (YYYY-MM-DD), tasks:[]}. "
            . "Keep descriptions under 100 characters. Keep smart_details values under 80 characters each. "
            . "Return [] if existing goals already cover the strategy. Return ONLY the JSON array, no other text.";

        try {
            $response = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 3000,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: 'claude-haiku-4-5-20251001',
            );
            $raw = $response->content[0]->text ?? '[]';
            $start = strpos($raw, '[');
            $end = strrpos($raw, ']');
            $raw = ($start !== false && $end !== false) ? substr($raw, $start, $end - $start + 1) : '[]';
            $suggestions = json_decode($raw, true);
            if (!is_array($suggestions)) {
                $this->generateError = 'Could not parse AI response. Please try again.';
                $this->generating = false;
                return;
            }
        } catch (\Exception $e) {
            $this->generateError = 'Failed to generate goals: ' . $e->getMessage();
            $this->generating = false;
            return;
        }

        // If no suggestions, nothing to review
        if (empty($suggestions)) {
            $this->generateError = 'Your existing goals already cover the strategy. No new goals to add.';
            $this->generating = false;
            return;
        }

        // Build review items: new suggestions only (existing goals stay as-is)
        $items = [];
        foreach ($suggestions as $i => $s) {
            $items[] = [
                'id'              => null,
                'title'           => $s['title'] ?? '',
                'description'     => $s['description'] ?? '',
                'isExisting'      => false,
                'suggestionIndex' => $i,
            ];
        }

        // All checked by default
        $this->reviewItems = $items;
        $this->reviewChecked = array_fill(0, count($items), true);
        $this->suggestionsJson = json_encode($suggestions);
        $this->generating = false;
        $this->reviewOpen = true;
    }

    public function deleteReviewItem(int $index): void
    {
        $item = $this->reviewItems[$index] ?? null;
        if (!$item) return;

        if ($item['isExisting'] && $item['id']) {
            Goal::where('id', $item['id'])->where('client_id', $this->client?->id)->delete();
        }

        // Remove from arrays
        array_splice($this->reviewItems, $index, 1);
        array_splice($this->reviewChecked, $index, 1);
    }

    public function applyReview(): void
    {
        if (!$this->client) return;
        $strategy = $this->approvedStrategy();
        $suggestions = json_decode($this->suggestionsJson, true) ?? [];

        foreach ($this->reviewItems as $i => $item) {
            $checked = (bool) ($this->reviewChecked[$i] ?? false);

            if ($item['isExisting']) {
                // Uncheck = archive existing goal
                if (!$checked && $item['id']) {
                    Goal::where('id', $item['id'])->where('client_id', $this->client->id)->update(['archived' => true]);
                }
            } else {
                // Check = save new suggestion
                if ($checked) {
                    $s = $suggestions[$item['suggestionIndex']] ?? [];
                    if (empty($s)) continue;

                    preg_match('/[\d.]+/', (string) ($s['target_value'] ?? 0), $m);
                    $dueDate = null;
                    if (!empty($s['due_date'])) {
                        try { $dueDate = \Carbon\Carbon::parse($s['due_date'])->toDateString(); } catch (\Exception $e) {}
                    }
                    $goal = Goal::create([
                        'client_id'     => $this->client->id,
                        'strategy_id'   => $strategy?->id,
                        'title'         => $s['title'],
                        'description'   => $s['description'] ?? null,
                        'smart_details' => $s['smart_details'] ?? [],
                        'metric_type'   => in_array($s['metric_type'] ?? '', ['number','percentage','currency','rank']) ? $s['metric_type'] : 'number',
                        'target_value'  => isset($m[0]) ? (float) $m[0] : 0,
                        'due_date'      => $dueDate,
                        'status'        => 'not_started',
                    ]);
                    foreach (($s['tasks'] ?? []) as $task) {
                        $title = is_array($task) ? ($task['title'] ?? '') : (string) $task;
                        if ($title) Task::create(['goal_id' => $goal->id, 'client_id' => $this->client->id, 'title' => $title]);
                    }
                }
            }
        }

        $this->reviewOpen = false;
        $this->reviewItems = [];
        $this->reviewChecked = [];
        $this->suggestionsJson = '[]';
        session()->flash('toast', 'Goals updated');
    }

    public function saveNewGoal(): void
    {
        $this->validate(['addTitle' => 'required|string|max:255']);
        if (!$this->client) return;

        Goal::create([
            'client_id'    => $this->client->id,
            'title'        => $this->addTitle,
            'description'  => $this->addDescription ?: null,
            'status'       => $this->addStatus,
            'metric_type'  => $this->addMetricType,
            'target_value' => $this->addTargetValue ?: 0,
            'due_date'     => $this->addDueDate ?: null,
            'smart_details' => [],
        ]);
        $this->addOpen = false;
        $this->addTitle = $this->addDescription = $this->addTargetValue = $this->addDueDate = '';
        session()->flash('toast', 'Goal added');
    }

    public function openManage(): void
    {
        $this->manageSelected = [];
        $this->confirmAction = null;
        $this->manageOpen = true;
    }

    public function deleteGoalDirect(string $id): void
    {
        Goal::where('id', $id)->where('client_id', $this->client?->id)->delete();
        unset($this->manageSelected[$id]);
        session()->flash('toast', 'Goal deleted');
    }

    public function executeBulk(): void
    {
        $ids = array_keys(array_filter($this->manageSelected));
        if (empty($ids)) return;

        if ($this->confirmAction === 'archive') {
            Goal::whereIn('id', $ids)->where('client_id', $this->client?->id)->update(['archived' => true]);
        } elseif ($this->confirmAction === 'restore') {
            Goal::whereIn('id', $ids)->where('client_id', $this->client?->id)->update(['archived' => false]);
        } elseif ($this->confirmAction === 'delete') {
            Goal::whereIn('id', $ids)->where('client_id', $this->client?->id)->delete();
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
