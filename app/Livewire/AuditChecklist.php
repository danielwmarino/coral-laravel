<?php

namespace App\Livewire;

use App\Models\Audit;
use App\Models\AuditResponse;
use App\Models\Client;
use App\Services\AuditChecklist as AuditChecklistService;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AuditChecklist extends Component
{
    public ?Audit $audit = null;

    public array $responses = [];

    public string $activeSection  = 'ux';
    public string $activeCategory = '';

    public function mount(string $auditId): void
    {
        $audit = Audit::find($auditId);

        if (!$audit) {
            $this->redirect(route('audits.index'));
            return;
        }

        // Verify ownership
        $client = $this->resolveClient();
        if (!$client || $audit->client_id !== $client->id) {
            $this->redirect(route('audits.index'));
            return;
        }

        $this->audit = $audit;

        // Load existing responses keyed by "section.item_key"
        foreach ($audit->responses as $r) {
            $this->responses[$r->section . '.' . $r->item_key] = $r->response;
        }

        // Set default active category
        $uxItems = AuditChecklistService::uxItems();
        $this->activeCategory = array_key_first($uxItems) ?? '';
    }

    public function setActiveSection(string $section): void
    {
        $this->activeSection = $section;

        if ($section === 'ux') {
            $items = AuditChecklistService::uxItems();
        } else {
            $items = AuditChecklistService::contentItems();
        }

        $this->activeCategory = array_key_first($items) ?? '';
    }

    public function setActiveCategory(string $category): void
    {
        $this->activeCategory = $category;
    }

    public function setResponse(string $section, string $key, string $value, string $category): void
    {
        if (!$this->audit) return;

        // Toggle off if clicking same value
        $currentKey = $section . '.' . $key;
        if (($this->responses[$currentKey] ?? null) === $value) {
            $value = '';
        }

        $this->responses[$currentKey] = $value ?: null;

        AuditResponse::updateOrCreate(
            [
                'audit_id' => $this->audit->id,
                'section'  => $section,
                'item_key' => $key,
            ],
            [
                'category' => $category,
                'response' => $value ?: null,
            ]
        );

        $this->calculateScores();
    }

    public function calculateScores(): void
    {
        if (!$this->audit) return;

        $sections = [
            'ux'      => AuditChecklistService::uxItems(),
            'content' => AuditChecklistService::contentItems(),
        ];

        $sectionScores = [];

        foreach ($sections as $sectionName => $categories) {
            $yes = 0;
            $scored = 0;

            foreach ($categories as $categoryName => $items) {
                foreach ($items as $item) {
                    $response = $this->responses[$sectionName . '.' . $item['key']] ?? null;
                    if (in_array($response, ['yes', 'no', 'fail'])) {
                        $scored++;
                        if ($response === 'yes') {
                            $yes++;
                        }
                    }
                }
            }

            $sectionScores[$sectionName] = $scored > 0 ? (int) round(($yes / $scored) * 100) : null;
        }

        $uxScore      = $sectionScores['ux'];
        $contentScore = $sectionScores['content'];

        // Overall = average of non-null scores
        $nonNull = array_filter([$uxScore, $contentScore], fn($v) => $v !== null);
        $overall = count($nonNull) > 0 ? (int) round(array_sum($nonNull) / count($nonNull)) : null;

        $this->audit->update([
            'ux_score'      => $uxScore,
            'content_score' => $contentScore,
            'overall_score' => $overall,
        ]);

        $this->audit->refresh();
    }

    public function completeAudit(): void
    {
        if (!$this->audit) return;

        $this->calculateScores();

        $this->audit->update(['status' => 'completed']);

        session()->flash('toast', 'Audit completed!');

        $this->redirect(route('audits.report', $this->audit->id));
    }

    public function progressStats(): array
    {
        $sections = [
            'ux'      => AuditChecklistService::uxItems(),
            'content' => AuditChecklistService::contentItems(),
        ];

        $total  = 0;
        $scored = 0;

        foreach ($sections as $sectionName => $categories) {
            foreach ($categories as $items) {
                foreach ($items as $item) {
                    $total++;
                    $response = $this->responses[$sectionName . '.' . $item['key']] ?? null;
                    if ($response !== null && $response !== '') {
                        $scored++;
                    }
                }
            }
        }

        return compact('total', 'scored');
    }

    public function render(): \Illuminate\View\View
    {
        $uxItems      = AuditChecklistService::uxItems();
        $contentItems = AuditChecklistService::contentItems();
        $progress     = $this->progressStats();

        return view('livewire.audit-checklist', compact('uxItems', 'contentItems', 'progress'));
    }

    private function resolveClient(): ?Client
    {
        $user = auth()->user();
        if ($user->isClientUser()) {
            return $user->profile?->client;
        }
        $clientId = Session::get('active_client_id');
        return $clientId ? Client::find($clientId) : null;
    }
}
