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
            $this->responses[$r->section . '.' . $r->item_key] = [
                'response'        => $r->response,
                'reason'          => $r->reason,
                'fix_instruction' => $r->fix_instruction,
            ];
        }

        $this->uxItems      = AuditChecklistService::uxItems();
        $this->contentItems = AuditChecklistService::contentItems();

        $this->categoryScores = $this->calculateCategoryScores();
    }

    public function calculateCategoryScores(): array
    {
        $scores = [];

        $sections = ['ux' => $this->uxItems, 'content' => $this->contentItems];

        foreach ($sections as $sectionName => $categories) {
            foreach ($categories as $categoryName => $items) {
                $yes = $total = $fails = $na = 0;

                foreach ($items as $item) {
                    $entry    = $this->responses[$sectionName . '.' . $item['key']] ?? null;
                    $response = is_array($entry) ? ($entry['response'] ?? null) : $entry;
                    if ($response === 'na') { $na++; continue; }
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
                    'all_na'   => $na > 0 && $total === 0,
                ];
            }
        }

        return $scores;
    }

    public function getFailedItems(): array
    {
        $failed = [];
        $sections = ['ux' => $this->uxItems, 'content' => $this->contentItems];

        foreach ($sections as $sectionName => $categories) {
            foreach ($categories as $categoryName => $items) {
                foreach ($items as $item) {
                    $entry    = $this->responses[$sectionName . '.' . $item['key']] ?? null;
                    $response = is_array($entry) ? ($entry['response'] ?? null) : $entry;
                    if ($response === 'fail') {
                        $failed[] = [
                            'section'         => $sectionName,
                            'category'        => $categoryName,
                            'text'            => $item['text'],
                            'key'             => $item['key'],
                            'reason'          => is_array($entry) ? ($entry['reason'] ?? null) : null,
                            'fix_instruction' => is_array($entry) ? ($entry['fix_instruction'] ?? null) : null,
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
        $sections = ['ux' => $this->uxItems, 'content' => $this->contentItems];

        foreach ($sections as $sectionName => $categories) {
            foreach ($categories as $categoryName => $items) {
                foreach ($items as $item) {
                    $entry    = $this->responses[$sectionName . '.' . $item['key']] ?? null;
                    $response = is_array($entry) ? ($entry['response'] ?? null) : $entry;
                    if ($response === 'no') {
                        $nos[] = [
                            'section'         => $sectionName,
                            'category'        => $categoryName,
                            'text'            => $item['text'],
                            'key'             => $item['key'],
                            'reason'          => is_array($entry) ? ($entry['reason'] ?? null) : null,
                            'fix_instruction' => is_array($entry) ? ($entry['fix_instruction'] ?? null) : null,
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

        $responseCounts = [
            'fail' => collect($this->responses)->filter(fn($e) => (is_array($e) ? $e['response'] : $e) === 'fail')->count(),
            'no'   => collect($this->responses)->filter(fn($e) => (is_array($e) ? $e['response'] : $e) === 'no')->count(),
            'yes'  => collect($this->responses)->filter(fn($e) => (is_array($e) ? $e['response'] : $e) === 'yes')->count(),
        ];

        return view('livewire.audit-report', compact('failedItems', 'noItems', 'responseCounts'));
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
