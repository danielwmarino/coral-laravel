<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-semibold text-[#003470]">Data Set</h1>
        <p class="text-sm text-gray-500 mt-1">Connected analytics platforms and data sources</p>
    </div>

    @php
        $platformLabels = [
            'google_analytics' => 'Google Analytics',
            'google_search_console' => 'Google Search Console',
            'facebook_ads' => 'Facebook Ads',
            'linkedin_ads' => 'LinkedIn Ads',
            'semrush' => 'SEMrush',
        ];
    @endphp

    @if($connections->isEmpty())
        <div class="border border-dashed rounded-xl p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            <p class="text-sm text-gray-500">No data connections yet</p>
            <p class="text-xs text-gray-400 mt-1">Analytics platform integrations coming soon</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($connections as $conn)
                <div class="bg-white border border-gray-100 rounded-xl p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $platformLabels[$conn->platform] ?? $conn->platform }}</p>
                            <p class="text-xs text-gray-500">Connected</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
