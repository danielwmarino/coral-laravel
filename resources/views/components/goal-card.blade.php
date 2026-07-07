@props(['goal', 'isAgency' => false])

@php
    $statusConfig = [
        'not_started'  => ['label' => 'Not Started',  'bg' => 'bg-gray-100',    'text' => 'text-gray-500'],
        'in_progress'  => ['label' => 'In Progress',   'bg' => 'bg-blue-50',     'text' => 'text-blue-600'],
        'completed'    => ['label' => 'Completed',     'bg' => 'bg-emerald-50',  'text' => 'text-emerald-600'],
        'at_risk'      => ['label' => 'At Risk',       'bg' => 'bg-rose-50',     'text' => 'text-rose-600'],
    ];
    $s = $statusConfig[$goal->status] ?? $statusConfig['not_started'];
    $pct = $goal->progressPercent();
@endphp

<a href="{{ route('goals.show', $goal->id) }}" class="block bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-[#FC54AA]/30 transition-all group">

    {{-- Header: title + status badge --}}
    <div class="flex items-start justify-between gap-2 mb-3">
        <h3 class="text-sm font-semibold text-[#003470] leading-snug group-hover:text-[#FC54AA] transition-colors line-clamp-2">
            {{ $goal->title }}
        </h3>
        <span class="flex-shrink-0 text-xs font-medium px-2 py-0.5 rounded-full {{ $s['bg'] }} {{ $s['text'] }}">
            {{ $s['label'] }}
        </span>
    </div>

    {{-- Description --}}
    @if($goal->description)
        <p class="text-xs text-gray-400 leading-relaxed mb-3 line-clamp-2">{{ $goal->description }}</p>
    @endif

    {{-- Progress bar --}}
    @if($goal->target_value)
        <div class="mb-3">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs text-gray-400">Progress</span>
                <span class="text-xs font-semibold text-[#003470]">{{ $pct }}%</span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full rounded-full transition-all duration-500
                           {{ $pct >= 100 ? 'bg-emerald-400' : ($pct >= 60 ? 'bg-[#FC54AA]' : 'bg-[#003470]') }}"
                    style="width: {{ min(100, $pct) }}%"
                ></div>
            </div>
            <div class="flex justify-between mt-1">
                <span class="text-xs text-gray-300">
                    {{ number_format($goal->current_value, $goal->metric_type === 'currency' ? 2 : 0) }}
                    {{ $goal->metric_type === 'percentage' ? '%' : '' }}
                    {{ $goal->metric_type === 'currency' ? '' : '' }}
                </span>
                <span class="text-xs text-gray-300">
                    of {{ number_format($goal->target_value, $goal->metric_type === 'currency' ? 2 : 0) }}
                    {{ $goal->metric_type === 'percentage' ? '%' : '' }}
                </span>
            </div>
        </div>
    @endif

    {{-- Footer: due date + strategist notes indicator --}}
    <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-50">
        @if($goal->due_date)
            <span class="flex items-center gap-1 text-xs text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ $goal->due_date->format('M j, Y') }}
            </span>
        @else
            <span></span>
        @endif

        @if($goal->strategist_notes && $isAgency)
            <span class="flex items-center gap-1 text-xs text-[#FC54AA]" title="{{ $goal->strategist_notes }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Note
            </span>
        @endif
    </div>

    {{-- Strategist notes visible to client too --}}
    @if($goal->strategist_notes && !$isAgency)
        <div class="mt-3 pt-3 border-t border-gray-50">
            <p class="text-xs text-gray-400 italic leading-relaxed line-clamp-2">
                <span class="font-medium text-[#FC54AA] not-italic">Strategist: </span>{{ $goal->strategist_notes }}
            </p>
        </div>
    @endif

</a>
