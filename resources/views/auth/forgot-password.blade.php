<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Kata Sandi — KosFly</title>
  <meta name="description" content="Atur ulang kata sandi akun KosFly Anda dengan aman.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
          Keamanan &amp; Akses<br>
          Akun Anda Terjamin.
        </h1>
        <p>
          Jangan khawatir jika Anda lupa kata sandi. Masukkan email yang terdaftar dan kami akan mengirimkan tautan aman untuk memulihkan akses ke akun KosFly Anda.
        </p>

        <div class="auth-mock" aria-hidden="true">
          <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 18px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #22c55e;"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span style="font-size: 13px; font-weight: 700; color: #fff;">Pemulihan Terenkripsi</span>
            </div>
            <p style="font-size: 12px; color: rgba(255,255,255,0.7); margin: 0; line-height: 1.5;">
              Tautan pemulihan memiliki batas waktu kedaluwarsa 60 menit dan hanya dapat digunakan satu kali demi menjaga keamanan privasi akun Anda.
            </p>
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
          <h1>Lupa Kata Sandi?</h1>
          <p>
            Masukkan alamat email akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi baru.
          </p>
        </header>

        <!-- Status Pengiriman Berhasil -->
        @if (session('status'))
          <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; display: flex; gap: 10px; align-items: flex-start;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top:2px;"><path d="M20 6L9 17l-5-5"/></svg>
            <div>
              <strong>Tautan Terkirim!</strong>
              <div style="margin-top: 2px;">{{ session('status') }}</div>
            </div>
          </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
          @csrf

          <!-- Email -->
          <div class="field">
            <label class="field-label" for="email">Alamat Email Terdaftar</label>
            <input
              class="input @error('email') input-error @enderror"
              type="email"
              id="email"
              name="email"
              value="{{ old('email') }}"
              placeholder="nama@email.com"
              autocomplete="email"
              required
              autofocus
            >
            @error('email')
              <p class="field-error">{{ $message }}</p>
            @enderror
          </div>

          <button class="btn btn-primary btn-block" type="submit" style="margin-top: 18px;">
            <span>Kirim Tautan Reset Kata Sandi</span>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
          </button>
        </form>

        <div class="auth-alt" style="margin-top: 24px;">
          <p>
            Ingat kata sandi Anda?
            <a class="link-primary" href="{{ route('login') }}">Masuk kembali</a>
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
