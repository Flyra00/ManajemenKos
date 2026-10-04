{{-- ============================================================================
    rooms/index.blade.php — Master Kamar (Room CRUD — Index)

    Siap dipindah ke Laravel: resources/views/rooms/index.blade.php
    Controller (dikerjakan terpisah) diharapkan mengirim:
      $rooms      → Room::with('facilities')->paginate(10)   (atau Collection)
      $facilities → Facility::all()                          (untuk menu fasilitas)

    Route yang dipakai (Resource Controller):
      rooms.index / rooms.create / rooms.store / rooms.show
      rooms.edit / rooms.update / rooms.destroy

    Catatan:
      - Sidebar/topbar statis (konvensi KosFly) — saat integrasi penuh,
        potong menjadi components/sidebar.blade.php & layouts.
      - Link menu lain masih href="#" — ganti dengan route masing-masing,
        mis. route('tenants.index').
    ============================================================================ --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f3f2f2">
  <meta name="mobile-web-app-capable" content="yes">
  <title>Kamar — KosFly Admin</title>
  {{-- Saat integrasi Laravel penuh: {{ asset('css/style.css') }} --}}
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
      <!-- data-role = daftar role yang boleh melihat menu ini (preview saja) -->
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
            <span class="current">Master Kamar</span>
          </nav>
          <h2 class="page-title">Kelola Kamar</h2>
          <p class="page-sub">Kelola data kamar kos, harga, status, fasilitas, dan foto kamar.</p>
        </div>
        <div class="flex head-actions">
          <a href="{{ route('rooms.create') }}" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Kamar
          </a>
        </div>
      </section>

      {{-- Flash message (session('success') / session('error')) --}}
      @if(session('success'))
        <div class="alert alert-success" role="status">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif
      @if(session('error'))
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      {{-- Statistik kamar. Kompatibel untuk $rooms Collection maupun paginator.
           Bila dihitung di controller, ganti dengan variabel yang dikirim
           (mis. $stats['total'], $stats['kosong'], …). --}}
      @php
        $roomList = $rooms instanceof \Illuminate\Pagination\AbstractPaginator ? $rooms->getCollection() : ($rooms ?? collect());
        $statTotal   = $rooms instanceof \Illuminate\Pagination\AbstractPaginator ? $rooms->total() : $roomList->count();
        $statKosong  = $roomList->where('status', 'Kosong')->count();
        $statTerisi  = $roomList->where('status', 'Terisi')->count();
        $statPerbaikan = $roomList->where('status', 'Perbaikan')->count();
        $statLantai  = $roomList->pluck('floor')->unique()->count();
      @endphp
      <section class="grid-4" aria-label="Statistik kamar">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Kamar</div>
          <div class="stat-value">{{ $statTotal }}</div>
          <div class="stat-sub muted">{{ $statLantai }} lantai</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Kamar Kosong</div>
          <div class="stat-value">{{ $statKosong }}</div>
          <div class="stat-sub muted">tersedia untuk disewa</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Kamar Terisi</div>
          <div class="stat-value">{{ $statTerisi }}</div>
          <div class="stat-sub muted">sedang dihuni</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Perbaikan</div>
          <div class="stat-value">{{ $statPerbaikan }}</div>
          <div class="stat-sub muted">sedang dalam perbaikan</div>
        </div>
      </section>

      <!-- Daftar kamar -->
      <section class="card elev-sm section-card" aria-label="Daftar kamar">
        <div class="card-head">
          <h3 class="card-title">Daftar Kamar</h3>
          <span class="small muted">{{ $statTotal }} kamar terdaftar</span>
        </div>

        {{-- Filter dikirim via GET agar Laravel menangani search/filter di server.
             Nama param: search, floor, status, is_active. --}}
        <form method="GET" action="{{ route('rooms.index') }}" class="filter-bar" role="search">
          <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nomor kamar…" aria-label="Cari nomor kamar">
          <select class="input" name="floor" aria-label="Filter lantai">
            <option value="">Semua Lantai</option>
            @for($i = 1; $i <= 3; $i++)
              <option value="{{ $i }}" @selected(request('floor') !== null && (string) request('floor') === (string) $i)>Lantai {{ $i }}</option>
            @endfor
          </select>
          <select class="input" name="status" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="Kosong" @selected(request('status') === 'Kosong')>Kosong</option>
            <option value="Terisi" @selected(request('status') === 'Terisi')>Terisi</option>
            <option value="Perbaikan" @selected(request('status') === 'Perbaikan')>Perbaikan</option>
          </select>
          <select class="input" name="is_active" aria-label="Filter status aktif">
            <option value="">Semua</option>
            <option value="1" @selected(request('is_active') === '1')>Aktif</option>
            <option value="0" @selected(request('is_active') === '0')>Nonaktif</option>
          </select>
          <button type="submit" class="btn btn-secondary">Terapkan</button>
          @if(request()->hasAny(['search', 'floor', 'status', 'is_active']))
            <a href="{{ route('rooms.index') }}" class="btn btn-ghost">Reset</a>
          @endif
        </form>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr>
                <th>Foto</th>
                <th>No. Kamar</th>
                <th>Lantai</th>
                <th>Harga / Bulan</th>
                <th>Status</th>
                <th>Aktif</th>
                <th>Fasilitas</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rooms as $room)
                <tr>
                  <td>
                    @if($room->image)
                      <div class="room-thumb">
                        <img src="{{ asset('storage/' . $room->image) }}" alt="Foto kamar {{ $room->room_number }}" loading="lazy">
                      </div>
                    @else
                      <div class="room-thumb room-thumb-empty" title="Belum ada foto — tambahkan lewat Edit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <span class="thumb-empty-txt">Tanpa Foto</span>
                      </div>
                    @endif
                  </td>
                  <td style="font-weight:600">{{ $room->room_number }}</td>
                  <td class="muted">Lantai {{ $room->floor }}</td>
                  <td>Rp {{ number_format($room->price_per_month, 0, ',', '.') }}<span class="muted small">/bln</span></td>
                  <td>
                    <span class="tag {{ $room->status === 'Terisi' ? 'tag-neutral' : ($room->status === 'Perbaikan' ? 'tag-accent' : 'tag-outline') }}">{{ $room->status }}</span>
                  </td>
                  <td>
                    @if($room->is_active)
                      <span class="tag tag-neutral">Aktif</span>
                    @else
                      <span class="tag tag-outline">Nonaktif</span>
                    @endif
                  </td>
                  <td>
                    @if($room->facilities->count())
                      <div class="facility-pills facility-pills--sm">
                        @foreach($room->facilities->take(3) as $facility)
                          <span class="facility-pill">{{ $facility->name }}</span>
                        @endforeach
                        @if($room->facilities->count() > 3)
                          <span class="facility-more">+{{ $room->facilities->count() - 3 }} lagi</span>
                        @endif
                      </div>
                    @else
                      <span class="muted small">—</span>
                    @endif
                  </td>
                  <td>
                    <div class="row-actions">
                      <a class="btn btn-secondary" href="{{ route('rooms.show', $room) }}" title="Detail">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                      </a>
                      <a class="btn btn-secondary" href="{{ route('rooms.edit', $room) }}" title="Edit">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                      </a>
                      <button type="button" class="btn btn-ghost" data-action="room-delete-open"
                              data-url="{{ route('rooms.destroy', $room) }}" data-name="{{ $room->room_number }}" title="Hapus">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11v6M14 11v6"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8">
                    <div class="empty-state">
                      <span class="es-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
                      </span>
                      <h4>Belum ada kamar</h4>
                      <p>Belum ada data kamar yang tersedia.</p>
                      <a href="{{ route('rooms.create') }}" class="btn btn-primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        Tambah kamar pertama
                      </a>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination Laravel — {{ $rooms->links() }} (bukan dummy JS) --}}
        @if($rooms instanceof \Illuminate\Pagination\AbstractPaginator)
          {{ $rooms->links() }}
        @endif
      </section>

    </main>
  </div>
