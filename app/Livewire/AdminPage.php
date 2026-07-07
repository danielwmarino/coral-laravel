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
    public string $clientMessage = '';
    public string $clientError = '';

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

        $base = strtolower(preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', strtolower($this->newClientName))));
        $slug = $this->newClientSlug ?: $base;
        $i = 1;
        while (Client::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $this->clientMessage = '';
        $this->clientError = '';
        try {
            Client::create([
                'name' => trim($this->newClientName),
                'slug' => $slug,
            ]);
            $this->newClientName = '';
            $this->newClientSlug = '';
            $this->clientMessage = 'Client added successfully!';
        } catch (\Exception $e) {
            $this->clientError = 'Error: ' . $e->getMessage();
        }
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
