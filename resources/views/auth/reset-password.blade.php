<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atur Ulang Kata Sandi — KosFly</title>
  <meta name="description" content="Buat kata sandi baru untuk akun KosFly Anda.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('style.css') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-page">

  <div class="auth">

    <!-- ===================== BRANDING KIRI ===================== -->
    <aside class="auth-visual">
      <a class="brand" href="{{ url('/') }}" aria-label="KosFly — Beranda">
        <span class="brand-mark">K</span>
        Kos<span class="brand-accent">Fly</span>
      </a>

      <div class="auth-visual-content">
        <h1>
          Buat Kata Sandi Baru<br>
          Yang Kuat &amp; Aman.
        </h1>
        <p>
          Pastikan kata sandi baru Anda unik, terdiri dari minimal 8 karakter dengan kombinasi huruf dan angka agar akun hunian Anda selalu terlindungi.
        </p>

        <div class="auth-mock" aria-hidden="true">
          <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 18px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #22c55e;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <span style="font-size: 13px; font-weight: 700; color: #fff;">Tips Keamanan</span>
            </div>
            <ul style="font-size: 12px; color: rgba(255,255,255,0.7); margin: 0; padding-left: 18px; line-height: 1.6;">
              <li>Minimal 8 karakter</li>
              <li>Hindari tanggal lahir atau nomor telepon</li>
              <li>Jangan bagikan kata sandi kepada orang lain</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="auth-visual-foot">
        &copy; {{ date('Y') }} KosFly Residence. Hak cipta dilindungi.
      </div>
    </aside>

    <!-- ===================== FORM ===================== -->
    <main class="auth-form">
      <div class="auth-form-inner">

        <!-- Mobile Brand -->
        <div class="auth-mobile-brand">
          <a class="brand" href="{{ url('/') }}" aria-label="KosFly — Beranda">
            <span class="brand-mark">K</span>
            Kos<span class="brand-accent">Fly</span>
          </a>
        </div>

        <header class="auth-head">
          <h1>Atur Kata Sandi Baru</h1>
          <p>
            Masukkan kata sandi baru Anda untuk memperbarui akses akun KosFly.
          </p>
        </header>

        <form method="POST" action="{{ route('password.store') }}">
          @csrf

          <!-- Password Reset Token -->
          <input type="hidden" name="token" value="{{ $request->route('token') }}">

          <!-- Email -->
          <div class="field">
            <label class="field-label" for="email">Alamat Email</label>
            <input
              class="input @error('email') input-error @enderror"
              type="email"
              id="email"
              name="email"
              value="{{ old('email', $request->email) }}"
              placeholder="nama@email.com"
              autocomplete="username"
              required
              autofocus
            >
            @error('email')
              <p class="field-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Password Baru -->
          <div class="field">
            <label class="field-label" for="password">Kata Sandi Baru</label>
            <div class="pw-wrap">
              <input
                class="input @error('password') input-error @enderror"
                type="password"
                id="password"
                name="password"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                required
              >
              <button
                class="pw-toggle"
                type="button"
                data-toggle-pw="password"
                aria-label="Tampilkan kata sandi"
                aria-pressed="false"
              >
                <svg class="ic-on" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none" aria-hidden="true">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                  <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                  <path d="M1 1l22 22"/>
                </svg>
                <svg class="ic-off" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
            @error('password')
              <p class="field-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Konfirmasi Password Baru -->
          <div class="field">
            <label class="field-label" for="password_confirmation">Ulangi Kata Sandi Baru</label>
            <div class="pw-wrap">
              <input
                class="input @error('password_confirmation') input-error @enderror"
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Ulangi kata sandi baru"
                autocomplete="new-password"
                required
              >
              <button
                class="pw-toggle"
                type="button"
                data-toggle-pw="password_confirmation"
                aria-label="Tampilkan kata sandi"
                aria-pressed="false"
              >
                <svg class="ic-on" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none" aria-hidden="true">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                  <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                  <path d="M1 1l22 22"/>
                </svg>
                <svg class="ic-off" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
            @error('password_confirmation')
              <p class="field-error">{{ $message }}</p>
            @enderror
          </div>

          <button class="btn btn-primary btn-block" type="submit" style="margin-top: 18px;">
            <span>Simpan Kata Sandi Baru</span>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
          </button>
        </form>

        <div class="auth-alt" style="margin-top: 24px;">
          <p>
            Sudah ingat kata sandi Anda?
            <a class="link-primary" href="{{ route('login') }}">Masuk sekarang</a>
          </p>

          <a class="back-link" href="{{ url('/') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Kembali ke Beranda
          </a>
        </div>

      </div>
    </main>

  </div>

</body>
</html>
