<section>
    <header class="profile-panel-head">
        <div>
            <div class="profile-section-title-wrap">
                <svg class="profile-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 12a3.25 3.25 0 1 0-3.25-3.25A3.25 3.25 0 0 0 12 12Z"/>
                    <path d="M6.5 19.25a5.5 5.5 0 0 1 11 0"/>
                </svg>
                <h2 class="profile-section-title">Account Information</h2>
            </div>
            <h2 class="profile-panel-title">
                {{ __('Personal Details') }}
            </h2>
        </div>

        <p class="profile-panel-copy">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="profile-form">
        @csrf
        @method('patch')

        <div class="profile-form-field">
            <x-input-label for="name" :value="__('Full Name')" class="profile-form-label" />
            <x-text-input id="name" name="name" type="text" class="profile-form-input" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="profile-form-error" :messages="$errors->get('name')" />
        </div>

        <div class="profile-form-field">
            <x-input-label for="email" :value="__('Email Address')" class="profile-form-label" />
            <x-text-input id="email" name="email" type="email" class="profile-form-input" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="profile-form-error" :messages="$errors->get('email')" />
            <p class="profile-field-note">Changing your email may require verification.</p>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="profile-inline-note">
                    <p class="profile-inline-copy">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="profile-inline-link">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="profile-form-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="profile-form-actions">
            <x-primary-button class="profile-primary-button">{{ __('Save Changes') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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
