<div class="flex flex-col h-full" x-data="{}" x-init="$watch('$wire.messages', () => { $nextTick(() => { let el = $refs.chatScroll; if (el) el.scrollTop = el.scrollHeight }) })">

    {{-- Header --}}
    <div class="shrink-0 h-12 border-b border-gray-100 flex items-center justify-between px-6">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#FC54AA]"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/><line x1="8" y1="16" x2="8" y2="16"/><line x1="16" y1="16" x2="16" y2="16"/></svg>
            <span class="text-sm font-medium text-gray-800">Marketing Strategist</span>
        </div>
        @if(count($messages) > 0)
            <button wire:click="clearChat" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">Clear chat</button>
        @endif
    </div>

    {{-- Messages --}}
    <div x-ref="chatScroll" class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
        @if(empty($messages))
            <div class="flex flex-col items-center justify-center h-full text-center gap-3 pb-20">
                <div class="w-12 h-12 rounded-full bg-[#FCE4F1] flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#FC54AA]"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-700">Ask your marketing strategist</p>
                <p class="text-xs text-gray-400 max-w-xs">Get AI-powered advice on your strategy, goals, campaigns, and marketing performance.</p>
            </div>
        @else
            @foreach($messages as $msg)
                @if($msg['role'] === 'user')
                    <div class="flex justify-end">
                        <div class="max-w-[75%] bg-[#003470] text-white text-sm px-4 py-3 rounded-2xl rounded-br-sm leading-relaxed">
                            {{ $msg['content'] }}
                        </div>
                    </div>
                @else
                    <div class="flex gap-3 items-start">
                        <div class="w-7 h-7 rounded-full bg-[#FCE4F1] flex items-center justify-center shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#FC54AA]"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <div class="max-w-[75%] bg-gray-50 text-sm px-4 py-3 rounded-2xl rounded-tl-sm text-gray-800 leading-relaxed prose prose-sm max-w-none">
                            {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                        </div>
                    </div>
                @endif
            @endforeach

            @if($thinking)
                <div class="flex gap-3 items-start">
                    <div class="w-7 h-7 rounded-full bg-[#FCE4F1] flex items-center justify-center shrink-0">
                        <svg class="animate-spin text-[#FC54AA]" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </div>
                    <div class="bg-gray-50 text-sm px-4 py-3 rounded-2xl rounded-tl-sm text-gray-400">Thinking…</div>
                </div>
            @endif
        @endif
    </div>

    {{-- Input --}}
    <div class="shrink-0 border-t border-gray-100 px-6 py-4">
        <div class="flex gap-2 items-end">
            <textarea
                wire:model="input"
                @keydown.enter.prevent="if (!$event.shiftKey) { $wire.send() }"
                placeholder="Ask about your strategy, goals, campaigns…"
                rows="1"
                class="flex-1 resize-none border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] leading-relaxed max-h-40 overflow-y-auto"
                style="field-sizing: content;"
            ></textarea>
            <button wire:click="send" wire:loading.attr="disabled" {{ $thinking ? 'disabled' : '' }}
                class="flex items-center justify-center w-10 h-10 bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-xl transition-colors disabled:opacity-50 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">Press Enter to send, Shift+Enter for new line</p>
    </div>
</div>
