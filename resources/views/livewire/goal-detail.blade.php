<div class="space-y-6 max-w-4xl">

    @if(session('toast'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg">
            {{ session('toast') }}
        </div>
    @endif

    @if(!$goal)
        <div class="text-sm text-gray-400 text-center py-20">Goal not found.</div>
    @else
        @php
            $statusColors = ['not_started' => 'bg-gray-100 text-gray-600', 'in_progress' => 'bg-blue-50 text-blue-700', 'completed' => 'bg-green-50 text-green-700', 'at_risk' => 'bg-red-50 text-red-600'];
            $progress = $goal->progressPercent();
        @endphp

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('goals.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-[#003470]">{{ $goal->title }}</h1>
                    @if($goal->description)
                        <p class="text-sm text-gray-500 mt-0.5">{{ $goal->description }}</p>
                    @endif
                </div>
            </div>
            @if($isAgency)
                <div class="flex gap-2 shrink-0">
                    <button wire:click="toggleArchive" class="flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors text-gray-600">
                        @if($goal->archived)
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.21"/></svg>
                            Restore
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                            Archive
                        @endif
                    </button>
                    <button wire:click="$set('editOpen', true)" class="flex items-center gap-1.5 px-3 py-1.5 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </button>
                    <button wire:click="$set('deleteOpen', true)" class="flex items-center gap-1.5 px-3 py-1.5 text-sm border border-red-200 hover:bg-red-50 text-red-600 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                        Delete
                    </button>
                </div>
            @endif
        </div>

        {{-- Status + Progress --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs {{ $statusColors[$goal->status] ?? 'bg-gray-100 text-gray-600' }} capitalize">{{ str_replace('_', ' ', $goal->status) }}</span>
                @if($goal->due_date)
                    <span class="text-xs text-gray-400">Due {{ $goal->due_date->format('M j, Y') }}</span>
                @endif
            </div>
            <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                <span>{{ number_format($goal->current_value) }} / {{ $goal->target_value ? number_format($goal->target_value) : '—' }}</span>
                <span class="font-medium">{{ $progress }}%</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-[#FC54AA] rounded-full transition-all" style="width: {{ $progress }}%"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- SMART Details --}}
            @if($goal->smart_details)
                <div class="bg-white border border-gray-100 rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-gray-900 mb-4">SMART Details</h2>
                    @php $labels = ['specific' => 'Specific', 'measurable' => 'Measurable', 'achievable' => 'Achievable', 'relevant' => 'Relevant', 'time_bound' => 'Time-bound']; @endphp
                    <div class="space-y-3">
                        @foreach($labels as $key => $label)
                            @if($goal->smart_details[$key] ?? null)
                                <div>
                                    <p class="text-xs font-medium text-[#FC54AA] mb-0.5">{{ $label }}</p>
                                    <p class="text-sm text-gray-700">{{ $goal->smart_details[$key] }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Tasks --}}
            <div class="bg-white border border-gray-100 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Tasks</h2>
                @if($goal->tasks->isEmpty())
                    <p class="text-sm text-gray-400">No tasks yet</p>
                @else
                    <div class="space-y-2">
                        @foreach($goal->tasks as $task)
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox" {{ $task->completed ? 'checked' : '' }}
                                    wire:click="toggleTask('{{ $task->id }}')"
                                    class="mt-0.5 rounded border-gray-300 text-[#FC54AA] focus:ring-[#FC54AA]">
                                <span class="text-sm {{ $task->completed ? 'line-through text-gray-400' : 'text-gray-700' }} leading-snug">{{ $task->title }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Strategist Notes --}}
        @if($goal->strategist_notes || $isAgency)
            <div class="bg-white border border-gray-100 rounded-xl p-6">
                <h2 class="text-sm font-semibold text-[#FC54AA] mb-2">Strategist Notes</h2>
                @if($goal->strategist_notes)
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $goal->strategist_notes }}</p>
                @else
                    <p class="text-sm text-gray-400">No notes yet. Edit the goal to add strategist notes.</p>
                @endif
            </div>
        @endif

        {{-- Edit Modal --}}
        @if($editOpen)
            <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold">Edit Goal</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Title *</label>
                            <input wire:model="editTitle" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Description</label>
                            <textarea wire:model="editDescription" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-medium text-gray-700 mb-1 block">Status</label>
                                <select wire:model="editStatus" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                                    <option value="not_started">Not Started</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="at_risk">At Risk</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-700 mb-1 block">Metric Type</label>
                                <select wire:model="editMetricType" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                                    <option value="number">Number</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="currency">Currency</option>
                                    <option value="rank">Rank</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-medium text-gray-700 mb-1 block">Current Value</label>
                                <input wire:model="editCurrentValue" type="number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-700 mb-1 block">Target Value</label>
                                <input wire:model="editTargetValue" type="number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Due Date</label>
                            <input wire:model="editDueDate" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Strategist Notes</label>
                            <textarea wire:model="editStrategistNotes" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                        <button wire:click="$set('editOpen', false)" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">Cancel</button>
                        <button wire:click="saveEdit" class="px-4 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors">Save</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Delete Modal --}}
        @if($deleteOpen)
            <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
                    <h3 class="text-base font-semibold mb-2">Delete goal?</h3>
                    <p class="text-sm text-gray-500 mb-5">This will permanently delete this goal and all its tasks. This cannot be undone.</p>
                    <div class="flex justify-end gap-2">
                        <button wire:click="$set('deleteOpen', false)" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg">Cancel</button>
                        <button wire:click="deleteGoal" wire:loading.attr="disabled" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            <span wire:loading.remove wire:target="deleteGoal">Delete Goal</span>
                            <span wire:loading wire:target="deleteGoal">Deleting…</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
