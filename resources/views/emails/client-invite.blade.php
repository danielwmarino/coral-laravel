<x-emails.layout>
    <h1>Your {{ $client->name }} dashboard is ready.</h1>
    <p>Your team has set up a Coral marketing intelligence dashboard for you. You can log in to view your strategy, goal progress, and insights.</p>

    <a href="{{ config('app.url') }}/login" class="btn">Access Your Dashboard →</a>

    <div class="detail-box">
        <p><strong>Login email:</strong> {{ $user->email }}</p>
        <p><strong>Temporary password:</strong> {{ $temporaryPassword }}</p>
        <p style="margin-top:10px; font-size:13px; color:#6b7280;">You'll be prompted to change your password after your first login.</p>
    </div>

    <hr class="divider">
    <p style="font-size:13px; color:#9ca3af;">Questions? Reply to this email and we'll get back to you.</p>
</x-emails.layout>
