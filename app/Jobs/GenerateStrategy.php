<?php

namespace App\Jobs;

use App\Models\Strategy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateStrategy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        public string $strategyId,
        public string $prompt,
    ) {}

    public function handle(): void
    {
        $response = app(\Anthropic\Client::class)->messages->create(
            maxTokens: 4000,
            messages: [['role' => 'user', 'content' => $this->prompt]],
            model: 'claude-sonnet-4-6',
        );

        $doc = $response->content[0]->text ?? '';

        Strategy::find($this->strategyId)?->update([
            'generated_document' => $doc,
            'status'             => 'draft',
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Strategy::find($this->strategyId)?->update([
            'generated_document' => 'ERROR: ' . $e->getMessage(),
        ]);
    }
}
