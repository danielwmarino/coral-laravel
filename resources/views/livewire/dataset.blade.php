<div class="space-y-6">

    @if(session('toast'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg">
            {{ session('toast') }}
        </div>
    @endif

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-semibold text-[#003470]">Dataset</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your client knowledge base and connected data sources</p>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex gap-6 overflow-x-auto">
            @foreach([
                ['id' => 'summary',   'label' => 'Summary'],
                ['id' => 'documents', 'label' => 'Documents'],
                ['id' => 'website',   'label' => 'Website'],
                ['id' => 'analytics', 'label' => 'Analytics'],
                ['id' => 'providers', 'label' => 'AI Providers'],
                ['id' => 'platform',  'label' => 'Platform Data'],
            ] as $tab)
                <button wire:click="$set('activeTab', '{{ $tab['id'] }}')"
                    class="pb-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                        {{ $activeTab === $tab['id'] ? 'border-[#FC54AA] text-[#FC54AA]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ── SUMMARY TAB ── --}}
    @if($activeTab === 'summary')
        <div class="space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Client Brief</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Auto-generated summary injected into every Agent conversation</p>
                </div>
                @if($isAgency)
                    <button wire:click="generateSummary" wire:loading.attr="disabled"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors disabled:opacity-60">
                        <span wire:loading.remove wire:target="generateSummary" class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            {{ $client?->executive_summary ? 'Regenerate' : 'Generate Brief' }}
                        </span>
                        <span wire:loading wire:target="generateSummary" class="flex items-center gap-1.5">
                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Generating…
                        </span>
                    </button>
                @endif
            </div>

            @if($summaryError)
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $summaryError }}</div>
            @endif

            @if($client?->executive_summary)
                <div class="bg-white border border-gray-100 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Executive Brief</span>
                        @if($client->executive_summary_updated_at)
                            <span class="text-xs text-gray-400">Updated {{ $client->executive_summary_updated_at->diffForHumans() }}</span>
                        @endif
                    </div>
                    <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                        {!! nl2br(e($client->executive_summary)) !!}
                    </div>
                </div>
            @else
                <div class="border border-dashed rounded-xl p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <p class="text-sm text-gray-500">No brief generated yet</p>
                    <p class="text-xs text-gray-400 mt-1">Approve a strategy and add goals first, then generate the brief</p>
                </div>
            @endif
        </div>
    @endif

    {{-- ── DOCUMENTS TAB ── --}}
    @if($activeTab === 'documents')
        <div class="space-y-6">
            @if($isAgency)
                <div class="bg-white border border-gray-100 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Upload Document</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Document Label</label>
                            <input wire:model="documentLabel" type="text" placeholder="e.g. Brand Guidelines, Q4 Report"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">File (PDF, DOCX, TXT — max 10MB)</label>
                            <input wire:model="documentFile" type="file" accept=".pdf,.doc,.docx,.txt"
                                class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                        </div>
                        @if($documentError)
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $documentError }}</div>
                        @endif
                        <button wire:click="uploadDocument" wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 px-4 py-2 text-sm bg-[#003470] hover:bg-[#002558] text-white rounded-lg transition-colors disabled:opacity-60">
                            <span wire:loading.remove wire:target="uploadDocument">Upload & Index</span>
                            <span wire:loading wire:target="uploadDocument" class="flex items-center gap-1.5">
                                <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                Indexing…
                            </span>
                        </button>
                    </div>
                </div>
            @endif

            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Indexed Documents</h3>
                @if($docChunks->isEmpty())
                    <div class="border border-dashed rounded-xl p-10 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                        <p class="text-sm text-gray-500">No documents indexed yet</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($docChunks as $doc)
                            <div class="flex items-center justify-between bg-white border border-gray-100 rounded-xl px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $doc->source_label }}</p>
                                        <p class="text-xs text-gray-500">{{ $doc->chunk_count }} chunks</p>
                                    </div>
                                </div>
                                @if($isAgency)
                                    <button wire:click="deleteChunksByLabel('document', '{{ $doc->source_label }}')"
                                        wire:confirm="Remove this document from the knowledge base?"
                                        class="text-gray-400 hover:text-red-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── WEBSITE TAB ── --}}
    @if($activeTab === 'website')
        <div class="space-y-6">
            @if($isAgency)
                <div class="bg-white border border-gray-100 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">Website Crawler</h3>
                    <p class="text-xs text-gray-500 mb-4">Crawl up to 20 pages and index them into the knowledge base</p>
                    <div class="flex gap-3">
                        <input wire:model="websiteUrl" type="url" placeholder="https://example.com"
                            class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                        <button wire:click="crawlWebsite" wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 px-4 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors disabled:opacity-60 shrink-0">
                            <span wire:loading.remove wire:target="crawlWebsite" class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                Crawl Website
                            </span>
                            <span wire:loading wire:target="crawlWebsite" class="flex items-center gap-1.5">
                                <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                Crawling…
                            </span>
                        </button>
                    </div>
                    @if($crawlError)
                        <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $crawlError }}</div>
                    @endif
                </div>
            @endif

            @if($meta)
                <div class="bg-white border border-gray-100 rounded-xl p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $meta->website_url ?? 'No URL set' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if($meta->crawl_status === 'done')
                                    {{ $meta->crawl_page_count }} pages indexed · Last crawled {{ $meta->last_crawled_at?->diffForHumans() }}
                                @elseif($meta->crawl_status === 'crawling')
                                    Crawling in progress…
                                @elseif($meta->crawl_status === 'error')
                                    Last crawl failed
                                @else
                                    Not yet crawled
                                @endif
                            </p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full font-medium
                            {{ $meta->crawl_status === 'done' ? 'bg-green-50 text-green-700' : ($meta->crawl_status === 'crawling' ? 'bg-yellow-50 text-yellow-700' : ($meta->crawl_status === 'error' ? 'bg-red-50 text-red-700' : 'bg-gray-100 text-gray-500')) }}">
                            {{ ucfirst($meta->crawl_status) }}
                        </span>
                    </div>
                </div>
            @endif

            @if($webChunks->isNotEmpty())
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Indexed Pages</h3>
                    <div class="space-y-1.5">
                        @foreach($webChunks as $page)
                            <div class="flex items-center justify-between bg-white border border-gray-100 rounded-lg px-4 py-2.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 shrink-0"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    <span class="text-xs text-gray-600 truncate">{{ $page->source_label }}</span>
                                </div>
                                <span class="text-xs text-gray-400 shrink-0 ml-3">{{ $page->chunk_count }} chunks</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($activeTab === 'website' && (!$meta || $meta->crawl_status === 'idle'))
                <div class="border border-dashed rounded-xl p-10 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <p class="text-sm text-gray-500">No pages crawled yet</p>
                    <p class="text-xs text-gray-400 mt-1">Enter a URL above and click Crawl Website</p>
                </div>
            @endif
        </div>
    @endif

    {{-- ── ANALYTICS TAB ── --}}
    @if($activeTab === 'analytics')
        @php
            $platforms = [
                ['id' => 'google_analytics',      'name' => 'Google Analytics 4',    'desc' => 'Traffic, sessions, conversions',    'color' => 'text-orange-500', 'bg' => 'bg-orange-50'],
                ['id' => 'google_search_console', 'name' => 'Google Search Console', 'desc' => 'Search rankings, impressions, CTR', 'color' => 'text-blue-500',   'bg' => 'bg-blue-50'],
                ['id' => 'facebook_ads',          'name' => 'Facebook / Meta Ads',   'desc' => 'Ad spend, reach, conversions',      'color' => 'text-indigo-500', 'bg' => 'bg-indigo-50'],
                ['id' => 'linkedin_ads',          'name' => 'LinkedIn Ads',           'desc' => 'B2B campaigns and lead gen',        'color' => 'text-sky-600',    'bg' => 'bg-sky-50'],
                ['id' => 'semrush',               'name' => 'SEMrush',                'desc' => 'SEO metrics, backlinks, keywords',  'color' => 'text-green-600',  'bg' => 'bg-green-50'],
            ];
            $connectedIds = $connections->pluck('platform')->toArray();
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($platforms as $p)
                @php $connected = in_array($p['id'], $connectedIds); @endphp
                <div class="bg-white border border-gray-100 rounded-xl p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 {{ $p['bg'] }} rounded-lg flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $p['color'] }}"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $p['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $p['desc'] }}</p>
                            </div>
                        </div>
                        @if($connected)
                            <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full font-medium">Connected</span>
                        @else
                            <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1.5 rounded-lg transition-colors font-medium">
                                Connect
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 text-center mt-2">OAuth integrations coming soon — connections shown above are manually configured</p>
    @endif

    {{-- ── AI PROVIDERS TAB ── --}}
    @if($activeTab === 'providers')
        <div class="space-y-6">
            @if($isAgency)
                <div class="bg-white border border-gray-100 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Add API Key</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Provider</label>
                            <select wire:model="newProvider"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                                <option value="anthropic">Claude (Anthropic)</option>
                                <option value="openai">GPT (OpenAI)</option>
                                <option value="gemini">Gemini (Google)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">API Key</label>
                            <input wire:model="newProviderKey" type="password" placeholder="sk-..."
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] font-mono">
                        </div>
                        @if($providerError)
                            <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $providerError }}</div>
                        @endif
                        <button wire:click="saveAiProvider" wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 px-4 py-2 text-sm bg-[#003470] hover:bg-[#002558] text-white rounded-lg transition-colors disabled:opacity-60">
                            <span wire:loading.remove wire:target="saveAiProvider">Save Key</span>
                            <span wire:loading wire:target="saveAiProvider">Saving…</span>
                        </button>
                    </div>
                </div>
            @endif

            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Saved API Keys</h3>
                @if($aiProviders->isEmpty())
                    <div class="border border-dashed rounded-xl p-10 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <p class="text-sm text-gray-500">No API keys saved yet</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($aiProviders as $prov)
                            <div class="flex items-center justify-between bg-white border border-gray-100 rounded-xl px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $prov->label }}</p>
                                        <p class="text-xs text-gray-500 font-mono">{{ $prov->key_preview }}</p>
                                    </div>
                                </div>
                                @if($isAgency)
                                    <button wire:click="deleteProvider('{{ $prov->id }}')"
                                        wire:confirm="Remove this API key?"
                                        class="text-gray-400 hover:text-red-500 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── PLATFORM DATA TAB ── --}}
    @if($activeTab === 'platform')
        @if($connections->isEmpty())
            <div class="border border-dashed rounded-xl p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                <p class="text-sm text-gray-500">No platform data yet</p>
                <p class="text-xs text-gray-400 mt-1">Connect an analytics platform in the Analytics tab first</p>
            </div>
        @else
            @php
                $platformLabels = [
                    'google_analytics' => 'Google Analytics',
                    'google_search_console' => 'Google Search Console',
                    'facebook_ads' => 'Facebook Ads',
                    'linkedin_ads' => 'LinkedIn Ads',
                    'semrush' => 'SEMrush',
                ];
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($connections as $conn)
                    <div class="bg-white border border-gray-100 rounded-xl p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $platformLabels[$conn->platform] ?? $conn->platform }}</p>
                                <p class="text-xs text-gray-500">Connected · Data syncing</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

</div>
