
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Akun — KosFly</title>
  <meta name="description" content="Daftar akun KosFly untuk mulai mengelola kos Anda.">
  @include('partials.favicons')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            Mulai Tinggal Nyaman<br>
            di KosFly Residence.
        </h1>

        <p>
            Daftar akun untuk reservasi kamar impian Anda,
            simpan riwayat sewa, dan nikmati fasilitas hunian modern.
        </p>

        <!-- Visual ringkas: fitur hunian untuk penyewa -->
        <div class="auth-mock" aria-hidden="true">
            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-accent-400); font-weight: 700;">
                        Keuntungan Penghuni
                    </span>
                    <span style="background: #2563eb; color: #ffffff; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                        Siap Huni
                    </span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #fff;">
                        <span style="color: #22c55e; font-weight: bold;">✓</span>
                        <span>Booking kamar online instan 2 menit</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #fff;">
                        <span style="color: #22c55e; font-weight: bold;">✓</span>
                        <span>Fasilitas lengkap (AC, WiFi, KM Dalam)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #fff;">
                        <span style="color: #22c55e; font-weight: bold;">✓</span>
                        <span>Bukti sewa digital &amp; kuitansi resmi</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: #fff;">
                        <span style="color: #22c55e; font-weight: bold;">✓</span>
                        <span>Bantuan perbaikan kamar cepat tanggap</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-visual-foot">
        © {{ date('Y') }} KosFly Residence. Hak cipta dilindungi.
    </div>
</aside>


