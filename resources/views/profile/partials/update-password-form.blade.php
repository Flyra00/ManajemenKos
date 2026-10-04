<section>
    <header style="margin-bottom: 20px;">
        <h3 style="font-size: 18px; font-weight: 800; color: var(--color-neutral-900); margin: 0 0 4px;">
            Perbarui Kata Sandi
        </h3>
        <p class="small muted" style="margin: 0; line-height: 1.5;">
            Pastikan akun Anda terlindungi dengan kata sandi yang aman dan tidak mudah ditebak.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" style="display: flex; flex-direction: column; gap: 16px;">
        @csrf
        @method('put')

        <!-- Kata Sandi Saat Ini -->
        <div class="field">
            <label for="update_password_current_password" style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: var(--color-neutral-800);">
                Kata Sandi Saat Ini <span class="text-accent">*</span>
            </label>
            <div class="pw-wrap">
                <input class="input @error('current_password', 'updatePassword') is-invalid @enderror" id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" required>
                <button class="pw-toggle" type="button" data-toggle-pw="update_password_current_password" aria-label="Tampilkan password" aria-pressed="false">
                    <svg class="ic-on" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                    <svg class="ic-off" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            @error('current_password', 'updatePassword')
                <p class="form-error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kata Sandi Baru -->
        <div class="field">
            <label for="update_password_password" style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: var(--color-neutral-800);">
                Kata Sandi Baru <span class="text-accent">*</span>
            </label>
            <div class="pw-wrap">
                <input class="input @error('password', 'updatePassword') is-invalid @enderror" id="update_password_password" name="password" type="password" autocomplete="new-password" required>
                <button class="pw-toggle" type="button" data-toggle-pw="update_password_password" aria-label="Tampilkan password" aria-pressed="false">
                    <svg class="ic-on" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                    <svg class="ic-off" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            @error('password', 'updatePassword')
                <p class="form-error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Konfirmasi Kata Sandi Baru -->
        <div class="field">
            <label for="update_password_password_confirmation" style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: var(--color-neutral-800);">
                Konfirmasi Kata Sandi Baru <span class="text-accent">*</span>
            </label>
            <div class="pw-wrap">
                <input class="input @error('password_confirmation', 'updatePassword') is-invalid @enderror" id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                <button class="pw-toggle" type="button" data-toggle-pw="update_password_password_confirmation" aria-label="Tampilkan password" aria-pressed="false">
                    <svg class="ic-on" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                    <svg class="ic-off" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            @error('password_confirmation', 'updatePassword')
                <p class="form-error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-top: 8px;">
            <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 10px 24px;">
                Perbarui Sandi
            </button>
        </div>
    </form>
</section>
