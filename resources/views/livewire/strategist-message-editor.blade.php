<div class="w-full">
    @if($editing)
        {{-- Edit mode --}}
        <div class="mt-1">
            <textarea
                wire:model="message"
                rows="5"
                placeholder="Write a message for your client..."
                class="w-full text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#FC54AA]/30 focus:border-[#FC54AA] resize-none"
            ></textarea>
            <div class="flex gap-2 mt-2">
                <button
                    wire:click="save"
                    class="px-3 py-1.5 bg-[#FC54AA] hover:bg-[#E0429A] text-white text-xs font-medium rounded-lg transition-colors"
                >
                    Save
                </button>
                <button
                    wire:click="cancel"
                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-medium rounded-lg transition-colors"
                >
                    Cancel
                </button>
            </div>
        </div>
    @else
        {{-- View mode (agency sees edit button) --}}
        <button
            wire:click="startEditing"
            class="text-xs font-medium text-[#FC54AA] hover:text-[#E0429A] transition-colors flex items-center gap-1"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
        </button>

        <div class="mt-2">
            @if($client->strategist_message)
                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $client->strategist_message }}</p>
            @else
                <p class="text-sm text-gray-300 italic">No message written yet. Click Edit to add one.</p>
            @endif
        </div>
    @endif
</div>
