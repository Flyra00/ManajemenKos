{{-- ============================================================================
    rooms/show.blade.php — Master Kamar (Room CRUD — Show / Detail)

    Siap dipindah ke Laravel: resources/views/rooms/show.blade.php
    Controller diharapkan mengirim: $room (Room dengan relasi facilities).
    Tidak ada form edit di halaman ini — tombol Edit mengarah ke rooms.edit.
    ============================================================================ --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f3f2f2">
  <meta name="mobile-web-app-capable" content="yes">
  <title>{{ $room->room_number }} — KosFly Admin</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body data-page="rooms">

<div class="layout">

  <!-- ============================================================
       SIDEBAR — struktur statis. Saat integrasi Laravel, potong
       blok ini menjadi components/sidebar.blade.php.
       ============================================================ -->
  <aside class="sidebar" id="sidebar">
    <div class="brand">Kos<span class="brand-accent">Fly</span></div>

    <nav class="side-nav" aria-label="Menu utama KosFly">
      <a class="nav-btn" href="#" data-role="owner admin tenant">{{-- route('dashboard') --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h7v9H3z"/><path d="M14 3h7v5h-7z"/><path d="M14 12h7v9h-7z"/><path d="M3 16h7v5H3z"/></svg>
        <span>Dashboard</span>
      </a>
      <a class="nav-btn active" href="{{ route('rooms.index') }}" data-role="owner admin">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
        <span>Kelola Kamar</span>
      </a>
      <a class="nav-btn" href="#" data-role="owner admin">{{-- route('tenants.index') --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Penghuni</span>
      </a>
      <a class="nav-btn" href="#" data-role="owner admin">{{-- route('leases.index') --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
        <span>Kontrak Sewa</span>
      </a>
      <a class="nav-btn" href="#" data-role="owner admin">{{-- route('payments.index') --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h20v12H2z"/><path d="M2 11h20"/><path d="M6 15h4"/></svg>
        <span>Pembayaran</span>
      </a>
      <a class="nav-btn" href="#" data-role="owner admin tenant">{{-- route('maintenance.index') --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span>Maintenance</span>
      </a>
      <a class="nav-btn" href="#" data-role="owner admin">{{-- route('expenses.index') --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 7l-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/></svg>
        <span>Pengeluaran</span>
      </a>
      <a class="nav-btn" href="#" data-role="owner admin">{{-- route('reports.index') --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15l2 2 4-4"/></svg>
        <span>Laporan</span>
      </a>
      <a class="nav-btn" href="#" data-role="owner admin">{{-- route('facilities.index') --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><path d="M12 20h.01"/></svg>
        <span>Fasilitas</span>
      </a>
      <a class="nav-btn" href="#" data-role="owner admin">{{-- route('settings.index') --}}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        <span>Pengaturan</span>
      </a>
    </nav>

    <div class="side-foot" id="sideFoot">KosFly Admin v2.0</div>
  </aside>

  <!-- ============================ KONTEN ============================ -->
  <div class="main">

    <!-- ============================ TOPBAR ============================ -->
    <header class="topbar">
      <button class="btn btn-icon btn-secondary hamburger" id="hamburger" data-action="toggle-nav" title="Menu" aria-label="Buka menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
      </button>

      <div class="searchbox">
        <svg class="search-ic" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input id="globalSearch" class="input" type="search" name="globalSearch" placeholder="Cari kamar, penghuni, pembayaran…" autocomplete="off" aria-label="Cari">
      </div>

      <div class="topbar-right">
        <div class="notif">
          <button class="btn btn-icon btn-secondary" id="notifBtn" data-action="toggle-notif" title="Notifikasi" aria-label="Notifikasi">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
          </button>
          <span class="notif-dot" id="notifDot" hidden></span>
          <div class="notif-menu" id="notifMenu"></div>
        </div>

        <div class="userchip" id="userChip" data-action="toggle-user">
          <div class="avatar" id="userAvatar">AK</div>
          <div class="user-meta">
            <b id="userName">Admin KosFly</b>
            <span id="userRole">Admin</span>
          </div>
          <span class="user-caret">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </span>
          <div class="user-menu" id="userMenu">
            <a href="#">Profil</a>{{-- route('profile') --}}
            <a href="#">Pengaturan</a>{{-- route('settings') --}}
            <button type="button">Keluar</button>
          </div>
        </div>
      </div>
    </header>

    <!-- ============================ KONTEN HALAMAN ============================ -->
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('rooms.index') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('rooms.index') }}">Master Kamar</a>
            <span class="sep">/</span>
            <span class="current">{{ $room->room_number }}</span>
          </nav>
          <h2 class="page-title">Detail Kamar {{ $room->room_number }}</h2>
          <p class="page-sub">Informasi lengkap kamar kos, status, fasilitas, dan foto.</p>
        </div>
        <div class="flex head-actions">
          <a href="{{ route('rooms.edit', $room) }}" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
            Edit
          </a>
        </div>
      </section>

      <!-- Hero: foto + informasi utama -->
      <section class="card elev-sm detail-hero" aria-label="Ringkasan kamar">
        <div class="detail-img-lg">
          @if($room->image)
            <img src="{{ asset('storage/' . $room->image) }}" alt="Foto kamar {{ $room->room_number }}">
          @else
            <span class="detail-img-ph">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
              <span>Belum ada foto</span>
            </span>
          @endif
        </div>

        <div>
          <h3 class="room-no-lg">{{ $room->room_number }}</h3>
          <p class="room-price">Rp {{ number_format($room->price_per_month, 0, ',', '.') }} <span>/ bulan</span></p>

          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Lantai</span>
              <span class="info-value">Lantai {{ $room->floor }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <span class="info-value">
                <span class="tag {{ $room->status === 'Terisi' ? 'tag-neutral' : ($room->status === 'Perbaikan' ? 'tag-accent' : 'tag-outline') }}">{{ $room->status }}</span>
              </span>
            </div>
            <div class="info-item">
              <span class="info-label">Status Aktif</span>
              <span class="info-value">
                @if($room->is_active)
                  <span class="tag tag-neutral">Aktif</span>
                @else
                  <span class="tag tag-outline">Nonaktif</span>
                @endif
              </span>
            </div>
            <div class="info-item">
              <span class="info-label">Harga</span>
              <span class="info-value">Rp {{ number_format($room->price_per_month, 0, ',', '.') }} / bulan</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Fasilitas + Deskripsi -->
      <section class="grid-2" aria-label="Fasilitas dan deskripsi kamar">
        <div class="card elev-sm form-card">
          <h3 class="card-title">Fasilitas</h3>
          @forelse($room->facilities as $facility)
            <div class="facility-row">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
              <span>{{ $facility->name }}</span>
            </div>
          @empty
            <p class="small muted" style="margin:0">Belum ada fasilitas untuk kamar ini.</p>
          @endforelse
        </div>

        <div class="card elev-sm form-card">
          <h3 class="card-title">Deskripsi</h3>
          @if($room->description)
            <p style="margin:0;font-size:14px;white-space:pre-line">{{ $room->description }}</p>
          @else
            <p class="small muted" style="margin:0">Belum ada deskripsi untuk kamar ini.</p>
          @endif
        </div>
      </section>

      <div class="form-actions">
        <a href="{{ route('rooms.edit', $room) }}" class="btn btn-primary">Edit Kamar</a>
        <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Kembali</a>
      </div>

    </main>
  </div>
</div>

<!-- Backdrop navigasi mobile -->
<div class="nav-backdrop" id="navBackdrop"></div>

<div id="toastRoot" aria-live="polite"></div>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
