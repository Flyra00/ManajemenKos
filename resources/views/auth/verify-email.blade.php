<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Email — KosFly</title>
  <meta name="description" content="Verifikasi alamat email Anda untuk mengaktifkan akun KosFly.">
  @include('partials.favicons')
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
          Verifikasi Email Anda<br>
          Untuk Melanjutkan.
        </h1>
        <p>
          Kami menerapkan verifikasi email demi keamanan hunian Anda dan melindungi ekosistem KosFly dari akun spam atau bot.
        </p>

        <div class="auth-mock" aria-hidden="true">
          <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 18px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #22c55e;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
              <span style="font-size: 13px; font-weight: 700; color: #fff;">Perlindungan Akun Anti-Spam</span>
            </div>
            <p style="font-size: 12px; color: rgba(255,255,255,0.7); margin: 0; line-height: 1.5;">
              Buka aplikasi Gmail atau peramban email Anda, lalu klik tombol verifikasi di email resmi yang kami kirimkan untuk membuka akses penuh ke kamar kos dan tagihan Anda.
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
          <div style="width: 52px; height: 52px; background: rgba(225, 29, 72, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--color-primary, #e11d48);">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
          </div>
          <h1>Cek Kotak Masuk Gmail Anda</h1>
          <p>
            Terima kasih telah mendaftar di KosFly! Kami telah mengirimkan tautan verifikasi ke:
          </p>
          <div style="margin-top: 8px; font-size: 15px; font-weight: 700; color: var(--color-text-main, #0f172a); background: var(--color-bg-subtle, #f8fafc); border: 1px solid var(--color-border, #e2e8f0); padding: 8px 14px; border-radius: 6px; display: inline-block;">
            {{ auth()->user()->email ?? 'Alamat email Anda' }}
          </div>
        </header>

        <!-- Status Pengiriman Ulang Berhasil -->
        @if (session('status') == 'verification-link-sent')
          <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; display: flex; gap: 10px; align-items: flex-start;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top:2px;"><path d="M20 6L9 17l-5-5"/></svg>
            <div>
              <strong>Email Verifikasi Baru Terkirim!</strong>
              <div style="margin-top: 2px;">Tautan verifikasi baru telah dikirimkan ke alamat email Anda. Silakan periksa inbox atau folder Spam.</div>
            </div>
          </div>
        @endif

        @if (session('error'))
          <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; display: flex; gap: 10px; align-items: flex-start;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; color:#dc2626; margin-top:2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
              <strong>Pemberitahuan:</strong>
              <div style="margin-top: 2px;">{{ session('error') }}</div>
            </div>
          </div>
        @endif

        <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 12px 14px; margin-bottom: 24px; font-size: 13px; color: #92400e; line-height: 1.5;">
          💡 <strong>Tips:</strong> Jika belum menerima email dalam 2-3 menit, periksa folder <em>Spam</em> / <em>Promosi</em> di Gmail, atau klik tombol di bawah untuk meminta email verifikasi baru.
        </div>

        <!-- Tombol Kirim Ulang -->
        <form method="POST" action="{{ route('verification.send') }}">
          @csrf
          <button class="btn btn-primary btn-block" type="submit">
            <span>Kirim Ulang Email Verifikasi</span>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
          </button>
        </form>

        <!-- Aksi Logout / Ganti Akun -->
        <div class="auth-alt" style="margin-top: 24px; display: flex; flex-direction: column; gap: 12px; align-items: center;">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background: none; border: none; padding: 0; color: var(--color-text-muted, #64748b); font-size: 13px; cursor: pointer; text-decoration: underline;">
              Salah memasukkan email? Keluar &amp; Daftar Ulang
            </button>
          </form>

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
