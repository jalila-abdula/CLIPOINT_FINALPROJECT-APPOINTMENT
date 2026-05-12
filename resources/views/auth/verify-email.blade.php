<x-guest-layout>
    <div>
        <p class="auth-kicker">Verification</p>
        <h2 class="auth-title">Verify your email address</h2>
        <p class="auth-copy">{{ __('Before getting started, please verify your email address by clicking the link we just emailed to you. If you didn\'t receive it, we can send another one.') }}</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-6 auth-status auth-status-success">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="auth-card mt-6">
        <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Inbox Check</p>
        <p class="mt-2 text-sm leading-6 text-stone-300">Open the message from the system, confirm the address, then return to continue using your account.</p>
    </div>

    <div class="auth-action-row mt-8">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <button type="submit" class="btn-primary">
                    {{ __('Resend Verification Email') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="link-accent">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
