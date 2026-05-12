<x-guest-layout>
    <div class="w-full">
        <a href="/" class="brand-mark">
            <x-application-logo class="h-12 w-12" />
            <span>
                <span class="brand-wordmark">CLI<span class="brand-wordmark-accent">POINT</span></span>
                <span class="brand-caption">Appointment System</span>
            </span>
        </a>

        <div class="mt-8">
            <h2 class="auth-title">Welcome Back!</h2>
            <p class="auth-copy">Please log in your account</p>
        </div>

        <x-auth-session-status class="mt-6 auth-status auth-status-success" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div>
                <label for="email" class="auth-label">Email</label>
                <div class="input-shell mt-2">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3 6.75A2.75 2.75 0 0 1 5.75 4h12.5A2.75 2.75 0 0 1 21 6.75v10.5A2.75 2.75 0 0 1 18.25 20H5.75A2.75 2.75 0 0 1 3 17.25V6.75Zm2.2-.25 6.1 4.76a1.15 1.15 0 0 0 1.4 0l6.1-4.76H5.2Z" />
                    </svg>
                    <input id="email" class="field field-with-icon" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus autocomplete="username" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="auth-label">Password</label>
                <div class="input-shell mt-2">
                    <svg class="input-icon text-teal-700" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M17 8h-1V6a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-6 7.73V17a1 1 0 1 0 2 0v-1.27a2 2 0 1 0-2 0ZM10 8V6a2 2 0 1 1 4 0v2h-4Z" />
                    </svg>
                    <input id="password" class="field field-with-icon" type="password" name="password" placeholder="Password" required autocomplete="current-password" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="auth-action-row">
                <label for="remember_me" class="inline-flex items-center gap-2">
                    <input id="remember_me" type="checkbox" class="h-3.5 w-3.5 rounded-sm border-slate-300 text-violet-500 focus:ring-violet-300" name="remember">
                    <span class="remember-copy">Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="link-accent" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="btn-primary mt-2 w-full py-3.5 text-[1.05rem]">Log In</button>
        </form>
    </div>
</x-guest-layout>
