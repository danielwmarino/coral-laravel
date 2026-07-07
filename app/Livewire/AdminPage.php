<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\UserProfile;
use Livewire\Component;

class AdminPage extends Component
{
    // Add Client
    public string $newClientName = '';
    public string $newClientSlug = '';
    public bool $addingClient = false;

    // Invite (display-only for now — no email infra)
    public string $inviteEmail = '';
    public string $inviteName = '';
    public string $inviteRole = 'client_user';
    public string $inviteClientId = '';

    public function updatedNewClientName(string $value): void
    {
        $this->newClientSlug = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', strtolower($value))));
    }

    public function addClient(): void
    {
        $this->validate([
            'newClientName' => 'required|string|max:255',
        ]);

        if (!$this->newClientSlug) {
            $this->newClientSlug = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', strtolower($this->newClientName))));
        }

        Client::create([
            'name' => trim($this->newClientName),
            'slug' => trim($this->newClientSlug),
        ]);

        $this->newClientName = '';
        $this->newClientSlug = '';
        session()->flash('toast', 'Client added');
    }

    public function updateUserRole(string $userId, string $role, ?string $clientId): void
    {
        UserProfile::where('user_id', $userId)->update([
            'role' => $role,
            'client_id' => $clientId === 'none' || !$clientId ? null : $clientId,
        ]);
        session()->flash('toast', 'User updated');
    }

    public function render(): \Illuminate\View\View
    {
        $profiles = UserProfile::orderBy('created_at', 'desc')->get();
        $clients = Client::orderBy('name')->get();
        return view('livewire.admin', compact('profiles', 'clients'));
    }
}
