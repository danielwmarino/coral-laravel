<div class="w-full">
    @if($editing)
        <div
            x-data="{
                exec(cmd, val = null) {
                    this.$refs.editor.focus();
                    document.execCommand(cmd, false, val);
                },
                saveContent() {
                    $wire.saveHtml(this.$refs.editor.innerHTML);
                }
            }"
            class="mt-1"
        >
            {{-- Toolbar --}}
            <div class="flex items-center gap-1 px-2 py-1.5 border border-gray-200 border-b-0 rounded-t-lg bg-gray-50">
                <button type="button" @click="exec('bold')"
                    class="w-7 h-7 flex items-center justify-center rounded text-gray-600 hover:bg-gray-200 font-bold text-sm transition-colors">B</button>
                <button type="button" @click="exec('italic')"
                    class="w-7 h-7 flex items-center justify-center rounded text-gray-600 hover:bg-gray-200 italic text-sm transition-colors">I</button>
                <button type="button" @click="exec('underline')"
                    class="w-7 h-7 flex items-center justify-center rounded text-gray-600 hover:bg-gray-200 underline text-sm transition-colors">U</button>
                <div class="w-px h-4 bg-gray-300 mx-1"></div>
                <button type="button" @click="exec('insertUnorderedList')"
                    class="w-7 h-7 flex items-center justify-center rounded text-gray-600 hover:bg-gray-200 text-sm transition-colors" title="Bullet list">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1" fill="currentColor"/><circle cx="4" cy="12" r="1" fill="currentColor"/><circle cx="4" cy="18" r="1" fill="currentColor"/></svg>
                </button>
                <button type="button" @click="exec('insertOrderedList')"
                    class="w-7 h-7 flex items-center justify-center rounded text-gray-600 hover:bg-gray-200 text-sm transition-colors" title="Numbered list">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
                </button>
                <div class="w-px h-4 bg-gray-300 mx-1"></div>
                <button type="button" @click="exec('removeFormat')"
                    class="w-7 h-7 flex items-center justify-center rounded text-gray-600 hover:bg-gray-200 text-xs transition-colors" title="Clear formatting">✕</button>
            </div>

            {{-- Editable area --}}
            <div
                x-ref="editor"
                contenteditable="true"
                class="w-full min-h-[120px] text-sm text-gray-700 border border-gray-200 rounded-b-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FC54AA]/30 focus:border-[#FC54AA] leading-relaxed prose prose-sm max-w-none"
                x-init="$el.innerHTML = @js($client->strategist_message ?? '')"
            ></div>

            <div class="flex gap-2 mt-2">
                <button type="button" @click="saveContent()"
                    class="px-3 py-1.5 bg-[#FC54AA] hover:bg-[#E0429A] text-white text-xs font-medium rounded-lg transition-colors">
                    Save
                </button>
                <button type="button" wire:click="cancel"
                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-medium rounded-lg transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    @else
        @if($client->strategist_message)
            <div class="text-sm text-gray-600 leading-relaxed prose prose-sm max-w-none">{!! $client->strategist_message !!}</div>
        @else
            <p class="text-sm text-gray-300 italic">No message written yet. Click Edit to add one.</p>
        @endif
    @endif
</div>
