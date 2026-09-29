<x-guest-layout>
    <h1 class="text-xl font-semibold text-gray-900">Verify your sign-in</h1>
    <p class="mt-2 text-sm text-gray-600">Enter the six-digit code from your authenticator app, or use a recovery code.</p>
    <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-6">
        @csrf
        <x-input-label for="code" value="Authenticator code" />
        <x-text-input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="block mt-1 w-full" autofocus />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
        <x-primary-button class="mt-4">Verify code</x-primary-button>
    </form>
    <details class="mt-6 text-sm text-gray-700">
        <summary class="cursor-pointer">Use a recovery code instead</summary>
        <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-4">
            @csrf
            <x-input-label for="recovery_code" value="Recovery code" />
            <x-text-input id="recovery_code" name="recovery_code" autocomplete="off" class="block mt-1 w-full" required />
            <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
            <x-primary-button class="mt-4">Use recovery code</x-primary-button>
        </form>
    </details>
    <a href="{{ route('login') }}" class="block mt-6 text-sm underline">Back to login</a>
</x-guest-layout>
