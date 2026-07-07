<div class="flex h-full overflow-hidden">

    {{-- Flash toast --}}
    @if(session('toast'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg">
            {{ session('toast') }}
        </div>
    @endif

    {{-- ── Left: strategy list ── --}}
    <div class="w-64 shrink-0 border-r border-gray-100 flex flex-col overflow-hidden bg-white">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Strategies</p>
            @if($isAgency)
                <a href="{{ route('strategy.new') }}" class="text-[#FC54AA] hover:text-[#E0429A] p-1 rounded transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </a>
            @endif
        </div>

        <div class="flex-1 overflow-y-auto py-2">
            @if($strategies->isEmpty())
                <div class="p-4 text-center">
                    <p class="text-xs text-gray-400 mb-3">No strategies yet</p>
                    @if($isAgency)
                        <a href="{{ route('strategy.new') }}" class="block w-full text-center text-xs font-medium bg-[#FC54AA] hover:bg-[#E0429A] text-white py-1.5 px-3 rounded-lg transition-colors">
                            Start Wizard
                        </a>
                    @endif
                </div>
            @else
                {{-- Active strategies --}}
                @foreach($active as $s)
                    @include('livewire.partials.strategy-list-item', ['strategy' => $s, 'isAgency' => $isAgency, 'selectedId' => $selectedId])
                @endforeach

                {{-- Archived --}}
                @if($archived->isNotEmpty())
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide px-4 pt-4 pb-1">Archived</p>
                    @foreach($archived as $s)
                        @include('livewire.partials.strategy-list-item', ['strategy' => $s, 'isAgency' => $isAgency, 'selectedId' => $selectedId])
                    @endforeach
                @endif
            @endif
        </div>
    </div>

    {{-- ── Right: document editor ── --}}
    <div class="flex-1 flex flex-col overflow-hidden bg-white">
        @if($selected)
            @php
                $statusConfig = [
                    'draft'              => ['label' => 'Draft',              'color' => 'bg-gray-100 text-gray-600'],
                    'in_review'          => ['label' => 'In Review',          'color' => 'bg-yellow-50 text-yellow-700'],
                    'approved'           => ['label' => 'Approved',           'color' => 'bg-green-50 text-green-700'],
                    'changes_requested'  => ['label' => 'Changes Requested',  'color' => 'bg-red-50 text-red-600'],
                ];
                $cfg = $statusConfig[$selected->status] ?? $statusConfig['draft'];
                $isEditable  = in_array($selected->status, ['draft', 'changes_requested']);
                $isApproved  = $selected->status === 'approved';
                $isInReview  = $selected->status === 'in_review';
                $isArchived  = $selected->archived;
                $activelyEditing = $isEditable || ($isApproved && $editing);
            @endphp

            {{-- Toolbar --}}
            <div class="h-12 px-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-800">Strategy Document</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $cfg['color'] }}">{{ $cfg['label'] }}</span>
                    @if($isArchived)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-500">Archived</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $selected->created_at->format('M j, Y') }}</span>
                </div>

                @if($isAgency && !$isArchived)
                    <div class="flex gap-2 items-center">
                        @if($isInReview)
                            <button wire:click="requestChanges" class="flex items-center gap-1 h-7 px-2.5 text-xs text-red-600 border border-red-200 hover:bg-red-50 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                Request Changes
                            </button>
                            <button wire:click="approve" class="flex items-center gap-1 h-7 px-2.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                Approve
                            </button>
                        @endif
                        @if($isApproved && !$editing)
                            <button wire:click="$set('editing', true)" class="flex items-center gap-1 h-7 px-2.5 text-xs border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Strategy
                            </button>
                        @endif
                        @if($isApproved && $editing)
                            <span class="text-xs text-yellow-600 font-medium">Editing will reset to In Review</span>
                            <button wire:click="$set('editing', false)" class="h-7 px-2.5 text-xs text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">Cancel</button>
                            <button wire:click="saveDocument" class="flex items-center gap-1 h-7 px-2.5 text-xs bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors">
                                Save & Submit for Review
                            </button>
                        @endif
                        @if($isEditable)
                            <button wire:click="saveDocument" class="flex items-center gap-1 h-7 px-2.5 text-xs bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors">
                                {{ $selected->status === 'changes_requested' ? 'Save & Resubmit for Review' : 'Save Draft' }}
                            </button>
                        @endif
                        @if($isApproved && !$editing && $docText)
                            <a href="{{ route('strategy.slides', $selected->id) }}"
                               class="flex items-center gap-1 h-7 px-2.5 text-xs bg-[#003470] hover:bg-[#002458] text-white rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                Create Slides
                            </a>
                        @endif
                        @if($docText)
                            <button
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText($wire.docText).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                class="flex items-center gap-1 h-7 px-2.5 text-xs border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors"
                            >
                                <template x-if="!copied">
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                        Copy
                                    </span>
                                </template>
                                <template x-if="copied">
                                    <span class="flex items-center gap-1 text-green-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        Copied!
                                    </span>
                                </template>
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Document body --}}
            <div class="flex-1 overflow-hidden flex flex-col">
                @if($activelyEditing)
                    <textarea
                        wire:model="docText"
                        class="flex-1 w-full resize-none border-0 border-b border-gray-100 focus:outline-none focus:ring-0 text-sm text-gray-800 leading-relaxed p-6"
                        placeholder="Write or paste the strategy document here…"
                    ></textarea>
                @else
                    <div class="flex-1 overflow-y-auto px-6 py-5">
                        @if($docText)
                            <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans leading-relaxed">{{ $docText }}</pre>
                        @else
                            <p class="text-sm text-gray-400 text-center mt-12">No document content yet</p>
                        @endif
                    </div>
                @endif

                {{-- Review panel --}}
                @if($isAgency && $isInReview && !$isArchived)
                    <div class="border-t border-gray-100 p-5 space-y-3 shrink-0 bg-white">
                        <p class="text-sm font-medium text-gray-900">Review</p>
                        <textarea
                            wire:model="reviewNotes"
                            placeholder="Add review notes (required for requesting changes)…"
                            rows="3"
                            class="w-full resize-none text-sm border border-gray-200 rounded-lg p-2.5 focus:outline-none focus:ring-1 focus:ring-[#FC54AA]"
                        ></textarea>
                        <div class="flex gap-2">
                            <button wire:click="approve" class="flex items-center gap-1.5 px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                Approve
                            </button>
                            <button wire:click="requestChanges" class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-red-600 border border-red-200 hover:bg-red-50 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                Request Changes
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Review notes display --}}
                @if($selected->review_notes && !$isInReview && !$isApproved)
                    <div class="mx-5 mb-5 bg-yellow-50 border border-yellow-200 rounded-lg p-3 shrink-0">
                        <p class="text-xs font-medium text-yellow-800 mb-1">Review notes</p>
                        <p class="text-xs text-yellow-700">{{ $selected->review_notes }}</p>
                    </div>
                @endif
            </div>
        @else
            <div class="flex items-center justify-center h-full text-sm text-gray-400">
                {{ $strategies->isEmpty() ? 'Create a strategy to get started' : 'Select a strategy' }}
            </div>
        @endif
    </div>

    {{-- Delete confirm modal --}}
    @if($deleteTargetId)
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
                <h3 class="text-base font-semibold mb-2">Delete strategy?</h3>
                <p class="text-sm text-gray-500 mb-5">This will permanently delete this strategy. This action cannot be undone.</p>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('deleteTargetId', null)" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">Cancel</button>
                    <button wire:click="deleteStrategy" wire:loading.attr="disabled" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        <span wire:loading.remove wire:target="deleteStrategy">Delete Strategy</span>
                        <span wire:loading wire:target="deleteStrategy">Deleting…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
