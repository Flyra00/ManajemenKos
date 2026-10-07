<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pilihan Kamar Kos — KosFly</title>
  <meta name="description" content="Daftar kamar kos yang tersedia untuk disewa di KosFly. Fasilitas lengkap, lokasi strategis, dan proses booking mudah.">
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
          <li><a href="{{ route('public.rooms.index') }}" class="active" style="font-weight:700; color:var(--color-accent)">Pilihan Kamar</a></li>
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
      <a class="drawer-link active" href="{{ route('public.rooms.index') }}" style="font-weight:700; color:var(--color-accent)">Pilihan Kamar
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

      <!-- Breadcrumb & Header Halaman -->
      <div style="margin-bottom: 32px;">
        <nav class="breadcrumb" style="margin-bottom: 12px;" aria-label="Breadcrumb">
          <a href="{{ route('home') }}">Beranda</a>
          <span class="sep">/</span>
          <span class="current">Pilihan Kamar Tersedia</span>
        </nav>
        <h1 style="font-size: 32px; font-weight: 800; margin: 0 0 8px; color: var(--color-neutral-900);">
          Temukan Kamar Kos Nyaman Anda
        </h1>
        <p class="muted" style="margin: 0; font-size: 15px;">
          Pilih kamar yang sesuai dengan kebutuhan Anda, periksa spesifikasi fasilitas, dan ajukan sewa langsung secara online.
        </p>
      </div>

      <!-- Toolbar Filter & Pencarian -->
      <div class="card elev-sm" style="padding: 16px 20px; margin-bottom: 32px; border: 1px solid var(--color-divider);">
        <form method="GET" action="{{ route('public.rooms.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
          
          <div style="flex: 2; min-width: 200px;">
            <input class="input" type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor kamar...">
          </div>

          <div style="flex: 1; min-width: 140px;">
            <select class="input" name="floor">
              <option value="semua">Semua Lantai</option>
              @foreach($floors as $fl)
                <option value="{{ $fl }}" @selected(request('floor') == $fl)>Lantai {{ $fl }}</option>
              @endforeach
            </select>
          </div>

          <div style="flex: 1; min-width: 160px;">
            <select class="input" name="max_price">
              <option value="">Semua Harga</option>
              <option value="1000000" @selected(request('max_price') == '1000000')>Maks. Rp 1.000.000</option>
              <option value="1500000" @selected(request('max_price') == '1500000')>Maks. Rp 1.500.000</option>
              <option value="2000000" @selected(request('max_price') == '2000000')>Maks. Rp 2.000.000</option>
              <option value="3000000" @selected(request('max_price') == '3000000')>Maks. Rp 3.000.000</option>
            </select>
          </div>

          <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['q', 'floor', 'max_price']))
              <a href="{{ route('public.rooms.index') }}" class="btn btn-secondary">Reset</a>
            @endif
          </div>
        </form>
      </div>

      <!-- Grid Daftar Kamar -->
      <div class="grid-3" style="gap: 24px;">
        @forelse($rooms as $room)
          <div class="card elev-sm" style="overflow: hidden; display: flex; flex-direction: column; border: 1px solid var(--color-divider); border-radius: 8px;">
            
            <!-- Foto Kamar -->
            <div style="position: relative; height: 190px; background: var(--color-neutral-100); overflow: hidden;">
              @if($room->image)
                <img src="{{ asset('storage/' . $room->image) }}" alt="Foto Kamar {{ $room->room_number }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=600&q=80';" style="width: 100%; height: 100%; object-fit: cover;">
              @else
                <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=600&q=80" alt="Foto Kamar {{ $room->room_number }}" style="width: 100%; height: 100%; object-fit: cover;">
              @endif
              <span class="tag tag-accent" style="position: absolute; top: 12px; right: 12px; font-weight: 700;">
                Tersedia
              </span>
            </div>

            <!-- Konten Informasi Kamar -->
            <div style="padding: 20px; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
              <div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                  <h3 style="font-size: 20px; font-weight: 800; margin: 0; color: var(--color-neutral-900);">
                    Kamar {{ $room->room_number }}
                  </h3>
                  <span class="small muted" style="font-weight: 600;">Lantai {{ $room->floor }}</span>
                </div>

                <div style="font-size: 18px; font-weight: 800; color: var(--color-neutral-900); margin-bottom: 12px;">
                  Rp {{ number_format($room->price, 0, ',', '.') }}
                  <span class="small muted" style="font-weight: 400; font-size: 13px;">/ bulan</span>
                </div>

                <!-- Fasilitas Kamar -->
                <div style="margin-bottom: 16px;">
                  @if($room->facilities->count())
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                      @foreach($room->facilities->take(3) as $fac)
                        <span class="tag tag-outline" style="font-size: 11px; padding: 2px 8px;">{{ $fac->name }}</span>
                      @endforeach
                      @if($room->facilities->count() > 3)
                        <span class="small muted">+{{ $room->facilities->count() - 3 }} lainnya</span>
                      @endif
                    </div>
                  @else
                    <span class="small muted">Fasilitas standar</span>
                  @endif
                </div>
              </div>

              <!-- Tombol Detail & Sewa -->
              <div style="margin-top: 16px; pt-3; border-top: 1px solid var(--color-divider);">
                <a href="{{ route('public.rooms.show', $room) }}" class="btn btn-primary btn-block" style="justify-content: center; text-align: center;">
                  Lihat Detail & Sewa
                </a>
              </div>
            </div>

          </div>
        @empty
          <div class="col-span-3 card elev-sm text-center" style="padding: 48px; border: 1px dashed var(--color-divider);">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 12px; color: var(--color-neutral-400);"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
            <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 6px;">Tidak ada kamar yang cocok</h3>
            <p class="small muted" style="margin: 0 0 16px;">Silakan coba atur kembali filter lantai atau kata kunci pencarian Anda.</p>
            <a href="{{ route('public.rooms.index') }}" class="btn btn-secondary">Reset Pencarian</a>
          </div>
        @endforelse
      </div>

      <!-- Pagination -->
      @if($rooms->hasPages())
        <div style="margin-top: 40px; display: flex; justify-content: center;">
          {{ $rooms->links() }}
        </div>
      @endif

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
  </script>
</body>
</html>

