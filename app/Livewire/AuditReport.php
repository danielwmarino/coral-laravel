<?php

namespace App\Livewire;

use App\Models\Audit;
use App\Models\Client;
use App\Services\AuditChecklist as AuditChecklistService;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AuditReport extends Component
{
    public ?Audit $audit = null;

    public array $responses     = [];
    public array $uxItems       = [];
    public array $contentItems  = [];
    public array $categoryScores = [];

    public function mount(string $auditId): void
    {
        $audit = Audit::with('responses')->find($auditId);

        if (!$audit) {
            $this->redirect(route('audits.index'));
            return;
        }

        $client = $this->resolveClient();
        if (!$client || $audit->client_id !== $client->id) {
            $this->redirect(route('audits.index'));
            return;
        }

        $this->audit = $audit;

        foreach ($audit->responses as $r) {
            $this->responses[$r->section . '.' . $r->item_key] = $r->response;
        }

        $this->uxItems      = AuditChecklistService::uxItems();
        $this->contentItems = AuditChecklistService::contentItems();

        $this->categoryScores = $this->calculateCategoryScores();
    }

    public function calculateCategoryScores(): array
    {
        $scores = [];

        $sections = [
            'ux'      => $this->uxItems,
            'content' => $this->contentItems,
        ];

        foreach ($sections as $sectionName => $categories) {
            foreach ($categories as $categoryName => $items) {
                $yes    = 0;
                $total  = 0;
                $fails  = 0;

                foreach ($items as $item) {
                    $response = $this->responses[$sectionName . '.' . $item['key']] ?? null;
                    if (in_array($response, ['yes', 'no', 'fail'])) {
                        $total++;
                        if ($response === 'yes')  $yes++;
                        if ($response === 'fail') $fails++;
                    }
                }

                $scores[$sectionName . '|' . $categoryName] = [
                    'section'  => $sectionName,
                    'category' => $categoryName,
                    'score'    => $total > 0 ? (int) round(($yes / $total) * 100) : null,
                    'pass'     => $yes,
                    'total'    => $total,
                    'fails'    => $fails,
                ];
            }
        }

        return $scores;
    }

    public function getFailedItems(): array
    {
        $failed = [];

        $sections = [
            'ux'      => $this->uxItems,
            'content' => $this->contentItems,
        ];

        foreach ($sections as $sectionName => $categories) {
            foreach ($categories as $categoryName => $items) {
                foreach ($items as $item) {
                    $response = $this->responses[$sectionName . '.' . $item['key']] ?? null;
                    if ($response === 'fail') {
                        $failed[] = [
                            'section'  => $sectionName,
                            'category' => $categoryName,
                            'text'     => $item['text'],
                            'key'      => $item['key'],
                        ];
                    }
                }
            }
        }

        return $failed;
    }

    public function getNoItems(): array
    {
        $nos = [];

        $sections = [
            'ux'      => $this->uxItems,
            'content' => $this->contentItems,
        ];

        foreach ($sections as $sectionName => $categories) {
            foreach ($categories as $categoryName => $items) {
                foreach ($items as $item) {
                    $response = $this->responses[$sectionName . '.' . $item['key']] ?? null;
                    if ($response === 'no') {
                        $nos[] = [
                            'section'  => $sectionName,
                            'category' => $categoryName,
                            'text'     => $item['text'],
                            'key'      => $item['key'],
                        ];
                    }
                }
            }
        }

        return $nos;
    }

    public function render(): \Illuminate\View\View
    {
        $failedItems = $this->getFailedItems();
        $noItems     = $this->getNoItems();

        return view('livewire.audit-report', compact('failedItems', 'noItems'));
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
