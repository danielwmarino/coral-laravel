<div>

    @if (session()->has('message'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (!$client)
        <div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
            <div class="w-16 h-16 rounded-full bg-[#FCE4F1] flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FC54AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-[#003470] mb-1">No client selected</h2>
            <p class="text-sm text-gray-400">Select a client from the sidebar to view their dashboard.</p>
        </div>
    @else

        {{-- ── 1. HEADER ── --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-[#003470]">{{ $client->name }}</h1>
            <p class="text-sm text-gray-400 mt-0.5">Marketing intelligence overview</p>
        </div>

        {{-- ── 2. MESSAGE FROM STRATEGIST ── --}}
        <div class="mb-6">
            <div class="flex items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FC54AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <h2 class="text-sm font-semibold text-[#003470]">Message from your Strategist</h2>
                </div>
                @if($isAgency)
                    <button onclick="Livewire.dispatch('start-editing-message')"
                        class="text-xs font-medium text-[#FC54AA] hover:text-[#E0429A] transition-colors flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </button>
                @endif
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                @if($isAgency)
                    @livewire('strategist-message-editor', ['client' => $client], key('msg-'.$client->id))
                @else
                    @if($client->strategist_message)
                        <div class="text-sm text-gray-600 leading-relaxed prose prose-sm max-w-none">{!! $client->strategist_message !!}</div>
                    @else
                        <p class="text-sm text-gray-300 italic">No message from your strategist yet.</p>
                    @endif
                @endif
            </div>
        </div>

        {{-- ── 3. DATA OVERVIEW ── --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FC54AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <h2 class="text-sm font-semibold text-[#003470]">Data Overview</h2>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Goals</p>
                    <p class="text-3xl font-bold mt-2 text-[#003470]">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">In Progress</p>
                    <p class="text-3xl font-bold mt-2 text-blue-500">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Completed</p>
                    <p class="text-3xl font-bold mt-2 text-emerald-500">{{ $stats['completed'] }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">At Risk</p>
                    <p class="text-3xl font-bold mt-2 text-rose-500">{{ $stats['at_risk'] }}</p>
                </div>
            </div>
        </div>

        {{-- ── 4. EXECUTIVE SUMMARY ── --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FC54AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    <h2 class="text-sm font-semibold text-[#003470]">Executive Summary</h2>
                </div>
                @if($isAgency)
                    <button wire:click="regenerateSummary" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-[#FC54AA] hover:text-[#E0429A] transition-colors disabled:opacity-50">
                        <svg wire:loading wire:target="regenerateSummary" class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        <svg wire:loading.remove wire:target="regenerateSummary" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.08-4.49"/></svg>
                        Regenerate
                    </button>
                @endif
            </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">

            @if($client->executive_summary)
                @php
                    $execData = json_decode($client->executive_summary, true);
                    $isStructured = is_array($execData) && isset($execData['verdict']);
                    $typeConfig = [
                        'win'   => ['text' => 'text-green-600',  'dot' => 'bg-green-500',  'label' => 'Win'],
                        'risk'  => ['text' => 'text-red-600',    'dot' => 'bg-red-500',    'label' => 'Risk'],
                        'gap'   => ['text' => 'text-yellow-600', 'dot' => 'bg-yellow-500', 'label' => 'Gap'],
                        'watch' => ['text' => 'text-blue-600',   'dot' => 'bg-blue-500',   'label' => 'Watch'],
                    ];
                @endphp

                @if($isStructured)
                    {{-- Verdict --}}
                    <p class="text-sm font-semibold text-gray-900 leading-snug mb-4">{{ $execData['verdict'] }}</p>

                    {{-- Callouts --}}
                    <div class="divide-y divide-gray-100 border-t border-gray-100">
                        @foreach($execData['callouts'] ?? [] as $callout)
                            @php $cfg = $typeConfig[$callout['type']] ?? $typeConfig['watch']; @endphp
                            <div class="flex items-start gap-3 py-2.5">
                                <span class="mt-1.5 w-2 h-2 rounded-full {{ $cfg['dot'] }} shrink-0"></span>
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wide {{ $cfg['text'] }} mr-1.5">{{ $cfg['label'] }}</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $callout['headline'] }}</span>
                                    @if(!empty($callout['detail']))
                                        <p class="text-xs text-gray-600 mt-0.5">{{ $callout['detail'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Bottom line --}}
                    @if(!empty($execData['bottom_line']))
                        <div class="mt-4 border-t border-gray-100 pt-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Bottom Line</p>
                            <p class="text-sm text-gray-800">{{ $execData['bottom_line'] }}</p>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $client->executive_summary }}</p>
                @endif

                @if($client->executive_summary_updated_at)
                    <p class="text-xs text-gray-300 mt-4">Generated {{ $client->executive_summary_updated_at->format('M j, Y, g:i A') }}</p>
                @endif
            @else
                <p class="text-sm text-gray-300 italic">
                    {{ $isAgency ? 'No summary yet — click Regenerate to generate one with AI.' : 'No summary available yet.' }}
                </p>
            @endif
        </div>
        </div>

        {{-- ── 5. GOALS ── --}}
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FC54AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                <h2 class="text-sm font-semibold text-[#003470]">Goals</h2>
            </div>
            @if($stats['total'] > 0)
                <a href="{{ route('goals.index') }}" class="inline-flex items-center gap-1 text-xs text-[#FC54AA] hover:text-[#E0429A] font-medium transition-colors">
                    View all <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @endif
        </div>

        @if($goals->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center mb-8">
                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                </div>
                <p class="text-sm text-gray-400">No active goals yet.</p>
                @if($isAgency)
                    <a href="{{ route('goals.index') }}" class="inline-block mt-3 text-xs font-medium text-[#FC54AA] hover:text-[#E0429A]">Add a goal →</a>
                @endif
            </div>
        @else
            <div class="space-y-3 mb-8">
                @foreach($goals as $goal)
                    @php
                        $statusColors = ['not_started'=>'bg-gray-100 text-gray-600','in_progress'=>'bg-blue-50 text-blue-700','completed'=>'bg-green-50 text-green-700','at_risk'=>'bg-red-50 text-red-600'];
                        $color = $statusColors[$goal->status] ?? 'bg-gray-100 text-gray-600';
                        $progress = $goal->progressPercent();
                    @endphp
                    <a href="{{ route('goals.show', $goal->id) }}" class="block group">
                        <div class="bg-white border border-gray-100 rounded-xl hover:border-[#f7a0bc] hover:shadow-sm transition-all">
                            <div class="px-8 py-5 grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-12">
                                <div>
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <p class="text-xl font-semibold text-[#003470] leading-snug">{{ $goal->title }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $color }} shrink-0 capitalize whitespace-nowrap">{{ str_replace('_', ' ', ucfirst($goal->status)) }}</span>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <span>{{ number_format($goal->current_value) }} / {{ $goal->target_value ? number_format($goal->target_value) : '—' }}</span>
                                            <span>{{ $progress }}%</span>
                                        </div>
                                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-[#FC54AA] rounded-full" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                    @if($goal->due_date)
                                        <p class="text-xs text-gray-400 mt-3">Due {{ $goal->due_date->format('n/j/Y') }}</p>
                                    @endif
                                </div>
                                <div>
                                    @if($goal->strategist_notes)
                                        <p class="text-xs font-medium text-[#FC54AA] mb-1">Strategist Notes</p>
                                        <p class="text-sm text-gray-500 line-clamp-4 leading-relaxed whitespace-pre-line">{{ $goal->strategist_notes }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif


    @endif
</div>
