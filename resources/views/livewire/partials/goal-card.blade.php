@php
    $statusColors = [
        'not_started' => 'bg-gray-100 text-gray-600',
        'in_progress'  => 'bg-blue-50 text-blue-700',
        'completed'    => 'bg-green-50 text-green-700',
        'at_risk'      => 'bg-red-50 text-red-600',
    ];
    $color = $statusColors[$goal->status] ?? 'bg-gray-100 text-gray-600';
    $progress = $goal->progressPercent();
@endphp

<a href="{{ route('goals.show', $goal->id) }}" class="block group">
    <div class="bg-white border border-gray-100 rounded-xl hover:border-[#f7a0bc] hover:shadow-sm transition-all">
        <div class="py-5 px-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-12">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <p class="text-2xl font-semibold text-[#003470] line-clamp-2">{{ $goal->title }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $color }} shrink-0 capitalize">{{ str_replace('_', ' ', $goal->status) }}</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ number_format($goal->current_value) }} / {{ $goal->target_value ? number_format($goal->target_value) : '—' }}</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#FC54AA] rounded-full transition-all" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    @if($goal->due_date)
                        <p class="text-xs text-gray-400 mt-2">Due {{ $goal->due_date->format('n/j/Y') }}</p>
                    @endif
                </div>
                <div>
                    @if($goal->strategist_notes)
                        <p class="text-xs font-medium text-[#FC54AA] mb-1">Strategist Notes</p>
                        <p class="text-gray-500 text-base line-clamp-4 leading-relaxed">{{ $goal->strategist_notes }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</a>
