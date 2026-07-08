<x-emails.layout>
    <h1>Welcome to Coral, {{ $user->name ?? 'there' }}.</h1>
    <p>Your account has been approved and you now have access to your marketing intelligence dashboard.</p>
    <p>Log in to view your strategy, track goals, and explore AI-generated insights for your campaigns.</p>

    <a href="{{ config('app.url') }}/dashboard" class="btn">Go to Dashboard →</a>

    <hr class="divider">
    <p style="font-size:13px; color:#9ca3af;">Questions? Reply to this email and we'll get back to you.</p>
</x-emails.layout>
