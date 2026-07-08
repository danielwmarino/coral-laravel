<div class="space-y-8" id="audit-report">

    <style>
        @media print {
            /* Un-clip the full-height layout so all content prints */
            html, body { height: auto !important; overflow: visible !important; background: white !important; }
            body > div { height: auto !important; overflow: visible !important; display: block !important; }
            aside, header, nav, .no-print { display: none !important; }
            main, main > div, main > div > div { height: auto !important; overflow: visible !important; }
            #audit-report { padding: 0 !important; }
            .print-break { page-break-before: always; }
            /* Ensure cards don't split across pages awkwardly */
            .border { break-inside: avoid; }
        }
    </style>

    @if(!$this->audit)
        <div class="border border-dashed rounded-xl p-12 text-center">
            <p class="text-sm text-gray-500">Audit not found.</p>
            <a href="{{ route('audits') }}" class="text-xs text-[#FC54AA] hover:underline mt-2 inline-block">Back to Audits</a>
        </div>
    @else

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap no-print">
        <div class="flex items-center gap-2">
            <a href="{{ route('audits.checklist', $this->audit->id) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <h1 class="text-xl font-semibold text-[#003470]">Audit Report</h1>
        </div>
        <button onclick="window.print()"
            class="flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-lg transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print / Save PDF
        </button>
    </div>

    {{-- Audit Meta Card --}}
    <div class="border border-gray-100 rounded-xl p-6 bg-white">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Product / Site</p>
                <p class="text-sm font-semibold text-gray-900">{{ $this->audit->product_name }}</p>
            </div>
            @if($this->audit->product_url)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">URL</p>
                    <a href="{{ $this->audit->product_url }}" target="_blank" class="text-sm text-[#FC54AA] hover:underline truncate block">{{ $this->audit->product_url }}</a>
                </div>
            @endif
            @if($this->audit->auditor_name)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Auditor</p>
                    <p class="text-sm text-gray-700">{{ $this->audit->auditor_name }}</p>
                </div>
            @endif
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Audit Date</p>
                <p class="text-sm text-gray-700">{{ $this->audit->audit_date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Product Type</p>
                <p class="text-sm text-gray-700">
                    @php
                        $typeLabels = ['marketing_site' => 'Marketing Site', 'web_app' => 'Web App', 'saas_dashboard' => 'SaaS Dashboard', 'ecommerce' => 'eCommerce', 'mobile_app' => 'Mobile App'];
                    @endphp
                    {{ $typeLabels[$this->audit->product_type] ?? $this->audit->product_type }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Items Evaluated</p>
                <p class="text-sm text-gray-700">
                    @php
                        $totalEvaluated = collect($responses)->filter(fn($r) => in_array($r, ['yes','no','fail']))->count();
                    @endphp
                    {{ $totalEvaluated }} items
                </p>
            </div>
        </div>
    </div>

    {{-- Score Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Overall Score Circle --}}
        @php
            $overall = $this->audit->overall_score;
            if ($overall === null) {
                $grade = '—';
                $gradeColor = 'text-gray-400';
                $circleColor = '#9ca3af';
                $scoreBg = 'bg-gray-50';
            } elseif ($overall >= 90) {
                $grade = 'A'; $gradeColor = 'text-green-600'; $circleColor = '#22c55e'; $scoreBg = 'bg-green-50';
            } elseif ($overall >= 80) {
                $grade = 'B'; $gradeColor = 'text-green-600'; $circleColor = '#22c55e'; $scoreBg = 'bg-green-50';
            } elseif ($overall >= 70) {
                $grade = 'C'; $gradeColor = 'text-amber-600'; $circleColor = '#f59e0b'; $scoreBg = 'bg-amber-50';
            } elseif ($overall >= 60) {
                $grade = 'D'; $gradeColor = 'text-orange-600'; $circleColor = '#f97316'; $scoreBg = 'bg-orange-50';
            } else {
                $grade = 'F'; $gradeColor = 'text-red-600'; $circleColor = '#ef4444'; $scoreBg = 'bg-red-50';
            }
            $circumference = 2 * 3.14159 * 40;
            $offset = $overall !== null ? $circumference - ($overall / 100) * $circumference : $circumference;
        @endphp
        <div class="border border-gray-100 rounded-xl p-6 bg-white flex flex-col items-center justify-center md:col-span-1">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Overall Score</p>
            <div class="relative w-28 h-28">
                <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="#f3f4f6" stroke-width="10"/>
                    <circle cx="50" cy="50" r="40" fill="none"
                        stroke="{{ $circleColor }}" stroke-width="10"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                        stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-bold {{ $gradeColor }}">{{ $grade }}</span>
                    <span class="text-xs text-gray-500">{{ $overall !== null ? $overall . '%' : 'N/A' }}</span>
                </div>
            </div>
        </div>

        {{-- UX Score --}}
        @php
            $ux = $this->audit->ux_score;
            $uxColor = $ux === null ? 'text-gray-400' : ($ux >= 80 ? 'text-green-600' : ($ux >= 60 ? 'text-amber-600' : 'text-red-600'));
        @endphp
        <div class="border border-gray-100 rounded-xl p-6 bg-white flex flex-col items-center justify-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">UX Score</p>
            <p class="text-4xl font-bold {{ $uxColor }}">{{ $ux !== null ? $ux . '%' : '—' }}</p>
            <p class="text-xs text-gray-400 mt-1">User Experience</p>
        </div>

        {{-- Content Score --}}
        @php
            $content = $this->audit->content_score;
            $contentColor = $content === null ? 'text-gray-400' : ($content >= 80 ? 'text-green-600' : ($content >= 60 ? 'text-amber-600' : 'text-red-600'));
        @endphp
        <div class="border border-gray-100 rounded-xl p-6 bg-white flex flex-col items-center justify-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Content Score</p>
            <p class="text-4xl font-bold {{ $contentColor }}">{{ $content !== null ? $content . '%' : '—' }}</p>
            <p class="text-xs text-gray-400 mt-1">Content Quality</p>
        </div>
    </div>

    {{-- Issue Breakdown --}}
    @php
        $failCount = $responseCounts['fail'];
        $noCount   = $responseCounts['no'];
        $yesCount  = $responseCounts['yes'];
    @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="border border-red-100 rounded-xl p-4 bg-red-50 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $failCount }}</p>
            <p class="text-xs font-medium text-red-500 mt-0.5">Critical Issues</p>
            <p class="text-xs text-red-400">Marked Fail</p>
        </div>
        <div class="border border-orange-100 rounded-xl p-4 bg-orange-50 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ $noCount }}</p>
            <p class="text-xs font-medium text-orange-500 mt-0.5">Issues Found</p>
            <p class="text-xs text-orange-400">Marked No</p>
        </div>
        <div class="border border-green-100 rounded-xl p-4 bg-green-50 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $yesCount }}</p>
            <p class="text-xs font-medium text-green-500 mt-0.5">Passing</p>
            <p class="text-xs text-green-400">Marked Yes</p>
        </div>
    </div>

    {{-- Findings by Category (accordion) --}}
    @php
        // Build a map: section|category → [items with response data]
        $allSectionsMap = ['ux' => $uxItems, 'content' => $contentItems];
        $categoryFindings = [];
        foreach ($allSectionsMap as $sectionName => $categories) {
            foreach ($categories as $categoryName => $items) {
                $catKey = $sectionName . '|' . $categoryName;
                $catItems = [];
                foreach ($items as $item) {
                    $entry    = $responses[$sectionName . '.' . $item['key']] ?? null;
                    $response = is_array($entry) ? ($entry['response'] ?? null) : $entry;
                    $catItems[] = [
                        'text'            => $item['text'],
                        'key'             => $item['key'],
                        'response'        => $response,
                        'reason'          => is_array($entry) ? ($entry['reason'] ?? null) : null,
                        'fix_instruction' => is_array($entry) ? ($entry['fix_instruction'] ?? null) : null,
                        'section'         => $sectionName,
                        'category'        => $categoryName,
                    ];
                }
                $order = ['fail' => 0, 'no' => 1, 'yes' => 2, 'na' => 3, null => 4];
                usort($catItems, fn($a, $b) => ($order[$a['response']] ?? 4) <=> ($order[$b['response']] ?? 4));

                $catScore = $categoryScores[$catKey] ?? null;
                $categoryFindings[$catKey] = [
                    'section'  => $sectionName,
                    'category' => $categoryName,
                    'items'    => $catItems,
                    'score'    => $catScore,
                ];
            }
        }
        $sectionLabels = ['ux' => 'UX Audit', 'content' => 'Content Audit'];
        $lastSectionLabel = null;
    @endphp

    @foreach($categoryFindings as $catKey => $catData)
        @php
            $cat       = $catData['score'];
            $catItems  = $catData['items'];
            $catPct    = ($cat && $cat['total'] > 0) ? ($cat['score'] ?? 0) : null;
            $failCount = collect($catItems)->where('response', 'fail')->count();
            $noCount   = collect($catItems)->where('response', 'no')->count();
            $yesCount  = collect($catItems)->where('response', 'yes')->count();
            $issueCount = $failCount + $noCount;

            if ($catPct === null) {
                $headerBg  = 'bg-gray-50 border-gray-100';
                $barColor  = 'bg-gray-300';
                $textColor = 'text-gray-400';
                $pctLabel  = ($cat['all_na'] ?? false) ? 'N/A' : '—';
            } elseif ($catPct >= 80) {
                $headerBg  = 'bg-green-50 border-green-100';
                $barColor  = 'bg-green-500';
                $textColor = 'text-green-600';
                $pctLabel  = $catPct . '%';
            } elseif ($catPct >= 60) {
                $headerBg  = 'bg-amber-50 border-amber-100';
                $barColor  = 'bg-amber-400';
                $textColor = 'text-amber-600';
                $pctLabel  = $catPct . '%';
            } else {
                $headerBg  = 'bg-red-50 border-red-100';
                $barColor  = 'bg-red-500';
                $textColor = 'text-red-600';
                $pctLabel  = $catPct . '%';
            }
            $sectionLabel = $sectionLabels[$catData['section']] ?? ucfirst($catData['section']);
        @endphp

        @if($lastSectionLabel !== $sectionLabel)
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider {{ $loop->first ? 'mt-2' : 'mt-6' }}">{{ $sectionLabel }}</p>
            @php $lastSectionLabel = $sectionLabel; @endphp
        @endif

        <div x-data="{ open: {{ $issueCount > 0 ? 'true' : 'false' }} }" class="border border-gray-100 rounded-xl bg-white overflow-hidden">

            {{-- Accordion Header --}}
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex-shrink-0">
                        @if($failCount > 0)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold">{{ $failCount }}</span>
                        @elseif($noCount > 0)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-400 text-white text-xs font-bold">{{ $noCount }}</span>
                        @else
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-500 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ $catData['category'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if($catPct !== null)
                                {{ $yesCount }} passed
                                @if($failCount > 0) · <span class="text-red-500">{{ $failCount }} critical</span>@endif
                                @if($noCount > 0) · <span class="text-amber-600">{{ $noCount }} issues</span>@endif
                            @else
                                {{ $pctLabel }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-4 flex-shrink-0 ml-4">
                    @if($catPct !== null)
                        <div class="hidden sm:flex items-center gap-2">
                            <div class="w-24 bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $barColor }}" style="width: {{ $catPct }}%"></div>
                            </div>
                            <span class="text-xs font-semibold {{ $textColor }} w-8 text-right">{{ $pctLabel }}</span>
                        </div>
                    @endif
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-gray-400 transition-transform duration-200 flex-shrink-0"
                        :class="open ? 'rotate-180' : ''">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
            </button>

            {{-- Accordion Body --}}
            <div x-show="open"
                x-transition:enter="transition-all duration-200 ease-out"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition-all duration-150 ease-in"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="border-t border-gray-100 divide-y divide-gray-50">
                @foreach($catItems as $item)
                    @php
                        $r = $item['response'];
                        if ($r === 'yes') {
                            $dot = 'bg-green-500'; $labelColor = 'text-green-700'; $label = 'Pass';
                        } elseif ($r === 'fail') {
                            $dot = 'bg-red-500'; $labelColor = 'text-red-600'; $label = 'Critical';
                        } elseif ($r === 'no') {
                            $dot = 'bg-amber-400'; $labelColor = 'text-amber-600'; $label = 'Issue';
                        } else {
                            $dot = 'bg-gray-300'; $labelColor = 'text-gray-400'; $label = 'N/A';
                        }
                    @endphp
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 mt-1 w-2 h-2 rounded-full {{ $dot }}"></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm text-gray-800 font-medium leading-snug">{{ $item['text'] }}</p>
                                    <span class="text-xs {{ $labelColor }} font-semibold flex-shrink-0">{{ $label }}</span>
                                </div>
                                @if($item['reason'])
                                    <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">{{ $item['reason'] }}</p>
                                @endif
                                @if($item['fix_instruction'])
                                    <div class="mt-2 {{ $r === 'fail' ? 'bg-red-50 border-red-100' : 'bg-amber-50 border-amber-100' }} border rounded-lg px-3 py-2">
                                        <p class="text-xs font-semibold {{ $r === 'fail' ? 'text-red-600' : 'text-amber-700' }} mb-0.5">How to fix</p>
                                        <p class="text-sm {{ $r === 'fail' ? 'text-red-800' : 'text-amber-900' }}">{{ $item['fix_instruction'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Pages Checked --}}
    @if(!empty($this->audit->crawled_pages))
        <div class="border border-gray-100 rounded-xl bg-white overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <h2 class="text-sm font-semibold text-gray-900">Pages Checked ({{ count($this->audit->crawled_pages) }})</h2>
            </div>
            <ul class="divide-y divide-gray-50">
                @foreach($this->audit->crawled_pages as $i => $pageUrl)
                    <li class="px-5 py-3 flex items-center gap-3">
                        <span class="text-xs text-gray-400 w-5 text-right flex-shrink-0">{{ $i + 1 }}</span>
                        <a href="{{ $pageUrl }}" target="_blank"
                           class="text-sm text-[#003470] hover:text-[#FC54AA] hover:underline truncate transition-colors">
                            {{ $pageUrl }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Footer links --}}
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('audits.checklist', $this->audit->id) }}"
            class="text-sm text-gray-500 hover:text-[#003470] transition-colors flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Checklist
        </a>
        <button onclick="window.print()"
            class="flex items-center gap-1.5 px-4 py-2 text-sm bg-[#003470] text-white rounded-lg hover:bg-[#002555] transition-colors font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print / Save PDF
        </button>
    </div>

    @endif
</div>
