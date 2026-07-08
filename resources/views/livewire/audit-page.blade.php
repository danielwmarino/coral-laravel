<div class="space-y-8">

    @if(session('toast'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg">
            {{ session('toast') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[#003470]">Audits</h1>
            <p class="text-sm text-gray-500 mt-1">UX &amp; Content audits for your client's digital presence</p>
        </div>
        <button wire:click="$set('showNew', true)"
            class="flex items-center gap-1.5 px-4 py-2 text-sm bg-[#003470] hover:bg-[#002555] text-white rounded-lg transition-colors font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Audit
        </button>
    </div>

    @if(!$this->client)
        <div class="border border-dashed rounded-xl p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <p class="text-sm text-gray-500">No client selected</p>
            <p class="text-xs text-gray-400 mt-1">Select a client from the sidebar to view their audits</p>
        </div>
    @else

        {{-- New Audit Form --}}
        @if($showNew)
            <div class="border border-gray-100 rounded-xl p-6 bg-white shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-gray-900">New Audit</h2>
                    <button wire:click="$set('showNew', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Product / Site Name <span class="text-red-500">*</span></label>
                            <input wire:model="productName" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]"
                                placeholder="e.g. Acme Homepage">
                            @error('productName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">URL</label>
                            <input wire:model="productUrl" type="url"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]"
                                placeholder="https://example.com">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Auditor Name</label>
                            <input wire:model="auditorName" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]"
                                placeholder="Your name">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1 block">Audit Date <span class="text-red-500">*</span></label>
                            <input wire:model="auditDate" type="date"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700 mb-2 block">Audit Mode</label>
                        <div class="flex gap-3">
                            <button type="button" wire:click="$set('auditMode', 'ai_assisted')"
                                class="flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-colors text-left
                                    {{ $auditMode === 'ai_assisted' ? 'border-[#FC54AA] bg-pink-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $auditMode === 'ai_assisted' ? 'text-[#FC54AA]' : 'text-gray-400' }}"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
                                <div>
                                    <p class="text-sm font-medium {{ $auditMode === 'ai_assisted' ? 'text-[#FC54AA]' : 'text-gray-700' }}">AI-Assisted</p>
                                    <p class="text-xs text-gray-400">AI crawls the site and scores all criteria automatically</p>
                                </div>
                            </button>
                            <button type="button" wire:click="$set('auditMode', 'manual')"
                                class="flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-colors text-left
                                    {{ $auditMode === 'manual' ? 'border-[#003470] bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $auditMode === 'manual' ? 'text-[#003470]' : 'text-gray-400' }}"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <div>
                                    <p class="text-sm font-medium {{ $auditMode === 'manual' ? 'text-[#003470]' : 'text-gray-700' }}">Manual Review</p>
                                    <p class="text-xs text-gray-400">Score each criterion yourself item by item</p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700 mb-2 block">Product Type <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['marketing_site' => 'Marketing Site', 'web_app' => 'Web App', 'saas_dashboard' => 'SaaS Dashboard', 'ecommerce' => 'eCommerce', 'mobile_app' => 'Mobile App'] as $value => $label)
                                <button type="button" wire:click="$set('productType', '{{ $value }}')"
                                    class="px-3 py-1.5 text-xs font-medium rounded-full border transition-colors
                                        {{ $productType === $value
                                            ? 'bg-[#003470] border-[#003470] text-white'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        @error('productType') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-5 border-t border-gray-100">
                    <button wire:click="$set('showNew', false)"
                        class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="createAudit" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors disabled:opacity-60">
                        <span wire:loading.remove wire:target="createAudit">Start Audit</span>
                        <span wire:loading wire:target="createAudit">Creating…</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- Audit List --}}
        @if($audits->isEmpty())
            <div class="border border-dashed rounded-xl p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <p class="text-sm text-gray-500">No audits yet</p>
                <p class="text-xs text-gray-400 mt-1">Run your first UX &amp; Content audit to get started</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($audits as $audit)
                    @php
                        $score = $audit->overall_score;
                        if ($score === null) {
                            $scoreColor = 'bg-gray-100 text-gray-500';
                            $scoreLabel = 'Pending';
                        } elseif ($score >= 80) {
                            $scoreColor = 'bg-green-100 text-green-700';
                            $scoreLabel = $score . '%';
                        } elseif ($score >= 60) {
                            $scoreColor = 'bg-amber-100 text-amber-700';
                            $scoreLabel = $score . '%';
                        } else {
                            $scoreColor = 'bg-red-100 text-red-700';
                            $scoreLabel = $score . '%';
                        }
                        $typeLabels = [
                            'marketing_site'  => 'Marketing Site',
                            'web_app'         => 'Web App',
                            'saas_dashboard'  => 'SaaS Dashboard',
                            'ecommerce'       => 'eCommerce',
                            'mobile_app'      => 'Mobile App',
                        ];
                    @endphp
                    <div class="border border-gray-100 rounded-xl p-5 bg-white hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $audit->product_name }}</h3>
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $scoreColor }} font-medium">{{ $scoreLabel }}</span>
                                    @if($audit->status === 'completed')
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Completed</span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">In Progress</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400 flex-wrap">
                                    @if($audit->product_url)
                                        <span class="truncate max-w-[200px]">{{ $audit->product_url }}</span>
                                        <span>·</span>
                                    @endif
                                    <span>{{ $typeLabels[$audit->product_type] ?? $audit->product_type }}</span>
                                    <span>·</span>
                                    <span>{{ $audit->audit_date->format('d M Y') }}</span>
                                    @if($audit->auditor_name)
                                        <span>·</span>
                                        <span>{{ $audit->auditor_name }}</span>
                                    @endif
                                </div>

                                @if($score !== null)
                                    <div class="flex items-center gap-4 mt-3">
                                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                            <span class="font-medium text-gray-700">UX</span>
                                            <span>{{ $audit->ux_score !== null ? $audit->ux_score . '%' : '—' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                            <span class="font-medium text-gray-700">Content</span>
                                            <span>{{ $audit->content_score !== null ? $audit->content_score . '%' : '—' }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-shrink-0">
                                @if($audit->status === 'completed')
                                    <a href="{{ route('audits.report', $audit->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-[#003470] text-white rounded-lg hover:bg-[#002555] transition-colors">
                                        View Report
                                    </a>
                                @else
                                    <a href="{{ route('audits.checklist', $audit->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-[#003470] text-[#003470] rounded-lg hover:bg-[#003470] hover:text-white transition-colors">
                                        Continue Audit
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    @endif
</div>
