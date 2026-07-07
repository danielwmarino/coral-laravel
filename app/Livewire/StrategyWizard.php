<?php

namespace App\Livewire;

use App\Jobs\GenerateStrategy;
use App\Models\Client;
use App\Models\Strategy;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class StrategyWizard extends Component
{
    public int $step = 0;
    public ?string $strategyId = null;

    // Step 0 – Business
    public string $businessName = '';
    public string $businessDescription = '';
    public string $industry = '';

    // Step 1 – Audience
    public string $targetAudience = '';
    public string $audienceAge = '';
    public string $audienceLocation = '';

    // Step 2 – Channels
    public string $currentChannels = '';
    public string $channelPerformance = '';

    // Step 3 – Competitors
    public string $competitors = '';
    public string $competitorStrengths = '';

    // Step 4 – Objectives
    public string $primaryObjective = '';
    public string $secondaryObjectives = '';

    // Step 5 – Budget
    public string $monthlyBudget = '';
    public string $timeline = '';

    // Step 6 – Review
    public bool $generating = false;
    public bool $polling = false;
    public string $generatedDoc = '';
    public bool $submitting = false;
    public string $generateError = '';

    public const STEPS = [
        ['id' => 'business',    'title' => 'Business Overview',   'description' => 'Tell us about the business'],
        ['id' => 'audience',    'title' => 'Target Audience',     'description' => 'Who are you trying to reach?'],
        ['id' => 'channels',    'title' => 'Current Channels',    'description' => 'Where are you already active?'],
        ['id' => 'competitors', 'title' => 'Competitors',         'description' => 'Who are you up against?'],
        ['id' => 'objectives',  'title' => 'Key Objectives',      'description' => 'What does success look like?'],
        ['id' => 'budget',      'title' => 'Budget & Timeline',   'description' => 'Resources and timeframe'],
        ['id' => 'review',      'title' => 'Review & Generate',   'description' => 'Generate your strategy with AI'],
    ];

    private function client(): ?Client
    {
        $user = auth()->user();
        if ($user->isClientUser()) return $user->profile?->client;
        $clientId = Session::get('active_client_id');
        return $clientId ? Client::find($clientId) : null;
    }

    private function wizardData(): array
    {
        return [
            'businessName'          => $this->businessName,
            'businessDescription'   => $this->businessDescription,
            'industry'              => $this->industry,
            'targetAudience'        => $this->targetAudience,
            'audienceAge'           => $this->audienceAge,
            'audienceLocation'      => $this->audienceLocation,
            'currentChannels'       => $this->currentChannels,
            'channelPerformance'    => $this->channelPerformance,
            'competitors'           => $this->competitors,
            'competitorStrengths'   => $this->competitorStrengths,
            'primaryObjective'      => $this->primaryObjective,
            'secondaryObjectives'   => $this->secondaryObjectives,
            'monthlyBudget'         => $this->monthlyBudget,
            'timeline'              => $this->timeline,
        ];
    }

    public function next(): void
    {
        $client = $this->client();
        if (!$client) return;

        $content = array_merge($this->wizardData(), ['step' => $this->step + 1]);

        if (!$this->strategyId) {
            $strategy = Strategy::create([
                'client_id' => $client->id,
                'content'   => $content,
                'status'    => 'draft',
                'created_by' => auth()->id(),
            ]);
            $this->strategyId = $strategy->id;
        } else {
            Strategy::find($this->strategyId)?->update(['content' => $content]);
        }

        $this->step++;
    }

    public function back(): void
    {
        if ($this->step > 0) $this->step--;
    }

    public function generateStrategy(): void
    {
        $client = $this->client();
        if (!$client || !$this->strategyId) return;

        $this->generating = true;
        $data = $this->wizardData();

        $hasCompetitors = trim($data['competitors']) !== '';
        $competitorSection = $hasCompetitors
            ? "- Competitors provided: {$data['competitors']}\n- Competitor strengths: {$data['competitorStrengths']}"
            : "No competitors were provided. Based on the business description, industry, and target audience above, identify 3–5 likely direct competitors. For each, list:\n  - Name and brief description\n  - Key marketing strengths\n  - Key weaknesses / gaps\nInclude this competitor analysis prominently in Section 2 (Situation Analysis).";

        $prompt = "You are a senior digital marketing strategist. Create a comprehensive, actionable digital marketing strategy for the following client.

CLIENT: {$client->name}
BUSINESS: {$data['businessName']}
DESCRIPTION: {$data['businessDescription']}
INDUSTRY: {$data['industry']}

TARGET AUDIENCE:
- Primary audience: {$data['targetAudience']}
- Age range: {$data['audienceAge']}
- Location: {$data['audienceLocation']}

CURRENT MARKETING:
- Active channels: {$data['currentChannels']}
- Current performance: {$data['channelPerformance']}

COMPETITIVE LANDSCAPE:
{$competitorSection}

OBJECTIVES:
- Primary: {$data['primaryObjective']}
- Secondary: {$data['secondaryObjectives']}

RESOURCES:
- Monthly budget: {$data['monthlyBudget']}
- Timeline: {$data['timeline']}

Generate a structured digital marketing strategy document with these sections:
1. Executive Summary
2. Situation Analysis (SWOT)
3. Target Audience Deep-Dive
4. Strategic Direction & Positioning
5. Channel Strategy (specific per channel)
6. Content Strategy
7. Paid Media Plan
8. SEO & Organic Growth Plan
9. Key Performance Indicators
10. 90-Day Action Plan
11. Budget Allocation Recommendation

Be specific and actionable. Avoid generic platitudes.";

        $this->generateError = '';
        try {
            set_time_limit(120);
            $response = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 4000,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: 'claude-sonnet-4-6',
            );
            $doc = $response->content[0]->text ?? '';
            $this->generatedDoc = $doc;
            Strategy::find($this->strategyId)?->update([
                'generated_document' => $doc,
                'status'             => 'draft',
            ]);
        } catch (\Exception $e) {
            $this->generateError = 'Failed: ' . $e->getMessage();
        }
        $this->generating = false;
        $this->polling = false;
    }

    public function checkGenerated(): void
    {
        if (!$this->strategyId) return;
        $strategy = Strategy::find($this->strategyId);
        if ($strategy && $strategy->generated_document) {
            $doc = $strategy->generated_document;
            if (str_starts_with($doc, 'ERROR:')) {
                $this->generateError = $doc;
                $this->polling = false;
            } else {
                $this->generatedDoc = $doc;
                $this->polling = false;
            }
        }
    }

    public function submitForReview(): void
    {
        if (!$this->strategyId) return;
        $this->submitting = true;
        Strategy::find($this->strategyId)?->update(['status' => 'in_review']);
        $this->submitting = false;
        session()->flash('toast', 'Strategy submitted for review');
        $this->redirect(route('strategy'));
    }

    public function render(): \Illuminate\View\View
    {
        $totalSteps = count(self::STEPS);
        $progress = (($this->step + 1) / $totalSteps) * 100;
        $currentStep = self::STEPS[$this->step];
        return view('livewire.strategy-wizard', compact('totalSteps', 'progress', 'currentStep'));
    }
}
