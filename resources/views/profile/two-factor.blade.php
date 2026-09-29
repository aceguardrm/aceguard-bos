<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Account security</h2></x-slot>
    <div class="py-12"><section class="max-w-3xl mx-auto p-6 bg-white shadow sm:rounded-lg space-y-6">
        <h1 class="text-xl font-semibold">Two-factor authentication</h1>
        <p class="text-gray-600">Protect your BOS login with an authenticator app and keep recovery codes somewhere safe.</p>
        @if ($errors->any())
            <ul class="text-red-600">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        @endif
        @if (! $user->two_factor_secret)
            <p>Two-factor authentication is off.</p>
            <form method="POST" action="{{ route('two-factor.enable') }}">@csrf<x-primary-button>Set up authenticator</x-primary-button></form>
        @elseif (! $user->two_factor_confirmed_at)
            <p>Scan this QR code with Microsoft Authenticator, Google Authenticator or another authenticator app. Then enter its code below to finish setup.</p>
            <div class="p-4 inline-block bg-white">{!! $user->twoFactorQrCodeSvg() !!}</div>
            <details><summary>Enter a setup key manually</summary><p class="font-mono break-all mt-2">{{ decrypt($user->two_factor_secret) }}</p></details>
            <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-3">
                @csrf
                <x-input-label for="code" value="Authenticator code" />
                <x-text-input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required />
                <x-primary-button>Confirm and enable</x-primary-button>
            </form>
            <form method="POST" action="{{ route('two-factor.disable') }}">@csrf @method('DELETE')<x-secondary-button type="submit">Cancel setup</x-secondary-button></form>
        @else
            <p class="text-green-700 font-semibold">Two-factor authentication is enabled.</p>
            <h2 class="font-semibold">Recovery codes</h2>
            <p>Save these in your password manager. Each code works once if you cannot access your authenticator. Keep them private.</p>
            <ul class="font-mono space-y-2">@foreach ($user->recoveryCodes() as $code)<li>{{ $code }}</li>@endforeach</ul>
            <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">@csrf<x-secondary-button type="submit">Replace recovery codes</x-secondary-button></form>
            <p class="text-sm text-gray-600">Replacing codes invalidates every previous recovery code.</p>
            <details><summary class="cursor-pointer">Turn off two-factor authentication</summary>
                <p class="mt-3">Your account will rely on your password alone.</p>
                <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-3">@csrf @method('DELETE')<x-danger-button>Disable two-factor authentication</x-danger-button></form>
            </details>
        @endif
        <a href="{{ route('profile.edit') }}" class="block underline text-sm">Back to profile</a>
    </section></div>
</x-app-layout>
