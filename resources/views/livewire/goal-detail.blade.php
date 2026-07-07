<div class="space-y-6">

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

        {{-- ── Progress Overview Chart ── --}}
        @php
            $now       = now();
            $start     = $goal->created_at ?? $now->copy()->subDays(30);
            $end       = $goal->due_date   ?? $now->copy()->addDays(90);
            $current   = (float) ($goal->current_value ?? 0);
            $target    = (float) ($goal->target_value  ?? 0);

            // Build ~10 evenly-spaced x-axis dates from start → end
            $totalDays   = max(1, $start->diffInDays($end));
            $steps       = min(12, max(6, (int) ceil($totalDays / 7)));
            $stepDays    = $totalDays / $steps;
            $labels      = [];
            $actualData  = [];   // known progress up to today, null after
            $projData    = [];   // null up to today, projected after

            for ($i = 0; $i <= $steps; $i++) {
                $d = $start->copy()->addDays(round($i * $stepDays));
                $labels[] = $d->format('M j');

                $fraction = $totalDays > 0 ? min(1, $d->diffInDays($start) / $totalDays) : 0;

                if ($d->lte($now)) {
                    // Actual: flat at 0 until today where we put current_value
                    $actualData[] = $d->isSameDay($now) || $i === $steps ? $current : ($i === 0 ? $current : $current);
                    $projData[]   = null;
                } else {
                    // Projected: linear ramp from current → target
                    $remainFraction = $totalDays > 0 ? ($d->diffInDays($now)) / max(1, $end->diffInDays($now)) : 1;
                    $projData[]   = round($current + ($target - $current) * min(1, $remainFraction), 2);
                    $actualData[] = null;
                }
            }

            // Connect the two lines at today's point
            $todayIdx = collect($labels)->search(fn($l) => $now->format('M j') === $l);
            if ($todayIdx !== false) {
                $projData[$todayIdx] = $current;
            } else {
                // inject today between last past and first future
                $insertAt = count(array_filter($actualData, fn($v) => $v !== null));
                array_splice($labels,     $insertAt, 0, [$now->format('M j')]);
                array_splice($actualData, $insertAt, 0, [$current]);
                array_splice($projData,   $insertAt, 0, [$current]);
            }
        @endphp

        <div class="bg-white border border-gray-100 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-5">Progress Overview</h2>
            <div class="relative" style="height:220px">
                <canvas id="goalProgressChart-{{ $goal->id }}"></canvas>
            </div>
            <div class="flex items-center justify-center gap-6 mt-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-6 h-0.5 bg-[#7C3AED] rounded"></span> Actual progress
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-6 border-t-2 border-dashed border-[#7C3AED]/60"></span> Projected
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-6 h-0.5 bg-emerald-400 rounded"></span> Target
                </span>
            </div>
            <script>
            (function() {
                function init() {
                    var canvas = document.getElementById('goalProgressChart-{{ $goal->id }}');
                    if (!canvas || !window.Chart) { setTimeout(init, 100); return; }
                    if (canvas._chartInstance) canvas._chartInstance.destroy();

                    var labels     = @json($labels);
                    var actual     = @json($actualData);
                    var projected  = @json($projData);
                    var target     = {{ $target }};
                    var targetLine = labels.map(function() { return target; });

                    canvas._chartInstance = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Actual progress',
                                    data: actual,
                                    borderColor: '#7C3AED',
                                    backgroundColor: 'transparent',
                                    borderWidth: 2,
                                    pointRadius: function(ctx) { return ctx.dataIndex === actual.filter(function(v){return v!==null}).length - 1 ? 4 : 0; },
                                    pointBackgroundColor: '#7C3AED',
                                    spanGaps: false,
                                    tension: 0,
                                },
                                {
                                    label: 'Projected',
                                    data: projected,
                                    borderColor: '#7C3AED',
                                    backgroundColor: 'transparent',
                                    borderWidth: 2,
                                    borderDash: [5, 4],
                                    pointRadius: 0,
                                    spanGaps: false,
                                    tension: 0,
                                },
                                {
                                    label: 'Target',
                                    data: targetLine,
                                    borderColor: '#34D399',
                                    backgroundColor: 'transparent',
                                    borderWidth: 1.5,
                                    pointRadius: 0,
                                    tension: 0,
                                },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            if (ctx.parsed.y === null) return null;
                                            return ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: '#f3f4f6', drawBorder: false },
                                    ticks: { color: '#9ca3af', font: { size: 11 } },
                                },
                                y: {
                                    grid: { color: '#f3f4f6', drawBorder: false },
                                    ticks: { color: '#9ca3af', font: { size: 11 } },
                                    min: 0,
                                }
                            }
                        }
                    });
                }
                init();
            })();
            </script>
        </div>

        {{-- ── Linked Analytics ── --}}
        @php $analyticsLinks = $goal->analyticsLinks()->with('analyticsConnection')->get(); @endphp
        <div class="bg-white border border-gray-100 rounded-xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-gray-900">Linked Analytics</h2>
                <a href="{{ route('dataset') }}"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs text-gray-600 border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    Connect Analytics
                </a>
            </div>

            @if($analyticsLinks->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-200"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    <p class="text-sm text-gray-400">No analytics connected to this goal yet</p>
                    <p class="text-xs text-gray-300">Link a data source to track real progress</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($analyticsLinks as $link)
                        @php
                            $platform  = $link->analyticsConnection->platform ?? 'Unknown';
                            $linkPct   = $link->target_value > 0 ? min(100, round(($link->current_value / $link->target_value) * 100, 1)) : 0;
                            $platformIcons = [
                                'google_analytics' => '#EA4335',
                                'google_ads'       => '#FBBC04',
                                'meta_ads'         => '#1877F2',
                                'hubspot'          => '#FF7A59',
                            ];
                            $color = $platformIcons[$platform] ?? '#9CA3AF';
                        @endphp
                        <div class="p-4 rounded-lg border border-gray-100 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $color }}"></span>
                                    <span class="text-xs font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $platform)) }}</span>
                                    <span class="text-xs text-gray-400">·</span>
                                    <span class="text-xs text-gray-500">{{ $link->metric_key }}</span>
                                </div>
                                @if($link->last_updated)
                                    <span class="text-xs text-gray-300">Synced {{ \Carbon\Carbon::parse($link->last_updated)->diffForHumans() }}</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>{{ number_format($link->current_value) }} / {{ $link->target_value ? number_format($link->target_value) : '—' }}</span>
                                <span class="font-medium text-gray-700">{{ $linkPct }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all" style="width:{{ $linkPct }}%;background:{{ $color }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
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
