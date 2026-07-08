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

    public bool $aiRunning = false;
    public string $aiError = '';

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

        // Auto-run AI audit if mode is ai_assisted and no responses yet
        if ($this->audit->audit_mode === 'ai_assisted' && empty($this->responses)) {
            $this->dispatch('auto-run-ai-audit');
        }
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

    public function runAiAudit(): void
    {
        if (!$this->audit || !$this->audit->product_url) {
            $this->aiError = 'No URL set for this audit. Edit the audit to add a product URL.';
            return;
        }

        $this->aiRunning = true;
        $this->aiError   = '';

        // Fetch up to 5 pages from the product URL
        $pageContent = $this->fetchSiteContent($this->audit->product_url, 5);

        if (!$pageContent) {
            $this->aiError   = 'Could not fetch the site. Check the URL and try again.';
            $this->aiRunning = false;
            return;
        }

        // Build checklist item list for the prompt
        $allSections = [
            'ux'      => AuditChecklistService::uxItems(),
            'content' => AuditChecklistService::contentItems(),
        ];

        $itemLines = '';
        foreach ($allSections as $section => $categories) {
            foreach ($categories as $category => $items) {
                foreach ($items as $item) {
                    $itemLines .= "{$section}.{$item['key']}: {$item['text']}\n";
                }
            }
        }

        $prompt = <<<PROMPT
You are a professional UX and content auditor. You have been given content scraped from a website and a list of audit criteria. Score each criterion based solely on what you can observe from the provided content.

WEBSITE: {$this->audit->product_url}

SCRAPED CONTENT:
{$pageContent}

---

AUDIT CRITERIA (one per line, format: section.key: description):
{$itemLines}

---

INSTRUCTIONS:
Return ONLY a valid JSON object. Keys are the criterion identifiers (e.g. "ux.first_value_prop"), values are one of: "yes", "no", "fail", "na".
- "yes" = criterion is clearly met
- "no" = criterion is not met
- "fail" = criterion is critically failing (use sparingly for the worst issues)
- "na" = not applicable based on the site type or content available

Do not include any explanation, markdown, or text outside the JSON object.
PROMPT;

        set_time_limit(120);

        try {
            $result = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 4000,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: 'claude-opus-4-8',
                system: 'You are a structured data extractor. Return only valid JSON, nothing else.',
            );

            $json = trim($result->content[0]->text ?? '');
            $json = preg_replace('/^```json\s*/i', '', $json);
            $json = preg_replace('/\s*```$/', '', $json);
            $scores = json_decode($json, true);

            if (!is_array($scores)) {
                $this->aiError   = 'AI returned an unexpected response format. Please try again.';
                $this->aiRunning = false;
                return;
            }

            // Save each response
            foreach ($allSections as $section => $categories) {
                foreach ($categories as $category => $items) {
                    foreach ($items as $item) {
                        $aiKey   = $section . '.' . $item['key'];
                        $response = $scores[$aiKey] ?? null;
                        if (!in_array($response, ['yes', 'no', 'fail', 'na'])) continue;

                        $this->responses[$aiKey] = $response;

                        AuditResponse::updateOrCreate(
                            ['audit_id' => $this->audit->id, 'section' => $section, 'item_key' => $item['key']],
                            ['category' => $category, 'response' => $response]
                        );
                    }
                }
            }

            $this->calculateScores();
            $this->audit->update(['status' => 'completed', 'audit_mode' => 'ai_assisted']);
            $this->audit->refresh();

            $this->aiRunning = false;
            session()->flash('toast', 'AI audit complete!');
            $this->redirect(route('audits.report', $this->audit->id));

        } catch (\Exception $e) {
            $this->aiError   = 'AI audit failed: ' . $e->getMessage();
            $this->aiRunning = false;
        }
    }

    private function fetchSiteContent(string $startUrl, int $maxPages): string
    {
        $context = stream_context_create([
            'http' => ['timeout' => 10, 'header' => "User-Agent: Mozilla/5.0\r\n", 'follow_location' => true],
            'ssl'  => ['verify_peer' => false],
        ]);

        $base    = parse_url($startUrl, PHP_URL_SCHEME) . '://' . parse_url($startUrl, PHP_URL_HOST);
        $queue   = [$startUrl];
        $visited = [];
        $output  = '';

        while (!empty($queue) && count($visited) < $maxPages) {
            $url = array_shift($queue);
            if (in_array($url, $visited)) continue;
            $visited[] = $url;

            $html = @file_get_contents($url, false, $context);
            if (!$html) continue;

            // Extract links for crawling
            if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
                foreach ($m[1] as $href) {
                    if (str_starts_with($href, '/')) $href = $base . $href;
                    if (str_starts_with($href, $base) && !in_array($href, $visited) && !str_contains($href, '#')) {
                        $queue[] = $href;
                    }
                }
            }

            $html = preg_replace('/<(script|style|nav|header|footer)[^>]*>.*?<\/\1>/si', '', $html);
            $text = preg_replace('/\s+/', ' ', strip_tags($html));
            $output .= "\n\n[PAGE: {$url}]\n" . mb_substr(trim($text), 0, 2000);
        }

        return trim($output);
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
