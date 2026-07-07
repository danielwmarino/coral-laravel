<x-app-layout>
    <div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
        <div class="w-16 h-16 rounded-full bg-[#FCE4F1] flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FC54AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-[#003470] mb-2">Account Pending</h1>
        <p class="text-gray-500 max-w-sm">
            Your account has been created. A member of our team will assign your access and notify you shortly.
        </p>
        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 underline">Log out</button>
        </form>
    </div>
</x-app-layout>
