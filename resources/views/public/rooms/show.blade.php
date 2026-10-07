<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Kamar {{ $room->room_number }} — KosFly</title>
  <meta name="description" content="Detail fasilitas dan pengajuan sewa kamar {{ $room->room_number }} di KosFly.">
  @include('partials.favicons')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="{{ asset('style.css') }}">
  @vite(['resources/css/app.css'])
</head>

<body class="public-page">

  <!-- NAVBAR -->
  <header class="site-nav">
    <div class="container">
      <a class="brand" href="{{ route('home') }}" aria-label="KosFly — Beranda">
        <span class="brand-mark">K</span>
        Kos<span class="brand-accent">Fly</span>
      </a>

      <nav aria-label="Navigasi utama">
        <ul class="nav-links">
          <li><a href="{{ route('public.rooms.index') }}" style="font-weight:700; color:var(--color-accent)">Pilihan Kamar</a></li>
          <li><a href="{{ route('home') }}#beranda">Beranda</a></li>
          <li><a href="{{ route('home') }}#fitur">Fitur</a></li>
          <li><a href="{{ route('home') }}#tentang">Tentang</a></li>
        </ul>
      </nav>

      <div class="nav-cta">
        @auth
          <a class="btn btn-primary btn-sm" href="{{ route('dashboard') }}">Dashboard Saya</a>
        @else
          <a class="btn btn-secondary btn-sm" href="{{ route('login') }}">Masuk</a>
          <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Daftar</a>
        @endauth
        <button class="hamburger" id="navToggle" aria-label="Buka menu" aria-controls="navDrawer" aria-expanded="false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
        </button>
      </div>
    </div>
  </header>

  <!-- Drawer navigasi mobile -->
  <aside class="nav-drawer" id="navDrawer" aria-label="Menu mobile">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-md, 16px);">
      <a class="brand" href="{{ route('home') }}" style="margin: 0;">
        <span class="brand-mark">K</span>
        Kos<span class="brand-accent">Fly</span>
      </a>
      <button type="button" id="navClose" class="hamburger" aria-label="Tutup menu" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <nav aria-label="Navigasi mobile">
      <a class="drawer-link" href="{{ route('public.rooms.index') }}" style="font-weight:700; color:var(--color-accent)">Pilihan Kamar
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="{{ route('home') }}#beranda">Beranda
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="{{ route('home') }}#fitur">Fitur
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="{{ route('home') }}#tentang">Tentang
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
    </nav>
    <div class="drawer-cta">
      @auth
        <a class="btn btn-primary btn-block" href="{{ route('dashboard') }}">Buka Dashboard Saya</a>
      @else
        <a class="btn btn-secondary btn-block" href="{{ route('login') }}">Masuk</a>
        <a class="btn btn-primary btn-block" href="{{ route('register') }}">Daftar Akun Baru</a>
      @endauth
    </div>
  </aside>
  <div class="nav-backdrop" id="navBackdrop" aria-hidden="true"></div>


  <main style="padding: 40px 0 80px;">
    <div class="container">

      <!-- Breadcrumb -->
      <nav class="breadcrumb" style="margin-bottom: 24px;" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Beranda</a>
        <span class="sep">/</span>
        <a href="{{ route('public.rooms.index') }}">Pilihan Kamar</a>
        <span class="sep">/</span>
        <span class="current">Kamar {{ $room->room_number }}</span>
      </nav>

      @if(session('error'))
        <div class="alert alert-error" style="margin-bottom: 24px;" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      <div class="grid-2" style="gap: 32px; align-items: flex-start;">

        <!-- KOLOM KIRI: SPESIFIKASI & FOTO KAMAR -->
        <div>
          <!-- Foto Kamar -->
          <div class="card elev-sm" style="padding: 0; overflow: hidden; margin-bottom: 24px; border: 1px solid var(--color-divider);">
            <div style="height: 340px; background: var(--color-neutral-100); position: relative;">
              @if($room->image)
                <img src="{{ asset('storage/' . $room->image) }}" alt="Foto Kamar {{ $room->room_number }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1000&q=80';" style="width: 100%; height: 100%; object-fit: cover;">
              @else
                <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1000&q=80" alt="Foto Kamar {{ $room->room_number }}" style="width: 100%; height: 100%; object-fit: cover;">
              @endif
              <span class="tag tag-accent" style="position: absolute; top: 16px; right: 16px; font-weight: 700; font-size: 13px;">
                Tersedia untuk Disewa
              </span>
            </div>
          </div>

          <!-- Informasi Detail Kamar -->
          <div class="card elev-sm" style="padding: 24px; border: 1px solid var(--color-divider);">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px; border-bottom: 1px solid var(--color-divider); padding-bottom: 16px;">
              <div>
                <h1 style="font-size: 28px; font-weight: 800; margin: 0; color: var(--color-neutral-900);">
                  Kamar {{ $room->room_number }}
                </h1>
                <span class="muted small">Lantai {{ $room->floor }} · {{ $kosSettings['name'] }}</span>
              </div>
              <div style="text-align: right;">
                <div style="font-size: 24px; font-weight: 800; color: var(--color-neutral-900);">
                  Rp {{ number_format($room->price, 0, ',', '.') }}
                </div>
                <span class="small muted">per bulan</span>
              </div>
            </div>

            <!-- Fasilitas Kamar -->
            <div style="margin-bottom: 24px;">
              <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 12px;">Fasilitas Kamar</h3>
              @if($room->facilities->count())
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                  @foreach($room->facilities as $fac)
                    <span class="tag tag-neutral" style="font-size: 13px; padding: 6px 12px;">
                      ✓ {{ $fac->name }}
                    </span>
                  @endforeach
                </div>
              @else
                <p class="small muted" style="margin: 0;">Fasilitas kamar standar kos.</p>
              @endif
            </div>

            <!-- Deskripsi / Catatan -->
            @if($room->description)
              <div style="margin-bottom: 24px;">
                <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 8px;">Deskripsi Kamar</h3>
                <p style="margin: 0; font-size: 14px; color: var(--color-neutral-700); line-height: 1.6;">
                  {{ $room->description }}
                </p>
              </div>
            @endif

            <!-- Kontak Pengelola -->
            <div style="padding-top: 16px; border-top: 1px solid var(--color-divider); display: flex; align-items: center; justify-content: space-between;">
              <div class="small muted">
                Ada pertanyaan tentang kamar ini?
              </div>
              <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $kosSettings['phone']) }}" target="_blank" class="btn btn-secondary btn-sm">
                Hubungi Pengelola
              </a>
            </div>
          </div>
        </div>

        <!-- KOLOM KANAN: FORM PENGAJUAN SEWA & AUTO-REGISTER -->
        <div>
          <div class="card elev-sm" style="padding: 24px; border: 1px solid var(--color-divider); position: sticky; top: 20px;">
            <h2 style="font-size: 20px; font-weight: 800; margin: 0 0 8px;">
              Formulir Sewa Kamar
            </h2>
            <p class="small muted" style="margin: 0 0 20px; line-height: 1.5;">
              Isi formulir di bawah ini untuk memesan kamar dan menerbitkan invoice resmi Anda.
            </p>

            @if($errors->any())
              <div class="alert alert-error" role="alert" style="margin-bottom: 16px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div style="font-size: 13px;">
                  <strong style="display:block; margin-bottom:4px;">Gagal Mengajukan Sewa:</strong>
                  <ul style="margin: 0 0 0 16px; padding: 0;">
                    @foreach($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              </div>
            @endif

            @if(session('error'))
              <div class="alert alert-error" role="alert" style="margin-bottom: 16px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>{{ session('error') }}</span>
              </div>
            @endif

            <form method="POST" action="{{ route('public.rooms.book', $room) }}" style="display: flex; flex-direction: column; gap: 16px;">
              @csrf

              @guest
                <!-- Identitas Calon Tenant (Auto-Register) -->
                <div class="field">
                  <label for="name">Nama Lengkap <span class="text-accent">*</span></label>
                  <input class="input @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Sesuai nama KTP" required>
                  @error('name')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="email">Alamat Email <span class="text-accent">*</span></label>
                  <input class="input @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contoh@gmail.com" required>
                  @error('email')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="phone">Nomor WhatsApp / HP <span class="text-accent">*</span></label>
                  <input class="input @error('phone') is-invalid @enderror" type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08123456789" required>
                  @error('phone')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="ktp_number">Nomor KTP / NIK (16 Digit) <span class="text-accent">*</span></label>
                  <input class="input @error('ktp_number') is-invalid @enderror" type="text" id="ktp_number" name="ktp_number" value="{{ old('ktp_number') }}" placeholder="16 digit angka sesuai KTP" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" required>
                  <span class="small muted" style="font-size: 11px;">Wajib diisi sesuai KTP asli untuk dokumen kontrak hunian resmi.</span>
                  @error('ktp_number')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="password">Kata Sandi Akun Baru <span class="text-accent">*</span></label>
                  <input class="input @error('password') is-invalid @enderror" type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
                  <span class="small muted" style="font-size: 11px;">Digunakan untuk login akun tenant Anda nantinya.</span>
                  @error('password')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              @else
                <!-- Info Akun Login -->
                <div class="card" style="padding: 12px 16px; background: var(--color-neutral-100); border: 1px solid var(--color-divider);">
                  <div class="small muted">Login sebagai penyewa:</div>
                  <strong style="font-size: 15px;">{{ auth()->user()->name }}</strong>
                  <div class="small muted">{{ auth()->user()->email }} · {{ auth()->user()->phone ?? 'Belum ada nomor HP' }}</div>
                </div>

                <div class="field">
                  <label for="ktp_number">Nomor KTP / NIK (16 Digit) <span class="text-accent">*</span></label>
                  <input class="input @error('ktp_number') is-invalid @enderror" type="text" id="ktp_number" name="ktp_number" value="{{ old('ktp_number', (auth()->user()->tenant && !str_starts_with(auth()->user()->tenant->ktp_number, 'KTP-')) ? auth()->user()->tenant->ktp_number : '') }}" placeholder="16 digit angka sesuai KTP" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" required>
                  <span class="small muted" style="font-size: 11px;">Wajib diisi sesuai KTP asli untuk dokumen kontrak hunian resmi.</span>
                  @error('ktp_number')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              @endguest

              <!-- Tanggal Mulai Sewa -->
              <div class="field">
                <label for="start_date">Tanggal Mulai Menghuni <span class="text-accent">*</span></label>
                <input class="input @error('start_date') is-invalid @enderror" type="date" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                @error('start_date')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <!-- Durasi Sewa -->
              <div class="field">
                <label for="duration_months">Durasi Sewa Awal</label>
                <select class="input" id="duration_months" name="duration_months">
                  <option value="1" @selected(old('duration_months') == '1')>1 Bulan (Bulanan)</option>
                  <option value="3" @selected(old('duration_months') == '3')>3 Bulan (Triwulan)</option>
                  <option value="6" @selected(old('duration_months') == '6')>6 Bulan (Semester)</option>
                  <option value="12" @selected(old('duration_months') == '12')>12 Bulan (1 Tahun)</option>
                </select>
              </div>

              <!-- Skema Pembayaran (Full vs Deposit) -->
              <div class="field">
                <label for="payment_type">Skema Pembayaran <span class="text-accent">*</span></label>
                <select class="input" id="payment_type" name="payment_type">
                  <option value="full" @selected(old('payment_type') === 'full')>Bayar Penuh 100% (Pelunasan di Awal)</option>
                  <option value="deposit_50" @selected(old('payment_type') === 'deposit_50')>Uang Muka / Deposit 50% (Sisa dibayar kemudian)</option>
                </select>
                <span class="small muted">Jika memilih Deposit 50%, sisa tagihan sewa berkurang 50% saat pelunasan berikutnya.</span>
              </div>

              <!-- Rangkuman Biaya -->
              <div style="padding: 16px; background: var(--color-neutral-50); border: 1px solid var(--color-divider); border-radius: 6px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                  <span class="muted">Tarif Kamar:</span>
                  <span>Rp {{ number_format($room->price, 0, ',', '.') }} / bln</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                  <span class="muted">Total Nilai Sewa:</span>
                  <span id="totalRentDisplay">Rp {{ number_format($room->price, 0, ',', '.') }}</span>
                </div>
                <div id="depositRow" style="display: none; justify-content: space-between; margin-bottom: 6px; font-size: 13px; color: var(--color-accent);">
                  <span>Uang Muka (Deposit 50%):</span>
                  <span id="depositDisplay" style="font-weight: 700;">Rp 0</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                  <span class="muted">Batas Waktu Bayar:</span>
                  <span style="font-weight: 600; color: var(--color-accent);">1 x 24 Jam</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid var(--color-divider); font-size: 15px; font-weight: 800;">
                  <span id="invoiceLabel">Total Tagihan Awal:</span>
                  <span id="totalDisplay" style="color: var(--color-neutral-900);">Rp {{ number_format($room->price, 0, ',', '.') }}</span>
                </div>
                <div id="remainingRow" style="display: none; justify-content: space-between; margin-top: 6px; font-size: 12px;" class="muted">
                  <span>Sisa Pelunasan Nanti:</span>
                  <span id="remainingDisplay" style="font-weight: 700;">Rp 0</span>
                </div>
              </div>

              <div style="margin-top: 8px;">
                <button type="submit" class="btn btn-primary btn-block" style="justify-content: center; font-size: 15px; font-weight: 700; padding: 12px 20px;">
                  Ajukan Sewa & Terbitkan Invoice
                </button>
              </div>


              <p class="small muted" style="text-align: center; margin: 0; font-size: 12px; line-height: 1.4;">
                Nomor invoice resmi akan langsung diterbitkan beserta petunjuk transfer rekening bank.
              </p>
            </form>
          </div>
        </div>

      </div>

    </div>
  </main>

  <!-- FOOTER -->
  <footer class="site-foot" style="background: var(--color-neutral-900); color: #fff; padding: 40px 0; border-top: 1px solid var(--color-divider);">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
      <div class="brand" style="color: #fff;">
        Kos<span class="brand-accent">Fly</span>
      </div>
      <p class="small" style="color: var(--color-neutral-400); margin: 0;">
        &copy; {{ date('Y') }} KosFly. Manajemen Kos & Sewa Online Terpadu.
      </p>
    </div>
  </footer>

  <script>
    // Nav Drawer Toggle
    const navToggle = document.getElementById('navToggle');
    const navClose = document.getElementById('navClose');
    const navDrawer = document.getElementById('navDrawer');
    const navBackdrop = document.getElementById('navBackdrop') || document.querySelector('.nav-backdrop');

    if (navToggle && navDrawer) {
      function openNav() {
        document.body.classList.add('nav-open');
        navDrawer.classList.add('open');
        if (navBackdrop) {
          navBackdrop.classList.add('show');
          navBackdrop.classList.add('open');
        }
        navToggle.setAttribute('aria-expanded', 'true');
      }

      function closeNav() {
        document.body.classList.remove('nav-open');
        navDrawer.classList.remove('open');
        if (navBackdrop) {
          navBackdrop.classList.remove('show');
          navBackdrop.classList.remove('open');
        }
        navToggle.setAttribute('aria-expanded', 'false');
      }

      navToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        if (document.body.classList.contains('nav-open') || navDrawer.classList.contains('open')) {
          closeNav();
        } else {
          openNav();
        }
      });

      if (navClose) {
        navClose.addEventListener('click', (e) => {
          e.stopPropagation();
          closeNav();
        });
      }

      if (navBackdrop) {
        navBackdrop.addEventListener('click', closeNav);
      }

      navDrawer.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeNav);
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.body.classList.contains('nav-open')) {
          closeNav();
        }
      });
    }

    // Dynamic Price & Deposit Calculation
    const monthlyPrice = {{ (int)$room->price }};
    const durationSelect = document.getElementById('duration_months');
    const paymentTypeSelect = document.getElementById('payment_type');
    const totalRentDisplay = document.getElementById('totalRentDisplay');
    const depositRow = document.getElementById('depositRow');
    const depositDisplay = document.getElementById('depositDisplay');
    const invoiceLabel = document.getElementById('invoiceLabel');
    const totalDisplay = document.getElementById('totalDisplay');
    const remainingRow = document.getElementById('remainingRow');
    const remainingDisplay = document.getElementById('remainingDisplay');

    function calculateCost() {
      const months = parseInt(durationSelect ? durationSelect.value : 1, 10) || 1;
      const totalRent = monthlyPrice * months;
      const isDeposit = paymentTypeSelect && paymentTypeSelect.value === 'deposit_50';

      if (totalRentDisplay) {
        totalRentDisplay.textContent = 'Rp ' + totalRent.toLocaleString('id-ID');
      }

      if (isDeposit) {
        const deposit = Math.round(totalRent * 0.5);
        const remaining = totalRent - deposit;

        if (depositRow) depositRow.style.display = 'flex';
        if (depositDisplay) depositDisplay.textContent = 'Rp ' + deposit.toLocaleString('id-ID');
        if (invoiceLabel) invoiceLabel.textContent = 'Tagihan Invoice Awal (DP 50%):';
        if (totalDisplay) totalDisplay.textContent = 'Rp ' + deposit.toLocaleString('id-ID');
        if (remainingRow) remainingRow.style.display = 'flex';
        if (remainingDisplay) remainingDisplay.textContent = 'Rp ' + remaining.toLocaleString('id-ID');
      } else {
        if (depositRow) depositRow.style.display = 'none';
        if (invoiceLabel) invoiceLabel.textContent = 'Total Tagihan Penuh (100%):';
        if (totalDisplay) totalDisplay.textContent = 'Rp ' + totalRent.toLocaleString('id-ID');
        if (remainingRow) remainingRow.style.display = 'none';
      }
    }

    if (durationSelect) durationSelect.addEventListener('change', calculateCost);
    if (paymentTypeSelect) paymentTypeSelect.addEventListener('change', calculateCost);
    calculateCost();
  </script>

</body>
</html>

