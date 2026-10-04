<section style="display: flex; flex-direction: column; gap: 16px;">
    <header>
        <h3 style="font-size: 18px; font-weight: 800; color: #dc2626; margin: 0 0 4px;">
            {{ __('Delete Account') }}
        </h3>

        <p class="small muted" style="margin: 0; line-height: 1.5;">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <div>
        <button
            type="button"
            class="btn btn-danger"
            style="background: #dc2626; color: #ffffff; border: 1px solid #dc2626; font-weight: 700; padding: 8px 18px; border-radius: 6px;"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >
            {{ __('Delete Account') }}
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" style="padding: 24px; background: #ffffff;">
            @csrf
            @method('delete')

            <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin: 0 0 8px;">
                {{ __('Are you sure you want to delete your account?') }}
            </h3>

            <p class="small muted" style="margin: 0 0 16px; line-height: 1.5;">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="field" style="margin-bottom: 20px;">
                <label for="password" class="sr-only">{{ __('Password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="input"
                    placeholder="{{ __('Password') }}"
                    required
                />
                @error('password', 'userDeletion')
                    <p class="form-error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="btn btn-danger" style="background: #dc2626; color: #ffffff; border-color: #dc2626; font-weight: 700;">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
