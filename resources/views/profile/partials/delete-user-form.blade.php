<section class="profile-danger-wrap">
    <header class="profile-panel-head">
        <div>
            <div class="profile-section-title-wrap">
                <svg class="profile-section-icon profile-section-icon-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 9v4"/>
                    <path d="M12 17h.01"/>
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.72 3h16.92a2 2 0 0 0 1.72-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                </svg>
                <h2 class="profile-section-title">Danger Zone</h2>
            </div>
            <h2 class="profile-panel-title">
                {{ __('Delete Account') }}
            </h2>
        </div>

        <p class="profile-panel-copy">
            {{ __('Permanently remove this account and all related access.') }}
        </p>
    </header>

    <p class="profile-danger-copy">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
    </p>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="profile-danger-button"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="profile-delete-modal">
            @csrf
            @method('delete')

            <h2 class="profile-delete-modal-title">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="profile-delete-modal-copy">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="profile-form-field">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="profile-form-input"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="profile-form-error" />
            </div>

            <div class="profile-delete-modal-actions">
                <x-secondary-button x-on:click="$dispatch('close')" class="profile-secondary-button">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="profile-danger-button">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
