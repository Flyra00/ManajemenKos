<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $kosSettings['name'] ?? 'KosFly Residence' }} — Sewa Kamar Kos Nyaman &amp; Modern</title>
  <meta name="description" content="Temukan dan sewa kamar kos impian Anda di {{ $kosSettings['name'] ?? 'KosFly Residence' }}. Fasilitas lengkap, kamar siap huni, AC, WiFi cepat, dan booking online mudah.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('style.css') }}">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  @vite(['resources/css/app.css'])
  <style>
    /* Styling tenang & elegan sesuai design system asli KosFly */
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 12px;
      border-radius: 999px;
      background: var(--color-surface);
      color: var(--color-neutral-800);
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 16px;
      border: 1px solid var(--color-divider);
      letter-spacing: 0.5px;
    }
    .trust-stat-card {
      background: #ffffff;
      border: 1px solid var(--color-divider);
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .trust-stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    }
    .room-card-public {
      background: #ffffff;
      border: 1px solid var(--color-divider);
      border-radius: 8px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .room-card-public:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.06);
      border-color: var(--color-neutral-400);
    }
    .amenity-card {
      background: #ffffff;
      border: 1px solid var(--color-divider);
      border-radius: 8px;
      padding: 22px;
      display: flex;
      gap: 16px;
      align-items: flex-start;
      transition: transform 0.2s ease;
    }
    .amenity-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    }
    .step-card {
      background: #ffffff;
      border: 1px solid var(--color-divider);
      border-radius: 8px;
      padding: 26px 20px;
      text-align: center;
      position: relative;
    }
    .step-number {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--color-neutral-900);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 16px;
      margin: 0 auto 14px;
    }
    .faq-item {
      background: #ffffff;
      border: 1px solid var(--color-divider);
      border-radius: 8px;
      margin-bottom: 12px;
      overflow: hidden;
    }
    .faq-question {
      padding: 16px 20px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      user-select: none;
    }
    .faq-answer {
      padding: 0 20px 16px;
      color: var(--color-neutral-700);
      font-size: 14px;
      line-height: 1.6;
    }

    /* Floating WhatsApp Button: Putih Bersih dengan Logo WhatsApp */
    .wa-float-btn {
      position: fixed;
      bottom: 24px;
      right: 24px;
      width: 52px;
      height: 52px;
      background: #ffffff;
      color: #25d366;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
      border: 1px solid rgba(0, 0, 0, 0.08);
      z-index: 999;
      text-decoration: none;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .wa-float-btn:hover {
      transform: translateY(-3px) scale(1.06);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
      color: #16a34a;
      background: #ffffff;
    }
  </style>
</head>

<body class="public-page">

  <!-- ===================== NAVBAR ===================== -->
  <header class="site-nav">
    <div class="container">
      <a class="brand" href="{{ route('home') }}" aria-label="KosFly — Beranda">
        <span class="brand-mark">K</span>
        Kos<span class="brand-accent">Fly</span>
      </a>

      <nav aria-label="Navigasi utama">
        <ul class="nav-links">
          <li><a href="#kamar-tersedia">Kamar Tersedia</a></li>
          <li><a href="{{ route('public.rooms.index') }}">Katalog Kamar</a></li>
          <li><a href="#fasilitas">Fasilitas</a></li>
          <li><a href="#cara-sewa">Cara Sewa</a></li>
          <li><a href="#lokasi">Lokasi</a></li>
          <li><a href="#faq">FAQ</a></li>
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

  <!-- Drawer mobile -->
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
      <a class="drawer-link" href="#kamar-tersedia">Kamar Tersedia
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="{{ route('public.rooms.index') }}">Katalog Kamar
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="#fasilitas">Fasilitas
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="#cara-sewa">Cara Sewa
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="#lokasi">Lokasi
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="#faq">FAQ
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

  <main id="beranda">

    <!-- ===================== HERO SECTION ===================== -->
    <section class="hero" style="padding: 50px 0 60px;">
      <div class="container hero-grid">
        <div>
          <div class="hero-badge">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            HUNIAN KOS NYAMAN &amp; STRATEGIS
          </div>

          <h1 style="font-size: 36px; line-height: 1.25; margin-bottom: 16px; color: var(--color-neutral-900);">
            Temukan Kamar Kos Siap Huni di {{ $kosSettings['name'] ?? 'KosFly Residence' }}
          </h1>

          <p class="hero-lead" style="font-size: 15px; margin-bottom: 24px; line-height: 1.6; color: var(--color-neutral-700);">
            Kamar furnished lengkap (AC, WiFi kencang, kasur empuk, kamar mandi dalam). Lingkungan tenang, bersih, dan aman 24 jam. Booking online sekarang dan langsung siap check-in!
          </p>

          <div class="hero-actions" style="margin-bottom: 28px;">
            <a class="btn btn-primary" href="#kamar-tersedia" style="font-size: 14px; padding: 10px 22px;">
              Lihat Kamar Kosong ({{ $totalAvailable }})
            </a>
            <a class="btn btn-secondary" href="{{ $waUrl }}" target="_blank" rel="noopener" style="font-size: 14px; padding: 10px 20px; display:inline-flex; align-items:center; gap:8px; background:#ffffff; color:var(--color-neutral-900); border:1px solid var(--color-divider);" title="Tanya Pengelola via WhatsApp">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="#25d366"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
              <span>Tanya Pengelola</span>
            </a>
          </div>

          <div class="hero-points">
            <span class="hero-point">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              Siap Huni (Fully Furnished)
            </span>
            <span class="hero-point">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              Bebas Biaya Siluman
            </span>
            <span class="hero-point">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              Keamanan 24 Jam
            </span>
          </div>
        </div>

        <!-- Visual Hero: Kartu Rekomendasi Kamar Kos Riil dari DB -->
        <div class="hero-visual">
          @if($featuredRoom)
            <div class="card elev-md" style="background: #ffffff; border: 1px solid var(--color-divider); border-radius: 8px; overflow: hidden; padding: 0;">
              <div style="height: 160px; background: linear-gradient(135deg, var(--color-neutral-800), var(--color-neutral-900)); position: relative; display: flex; align-items: center; justify-content: center; color: #ffffff;">
                @if($featuredRoom->image)
                  <img src="{{ asset('storage/' . $featuredRoom->image) }}" alt="Kamar {{ $featuredRoom->room_number }}" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;">
                  <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.35);"></div>
                @endif
                <div style="text-align: center; padding: 16px; position: relative; z-index: 2;">
                  <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-neutral-300); font-weight: 700; margin-bottom: 4px;">Rekomendasi Kamar</div>
                  <div style="font-size: 26px; font-weight: 800; color: #ffffff;">Kamar {{ $featuredRoom->room_number }}</div>
                  <div style="font-size: 13px; color: var(--color-neutral-200); margin-top: 4px;">Lantai {{ $featuredRoom->floor }} • {{ $kosSettings['name'] ?? 'KosFly Residence' }}</div>
                </div>
                <span class="tag tag-accent" style="position: absolute; top: 12px; right: 12px; font-weight: 700; z-index: 2;">
                  Siap Huni
                </span>
              </div>
              <div style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px;">
                  <span class="small muted">Tarif Sewa:</span>
                  <span style="font-size: 22px; font-weight: 800; color: var(--color-neutral-900);">
                    Rp {{ number_format($featuredRoom->price, 0, ',', '.') }}<span class="small muted" style="font-weight: normal; font-size: 13px;"> / bulan</span>
                  </span>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 18px;">
                  @forelse($featuredRoom->facilities->take(4) as $facility)
                    <span class="tag tag-outline" style="font-size: 11px;">{{ $facility->name }}</span>
                  @empty
                    <span class="tag tag-outline" style="font-size: 11px;">Fasilitas Lengkap</span>
                  @endforelse
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                  <a href="{{ route('public.rooms.show', $featuredRoom) }}" class="btn btn-primary btn-block" style="text-align: center; justify-content: center; font-weight: 700;">
                    Lihat Detail &amp; Sewa Kamar Ini
                  </a>
                  <a href="#kamar-tersedia" class="small muted text-center" style="display: block; text-decoration: none; padding-top: 4px;">
                    Lihat semua {{ $totalAvailable }} kamar kosong lainnya &darr;
                  </a>
                </div>
              </div>
            </div>
          @else
            <div class="card elev-md" style="background: #ffffff; border: 1px solid var(--color-divider); border-radius: 8px; overflow: hidden; padding: 0;">
              <div style="height: 160px; background: linear-gradient(135deg, var(--color-neutral-800), var(--color-neutral-900)); position: relative; display: flex; align-items: center; justify-content: center; color: #ffffff;">
                <div style="text-align: center; padding: 16px;">
                  <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-neutral-400); font-weight: 700; margin-bottom: 4px;">Status Hunian</div>
                  <div style="font-size: 22px; font-weight: 800;">{{ $kosSettings['name'] ?? 'KosFly Residence' }}</div>
                  <div style="font-size: 12px; color: var(--color-neutral-300); margin-top: 4px;">{{ $kosSettings['address'] ?? 'Bandung' }}</div>
                </div>
                <span class="tag tag-neutral" style="position: absolute; top: 12px; right: 12px; font-weight: 700;">
                  Penuh
                </span>
              </div>
              <div style="padding: 20px; text-align: center;">
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 8px;">Semua Kamar Saat Ini Terisi</h4>
                <p class="small muted" style="margin-bottom: 16px;">Hubungi pengelola untuk reservasi kamar kosong berikutnya.</p>
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-secondary btn-block" style="justify-content: center;">
                  Hubungi Pengelola via WA
                </a>
              </div>
            </div>
          @endif
        </div>
      </div>
    </section>

    <!-- ===================== BAR STATISTIK / NILAI ===================== -->
    <section style="padding: 10px 0 35px;">
      <div class="container">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="trust-stat-card">
            <div style="font-size: 30px; font-weight: 800; color: var(--color-neutral-900); line-height: 1;">{{ $totalAvailable }}</div>
            <div style="font-weight: 700; margin-top: 6px; font-size: 14px;">Kamar Siap Huni</div>
            <div class="small muted">dari {{ $totalRooms }} total kamar</div>
          </div>
          <div class="trust-stat-card">
            <div style="font-size: 30px; font-weight: 800; color: var(--color-neutral-900); line-height: 1;">100%</div>
            <div style="font-weight: 700; margin-top: 6px; font-size: 14px;">Fully Furnished</div>
            <div class="small muted">Tinggal bawa koper pakaian</div>
          </div>
          <div class="trust-stat-card">
            <div style="font-size: 30px; font-weight: 800; color: var(--color-neutral-900); line-height: 1;">24 Jam</div>
            <div style="font-weight: 700; margin-top: 6px; font-size: 14px;">Akses Aman</div>
            <div class="small muted">CCTV &amp; gerbang kunci mandiri</div>
          </div>
          <div class="trust-stat-card">
            <div style="font-size: 30px; font-weight: 800; color: var(--color-neutral-900); line-height: 1;">2 Menit</div>
            <div style="font-weight: 700; margin-top: 6px; font-size: 14px;">Booking Online</div>
            <div class="small muted">Konfirmasi cepat &amp; resmi</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== SHOWCASE KAMAR TERSEDIA ===================== -->
    <section class="section" id="kamar-tersedia" style="background: var(--color-surface); padding: 50px 0 70px;">
      <div class="container">
        <div class="section-head center">
          <span class="eyebrow">PILIHAN TERBAIK</span>
          <h2 style="font-size: 28px;">Kamar Kosong Siap Huni</h2>
          <p>Seluruh kamar dalam kondisi terawat, bersih, dan siap ditempati hari ini. Pilih nomor kamar favorit Anda!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" style="margin-top: 32px;">
          @forelse($availableRooms as $room)
            <div class="room-card-public">
              <!-- Foto Kamar -->
              <div style="height: 190px; width: 100%; position: relative; background: var(--color-neutral-200); overflow: hidden;">
                @if($room->image)
                  <img src="{{ asset('storage/' . $room->image) }}" alt="Kamar {{ $room->room_number }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                  <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--color-neutral-400);">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                  </div>
                @endif
                <span class="tag tag-accent" style="position: absolute; top: 12px; right: 12px; font-weight: 700;">
                  Tersedia
                </span>
                <span class="tag tag-neutral" style="position: absolute; bottom: 12px; left: 12px; background: rgba(0,0,0,0.65); color: #fff; font-size: 11px;">
                  Lantai {{ $room->floor }}
                </span>
              </div>

              <!-- Rincian Kamar -->
              <div style="padding: 18px; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
                <div>
                  <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                    <h3 style="font-size: 19px; font-weight: 800; margin: 0; color: var(--color-neutral-900);">
                      Kamar {{ $room->room_number }}
                    </h3>
                  </div>

                  <div style="font-size: 19px; font-weight: 800; color: var(--color-neutral-900); margin-bottom: 12px;">
                    Rp {{ number_format($room->price, 0, ',', '.') }}
                    <span class="small muted" style="font-weight: 400; font-size: 13px;">/ bulan</span>
                  </div>

                  <!-- Fasilitas Kamar -->
                  <div style="margin-bottom: 14px;">
                    @if($room->facilities->count())
                      <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        @foreach($room->facilities->take(3) as $fac)
                          <span class="tag tag-outline" style="font-size: 11px; padding: 2px 8px;">{{ $fac->name }}</span>
                        @endforeach
                        @if($room->facilities->count() > 3)
                          <span class="small muted" style="font-size: 11px;">+{{ $room->facilities->count() - 3 }} lainnya</span>
                        @endif
                      </div>
                    @else
                      <span class="small muted" style="font-size: 12px;">Fasilitas standar siap huni</span>
                    @endif
                  </div>
                </div>

                <div style="margin-top: 12px; pt-3; border-top: 1px solid var(--color-divider);">
                  <a href="{{ route('public.rooms.show', $room) }}" class="btn btn-primary btn-block" style="text-align: center; justify-content: center;">
                    Lihat Detail &amp; Sewa
                  </a>
                </div>
              </div>
            </div>
          @empty
            <div class="col-span-3 card elev-sm text-center" style="padding: 48px; border: 1px dashed var(--color-divider); background: #fff;">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 12px; color: var(--color-neutral-400);"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
              <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 6px;">Seluruh Kamar Sedang Penuh</h3>
              <p class="small muted" style="margin: 0 0 16px;">Saat ini semua kamar terisi. Silakan hubungi pengelola untuk masuk ke daftar tunggu / reservasi kamar berikutnya.</p>
              <a href="{{ $waUrl }}" target="_blank" class="btn btn-secondary">Hubungi Pengelola via WA</a>
            </div>
          @endforelse
        </div>

        <div style="text-align: center; margin-top: 36px;">
          <a href="{{ route('public.rooms.index') }}" class="btn btn-secondary" style="font-size: 14px; padding: 10px 24px;">
            Lihat Katalog Semua Kamar &rarr;
          </a>
        </div>
      </div>
    </section>

    <!-- ===================== FASILITAS UNGGULAN ===================== -->
    <section class="section" id="fasilitas" style="padding: 60px 0;">
      <div class="container">
        <div class="section-head center">
          <span class="eyebrow">KENYAMANAN HUNIAN</span>
          <h2 style="font-size: 28px;">Fasilitas Lengkap untuk Kenyamanan Anda</h2>
          <p>Dirancang khusus untuk mahasiswa dan pekerja yang mendambakan istirahat tenang dan lingkungan produktif.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" style="margin-top: 32px;">
          <div class="amenity-card">
            <div style="font-size: 26px;">🛏️</div>
            <div>
              <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Kamar Full Furnished</h4>
              <p class="small muted" style="margin: 0; line-height: 1.5;">Kasur springbed empuk, bantal, lemari pakaian, dan meja kursi belajar.</p>
            </div>
          </div>

          <div class="amenity-card">
            <div style="font-size: 26px;">❄️</div>
            <div>
              <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">AC Dingin &amp; Bersih</h4>
              <p class="small muted" style="margin: 0; line-height: 1.5;">Pendingin ruangan terawat, dingin optimal, dan rutin diservis berkala.</p>
            </div>
          </div>

          <div class="amenity-card">
            <div style="font-size: 26px;">📶</div>
            <div>
              <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">WiFi Fiber Kencang</h4>
              <p class="small muted" style="margin: 0; line-height: 1.5;">Internet stabil tanpa kuota untuk kebutuhan kuliah online, streaming, dan WFA.</p>
            </div>
          </div>

          <div class="amenity-card">
            <div style="font-size: 26px;">🚿</div>
            <div>
              <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Kamar Mandi Dalam</h4>
              <p class="small muted" style="margin: 0; line-height: 1.5;">Dilengkapi shower, kloset duduk, dan suplai air bersih lancar setiap hari.</p>
            </div>
          </div>

          <div class="amenity-card">
            <div style="font-size: 26px;">🍳</div>
            <div>
              <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Dapur Bersama</h4>
              <p class="small muted" style="margin: 0; line-height: 1.5;">Kompor gas, wastafel cuci piring, kulkas bersama, dan dispenser air minum.</p>
            </div>
          </div>

          <div class="amenity-card">
            <div style="font-size: 26px;">🛵</div>
            <div>
              <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Parkir Motor &amp; Mobil</h4>
              <p class="small muted" style="margin: 0; line-height: 1.5;">Area parkir luas dan aman di dalam pagar tertutup khusus penghuni.</p>
            </div>
          </div>

          <div class="amenity-card">
            <div style="font-size: 26px;">🛡️</div>
            <div>
              <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">CCTV &amp; Kunci Mandiri</h4>
              <p class="small muted" style="margin: 0; line-height: 1.5;">Keamanan terpantau CCTV 24 jam dengan gerbang kunci mandiri bebas jam malam kaku.</p>
            </div>
          </div>

          <div class="amenity-card">
            <div style="font-size: 26px;">🔧</div>
            <div>
              <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Teknisi Siap Bantu</h4>
              <p class="small muted" style="margin: 0; line-height: 1.5;">Jika ada kran rusak atau lampu mati, cukup lapor di web dan teknisi segera datang.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== ALUR MUDAH MENYEWA ===================== -->
    <section class="section" id="cara-sewa" style="background: var(--color-surface); padding: 60px 0;">
      <div class="container">
        <div class="section-head center">
          <span class="eyebrow">PROSES PRAKTIS</span>
          <h2 style="font-size: 28px;">3 Langkah Mudah Menyewa Kamar Kos</h2>
          <p>Tanpa ribet bolak-balik survei manual. Reservasi kamar impian Anda langsung dari layar HP.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" style="margin-top: 36px;">
          <div class="step-card">
            <div class="step-number">1</div>
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">Pilih Kamar</h3>
            <p class="small muted" style="margin: 0; line-height: 1.6;">
              Pilih nomor kamar kosong yang sesuai dengan selera lantai dan budget bulanan Anda di daftar kamar.
            </p>
          </div>

          <div class="step-card">
            <div class="step-number">2</div>
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">Isi Data &amp; Booking</h3>
            <p class="small muted" style="margin: 0; line-height: 1.6;">
              Lengkapi formulir online (nama, nomor WA, NIK, dan tanggal mulai sewa) hanya dalam waktu 2 menit.
            </p>
          </div>

          <div class="step-card">
            <div class="step-number">3</div>
            <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 6px;">Bayar &amp; Check-In</h3>
            <p class="small muted" style="margin: 0; line-height: 1.6;">
              Dapatkan invoice resmi, konfirmasi pembayaran, dan ambil kunci kamar Anda. Selamat menikmati hunian baru!
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== LOKASI & LINGKUNGAN ===================== -->
    <section class="section" id="lokasi" style="padding: 60px 0;">
      <div class="container">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
          <div>
            <span class="eyebrow">LOKASI STRATEGIS</span>
            <h2 style="font-size: 28px; margin-bottom: 14px;">Dekat ke Mana Saja</h2>
            <p class="muted" style="margin-bottom: 22px; line-height: 1.6;">
              Berlokasi di kawasan yang tenang namun sangat dekat dengan pusat aktivitas, fasilitas umum, dan sentra kuliner.
            </p>

            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 22px;">
              <div style="display: flex; gap: 12px; align-items: center;">
                <span style="font-size: 18px;">🎓</span>
                <span style="font-size: 14px; font-weight: 600;">5 Menit ke Kampus / Kawasan Pendidikan</span>
              </div>
              <div style="display: flex; gap: 12px; align-items: center;">
                <span style="font-size: 18px;">🛒</span>
                <span style="font-size: 14px; font-weight: 600;">2 Menit ke Minimarket (Indomaret / Alfamart)</span>
              </div>
              <div style="display: flex; gap: 12px; align-items: center;">
                <span style="font-size: 18px;">🍜</span>
                <span style="font-size: 14px; font-weight: 600;">1 Menit ke Sentra Kuliner, Warteg &amp; Kafe</span>
              </div>
              <div style="display: flex; gap: 12px; align-items: center;">
                <span style="font-size: 18px;">🏥</span>
                <span style="font-size: 14px; font-weight: 600;">10 Menit ke Klinik / Rumah Sakit Terdekat</span>
              </div>
              <div style="display: flex; gap: 12px; align-items: center;">
                <span style="font-size: 18px;">🚆</span>
                <span style="font-size: 14px; font-weight: 600;">Akses Transportasi Umum Mudah</span>
              </div>
            </div>

            <div class="card p-3" style="background: var(--color-surface); border: 1px solid var(--color-divider);">
              <div class="small font-semibold text-neutral-800">Alamat Gedung Kos:</div>
              <div class="small muted">{{ $kosSettings['address'] ?? 'Jl. Sukabirus No. 12, Dayeuhkolot, Bandung' }}</div>
            </div>
          </div>

          <div>
            <!-- Wadah Peta Leaflet Interaktif -->
            <div class="card elev-sm" style="background: #fff; border: 1px solid var(--color-divider); overflow: hidden; margin-bottom: 16px;">
              <div id="kosPublicMap" style="height: 320px; width: 100%; z-index: 1;"></div>
              <div style="padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; background: var(--color-surface); border-top: 1px solid var(--color-divider);">
                <div style="font-size: 13px; font-weight: 600; color: var(--color-neutral-800);">
                  📍 {{ $kosSettings['name'] ?? 'KosFly Residence' }}
                </div>
                @php
                  $latVal = $kosSettings['latitude'] ?? -6.9740;
                  $lngVal = $kosSettings['longitude'] ?? 107.6305;
                  $gmapsUrl = "https://www.google.com/maps/dir/?api=1&destination={$latVal},{$lngVal}";
                @endphp
                <a href="{{ $gmapsUrl }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm" style="font-size: 12px; gap: 6px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                  Petunjuk Arah (Google Maps) &rarr;
                </a>
              </div>
            </div>

            <!-- Kartu Survei Kamar -->
            <div class="card elev-sm" style="padding: 20px; background: #fff; border: 1px solid var(--color-divider); text-align: center;">
              <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 4px;">Ingin Survei Langsung ke Lokasi?</h3>
              <p class="small muted" style="margin-bottom: 14px;">
                Jadwalkan kunjungan survei kamar kos bersama pengelola. Kami siap menyambut Anda melihat langsung suasana kamar.
              </p>
              <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-secondary btn-block" style="justify-content: center; font-weight: 700;">
                Jadwalkan Survei via WhatsApp
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== FAQ SEPUTAR SEWA ===================== -->
    <section class="section" id="faq" style="background: var(--color-surface); padding: 60px 0;">
      <div class="container" style="max-width: 800px;">
        <div class="section-head center">
          <span class="eyebrow">PERTANYAAN UMUM</span>
          <h2 style="font-size: 28px;">Pertanyaan yang Sering Diajukan</h2>
          <p>Informasi seputar ketentuan sewa, pembayaran, dan kehidupan sehari-hari di KosFly.</p>
        </div>

        <div style="margin-top: 32px;">
          <div class="faq-item">
            <div class="faq-question">
              <span>Apakah ada jam malam di KosFly?</span>
              <span>+</span>
            </div>
            <div class="faq-answer">
              Setiap penghuni diberikan akses kunci/gerbang mandiri, sehingga Anda dapat beraktivitas secara fleksibel dengan tetap menjaga ketertiban dan ketenangan lingkungan kos bagi penghuni lainnya.
            </div>
          </div>

          <div class="faq-item">
            <div class="faq-question">
              <span>Apakah biaya sewa bulanan sudah termasuk air dan internet?</span>
              <span>+</span>
            </div>
            <div class="faq-answer">
              Ya, fasilitas air bersih dan koneksi internet WiFi fiber berkecepatan tinggi sudah termasuk dalam tarif sewa bulanan tanpa biaya tambahan.
            </div>
          </div>

          <div class="faq-item">
            <div class="faq-question">
              <span>Bagaimana cara melakukan pembayaran uang sewa?</span>
              <span>+</span>
            </div>
            <div class="faq-answer">
              Pembayaran dapat dilakukan dengan mudah secara online (QRIS, GoPay, OVO, ShopeePay, serta Virtual Account berbagai bank) melalui sistem invoice kami. Setiap pembayaran akan langsung diverifikasi otomatis dan diterbitkan kuitansi lunas resmi yang dapat diunduh kapan saja.
            </div>
          </div>

          <div class="faq-item">
            <div class="faq-question">
              <span>Bagaimana jika ada fasilitas kamar yang mengalami kerusakan?</span>
              <span>+</span>
            </div>
            <div class="faq-answer">
              Anda cukup login ke akun penghuni dan membuat tiket perbaikan di menu Keluhan. Tim teknisi pengelola kos akan segera datang mengecek dan memperbaikinya.
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== CTA BANNER ===================== -->
    <section style="background: var(--color-neutral-900); color: #fff; padding: 50px 0; text-align: center;">
      <div class="container" style="max-width: 680px;">
        <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 10px; color: #fff;">
          Kamar Kosong Sangat Terbatas!
        </h2>
        <p style="color: var(--color-neutral-300); font-size: 14px; margin-bottom: 22px; line-height: 1.6;">
          Jangan lewatkan kesempatan menempati kamar kos yang nyaman dan strategis. Amankan kamar Anda sebelum kehabisan.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
          <a href="#kamar-tersedia" class="btn btn-primary" style="font-size: 14px; padding: 10px 24px;">
            Pilih Kamar Sekarang
          </a>
          <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-secondary" style="font-size: 14px; padding: 10px 22px; color: var(--color-neutral-900); background: #ffffff; border: 1px solid rgba(255,255,255,0.9); display: inline-flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#25d366"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
            <span>Chat via WhatsApp</span>
          </a>
        </div>
      </div>
    </section>

  </main>

  <!-- ===================== FOOTER ===================== -->
  <footer class="site-footer" style="background: #ffffff; border-top: 1px solid var(--color-divider); padding: 50px 0 28px;">
    <div class="container">
      <div class="footer-grid">

        <!-- Kolom 1: Brand & Profil Singkat -->
        <div class="footer-brand" style="max-width: 340px;">
          <a class="brand" href="{{ route('home') }}" aria-label="KosFly — Beranda" style="margin-bottom: 12px; display: inline-block;">
            <span class="brand-mark" aria-hidden="true">K</span>
            Kos<span class="brand-accent">Fly</span>
          </a>
          <p style="font-size: 14px; color: var(--color-text-muted); line-height: 1.6; margin: 0 0 16px;">
            Hunian kos modern, nyaman, dan strategis dengan sistem reservasi instan, pembayaran tagihan transparan, dan layanan penghuni terpercaya.
          </p>
          <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; color: var(--color-text-muted);">
            <div style="display: flex; align-items: flex-start; gap: 8px;">
              <span style="color: var(--color-primary); flex-shrink: 0; font-size: 15px; line-height: 1.2;">📍</span>
              <span>{{ $kosSettings['address'] ?? 'Jl. Sukabirus No. 12, Dayeuhkolot, Bandung' }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="color: var(--color-primary); flex-shrink: 0; font-size: 15px;">📞</span>
              <span>{{ $kosSettings['phone'] ?? '0812-3456-7890' }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="color: #22c55e; flex-shrink: 0; font-size: 15px;">💬</span>
              <span>Layanan Respon: Setiap Hari 08.00 – 21.00 WIB</span>
            </div>
          </div>
        </div>

        <!-- Kolom 2: Jelajahi Kos -->
        <nav class="footer-col" aria-label="Navigasi jelajahi kos">
          <h4 style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-soft); margin-bottom: 16px;">
            Jelajahi Kos
          </h4>
          <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
            <li><a href="#kamar-tersedia" style="color: var(--color-text-muted); text-decoration: none;">Pilihan Kamar Tersedia</a></li>
            <li><a href="#fasilitas" style="color: var(--color-text-muted); text-decoration: none;">Fasilitas Gedung &amp; Kamar</a></li>
            <li><a href="#lokasi" style="color: var(--color-text-muted); text-decoration: none;">Akses &amp; Lokasi Sekitar</a></li>
            <li><a href="#faq" style="color: var(--color-text-muted); text-decoration: none;">Tanya Jawab (FAQ)</a></li>
          </ul>
        </nav>

        <!-- Kolom 3: Layanan & Survei -->
        <nav class="footer-col" aria-label="Navigasi layanan dan bantuan">
          <h4 style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-soft); margin-bottom: 16px;">
            Layanan &amp; Bantuan
          </h4>
          <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
            <li><a href="{{ $waUrl }}" target="_blank" rel="noopener" style="color: var(--color-text-muted); text-decoration: none;">Jadwalkan Survei Kamar</a></li>
            <li><a href="{{ $gmapsUrl ?? '#' }}" target="_blank" rel="noopener" style="color: var(--color-text-muted); text-decoration: none;">Petunjuk Arah (Google Maps)</a></li>
            <li><a href="{{ $waUrl }}" target="_blank" rel="noopener" style="color: var(--color-text-muted); text-decoration: none;">Hubungi Pengelola via WhatsApp</a></li>
            <li><a href="{{ route('login') }}" style="color: var(--color-text-muted); text-decoration: none;">Tiket Bantuan Perbaikan</a></li>
          </ul>
        </nav>

        <!-- Kolom 4: Akun & Portal Penghuni -->
        <nav class="footer-col" aria-label="Navigasi akun penghuni">
          <h4 style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-soft); margin-bottom: 16px;">
            Portal Penghuni
          </h4>
          <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
            @auth
              <li><a href="{{ route('dashboard') }}" style="color: var(--color-primary); font-weight: 600; text-decoration: none;">Buka Dashboard Saya &rarr;</a></li>
              <li><a href="{{ route('profile.edit') }}" style="color: var(--color-text-muted); text-decoration: none;">Pengaturan Profil</a></li>
            @else
              <li><a href="{{ route('login') }}" style="color: var(--color-text-muted); text-decoration: none;">Masuk Akun Penghuni</a></li>
              <li><a href="{{ route('register') }}" style="color: var(--color-text-muted); text-decoration: none;">Daftar Akun Baru</a></li>
              <li><a href="{{ route('password.request') }}" style="color: var(--color-text-muted); text-decoration: none;">Lupa Kata Sandi?</a></li>
            @endauth
          </ul>
        </nav>

      </div>

      <!-- Bagian Bawah Footer (Copyright & Label) -->
      <div class="footer-bottom" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--color-divider); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; font-size: 13px; color: var(--color-text-soft);">
        <span>&copy; {{ date('Y') }} {{ $kosSettings['name'] ?? 'KosFly Residence' }}. Hak Cipta Dilindungi.</span>
        <span>Sistem Manajemen Hunian Kos Modern &amp; Terpercaya</span>
      </div>
    </div>
  </footer>

  <!-- ===================== FLOATING WHATSAPP BUTTON (HANYA LOGO WA) ===================== -->
  <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="wa-float-btn" title="Tanya Pengelola via WhatsApp" aria-label="Tanya Pengelola via WhatsApp">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
  </a>

  <script>
    // Toggle Mobile Drawer
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

      // Close when clicking any link inside drawer (smooth jump / navigation)
      navDrawer.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeNav);
      });

      // Close on Escape key
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.body.classList.contains('nav-open')) {
          closeNav();
        }
      });
    }
  </script>

  <!-- Leaflet JS CDN -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const mapContainer = document.getElementById('kosPublicMap');
      if (!mapContainer) return;

      const lat = {{ (float) ($kosSettings['latitude'] ?? -6.9740) }};
      const lng = {{ (float) ($kosSettings['longitude'] ?? 107.6305) }};
      const zoom = {{ (int) ($kosSettings['map_zoom'] ?? 16) }};
      const kosName = @json($kosSettings['name'] ?? 'KosFly Residence');
      const kosAddress = @json($kosSettings['address'] ?? 'Bandung');

      const map = L.map('kosPublicMap', {
        scrollWheelZoom: false
      }).setView([lat, lng], zoom);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }).addTo(map);

      // Marker kustom / pin lokasi
      const marker = L.marker([lat, lng]).addTo(map);
      marker.bindPopup(`
        <div style="font-family: inherit; font-size: 13px; line-height: 1.4; padding: 4px;">
          <strong style="color: #0f172a; font-size: 14px; display: block; margin-bottom: 2px;">${kosName}</strong>
          <span style="color: #64748b; font-size: 12px; display: block; margin-bottom: 8px;">${kosAddress}</span>
          <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" rel="noopener" style="display: inline-block; background: #0f172a; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 11px; text-decoration: none; font-weight: 600;">
            Buka Navigasi Rute &rarr;
          </a>
        </div>
      `).openPopup();
    });
  </script>
</body>
</html>