<!-- ===================== FORM ===================== -->
<main class="auth-form">

    <div class="auth-form-inner">

        <!-- Mobile Branding -->
        <div class="auth-mobile-brand">
            <a
                class="brand"
                href="{{ url('/') }}"
                aria-label="KosFly — Beranda"
            >
                <span class="brand-mark">K</span>
                Kos<span class="brand-accent">Fly</span>
            </a>
        </div>


        <!-- Header -->
        <header class="auth-head">
            <h1>Buat Akun KosFly</h1>

            <p>
                Mulai kelola kos Anda dengan lebih mudah.
            </p>
        </header>

        <!-- Alert Error Validasi -->
        @if ($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; display: flex; gap: 10px; align-items: flex-start;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; color:#dc2626; margin-top:2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div style="flex: 1;">
                    <strong style="display:block; margin-bottom: 4px; font-weight: 700;">Pendaftaran Belum Berhasil:</strong>
                    <ul style="margin: 0; padding-left: 18px; font-size: 13px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- ===================== REGISTER FORM ===================== -->
        <form
            id="registerForm"
            method="POST"
            action="{{ route('register') }}"
        >
            @csrf


            <!-- ===================== NAMA ===================== -->
            <div class="field">

                <label
                    class="field-label"
                    for="regName"
                >
                    Nama Lengkap
                </label>

                <input
                    class="input @error('name') is-invalid input-error @enderror"
                    type="text"
                    id="regName"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Masukkan nama lengkap"
                    autocomplete="name"
                    required
                    autofocus
                >

                @error('name')
                    <p class="field-error show">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- ===================== EMAIL ===================== -->
            <div class="field">

                <label
                    class="field-label"
                    for="regEmail"
                >
                    Email
                </label>

                <input
                    class="input @error('email') is-invalid input-error @enderror"
                    type="email"
                    id="regEmail"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    required
                >

                @error('email')
                    <p class="field-error show">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- ===================== NOMOR TELEPON ===================== -->
            <div class="field">

                <label
                    class="field-label"
                    for="regPhone"
                >
                    Nomor Telepon
                </label>

                <input
                    class="input @error('phone') is-invalid input-error @enderror"
                    type="tel"
                    id="regPhone"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="08xxxxxxxxxx"
                    autocomplete="tel"
                    required
                >

                @error('phone')
                    <p class="field-error show">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- ===================== PASSWORD ===================== -->
            <div class="field">

                <label
                    class="field-label"
                    for="regPassword"
                >
                    Password
                </label>

                <div class="pw-wrap">

                    <input
                        class="input @error('password') is-invalid input-error @enderror"
                        type="password"
                        id="regPassword"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        class="pw-toggle"
                        type="button"
                        data-toggle-pw="regPassword"
                        aria-label="Tampilkan password"
                        aria-pressed="false"
                    >

                        <!-- Eye Off -->
                        <svg
                            class="ic-on"
                            width="19"
                            height="19"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            style="display:none"
                            aria-hidden="true"
                        >
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                            <path d="M1 1l22 22"/>
                        </svg>

                        <!-- Eye -->
                        <svg
                            class="ic-off"
                            width="19"
                            height="19"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>

                    </button>

                </div>


                <!-- Password Strength -->
                <div
                    class="strength-meter"
                    id="strengthBar"
                    aria-hidden="true"
                >
                    <i></i>
                    <i></i>
                    <i></i>
                    <i></i>
                </div>

                <p
                    class="strength-label"
                    id="strengthLabel"
                >
                    Kekuatan password: —
                </p>


                @error('password')
                    <p class="field-error show">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- ===================== KONFIRMASI PASSWORD ===================== -->
            <div class="field">

                <label
                    class="field-label"
                    for="regConfirm"
                >
                    Konfirmasi Password
                </label>

                <div class="pw-wrap">

                    <input
                        class="input"
                        type="password"
                        id="regConfirm"
                        name="password_confirmation"
                        placeholder="Ulangi password"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        class="pw-toggle"
                        type="button"
                        data-toggle-pw="regConfirm"
                        aria-label="Tampilkan password"
                        aria-pressed="false"
                    >

                        <svg
                            class="ic-on"
                            width="19"
                            height="19"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            style="display:none"
                            aria-hidden="true"
                        >
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                            <path d="M1 1l22 22"/>
                        </svg>

                        <svg
                            class="ic-off"
                            width="19"
                            height="19"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>

                    </button>

                </div>


                @error('password')
                    <p class="field-error">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- ===================== TERMS ===================== -->
            <label
                class="check-label"
                style="align-items:flex-start"
            >

                <input
                    type="checkbox"
                    id="regTerms"
                    name="terms"
                    value="1"
                    style="margin-top:3px"
                >

                <span>
                    Saya menyetujui
                    <a href="#">
                        Syarat &amp; Ketentuan
                    </a>
                    dan
                    <a href="#">
                        Kebijakan Privasi
                    </a>.
                </span>

            </label>


            <!-- ===================== SUBMIT ===================== -->
            <button
                class="btn btn-primary btn-block"
                type="submit"
            >
                Daftar
            </button>


            <!-- Form Note -->
            <div
                class="form-note"
                style="display:none"
            >
                <svg
                    width="15"
                    height="15"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 16v-4"/>
                    <path d="M12 8h.01"/>
                </svg>

                <span></span>
            </div>

        </form>


        <!-- ===================== AUTH ALTERNATIVE ===================== -->
        <div class="auth-alt">

            <p>
                Sudah punya akun?

                <a
                    class="link-primary"
                    href="{{ route('login') }}"
                >
                    Masuk sekarang
                </a>
            </p>


            <a
                class="back-link"
                href="{{ url('/') }}"
            >

                <svg
                    width="15"
                    height="15"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M19 12H5"/>
                    <path d="M12 19l-7-7 7-7"/>
                </svg>

                Kembali ke Beranda

            </a>

        </div>

    </div>
</main>

  </div><!-- /auth -->

  <!-- Toast Root -->
  <div id="toastRoot" aria-live="polite">
    @if(session('success'))
      <div class="toast ok" style="cursor:pointer;" onclick="this.remove()" title="Klik untuk menutup">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
        <span>{{ session('success') }}</span>
      </div>
    @endif
    @if(session('error'))
      <div class="toast err" style="cursor:pointer;" onclick="this.remove()" title="Klik untuk menutup">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span>{{ session('error') }}</span>
      </div>
    @endif
    @if($errors->any())
      @foreach($errors->all() as $error)
        <div class="toast err" style="cursor:pointer;" onclick="this.remove()" title="Klik untuk menutup">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <span>{{ $error }}</span>
        </div>
      @endforeach
    @endif
  </div>

</body>
</html>

