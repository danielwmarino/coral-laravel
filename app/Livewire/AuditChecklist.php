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

        // x-init on the "else" card triggers runAiAudit() for ai_assisted mode with no responses
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

        $itemLines  = '';
        $totalItems = 0;
        foreach ($allSections as $section => $categories) {
            foreach ($categories as $category => $items) {
                foreach ($items as $item) {
                    $itemLines .= "{$section}.{$item['key']}: {$item['text']}\n";
                    $totalItems++;
                }
            }
        }

        $url         = $this->audit->product_url;
        $productType = $this->audit->product_type;

        $prompt = <<<PROMPT
You are a professional UX and content auditor. Analyse the scraped website content below and score every single one of the {$totalItems} criteria listed. Do not skip any item.

WEBSITE: {$url}
PRODUCT TYPE: {$productType}

SCRAPED CONTENT:
{$pageContent}

---

SCORING RULES:
- "yes"  = criterion is clearly met based on the content
- "no"   = criterion is not met but is fixable (moderate issue)
- "fail" = critical failure requiring immediate attention (use for the worst 10-15% of issues)
- "na"   = genuinely not applicable for this product type (use sparingly)

For accessibility/forms/mobile criteria: infer from HTML evidence — look for form elements, label tags, alt attributes, aria attributes, viewport meta tag, responsive CSS classes. Do NOT mark these "na" just because you cannot see the rendered output.

---

AUDIT CRITERIA:
{$itemLines}

---

Return ONLY a valid JSON object. Every key is a criterion ID. Every value is an object with:
- "r": the score ("yes", "no", "fail", or "na")
- "why": 1-2 sentences explaining your assessment with specific evidence
- "fix": specific actionable fix instruction (required for "no" and "fail"; null for "yes" and "na")

Start your response with { and end with }. No markdown fences, no text outside the JSON.
PROMPT;

        set_time_limit(180);

        try {
            $result = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 8000,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: 'claude-sonnet-5',
                system: 'You are a JSON-only responder. Output must start with { and end with }. No markdown, no explanation.',
            );

            // claude-sonnet-5 may return a thinking block first — find the text block
            $textBlock = collect($result->content)->first(fn($b) => ($b->type ?? '') === 'text');
            $raw  = trim($textBlock->text ?? '');
            // Strip any markdown fences
            $json = preg_replace('/^```(?:json)?\s*/i', '', $raw);
            $json = preg_replace('/\s*```\s*$/i', '', $json);
            // Extract JSON object if there's surrounding text
            if (preg_match('/\{.*\}/s', $json, $m)) {
                $json = $m[0];
            }
            $scores = json_decode($json, true);

            if (!is_array($scores)) {
                $this->aiError   = 'AI returned an unexpected format (JSON parse failed). Please try again.';
                $this->aiRunning = false;
                return;
            }

            // Save each response
            foreach ($allSections as $section => $categories) {
                foreach ($categories as $category => $items) {
                    foreach ($items as $item) {
                        $aiKey  = $section . '.' . $item['key'];
                        $entry  = $scores[$aiKey] ?? null;

                        // Support both old (string) and new (object) format
                        if (is_array($entry)) {
                            $response = $entry['r'] ?? null;
                            $reason   = $entry['why'] ?? null;
                            $fix      = $entry['fix'] ?? null;
                        } else {
                            $response = is_string($entry) ? $entry : null;
                            $reason   = null;
                            $fix      = null;
                        }

                        if (!in_array($response, ['yes', 'no', 'fail', 'na'])) continue;

                        $this->responses[$aiKey] = $response;

                        AuditResponse::updateOrCreate(
                            ['audit_id' => $this->audit->id, 'section' => $section, 'item_key' => $item['key']],
                            ['category' => $category, 'response' => $response, 'reason' => $reason, 'fix_instruction' => $fix]
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

            // Extract links for crawling — collect then sort for deterministic order
            $newLinks = [];
            if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
                foreach ($m[1] as $href) {
                    if (str_starts_with($href, '/')) $href = $base . $href;
                    if (str_starts_with($href, $base) && !in_array($href, $visited) && !str_contains($href, '#')) {
                        $newLinks[] = $href;
                    }
                }
            }
            sort($newLinks);
            foreach ($newLinks as $href) {
                if (!in_array($href, $queue)) $queue[] = $href;
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
