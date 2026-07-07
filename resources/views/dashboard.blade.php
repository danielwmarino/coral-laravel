<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#003470]">Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Welcome back, {{ auth()->user()->profile?->full_name ?? auth()->user()->name }}</p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['label' => 'Total Goals', 'value' => '0', 'color' => 'text-[#003470]'],
            ['label' => 'In Progress', 'value' => '0', 'color' => 'text-blue-600'],
            ['label' => 'Completed', 'value' => '0', 'color' => 'text-green-600'],
            ['label' => 'At Risk', 'value' => '0', 'color' => 'text-red-500'],
        ] as $stat)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $stat['label'] }}</p>
                <p class="text-3xl font-bold mt-2 {{ $stat['color'] }}">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Placeholder cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-[#003470] mb-3">Executive Summary</h2>
            <p class="text-sm text-gray-400 italic">No summary generated yet.</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-base font-semibold text-[#003470] mb-3">Message from your Strategist</h2>
            <p class="text-sm text-gray-400 italic">No message yet.</p>
        </div>
    </div>
</x-app-layout>
