<?php

namespace App\Livewire;

use App\Mail\ClientInviteMail;
use App\Mail\WelcomeMail;
use App\Models\Client;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class AdminPage extends Component
{
    // Add Client
    public string $newClientName = '';
    public string $newClientSlug = '';
    public string $clientMessage = '';
    public string $clientError = '';

    // Invite
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
        abort_unless(auth()->user()->isSuperAdmin(), 403);

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
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $profile = UserProfile::where('user_id', $userId)->first();
        $wasUnassigned = !$profile || !$profile->role;

        UserProfile::where('user_id', $userId)->update([
            'role' => $role,
            'client_id' => $clientId === 'none' || !$clientId ? null : $clientId,
        ]);

        // Send welcome email the first time a role is assigned
        if ($wasUnassigned && $role) {
            $user = User::find($userId);
            if ($user) {
                Mail::to($user->email)->send(new WelcomeMail($user));
            }
        }

        session()->flash('toast', 'User updated');
    }

    public function sendInvite(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $this->validate([
            'inviteEmail'    => 'required|email|unique:users,email',
            'inviteName'     => 'required|string|max:255',
            'inviteRole'     => 'required|in:client_user,agency_staff,super_admin',
            'inviteClientId' => 'nullable|exists:clients,id',
        ]);

        $tempPassword = Str::password(12, symbols: false);

        $user = User::create([
            'name'     => $this->inviteName,
            'email'    => $this->inviteEmail,
            'password' => Hash::make($tempPassword),
        ]);

        UserProfile::create([
            'user_id'   => $user->id,
            'role'      => $this->inviteRole,
            'client_id' => $this->inviteClientId ?: null,
        ]);

        if ($this->inviteRole === 'client_user' && $this->inviteClientId) {
            $client = Client::find($this->inviteClientId);
            Mail::to($user->email)->send(new ClientInviteMail($user, $client, $tempPassword));
        } else {
            Mail::to($user->email)->send(new WelcomeMail($user));
        }

        $sentTo = $this->inviteName;
        $this->inviteEmail = '';
        $this->inviteName = '';
        $this->inviteRole = 'client_user';
        $this->inviteClientId = '';

        session()->flash('toast', 'Invite sent to ' . $sentTo);
    }

    public function render(): \Illuminate\View\View
    {
        $profiles = UserProfile::orderBy('created_at', 'desc')->get();
        $clients = Client::orderBy('name')->get();
        return view('livewire.admin', compact('profiles', 'clients'));
    }
}
