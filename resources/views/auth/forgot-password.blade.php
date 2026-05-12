<x-guest-layout>
    <div>
        <p class="auth-kicker">Recovery</p>
        <h2 class="auth-title">Reset your account password</h2>
        <p class="auth-copy">
            {{ __('Forgot your password? No problem. Enter your email address and we will send a password reset link so you can choose a new one.') }}
        </p>
    </div>

    <div class="auth-card mt-6">
        <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Secure Assistance</p>
        <p class="mt-2 text-sm leading-6 text-stone-300">The reset link goes to the registered email address for the account you want to recover.</p>
    </div>

    <x-auth-session-status class="mt-6 auth-status auth-status-success" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <div>
            <label for="email" class="auth-label">{{ __('Email') }}</label>
            <input id="email" class="field" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="auth-action-row">
            <a href="{{ route('login') }}" class="link-accent">Back to login</a>
            <button type="submit" class="btn-primary">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout>
