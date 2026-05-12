<x-guest-layout>
    <div>
        <p class="auth-kicker">Password Reset</p>
        <h2 class="auth-title">Create a new password</h2>
        <p class="auth-copy">Set a fresh password for your workspace account, then sign back in with your updated credentials.</p>
    </div>

    <div class="auth-card mt-6">
        <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Account Recovery</p>
        <p class="mt-2 text-sm leading-6 text-stone-300">Choose a strong password that your team account has not used before.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="auth-form">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="auth-label">{{ __('Email') }}</label>
            <input id="email" class="field" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="auth-label">{{ __('Password') }}</label>
            <input id="password" class="field" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="auth-label">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" class="field" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="auth-action-row">
            <a href="{{ route('login') }}" class="link-accent">Back to login</a>
            <button type="submit" class="btn-primary">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
