<div class="max-w-2xl mx-auto">

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Progress bar --}}
    <div class="mb-6">
        <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
            <span>Step {{ $step + 1 }} of {{ $totalSteps }}</span>
            <span>{{ $currentStep['title'] }}</span>
        </div>
        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-1.5 bg-[#FC54AA] rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    {{-- Step card --}}
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm">
        <div class="px-6 pt-6 pb-2">
            <h2 class="text-base font-semibold text-gray-900">{{ $currentStep['title'] }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $currentStep['description'] }}</p>
        </div>
        <div class="px-6 pb-6 pt-4 space-y-4">

            {{-- Step 0: Business --}}
            @if($step === 0)
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Business / Brand name</label>
                    <input wire:model="businessName" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="Acme Corp">
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">What does the business do?</label>
                    <textarea wire:model="businessDescription" rows="4" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none" placeholder="Describe the product or service, value proposition, and current market position…"></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Industry / Sector</label>
                    <input wire:model="industry" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="e.g. B2B SaaS, ecommerce, professional services">
                </div>
            @endif

            {{-- Step 1: Audience --}}
            @if($step === 1)
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Primary target audience</label>
                    <textarea wire:model="targetAudience" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none" placeholder="Who is the ideal customer? Job titles, pain points, buying behaviours…"></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Age range</label>
                    <input wire:model="audienceAge" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="e.g. 25–45">
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Primary market / location</label>
                    <input wire:model="audienceLocation" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="e.g. United States, Australia, Global">
                </div>
            @endif

            {{-- Step 2: Channels --}}
            @if($step === 2)
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Current marketing channels</label>
                    <textarea wire:model="currentChannels" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none" placeholder="e.g. Google Ads, Instagram, email newsletter, SEO blog…"></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Current performance / what's working?</label>
                    <textarea wire:model="channelPerformance" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none" placeholder="Any context on current results, CAC, ROAS, traffic, conversion rates…"></textarea>
                </div>
            @endif

            {{-- Step 3: Competitors --}}
            @if($step === 3)
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Main competitors (name or URL)</label>
                    <textarea wire:model="competitors" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none" placeholder="List 2–5 direct competitors"></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Competitor strengths / what are they doing well?</label>
                    <textarea wire:model="competitorStrengths" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none" placeholder="e.g. strong SEO presence, aggressive paid social, influencer partnerships…"></textarea>
                </div>
            @endif

            {{-- Step 4: Objectives --}}
            @if($step === 4)
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Primary marketing objective</label>
                    <textarea wire:model="primaryObjective" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none" placeholder="e.g. Increase organic traffic by 50% in 6 months, reduce CAC by 20%…"></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Secondary objectives (optional)</label>
                    <textarea wire:model="secondaryObjectives" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] resize-none" placeholder="Additional goals, KPIs, or outcomes…"></textarea>
                </div>
            @endif

            {{-- Step 5: Budget --}}
            @if($step === 5)
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Monthly marketing budget</label>
                    <input wire:model="monthlyBudget" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="e.g. $5,000 / month">
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Strategy timeline</label>
                    <input wire:model="timeline" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="e.g. 6 months, Q3–Q4 2025">
                </div>
            @endif

            {{-- Step 6: Review & Generate --}}
            @if($step === 6)
                <div class="grid grid-cols-2 gap-3 text-sm">
                    @foreach([['Business', $businessName], ['Industry', $industry], ['Primary objective', $primaryObjective], ['Budget', $monthlyBudget], ['Timeline', $timeline]] as [$k, $v])
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-400 mb-0.5">{{ $k }}</p>
                            <p class="font-medium text-gray-800 text-sm">{{ $v ?: '—' }}</p>
                        </div>
                    @endforeach
                </div>

                @if(!$generatedDoc)
                    <button wire:click="generateStrategy" wire:loading.attr="disabled" class="w-full flex items-center justify-center gap-2 py-2.5 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors disabled:opacity-60">
                        <span wire:loading.remove wire:target="generateStrategy" class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            Generate strategy with AI
                        </span>
                        <span wire:loading wire:target="generateStrategy" class="flex items-center gap-2">
                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Generating strategy…
                        </span>
                    </button>
                @else
                    <div class="border border-gray-100 rounded-lg p-4 bg-gray-50 max-h-80 overflow-y-auto">
                        <pre class="text-xs text-gray-700 whitespace-pre-wrap font-sans leading-relaxed">{{ $generatedDoc }}</pre>
                    </div>
                    <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600 shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <p class="text-sm text-green-700">Strategy generated. Submit for review to proceed.</p>
                    </div>
                    <button wire:click="submitForReview" wire:loading.attr="disabled" class="w-full flex items-center justify-center gap-2 py-2.5 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors disabled:opacity-60">
                        <span wire:loading.remove wire:target="submitForReview">Submit for review</span>
                        <span wire:loading wire:target="submitForReview" class="flex items-center gap-2">
                            <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            Submitting…
                        </span>
                    </button>
                @endif
            @endif

        </div>
    </div>

    {{-- Navigation --}}
    @if($step < 6)
        <div class="flex justify-between mt-4">
            <button wire:click="back" {{ $step === 0 ? 'disabled' : '' }} class="flex items-center gap-1 px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-40">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Back
            </button>
            <button wire:click="next" class="flex items-center gap-1 px-4 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors">
                {{ $step === 5 ? 'Review' : 'Next' }}
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    @endif
</div>
