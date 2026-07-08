<div class="flex flex-col h-full overflow-hidden"
    x-data="{ revealText: '', revealInterval: null }"
    x-init="
        $watch('$wire.messages', (msgs) => {
            clearInterval(revealInterval);
            revealText = '';
            const last = msgs[msgs.length - 1];
            if (last && last.role === 'assistant') {
                const words = last.content.split(' ');
                let i = 0;
                revealInterval = setInterval(() => {
                    if (i < words.length) {
                        revealText += (i > 0 ? ' ' : '') + words[i++];
                        $nextTick(() => { let el = $refs.chatScroll; if (el) el.scrollTop = el.scrollHeight });
                    } else {
                        clearInterval(revealInterval);
                        revealText = last.content;
                    }
                }, 18);
            } else {
                $nextTick(() => { let el = $refs.chatScroll; if (el) el.scrollTop = el.scrollHeight });
            }
        });
    ">

    {{-- Header --}}
    <div class="shrink-0 h-12 border-b border-gray-100 flex items-center justify-between px-6 relative">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#FC54AA]"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/><line x1="8" y1="16" x2="8" y2="16"/><line x1="16" y1="16" x2="16" y2="16"/></svg>
            <span class="text-sm font-medium text-gray-800">Marketing Strategist</span>
        </div>
        <div class="flex items-center gap-2">
            {{-- New Chat --}}
            <button wire:click="newChat" title="New chat"
                class="flex items-center gap-1.5 px-3 py-1.5 text-xs text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Chat
            </button>

            {{-- History dropdown --}}
            @if($conversations->isNotEmpty())
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs text-gray-600 hover:text-gray-900 border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        History
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="absolute right-0 top-full mt-1 w-72 bg-white border border-gray-100 rounded-xl shadow-lg z-50 overflow-hidden py-1">
                        @foreach($conversations as $conv)
                            <button wire:click="loadConversation('{{ $conv->id }}')" @click="open = false"
                                class="w-full text-left px-4 py-2.5 hover:bg-gray-50 transition-colors flex items-center gap-3 {{ $conversationId === $conv->id ? 'bg-[#FCE4F1]/40' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 shrink-0"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-800 truncate">{{ $conv->title ?: 'Untitled chat' }}</p>
                                    <p class="text-xs text-gray-400">{{ $conv->created_at->diffForHumans() }}</p>
                                </div>
                                @if($conversationId === $conv->id)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-[#FC54AA] shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
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
            @foreach($messages as $i => $msg)
                @if($msg['role'] === 'user')
                    <div class="flex justify-end">
                        <div class="max-w-[75%] bg-[#003470] text-white text-sm px-4 py-3 rounded-2xl rounded-br-sm leading-relaxed">
                            {{ $msg['content'] }}
                        </div>
                    </div>
                @else
                    @php $isLast = $i === count($messages) - 1; @endphp
                    <div class="flex gap-3 items-start">
                        <div class="w-7 h-7 rounded-full bg-[#FCE4F1] flex items-center justify-center shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#FC54AA]"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <div x-data="{ copied: false }" class="group max-w-[75%]">
                            <div class="agent-message bg-white border border-gray-100 text-sm px-4 py-3 rounded-2xl rounded-tl-sm text-gray-800 prose prose-sm max-w-none" style="line-height: 1.85;">
                                @if($isLast)
                                    <span x-html="revealText ? revealText.replace(/\n/g, '<br>') : {{ json_encode(\Illuminate\Support\Str::markdown($msg['content'])) }}"></span>
                                    <span x-show="revealText && revealText.length < {{ strlen($msg['content']) }}" class="inline-block w-0.5 h-4 bg-[#FC54AA] animate-pulse ml-0.5 align-middle"></span>
                                @else
                                    {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                                @endif
                            </div>
                            <div class="flex justify-end mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="navigator.clipboard.writeText({{ json_encode($msg['content']) }}); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 transition-colors px-1 py-0.5 rounded">
                                    <template x-if="!copied">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </template>
                                    <template x-if="copied">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                                    </template>
                                    <span x-text="copied ? 'Copied' : 'Copy'" :class="copied ? 'text-green-500' : ''"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if($thinking)
                <div class="flex gap-3 items-start">
                    <div class="w-7 h-7 rounded-full bg-[#FCE4F1] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="animate-spin text-[#FC54AA]" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </div>
                    <div class="bg-gray-50 text-sm px-4 py-3 rounded-2xl rounded-tl-sm text-gray-400">Thinking…</div>
                </div>
            @endif
        @endif
    </div>

    {{-- Input --}}
    <div class="shrink-0 border-t border-gray-100 px-6 py-4">
        <div class="flex gap-2 items-stretch">
            <textarea
                wire:model="input"
                @keydown.enter.prevent="if (!$event.shiftKey) { $wire.send() }"
                placeholder="Ask about your strategy, goals, campaigns…"
                rows="1"
                class="flex-1 resize-none border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA] leading-relaxed max-h-40 overflow-y-auto"
                style="field-sizing: content;"
            ></textarea>
            <button wire:click="send" wire:loading.attr="disabled" {{ $thinking ? 'disabled' : '' }}
                class="flex items-center justify-center w-10 h-10 bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-xl transition-colors disabled:opacity-60 shrink-0 self-end">
                <span wire:loading.remove wire:target="send">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </span>
                <span wire:loading wire:target="send">
                    <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                </span>
            </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">Press Enter to send, Shift+Enter for new line</p>
    </div>
</div>
