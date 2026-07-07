<div class="space-y-8">

    @if(session('toast'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg">
            {{ session('toast') }}
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-semibold text-[#003470]">Admin</h1>
        <p class="text-sm text-gray-500 mt-1">Manage clients and user assignments</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Add Client --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Add Client</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-medium text-gray-700 mb-1 block">Client name</label>
                    <input wire:model.live="newClientName" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="Acme Corp">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 mb-1 block">Slug (auto-generated)</label>
                    <input wire:model="newClientSlug" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="acme-corp">
                </div>
                <button wire:click="addClient" wire:loading.attr="disabled" class="w-full flex items-center justify-center gap-1.5 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Client
                </button>

                @if($clients->isNotEmpty())
                    <div class="pt-3 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-500 mb-2">Existing clients ({{ $clients->count() }})</p>
                        <div class="space-y-1">
                            @foreach($clients as $c)
                                <div class="text-xs text-gray-600 py-1 border-b border-gray-50">{{ $c->name }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Invite User (UI only) --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Invite User
            </h2>
            <div class="space-y-3">
                <input wire:model="inviteEmail" type="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="Email address">
                <input wire:model="inviteName" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]" placeholder="Full name (optional)">
                <select wire:model="inviteRole" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                    <option value="client_user">Client User</option>
                    <option value="agency_staff">Agency Staff</option>
                    <option value="super_admin">Super Admin</option>
                </select>
                <select wire:model="inviteClientId" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#FC54AA]">
                    <option value="">No client</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400">
                    {{ $inviteRole === 'client_user' ? 'Client users must be assigned to a client.' : 'Agency staff and super admins can see every client.' }}
                </p>
                <button class="w-full flex items-center justify-center gap-1.5 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors opacity-50 cursor-not-allowed" disabled title="Email invites coming soon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    Send Invite (coming soon)
                </button>
            </div>
        </div>
    </div>

    {{-- Users --}}
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Users ({{ $profiles->count() }})
        </h2>
        <div class="space-y-3">
            @foreach($profiles as $p)
                @php $roleColors = ['super_admin' => 'bg-red-50 text-red-700', 'agency_staff' => 'bg-blue-50 text-blue-700', 'client_user' => 'bg-gray-100 text-gray-600']; @endphp
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg" x-data="{ role: '{{ $p->role }}', client: '{{ $p->client_id ?? 'none' }}' }">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $p->full_name ?? 'Unnamed' }}</p>
                        <p class="text-xs text-gray-500">{{ substr($p->user_id, 0, 8) }}…</p>
                    </div>
                    <select
                        x-model="role"
                        @change="$wire.updateUserRole('{{ $p->user_id }}', role, client)"
                        class="w-36 border border-gray-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-[#FC54AA]"
                    >
                        <option value="super_admin">Super Admin</option>
                        <option value="agency_staff">Agency Staff</option>
                        <option value="client_user">Client User</option>
                    </select>
                    <select
                        x-model="client"
                        @change="$wire.updateUserRole('{{ $p->user_id }}', role, client)"
                        class="w-40 border border-gray-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-[#FC54AA]"
                    >
                        <option value="none">No client</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-xs px-2 py-0.5 rounded {{ $roleColors[$p->role] ?? 'bg-gray-100 text-gray-600' }} shrink-0 capitalize">{{ str_replace('_', ' ', $p->role) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
