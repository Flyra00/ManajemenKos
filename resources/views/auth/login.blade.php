<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — KosFly</title>
  <meta name="description" content="Masuk ke akun KosFly untuk mengelola kos Anda.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-page">

  <div class="auth">
<!-- ===================== BRANDING KIRI ===================== -->
<aside class="auth-visual">

    <a
        class="brand"
        href="{{ url('/') }}"
        aria-label="KosFly — Beranda"
    >
        <span class="brand-mark">K</span>
        Kos<span class="brand-accent">Fly</span>
    </a>


    <div class="auth-visual-content">

        <h1>
            Hunian Kos Nyaman,<br>
            Modern &amp; Terpercaya.
        </h1>

        <p>
            Masuk ke akun Anda untuk mengelola sewa kamar,
            memeriksa invoice tagihan, dan menikmati layanan hunian KosFly.
        </p>


        <!-- ===================== ROOM / TENANT PREVIEW CARD ===================== -->
        <div
            class="auth-mock"
            aria-hidden="true"
        >
            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-accent-400); font-weight: 700;">
                        Portal Penghuni &amp; Sewa
                    </span>
                    <span style="background: #22c55e; color: #ffffff; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">
                        Aktif
                    </span>
                </div>
                <div style="font-size: 16px; font-weight: 800; color: #ffffff; margin-bottom: 4px;">
                    KosFly Residence
                </div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.7); margin-bottom: 14px;">
                    Akses cepat bukti sewa, status kamar, dan invoice resmi
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <div style="background: rgba(0,0,0,0.2); padding: 8px 10px; border-radius: 6px;">
                        <div style="font-size: 10px; color: rgba(255,255,255,0.6);">Layanan</div>
                        <div style="font-size: 12px; font-weight: 700; color: #fff;">Sewa &amp; Tagihan</div>
                    </div>
                    <div style="background: rgba(0,0,0,0.2); padding: 8px 10px; border-radius: 6px;">
                        <div style="font-size: 10px; color: rgba(255,255,255,0.6);">Bantuan</div>
                        <div style="font-size: 12px; font-weight: 700; color: #fff;">Tiket Perbaikan</div>
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


        <!-- ===================== MOBILE BRAND ===================== -->
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


        <!-- ===================== HEADER ===================== -->
        <header class="auth-head">

            <h1>
                Selamat Datang Kembali
            </h1>

            <p>
                Masuk untuk melanjutkan ke KosFly.
            </p>

        </header>

        <!-- Session Status (Misal setelah reset kata sandi) -->
        @if (session('status'))
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 14px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; line-height: 1.5; display: flex; gap: 8px; align-items: center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M20 6L9 17l-5-5"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- ===================== LOGIN FORM ===================== -->
        <form
            id="loginForm"
            method="POST"
            action="{{ route('login') }}"
        >

            @csrf


            <!-- ===================== EMAIL ===================== -->
            <div class="field">

                <label
                    class="field-label"
                    for="loginEmail"
                >
                    Alamat Email
                </label>


                <input
                    class="input @error('email') input-error @enderror"
                    type="email"
                    id="loginEmail"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    autocomplete="username"
                    required
                    autofocus
                >


                @error('email')
                    <p class="field-error">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- ===================== PASSWORD ===================== -->
            <div class="field">

                <label
                    class="field-label"
                    for="loginPassword"
                >
                    Kata Sandi
                </label>


                <div class="pw-wrap">

                    <input
                        class="input @error('password') input-error @enderror"
                        type="password"
                        id="loginPassword"
                        name="password"
                        placeholder="Masukkan kata sandi Anda"
                        autocomplete="current-password"
                        required
                    >


                    <button
                        class="pw-toggle"
                        type="button"
                        data-toggle-pw="loginPassword"
                        aria-label="Tampilkan kata sandi"
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
                            <circle
                                cx="12"
                                cy="12"
                                r="3"
                            />
                        </svg>

                    </button>

                </div>


                @error('password')
                    <p class="field-error">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- ===================== REMEMBER + FORGOT ===================== -->
            <div class="auth-row">

                <label class="check-label">

                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        {{ old('remember') ? 'checked' : '' }}
                    >

                    Ingat saya

                </label>


                @if (Route::has('password.request'))

                    <a
                        class="link-primary"
                        href="{{ route('password.request') }}"
                    >
                        Lupa password?
                    </a>

                @endif

            </div>


            <!-- ===================== SUBMIT ===================== -->
            <button
                class="btn btn-primary btn-block"
                type="submit"
            >

                Masuk

                <svg
                    width="17"
                    height="17"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M5 12h14"/>
                    <path d="M12 5l7 7-7 7"/>
                </svg>

            </button>


            <!-- ===================== FORM NOTE ===================== -->
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
                    <circle
                        cx="12"
                        cy="12"
                        r="10"
                    />

                    <path d="M12 16v-4"/>
                    <path d="M12 8h.01"/>

                </svg>

                <span></span>

            </div>

        </form>


        <!-- ===================== AUTH ALTERNATIVE ===================== -->
        <div class="auth-alt">

            <p>
                Belum punya akun?

                <a
                    class="link-primary"
                    href="{{ route('register') }}"
                >
                    Daftar sekarang
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
