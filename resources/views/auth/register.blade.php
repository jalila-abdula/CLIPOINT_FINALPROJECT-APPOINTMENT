<x-guest-layout>
    <div>
        <p class="auth-kicker">Admin Only</p>
        <h2 class="auth-title">Register receptionist or staff</h2>
        <p class="auth-copy">Admin can create operational accounts here.</p>
    </div>

    <div class="auth-card mt-6">
        <p class="text-xs uppercase tracking-[0.3em] text-stone-500">User Provisioning</p>
        <p class="mt-2 text-sm leading-6 text-stone-300">Create access for front desk and operational staff.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div>
            <label for="name" class="auth-label">Full name</label>
            <input id="name" class="field" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <label for="role" class="auth-label">Role</label>
            <select id="role" name="role" class="field" required>
                <option value="">Select role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div>
            <label for="email" class="auth-label">Email</label>
            <input id="email" class="field" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="auth-label">Password</label>
            <input id="password" class="field" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="auth-label">Confirm password</label>
            <input id="password_confirmation" class="field" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Create account</button>
            <a href="{{ route('users.index') }}" class="btn-secondary">Back to team</a>
        </div>
    </form>
</x-guest-layout>
