<div class="w-full">
    @if($editing)
        <div
            x-data="{
                quill: null,
                init() {
                    this.$nextTick(() => {
                        this.quill = new Quill(this.$refs.editor, {
                            theme: 'snow',
                            placeholder: 'Write a message for your client...',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline'],
                                    [{ list: 'ordered' }, { list: 'bullet' }],
                                    ['clean']
                                ]
                            }
                        });
                        const existing = @js($client->strategist_message ?? '');
                        if (existing) {
                            this.quill.clipboard.dangerouslyPasteHTML(existing);
                        }
                    });
                },
                saveContent() {
                    const html = this.$refs.editor.querySelector('.ql-editor').innerHTML;
                    $wire.saveHtml(html);
                }
            }"
            class="mt-1"
        >
            <div x-ref="editor" class="bg-white rounded-lg text-sm text-gray-700"></div>

            <div class="flex gap-2 mt-2">
                <button
                    @click="saveContent()"
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
        <button
            wire:click="startEditing"
            class="text-xs font-medium text-[#FC54AA] hover:text-[#E0429A] transition-colors flex items-center gap-1"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
        </button>

        <div class="mt-2">
            @if($client->strategist_message)
                <div class="text-sm text-gray-600 leading-relaxed prose prose-sm max-w-none">{!! $client->strategist_message !!}</div>
            @else
                <p class="text-sm text-gray-300 italic">No message written yet. Click Edit to add one.</p>
            @endif
        </div>
    @endif
</div>
