<section>
    <header class="profile-panel-head">
        <div>
            <div class="profile-section-title-wrap">
                <svg class="profile-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 3 5 6v5c0 4.7 2.9 8.9 7 10 4.1-1.1 7-5.3 7-10V6l-7-3Z"/>
                    <path d="M9.5 12.5 11 14l3.5-4"/>
                </svg>
                <h2 class="profile-section-title">Password Security</h2>
            </div>
            <h2 class="profile-panel-title">
                {{ __('Update Password') }}
            </h2>
        </div>

        <p class="profile-panel-copy">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="profile-form profile-form-secondary">
        @csrf
        @method('put')

        <div class="profile-form-field">
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="profile-form-label" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="profile-form-input" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="profile-form-error" />
        </div>

        <div class="profile-form-field">
            <x-input-label for="update_password_password" :value="__('New Password')" class="profile-form-label" />
            <x-text-input id="update_password_password" name="password" type="password" class="profile-form-input" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="profile-form-error" />
        </div>

        <div class="profile-form-field">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="profile-form-label" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="profile-form-input" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="profile-form-error" />
        </div>

        <div class="profile-form-actions">
            <x-primary-button class="profile-primary-button">{{ __('Save Password') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="profile-form-success"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
