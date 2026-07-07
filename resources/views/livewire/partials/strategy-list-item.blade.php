@php
    $statusConfig = [
        'draft'              => ['label' => 'Draft',              'color' => 'bg-gray-100 text-gray-600'],
        'in_review'          => ['label' => 'In Review',          'color' => 'bg-yellow-50 text-yellow-700'],
        'approved'           => ['label' => 'Approved',           'color' => 'bg-green-50 text-green-700'],
        'changes_requested'  => ['label' => 'Changes Requested',  'color' => 'bg-red-50 text-red-600'],
    ];
    $cfg = $statusConfig[$strategy->status] ?? $statusConfig['draft'];
    $isSelected = $selectedId === $strategy->id;
@endphp

<div class="group mx-2 mb-1 rounded-lg border transition-all {{ $isSelected ? 'border-[#FC54AA] bg-[#FCE4F1]' : 'border-transparent hover:border-gray-200 hover:bg-gray-50' }}">
    <button wire:click="selectStrategy('{{ $strategy->id }}')" class="w-full text-left p-3">
        <div class="flex items-center gap-1.5 mb-1">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs {{ $cfg['color'] }}">{{ $cfg['label'] }}</span>
        </div>
        <p class="text-xs text-gray-500">{{ $strategy->created_at->format('M j, Y') }}</p>
    </button>
    @if($isAgency)
        <div class="px-3 pb-2 flex justify-end items-center gap-3">
            @if($strategy->archived)
                <button wire:click="unarchiveStrategy('{{ $strategy->id }}')" class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.21"/></svg>
                    Restore
                </button>
                <button wire:click="confirmDelete('{{ $strategy->id }}')" class="text-xs text-red-400 hover:text-red-600 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    Delete
                </button>
            @else
                <button wire:click="archiveStrategy('{{ $strategy->id }}')" class="text-xs text-gray-400 hover:text-gray-600 opacity-0 group-hover:opacity-100 flex items-center gap-1 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                    Archive
                </button>
            @endif
        </div>
    @endif
</div>
