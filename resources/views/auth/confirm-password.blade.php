<x-guest-layout>
    <div>
        <p class="auth-kicker">Confirmation</p>
        <h2 class="auth-title">Confirm your password</h2>
        <p class="auth-copy">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>
    </div>

    <div class="auth-card mt-6">
        <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Protected Action</p>
        <p class="mt-2 text-sm leading-6 text-stone-300">This extra check helps protect sensitive changes inside the appointment system.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
        @csrf

        <div>
            <label for="password" class="auth-label">{{ __('Password') }}</label>
            <input id="password" class="field" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
</x-guest-layout>
