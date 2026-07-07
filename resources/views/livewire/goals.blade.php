<div class="space-y-8">

    @if(session('toast'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg">
            {{ session('toast') }}
        </div>
    @endif

    @if($generateError)
        <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $generateError }}</div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[#003470]">Goals</h1>
            <p class="text-sm text-gray-500 mt-1">Track your SMART marketing goals</p>
        </div>
        <div class="flex gap-2">
            @if($isAgency)
                <button wire:click="openManage" class="flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    Manage Goals
                </button>
                <button wire:click="$set('addOpen', true)" class="flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Goal
                </button>
                @if($hasStrategy)
                    <button wire:click="generateGoals" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors disabled:opacity-60 whitespace-nowrap">
                        <span class="relative inline-flex w-4 h-4 shrink-0">
                            <svg wire:loading.remove wire:target="generateGoals" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute inset-0"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            <svg wire:loading wire:target="generateGoals" class="animate-spin absolute inset-0" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        </span>
                        <span wire:loading.remove wire:target="generateGoals">Generate from Strategy</span>
                        <span wire:loading wire:target="generateGoals">Generating…</span>
                    </button>
                @endif
            @endif
        </div>
    </div>

    {{-- Generating progress overlay --}}
    @teleport('body')
    <style>
        @keyframes goalProgress {
            0%   { stroke-dashoffset: 163.4; }
            80%  { stroke-dashoffset: 30; }
            100% { stroke-dashoffset: 10; }
        }
    </style>
    <div wire:loading.flex wire:target="generateGoals"
         style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.4);display:none;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:1rem;box-shadow:0 20px 60px rgba(0,0,0,0.15);padding:2.5rem;display:flex;flex-direction:column;align-items:center;gap:1.25rem;width:18rem;">
            <svg style="width:4rem;height:4rem;transform:rotate(-90deg);" viewBox="0 0 64 64">
                <circle cx="32" cy="32" r="26" fill="none" stroke="#F3F4F6" stroke-width="6"/>
                <circle cx="32" cy="32" r="26" fill="none" stroke="#FC54AA" stroke-width="6"
                    stroke-dasharray="163.4"
                    stroke-dashoffset="163.4"
                    stroke-linecap="round"
                    style="animation: goalProgress 25s linear forwards;">
                </circle>
            </svg>
            <div style="text-align:center;">
                <p style="font-size:0.875rem;font-weight:600;color:#111827;">Generating Goals</p>
                <p style="font-size:0.75rem;color:#6b7280;margin-top:0.25rem;">Analysing your strategy…</p>
            </div>
        </div>
    </div>
    @endteleport

    {{-- Active goals grid --}}
    @if($active->isEmpty())
        <div class="border border-dashed rounded-xl p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
            <p class="text-sm text-gray-500">No goals yet</p>
            <p class="text-xs text-gray-400 mt-1">{{ $hasStrategy ? 'Generate goals from your approved strategy' : 'Approve a strategy to auto-generate SMART goals' }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4">
            @foreach($active as $goal)
                @include('livewire.partials.goal-card', ['goal' => $goal])
            @endforeach
        </div>
    @endif

    {{-- Archived goals --}}
    @if($archived->isNotEmpty())
        <div class="opacity-60">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Archived</p>
            <div class="grid grid-cols-1 gap-4">
                @foreach($archived as $goal)
                    @include('livewire.partials.goal-card', ['goal' => $goal])
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Review Dialog ── --}}
    @if($reviewOpen)
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" style="position:fixed;top:0;left:0;right:0;bottom:0;">
            <div class="bg-white rounded-xl shadow-xl w-[640px] max-w-[calc(100%-2rem)] max-h-[840px] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold">Add New Goals</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Select the goals you'd like to add. Unchecked goals will be skipped.</p>
                </div>
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-2">
                    @foreach($reviewItems as $i => $item)
                        <div class="flex items-start gap-3 p-3 rounded-lg border {{ ($reviewChecked[$i] ?? false) ? 'border-[#FC54AA]/30 bg-[#FCE4F1]/20' : 'border-gray-100 bg-white' }} transition-colors">
                            <label class="flex items-start gap-3 flex-1 cursor-pointer min-w-0">
                                <input type="checkbox" wire:model="reviewChecked.{{ $i }}" class="mt-0.5 rounded border-gray-300 text-[#FC54AA] focus:ring-[#FC54AA]">
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-medium text-gray-900 block">{{ $item['title'] }}</span>
                                    @if($item['description'])
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $item['description'] }}</p>
                                    @endif
                                </div>
                            </label>
                            <button wire:click="deleteReviewItem({{ $i }})" title="Remove" class="shrink-0 p-1 text-gray-300 hover:text-red-500 transition-colors mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                    <p class="text-xs text-gray-400">{{ count(array_filter($reviewChecked)) }} of {{ count($reviewItems) }} selected</p>
                    <div class="flex gap-2">
                        <button wire:click="$set('reviewOpen', false)" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">Cancel</button>
                        <button wire:click="applyReview" class="px-4 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors">Add Selected Goals</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Add Goal Dialog ── --}}
    @if($addOpen)
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold">Add Goal</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-700 mb-1 block">Title *</label>
                        <input wire:model="addTitle" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="Goal title">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 mb-1 block">Description</label>
                        <textarea wire:model="addDescription" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none" placeholder="Brief description"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Status</label>
                            <select wire:model="addStatus" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                                <option value="not_started">Not Started</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="at_risk">At Risk</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Metric Type</label>
                            <select wire:model="addMetricType" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                                <option value="number">Number</option>
                                <option value="percentage">Percentage</option>
                                <option value="currency">Currency</option>
                                <option value="rank">Rank</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Target Value</label>
                            <input wire:model="addTargetValue" type="number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="0">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Due Date</label>
                            <input wire:model="addDueDate" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                    <button wire:click="$set('addOpen', false)" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">Cancel</button>
                    <button wire:click="saveNewGoal" wire:loading.attr="disabled" class="px-4 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors">
                        <span wire:loading.remove wire:target="saveNewGoal">Add Goal</span>
                        <span wire:loading wire:target="saveNewGoal">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Manage Goals Dialog ── --}}
    @if($manageOpen)
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-[640px] max-w-[calc(100%-2rem)] max-h-[840px] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold">Manage Goals</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Select goals then choose an action.</p>
                </div>
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-1">
                    @php
                        $activeGoals = $allGoals->where('archived', false);
                        $archivedGoals = $allGoals->where('archived', true);
                    @endphp
                    @if($activeGoals->isNotEmpty())
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Active</p>
                        @foreach($activeGoals as $goal)
                            <div class="flex items-center gap-3 p-3 rounded-lg border {{ !empty($manageSelected[$goal->id]) ? 'border-[#FC54AA]/30 bg-[#FCE4F1]/20' : 'border-gray-100' }} transition-colors">
                                <label class="flex items-center gap-3 flex-1 cursor-pointer min-w-0">
                                    <input type="checkbox" wire:model="manageSelected.{{ $goal->id }}" class="rounded border-gray-300 text-[#FC54AA] focus:ring-[#FC54AA]">
                                    <span class="text-sm font-medium text-gray-900 truncate">{{ $goal->title }}</span>
                                </label>
                                <button wire:click="deleteGoalDirect('{{ $goal->id }}')"
                                    wire:confirm="Delete '{{ addslashes($goal->title) }}'? This cannot be undone."
                                    title="Delete" class="shrink-0 p-1 text-gray-300 hover:text-red-500 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                </button>
                            </div>
                        @endforeach
                    @endif
                    @if($archivedGoals->isNotEmpty())
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mt-4 mb-2">Archived</p>
                        @foreach($archivedGoals as $goal)
                            <div class="flex items-center gap-3 p-3 rounded-lg border {{ !empty($manageSelected[$goal->id]) ? 'border-[#FC54AA]/30 bg-[#FCE4F1]/20' : 'border-gray-100 opacity-60' }} transition-colors">
                                <label class="flex items-center gap-3 flex-1 cursor-pointer min-w-0">
                                    <input type="checkbox" wire:model="manageSelected.{{ $goal->id }}" class="rounded border-gray-300 text-[#FC54AA] focus:ring-[#FC54AA]">
                                    <span class="text-sm font-medium text-gray-600 truncate">{{ $goal->title }}</span>
                                </label>
                                <button wire:click="deleteGoalDirect('{{ $goal->id }}')"
                                    wire:confirm="Delete '{{ addslashes($goal->title) }}'? This cannot be undone."
                                    title="Delete" class="shrink-0 p-1 text-gray-300 hover:text-red-500 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                </button>
                            </div>
                        @endforeach
                    @endif
                    @if($allGoals->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-8">No goals to manage.</p>
                    @endif
                </div>
                @if($confirmAction)
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                        <p class="text-sm text-gray-700 mb-3">
                            @if($confirmAction === 'archive') Are you sure you want to archive the selected goals?
                            @elseif($confirmAction === 'restore') Restore selected goals to active?
                            @elseif($confirmAction === 'delete') Permanently delete selected goals? This cannot be undone.
                            @endif
                        </p>
                        <div class="flex gap-2">
                            <button wire:click="$set('confirmAction', null)" class="px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                            <button wire:click="executeBulk" class="px-3 py-1.5 text-sm {{ $confirmAction === 'delete' ? 'bg-red-600 hover:bg-red-700' : 'bg-[#FC54AA] hover:bg-[#E0429A]' }} text-white rounded-lg transition-colors">
                                Confirm
                            </button>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                        <div class="flex gap-2">
                            <button wire:click="$set('confirmAction', 'restore')" class="px-3 py-1.5 text-sm border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors text-gray-700">Add to List</button>
                            <button wire:click="$set('confirmAction', 'archive')" class="px-3 py-1.5 text-sm border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors text-gray-700">Archive</button>
                            <button wire:click="$set('confirmAction', 'delete')" class="px-3 py-1.5 text-sm border border-red-200 hover:bg-red-50 rounded-lg transition-colors text-red-600">Delete</button>
                        </div>
                        <button wire:click="$set('manageOpen', false)" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">Close</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
