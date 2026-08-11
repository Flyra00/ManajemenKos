
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buat Akun — KosFly</title>
  <meta name="description" content="Daftar akun KosFly untuk mulai mengelola kos Anda.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css',])

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
            Kelola Operasional Kos<br>
            Lebih Teratur.
        </h1>

        <p>
            Buat akun KosFly dan mulai kelola kamar, penghuni,
            pembayaran, serta laporan kos Anda.
        </p>

        <!-- Visual ringkas: status kamar -->
        <div class="auth-mock" aria-hidden="true">
            <div class="mock-bar">
                <span class="mock-bar-dots">
                    <i></i>
                    <i></i>
                    <i></i>
                </span>

                <span class="mock-url">
                    app.kosfly.id/kamar
                </span>
            </div>

            <div class="mock-body">
                <div class="mock-chart" style="margin:0">
                    <div class="mock-chart-head">
                        <span class="mock-chart-title">
                            Status Kamar
                        </span>

                        <span class="mock-chart-legend">
                            <span class="lg lg-2">
                                7/12 terisi
                            </span>
                        </span>
                    </div>

                    <div class="mock-rooms">

                        <div class="mock-room">
                            <span class="r-no">A-01</span>
                            <span class="r-name">Budi Santoso</span>
                            <span class="chip green">Terisi</span>
                        </div>

                        <div class="mock-room">
                            <span class="r-no">A-02</span>
                            <span class="r-name">Siti Aminah</span>
                            <span class="chip green">Terisi</span>
                        </div>

                        <div class="mock-room">
                            <span class="r-no">A-03</span>
                            <span class="r-name">Kosong</span>
                            <span class="chip gray">Kosong</span>
                        </div>

                        <div class="mock-room">
                            <span class="r-no">B-01</span>
                            <span class="r-name">Perbaikan AC</span>
                            <span class="chip amber">Perbaikan</span>
                        </div>

                        <div class="mock-room">
                            <span class="r-no">B-02</span>
                            <span class="r-name">Kosong</span>
                            <span class="chip gray">Kosong</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-visual-foot">
        © {{ date('Y') }} KosFly Management System.
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
                    class="input @error('name') input-error @enderror"
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
                    <p class="field-error">
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
                    class="input @error('email') input-error @enderror"
                    type="email"
                    id="regEmail"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    autocomplete="email"
                    required
                >

                @error('email')
                    <p class="field-error">
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
                    class="input @error('phone') input-error @enderror"
                    type="tel"
                    id="regPhone"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="08xxxxxxxxxx"
                    autocomplete="tel"
                    required
                >

                @error('phone')
                    <p class="field-error">
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
                        class="input @error('password') input-error @enderror"
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
                    Kekuatan password: Lemah
                </p>


                @error('password')
                    <p class="field-error">
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

