<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\Goal;
use App\Models\Strategy;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateGoals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        public string $clientId,
        public string $strategyId,
    ) {}

    public function handle(): void
    {
        $client = Client::find($this->clientId);
        $strategy = Strategy::find($this->strategyId);
        if (!$client || !$strategy) return;

        $existingGoals = Goal::where('client_id', $this->clientId)->get();
        $existingText = $existingGoals->isNotEmpty()
            ? $existingGoals->map(fn($g) => "- {$g->title}" . ($g->description ? ": {$g->description}" : ''))->join("\n")
            : '(none)';

        // Trim strategy doc to avoid huge prompts
        $strategyText = $strategy->generated_document ?? json_encode($strategy->content);
        $strategyText = mb_substr($strategyText, 0, 6000);

        $prompt = "Based on the following approved digital marketing strategy, suggest new SMART goals not already covered by the client's existing goals.\n\n"
            . "STRATEGY:\n{$strategyText}\n\n"
            . "EXISTING GOALS (do not duplicate):\n{$existingText}\n\n"
            . "Return a JSON array of new goals only. Each object: {title, description, smart_details:{specific,measurable,achievable,relevant,time_bound}, metric_type, target_value, due_date, tasks:[]}. "
            . "Return [] if existing goals already cover the strategy. Return ONLY the JSON array.";

        $response = app(\Anthropic\Client::class)->messages->create(
            maxTokens: 2000,
            messages: [['role' => 'user', 'content' => $prompt]],
            model: 'claude-haiku-4-5-20251001',
        );

        $raw = $response->content[0]->text ?? '[]';
        $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $raw = preg_replace('/\s*```$/i', '', $raw);
        $suggestions = json_decode($raw, true) ?? [];

        foreach ($suggestions as $s) {
            $goal = Goal::create([
                'client_id'    => $this->clientId,
                'strategy_id'  => $this->strategyId,
                'title'        => $s['title'],
                'description'  => $s['description'] ?? null,
                'smart_details' => $s['smart_details'] ?? null,
                'metric_type'  => $s['metric_type'] ?? 'number',
                'target_value' => $s['target_value'] ?? 0,
                'due_date'     => $s['due_date'] ?? null,
                'status'       => 'not_started',
            ]);
            foreach (($s['tasks'] ?? []) as $taskTitle) {
                Task::create(['goal_id' => $goal->id, 'title' => is_array($taskTitle) ? ($taskTitle['title'] ?? '') : $taskTitle]);
            }
        }

        // Signal completion by storing count in cache
        cache()->put("goals_generated_{$this->clientId}", count($suggestions), now()->addMinutes(5));
    }

    public function failed(\Throwable $e): void
    {
        cache()->put("goals_generated_{$this->clientId}", 'ERROR: ' . $e->getMessage(), now()->addMinutes(5));
    }
}
