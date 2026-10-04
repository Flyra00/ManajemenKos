<section>
    <header style="margin-bottom: 20px;">
        <h3 style="font-size: 18px; font-weight: 800; color: var(--color-neutral-900); margin: 0 0 4px;">
            Informasi Profil Akun
        </h3>
        <p class="small muted" style="margin: 0; line-height: 1.5;">
            Perbarui data nama lengkap, alamat email, nomor telepon/WhatsApp, dan identitas NIK KTP Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" style="display: flex; flex-direction: column; gap: 16px;">
        @csrf
        @method('patch')

        <!-- Nama Lengkap -->
        <div class="field">
            <label for="name" style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: var(--color-neutral-800);">
                Nama Lengkap <span class="text-accent">*</span>
            </label>
            <input class="input @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
            @error('name')
                <p class="form-error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Alamat Email -->
        <div class="field">
            <label for="email" style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: var(--color-neutral-800);">
                Alamat Email <span class="text-accent">*</span>
            </label>
            <input class="input @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <p class="form-error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div style="margin-top: 8px;">
                    <p class="small muted" style="color: #b45309;">
                        Email Anda belum diverifikasi.
                        <button form="send-verification" class="btn btn-ghost btn-sm" style="font-size: 12px; padding: 2px 6px; text-decoration: underline;">
                            Kirim ulang tautan verifikasi
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="small" style="color: #16a34a; margin-top: 4px;">
                            Tautan verifikasi baru telah dikirimkan ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Nomor Telepon / WhatsApp -->
        <div class="field">
            <label for="phone" style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: var(--color-neutral-800);">
                Nomor Telepon / WhatsApp
            </label>
            <input class="input @error('phone') is-invalid @enderror" type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890" autocomplete="tel">
            <p class="small muted" style="font-size: 11px; margin-top: 4px;">
                Digunakan untuk notifikasi sewa, invoice otomatis, dan komunikasi darurat dengan pengelola kos.
            </p>
            @error('phone')
                <p class="form-error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nomor KTP (NIK Pribadi) -->
        <div class="field">
            <label for="ktp_number" style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; color: var(--color-neutral-800);">
                Nomor KTP (NIK Pribadi)
            </label>
            <input
                class="input @error('ktp_number') is-invalid @enderror"
                type="text"
                id="ktp_number"
                name="ktp_number"
                maxlength="16"
                pattern="[0-9]{16}"
                inputmode="numeric"
                value="{{ old('ktp_number', ($user->tenant && !str_starts_with($user->tenant->ktp_number, 'KTP-')) ? $user->tenant->ktp_number : '') }}"
                placeholder="Masukkan 16 digit NIK KTP Anda"
            >
            <p class="small muted" style="font-size: 11px; margin-top: 4px;">
                Nomor identitas KTP Anda dilindungi hak privasi dan tidak ditampilkan kepada publik atau admin.
            </p>
            @error('ktp_number')
                <p class="form-error" style="color: #dc2626; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-top: 8px;">
            <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 10px 24px;">
                Simpan Perubahan
            </button>
        </div>
    </form>
</section>
