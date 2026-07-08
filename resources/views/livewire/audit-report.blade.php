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
            <a href="{{ route('audits.index') }}" class="text-xs text-[#FC54AA] hover:underline mt-2 inline-block">Back to Audits</a>
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

    {{-- Priority Actions (Fails) --}}
    @if(count($failedItems) > 0)
        <div class="border border-red-100 rounded-xl bg-white overflow-hidden">
            <div class="px-5 py-4 bg-red-50 border-b border-red-100">
                <h2 class="text-sm font-semibold text-red-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Critical Issues — Fix Immediately ({{ count($failedItems) }})
                </h2>
            </div>
            <div class="divide-y divide-red-50">
                @foreach($failedItems as $item)
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red-500 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $item['text'] }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ ucfirst($item['section']) }} › {{ $item['category'] }}</p>
                                @if($item['reason'])
                                    <p class="text-sm text-red-700 mt-2">{{ $item['reason'] }}</p>
                                @endif
                                @if($item['fix_instruction'])
                                    <div class="mt-2 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
                                        <p class="text-xs font-semibold text-red-600 mb-0.5">How to fix</p>
                                        <p class="text-sm text-red-800">{{ $item['fix_instruction'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Issues to Address --}}
    @if(count($noItems) > 0)
        <div class="border border-orange-100 rounded-xl bg-white overflow-hidden">
            <div class="px-5 py-4 bg-orange-50 border-b border-orange-100">
                <h2 class="text-sm font-semibold text-orange-700">Issues to Address ({{ count($noItems) }})</h2>
            </div>
            <div class="divide-y divide-orange-50">
                @foreach($noItems as $item)
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 mt-0.5 w-5 h-5 rounded-full bg-orange-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $item['text'] }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ ucfirst($item['section']) }} › {{ $item['category'] }}</p>
                                @if($item['reason'])
                                    <p class="text-sm text-gray-600 mt-2">{{ $item['reason'] }}</p>
                                @endif
                                @if($item['fix_instruction'])
                                    <div class="mt-2 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                                        <p class="text-xs font-semibold text-amber-700 mb-0.5">How to fix</p>
                                        <p class="text-sm text-amber-900">{{ $item['fix_instruction'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Score by Category --}}
    <div class="border border-gray-100 rounded-xl bg-white overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Score by Category</h2>
        </div>
        <div class="p-5 space-y-4">
            @php
                $sectionLabels = ['ux' => 'UX', 'content' => 'Content'];
                $lastSection = null;
            @endphp
            @foreach($categoryScores as $key => $cat)
                @if($cat['section'] !== $lastSection)
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider {{ $loop->first ? '' : 'mt-6 pt-4 border-t border-gray-100' }}">
                        {{ $sectionLabels[$cat['section']] ?? $cat['section'] }} Audit
                    </p>
                    @php $lastSection = $cat['section']; @endphp
                @endif
                @if($cat['total'] > 0)
                    @php
                        $catPct = $cat['score'] ?? 0;
                        $barColor = $catPct >= 80 ? 'bg-green-500' : ($catPct >= 60 ? 'bg-amber-400' : 'bg-red-500');
                        $textColor = $catPct >= 80 ? 'text-green-600' : ($catPct >= 60 ? 'text-amber-600' : 'text-red-600');
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs text-gray-700">{{ $cat['category'] }}</span>
                            <span class="text-xs font-semibold {{ $textColor }}">{{ $catPct }}% ({{ $cat['pass'] }}/{{ $cat['total'] }})</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full {{ $barColor }} transition-all" style="width: {{ $catPct }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">{{ $cat['category'] }}</span>
                        <span class="text-xs text-gray-400">{{ ($cat['all_na'] ?? false) ? 'N/A for this product type' : 'Not scored' }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

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
