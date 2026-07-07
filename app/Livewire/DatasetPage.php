<?php

namespace App\Livewire;

use App\Models\AiProviderConnection;
use App\Models\Client;
use App\Models\ClientKnowledgeMeta;
use App\Models\KnowledgeChunk;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class DatasetPage extends Component
{
    use WithFileUploads;

    public ?Client $client = null;
    public string $activeTab = 'summary';

    // Summary tab
    public bool $generatingSummary = false;
    public string $summaryError = '';

    // Documents tab
    public $documentFile = null;
    public string $documentLabel = '';
    public bool $uploadingDocument = false;
    public string $documentError = '';

    // Website tab
    public string $websiteUrl = '';
    public bool $crawling = false;
    public string $crawlError = '';

    // AI Providers tab
    public string $newProvider = 'anthropic';
    public string $newProviderKey = '';
    public bool $savingProvider = false;
    public string $providerError = '';

    // Analytics connections
    public bool $connectModalOpen = false;
    public string $connectingPlatform = '';
    public array $connectFields = [];   // dynamic field values keyed by field name
    public string $connectError = '';

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isClientUser()) {
            $this->client = $user->profile?->client;
        } else {
            $clientId = Session::get('active_client_id');
            $this->client = $clientId ? Client::find($clientId) : null;
        }

        // Pre-fill website URL from meta
        if ($this->client) {
            $meta = $this->client->knowledgeMeta;
            if ($meta?->website_url) {
                $this->websiteUrl = $meta->website_url;
            }
        }
    }

    public function generateSummary(): void
    {
        if (!$this->client) return;

        $this->generatingSummary = true;
        $this->summaryError = '';
        set_time_limit(120);

        $strategy = \App\Models\Strategy::where('client_id', $this->client->id)
            ->where('status', 'approved')->latest()->first();
        $goals = \App\Models\Goal::where('client_id', $this->client->id)
            ->where('archived', false)->get();

        $strategyText = $strategy
            ? mb_substr($strategy->generated_document ?? json_encode($strategy->content), 0, 3000)
            : '(no approved strategy)';

        $goalsText = $goals->isNotEmpty()
            ? $goals->map(fn($g) => "- {$g->title} ({$g->status})")->join("\n")
            : '(no goals set)';

        $prompt = "You are a senior marketing strategist writing a performance executive summary for {$this->client->name}.\n\n"
            . "APPROVED STRATEGY:\n{$strategyText}\n\n"
            . "CURRENT GOALS & PROGRESS:\n{$goalsText}\n\n"
            . "Write 2–3 short paragraphs (no bullet points, flowing prose) that:\n"
            . "1. Honestly assess current performance — what's working, what's not, what the numbers show\n"
            . "2. Call out wins, risks, or gaps that need attention right now\n"
            . "3. Give a clear sense of momentum — are things on track, ahead, or behind?\n\n"
            . "Be direct and specific. Reference actual goal names and numbers. Avoid generic marketing language. "
            . "Write as if briefing a CEO before a board meeting. Return only the summary text, no headers.";

        try {
            $response = app(\Anthropic\Client::class)->messages->create(
                maxTokens: 1000,
                messages: [['role' => 'user', 'content' => $prompt]],
                model: 'claude-haiku-4-5-20251001',
            );
            $summary = $response->content[0]->text ?? '';
            $this->client->update([
                'executive_summary' => $summary,
                'executive_summary_updated_at' => now(),
            ]);
            $this->client->refresh();
        } catch (\Exception $e) {
            $this->summaryError = 'Failed to generate summary: ' . $e->getMessage();
        }

        $this->generatingSummary = false;
    }

    public function uploadDocument(): void
    {
        if (!$this->client || !$this->documentFile) return;

        $this->validate([
            'documentFile' => 'required|file|mimes:pdf,txt,doc,docx|max:10240',
            'documentLabel' => 'required|string|max:255',
        ]);

        $this->uploadingDocument = true;
        $this->documentError = '';

        try {
            $path = $this->documentFile->getRealPath();
            $ext = strtolower($this->documentFile->getClientOriginalExtension());

            if ($ext === 'txt') {
                $text = file_get_contents($path);
            } elseif ($ext === 'pdf') {
                // Basic PDF text extraction — read raw and strip binary
                $text = shell_exec("strings " . escapeshellarg($path) . " | grep -v '^[^a-zA-Z]*$' | head -500") ?? '';
                if (empty(trim($text))) {
                    $text = '[PDF content — text extraction requires pdftotext]';
                }
            } else {
                // docx/doc — read as zip and extract word/document.xml
                $zip = new \ZipArchive();
                if ($zip->open($path) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();
                    $text = $xml ? strip_tags($xml) : '[Could not extract text from document]';
                } else {
                    $text = '[Could not open document]';
                }
            }

            // Chunk into ~1000-char pieces
            $chunks = $this->chunkText($text, 1000);
            KnowledgeChunk::where('client_id', $this->client->id)
                ->where('source_label', $this->documentLabel)
                ->where('source_type', 'document')
                ->delete();

            foreach ($chunks as $chunk) {
                KnowledgeChunk::create([
                    'client_id'    => $this->client->id,
                    'source_type'  => 'document',
                    'source_label' => $this->documentLabel,
                    'chunk_text'   => $chunk,
                ]);
            }

            $this->documentFile = null;
            $this->documentLabel = '';
            session()->flash('toast', 'Document added to knowledge base (' . count($chunks) . ' chunks)');
        } catch (\Exception $e) {
            $this->documentError = 'Upload failed: ' . $e->getMessage();
        }

        $this->uploadingDocument = false;
    }

    public function deleteChunksByLabel(string $sourceType, string $label): void
    {
        if (!$this->client) return;
        KnowledgeChunk::where('client_id', $this->client->id)
            ->where('source_type', $sourceType)
            ->where('source_label', $label)
            ->delete();
        session()->flash('toast', 'Removed from knowledge base');
    }

    public function saveWebsiteUrl(): void
    {
        if (!$this->client) return;
        $this->validate(['websiteUrl' => 'required|url|max:500']);

        $meta = $this->client->knowledgeMeta ?? new ClientKnowledgeMeta(['client_id' => $this->client->id]);
        $meta->fill(['client_id' => $this->client->id, 'website_url' => $this->websiteUrl])->save();
        session()->flash('toast', 'Website URL saved');
    }

    public function crawlWebsite(): void
    {
        if (!$this->client) return;
        $this->validate(['websiteUrl' => 'required|url|max:500']);

        $this->crawling = true;
        $this->crawlError = '';
        set_time_limit(180);

        try {
            $meta = $this->client->knowledgeMeta ?? new ClientKnowledgeMeta(['client_id' => $this->client->id]);
            $meta->fill([
                'client_id'    => $this->client->id,
                'website_url'  => $this->websiteUrl,
                'crawl_status' => 'crawling',
            ])->save();

            $crawled = $this->crawlUrl($this->websiteUrl, 20);

            // Remove old website chunks
            KnowledgeChunk::where('client_id', $this->client->id)
                ->where('source_type', 'website')
                ->delete();

            $pageCount = 0;
            foreach ($crawled as $url => $pageText) {
                $chunks = $this->chunkText($pageText, 1000);
                foreach ($chunks as $chunk) {
                    KnowledgeChunk::create([
                        'client_id'    => $this->client->id,
                        'source_type'  => 'website',
                        'source_label' => $url,
                        'chunk_text'   => $chunk,
                    ]);
                }
                $pageCount++;
            }

            $meta->fill([
                'crawl_status'    => 'done',
                'crawl_page_count' => $pageCount,
                'last_crawled_at' => now(),
            ])->save();

            session()->flash('toast', "Crawled {$pageCount} pages into knowledge base");
        } catch (\Exception $e) {
            $this->crawlError = 'Crawl failed: ' . $e->getMessage();
            $meta = $this->client->knowledgeMeta;
            $meta?->update(['crawl_status' => 'error']);
        }

        $this->crawling = false;
    }

    public function saveAiProvider(): void
    {
        if (!$this->client) return;

        $this->validate([
            'newProvider'    => 'required|in:anthropic,openai,gemini',
            'newProviderKey' => 'required|string|min:10|max:500',
        ]);

        $this->savingProvider = true;
        $this->providerError = '';

        try {
            $labels = [
                'anthropic' => 'Claude (Anthropic)',
                'openai'    => 'GPT (OpenAI)',
                'gemini'    => 'Gemini (Google)',
            ];

            $conn = AiProviderConnection::where('provider', $this->newProvider)->first()
                ?? new AiProviderConnection(['provider' => $this->newProvider, 'label' => $labels[$this->newProvider] ?? $this->newProvider, 'added_by' => auth()->id()]);

            $conn->setRawKey($this->newProviderKey);
            $conn->provider = $this->newProvider;
            $conn->label = $labels[$this->newProvider] ?? $this->newProvider;
            $conn->added_by = auth()->id();
            $conn->save();

            $this->newProviderKey = '';
            session()->flash('toast', 'API key saved');
        } catch (\Exception $e) {
            $this->providerError = 'Failed to save key: ' . $e->getMessage();
        }

        $this->savingProvider = false;
    }

    public function openConnectModal(string $platform): void
    {
        $this->connectingPlatform = $platform;
        $this->connectError = '';

        // Pre-fill from existing connection if any
        $existing = \App\Models\AnalyticsConnection::where('client_id', $this->client?->id)
            ->where('platform', $platform)->first();

        $fields = $this->platformFields($platform);
        $this->connectFields = [];
        foreach ($fields as $field) {
            $this->connectFields[$field['key']] = '';
        }

        if ($existing) {
            foreach ($fields as $field) {
                if (!$field['secret']) {
                    $this->connectFields[$field['key']] = $existing->config[$field['key']] ?? '';
                }
            }
        }

        $this->connectModalOpen = true;
    }

    public function saveConnection(): void
    {
        if (!$this->client) return;

        $fields = $this->platformFields($this->connectingPlatform);
        $rules = [];
        foreach ($fields as $field) {
            $rules["connectFields.{$field['key']}"] = $field['required'] ? 'required|string' : 'nullable|string';
        }
        $this->validate($rules);

        $this->connectError = '';

        try {
            // Separate secret (token) from config fields
            $secretField = collect($fields)->firstWhere('secret', true);
            $token = $secretField ? ($this->connectFields[$secretField['key']] ?? '') : '';
            $config = [];
            foreach ($fields as $field) {
                if (!$field['secret']) {
                    $config[$field['key']] = $this->connectFields[$field['key']] ?? '';
                }
            }

            $conn = \App\Models\AnalyticsConnection::firstOrNew([
                'client_id' => $this->client->id,
                'platform'  => $this->connectingPlatform,
            ]);

            // Only update token if a new one was provided
            if ($token) {
                $conn->oauth_token = Crypt::encryptString($token);
            }
            $conn->config = $config;
            $conn->connected_at = now();
            $conn->save();

            $this->connectModalOpen = false;
            $this->connectFields = [];
            session()->flash('toast', 'Connection saved');
        } catch (\Exception $e) {
            $this->connectError = 'Failed to save: ' . $e->getMessage();
        }
    }

    public function disconnectPlatform(string $id): void
    {
        \App\Models\AnalyticsConnection::where('id', $id)
            ->where('client_id', $this->client?->id)
            ->delete();
        session()->flash('toast', 'Disconnected');
    }

    private function platformFields(string $platform): array
    {
        return match ($platform) {
            'google_analytics' => [
                ['key' => 'property_id',   'label' => 'GA4 Property ID',     'placeholder' => 'e.g. 123456789',          'secret' => false, 'required' => true],
                ['key' => 'api_key',       'label' => 'API Key / Service Account JSON', 'placeholder' => 'Paste your API key or service account JSON', 'secret' => true,  'required' => true],
            ],
            'google_search_console' => [
                ['key' => 'site_url',      'label' => 'Site URL',            'placeholder' => 'https://example.com',      'secret' => false, 'required' => true],
                ['key' => 'api_key',       'label' => 'API Key / Service Account JSON', 'placeholder' => 'Paste your API key or service account JSON', 'secret' => true,  'required' => true],
            ],
            'facebook_ads' => [
                ['key' => 'account_id',    'label' => 'Ad Account ID',       'placeholder' => 'act_123456789',            'secret' => false, 'required' => true],
                ['key' => 'access_token',  'label' => 'Access Token',        'placeholder' => 'Paste your access token',  'secret' => true,  'required' => true],
            ],
            'linkedin_ads' => [
                ['key' => 'account_id',    'label' => 'Account ID',          'placeholder' => 'e.g. 123456789',           'secret' => false, 'required' => true],
                ['key' => 'access_token',  'label' => 'Access Token',        'placeholder' => 'Paste your access token',  'secret' => true,  'required' => true],
            ],
            'semrush' => [
                ['key' => 'api_key',       'label' => 'API Key',             'placeholder' => 'Paste your SEMrush API key', 'secret' => true, 'required' => true],
            ],
            default => [
                ['key' => 'api_key',       'label' => 'API Key',             'placeholder' => 'Paste your API key',       'secret' => true,  'required' => true],
            ],
        };
    }

    public function deleteProvider(string $id): void
    {
        AiProviderConnection::find($id)?->delete();
        session()->flash('toast', 'API key removed');
    }

    private function chunkText(string $text, int $size): array
    {
        $text = preg_replace('/\s+/', ' ', trim($text));
        if (strlen($text) <= $size) return $text ? [$text] : [];

        $chunks = [];
        $words = explode(' ', $text);
        $current = '';
        foreach ($words as $word) {
            if (strlen($current) + strlen($word) + 1 > $size && $current !== '') {
                $chunks[] = trim($current);
                $current = $word;
            } else {
                $current .= ($current ? ' ' : '') . $word;
            }
        }
        if (trim($current)) $chunks[] = trim($current);
        return $chunks;
    }

    private function crawlUrl(string $startUrl, int $maxPages): array
    {
        $visited = [];
        $queue = [$startUrl];
        $results = [];
        $base = parse_url($startUrl, PHP_URL_SCHEME) . '://' . parse_url($startUrl, PHP_URL_HOST);

        $context = stream_context_create([
            'http' => ['timeout' => 10, 'user_agent' => 'Mozilla/5.0 (compatible; CoralBot/1.0)'],
            'ssl'  => ['verify_peer' => false],
        ]);

        while (!empty($queue) && count($results) < $maxPages) {
            $url = array_shift($queue);
            if (in_array($url, $visited)) continue;
            $visited[] = $url;

            $html = @file_get_contents($url, false, $context);
            if (!$html) continue;

            // Extract text
            $text = strip_tags(preg_replace('/<(script|style|nav|header|footer)[^>]*>.*?<\/\1>/si', '', $html));
            $text = preg_replace('/\s+/', ' ', trim($text));
            if (strlen($text) > 100) {
                $results[$url] = mb_substr($text, 0, 5000);
            }

            // Find links on same domain
            if (count($results) < $maxPages) {
                preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
                foreach ($matches[1] as $href) {
                    if (str_starts_with($href, '/')) $href = $base . $href;
                    if (!str_starts_with($href, $base)) continue;
                    if (preg_match('/\.(jpg|jpeg|png|gif|pdf|zip|css|js)$/i', $href)) continue;
                    if (!in_array($href, $visited) && !in_array($href, $queue)) {
                        $queue[] = $href;
                    }
                }
            }
        }

        return $results;
    }

    public function render(): \Illuminate\View\View
    {
        $meta = $this->client?->knowledgeMeta;
        $connections = $this->client
            ? \App\Models\AnalyticsConnection::where('client_id', $this->client->id)->get()
            : collect();

        // Document chunks grouped by label
        $docChunks = $this->client
            ? KnowledgeChunk::where('client_id', $this->client->id)
                ->where('source_type', 'document')
                ->selectRaw('source_label, count(*) as chunk_count, max(created_at) as last_updated')
                ->groupBy('source_label')
                ->toBase()
                ->get()
            : collect();

        // Website chunks grouped by label (page URL)
        $webChunks = $this->client
            ? KnowledgeChunk::where('client_id', $this->client->id)
                ->where('source_type', 'website')
                ->selectRaw('source_label, count(*) as chunk_count')
                ->groupBy('source_label')
                ->limit(30)
                ->toBase()
                ->get()
            : collect();

        $aiProviders = AiProviderConnection::orderBy('provider')->get();

        $isAgency = auth()->user()->isAgency();

        return view('livewire.dataset', compact(
            'meta', 'connections', 'docChunks', 'webChunks', 'aiProviders', 'isAgency'
        ));
    }
}