</div>

<!-- Backdrop navigasi mobile -->
<div class="nav-backdrop" id="navBackdrop"></div>

<!-- ============================================================
     MODAL KONFIRMASI HAPUS — form dikirim ke rooms.destroy
     ============================================================ -->
<div class="dialog-backdrop" id="modalConfirm" hidden data-close="1">
  <div class="dialog">
    <div class="dialog-title">Hapus Kamar?</div>
    <p class="small muted" style="margin:0;line-height:1.6">
      Apakah Anda yakin ingin menghapus kamar <b id="delRoomName">—</b>?
      Tindakan ini tidak dapat dibatalkan.
    </p>
    <form method="POST" id="formDelete" action="">
      @csrf
      @method('DELETE')
      <div class="dialog-actions" style="margin-top:16px">
        <button type="button" class="btn btn-secondary" data-action="close-dialog">Batal</button>
        <button type="submit" class="btn btn-primary">Hapus</button>
      </div>
    </form>
  </div>
</div>

{{-- Toast notifications --}}
<div id="toastRoot" aria-live="polite"></div>

<script src="{{ asset('js/app.js') }}"></script>
<script>
  // UI kecil halaman ini saja (tidak menyentuh CRUD/data):
  // buka modal konfirmasi hapus + isi action form & nama kamar.
  (function () {
    var modal = document.getElementById('modalConfirm');
    if (!modal) return;
    var form = document.getElementById('formDelete');
    var nameEl = document.getElementById('delRoomName');

    function open(el) {
      if (form) form.action = el.getAttribute('data-url') || '';
      if (nameEl) nameEl.textContent = el.getAttribute('data-name') || '—';
      modal.hidden = false;
    }
    function close() { modal.hidden = true; }

    document.addEventListener('click', function (e) {
      var opener = e.target.closest && e.target.closest('[data-action="room-delete-open"]');
      if (opener) { open(opener); return; }
      if (e.target.closest && e.target.closest('[data-action="close-dialog"]')) { close(); return; }
      if (e.target.getAttribute && e.target.getAttribute('data-close') === '1') close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hidden) close();
    });
  })();
</script>
</body>
</html>
