{{-- ============================================================================
    rooms/create.blade.php — Master Kamar (Room CRUD — Create)

    Siap dipindah ke Laravel: resources/views/rooms/create.blade.php
    Form dikirim ke route('rooms.store') dengan enctype="multipart/form-data".
    Field name mengikuti kolom tabel `rooms`:
      room_number, floor, price_per_month, status, is_active,
      facilities[] (pivot room_facilities), description, image

    Controller diharapkan mengirim: $facilities (semua fasilitas untuk checkbox).
    ============================================================================ --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f3f2f2">
  <meta name="mobile-web-app-capable" content="yes">
  <title>Tambah Kamar — KosFly Admin</title>
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
            <span class="current">Tambah Kamar</span>
          </nav>
          <h2 class="page-title">Tambah Kamar</h2>
          <p class="page-sub">Lengkapi data kamar kos, harga, status, fasilitas, dan foto kamar.</p>
        </div>
      </section>

      {{-- Ringkasan error validasi (Laravel Validation) --}}
      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada form. Periksa kembali isian di bawah.</span>
        </div>
      @endif

      <form method="POST" action="{{ route('rooms.store') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <div class="form-layout">

          <!-- ============ FORM UTAMA ============ -->
          <div class="card elev-sm form-card">

            <div class="field">
              <label for="room_number">Nomor Kamar</label>
              <input class="input @error('room_number') is-invalid @enderror" type="text" id="room_number"
                     name="room_number" value="{{ old('room_number') }}" placeholder="contoh: A-01"
                     maxlength="10" required>
              @error('room_number')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="grid-2">
              <div class="field">
                <label for="floor">Lantai</label>
                <select class="input @error('floor') is-invalid @enderror" id="floor" name="floor">
                  <option value="">Pilih lantai</option>
                  @for($i = 1; $i <= 3; $i++)
                    <option value="{{ $i }}" @selected((string) old('floor') === (string) $i)>Lantai {{ $i }}</option>
                  @endfor
                </select>
                @error('floor')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="price_per_month">Harga per Bulan (Rp)</label>
                <input class="input @error('price_per_month') is-invalid @enderror" type="number" id="price_per_month"
                       name="price_per_month" value="{{ old('price_per_month') }}" min="0" step="50000"
                       placeholder="900000" required>
                @error('price_per_month')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="status">Status</label>
                <select class="input @error('status') is-invalid @enderror" id="status" name="status">
                  <option value="Kosong" @selected(old('status') === 'Kosong')>Kosong</option>
                  <option value="Terisi" @selected(old('status') === 'Terisi')>Terisi</option>
                  <option value="Perbaikan" @selected(old('status') === 'Perbaikan')>Perbaikan</option>
                </select>
                @error('status')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="is_active">Status Aktif</label>
                <select class="input @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                  <option value="1" @selected(old('is_active', '1') !== '0')>Aktif</option>
                  <option value="0" @selected(old('is_active') === '0')>Nonaktif</option>
                </select>
                @error('is_active')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Fasilitas</h3>
            <div class="field">
              <div class="chk-grid">
                {{-- Daftar fasilitas dari backend: @foreach($facilities as $facility) --}}
                @forelse($facilities as $facility)
                  <label class="chk">
                    <input type="checkbox" name="facilities[]" value="{{ $facility->id }}"
                           @checked(in_array($facility->id, old('facilities', [])))>
                    <span>{{ $facility->name }}</span>
                  </label>
                @empty
                  <p class="small muted" style="margin:0">Belum ada fasilitas. Tambahkan lewat menu Fasilitas terlebih dahulu.</p>
                @endforelse
              </div>
              <p class="small muted" style="margin:6px 0 0">Pilih fasilitas yang tersedia di kamar ini (relasi room_facilities).</p>
              @error('facilities')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <h3 class="form-section-title">Deskripsi</h3>
            <div class="field">
              <label for="description">Deskripsi</label>
              <textarea class="input @error('description') is-invalid @enderror" id="description" name="description"
                        rows="3" placeholder="Deskripsi kamar, kondisi, atau keterangan lain…">{{ old('description') }}</textarea>
              @error('description')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- ============ PANEL FOTO ============ -->
          <div class="card elev-sm form-card photo-card">
            <h3 class="card-title">Foto Kamar</h3>

            <div class="photo-preview" id="photoPreview" data-empty="1">
              <span class="photo-placeholder">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <span>Belum ada foto</span>
              </span>
            </div>

            <div class="img-upload-actions">
              <label class="btn btn-secondary" for="image" role="button" tabindex="0" title="Pilih gambar kamar">Pilih Gambar</label>
              <button type="button" class="btn btn-ghost" id="photoRemove" data-action="room-photo-remove" hidden>Hapus Foto</button>
            </div>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" hidden>
            <p class="small muted" style="margin:0">JPG, JPEG, PNG, atau WebP. Gambar disimpan di Storage Laravel, bukan base64.</p>
            <p class="form-error" id="photoError" hidden>Format foto harus JPG, JPEG, PNG, atau WebP.</p>
            @error('image')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Simpan Kamar</button>
          <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Batal</a>
        </div>
      </form>

    </main>
  </div>
</div>

<!-- Backdrop navigasi mobile -->
<div class="nav-backdrop" id="navBackdrop"></div>

<div id="toastRoot" aria-live="polite"></div>

<script src="{{ asset('js/app.js') }}"></script>
<script>
  // UI kecil halaman ini: preview foto sebelum submit (tanpa menyimpan data).
  (function () {
    var input = document.getElementById('image');
    var preview = document.getElementById('photoPreview');
    var removeBtn = document.getElementById('photoRemove');
    var errMsg = document.getElementById('photoError');
    if (!input || !preview) return;

    function setPreview(src) {
      if (!src) {
        preview.dataset.empty = '1';
        preview.innerHTML = '<span class="photo-placeholder">' +
          '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>' +
          '<span>Belum ada foto</span></span>';
        if (removeBtn) removeBtn.hidden = true;
        return;
      }
      preview.dataset.empty = '0';
      preview.innerHTML = '<img src="' + src + '" alt="Pratinjau foto kamar">';
      if (removeBtn) removeBtn.hidden = false;
    }

    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file) return;
      if (errMsg) errMsg.hidden = true;
      if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
        input.value = '';
        setPreview('');
        if (errMsg) errMsg.hidden = false;
        return;
      }
      // Object URL untuk preview — file asli tetap dikirim via <input type="file">.
      setPreview(URL.createObjectURL(file));
    });

    if (removeBtn) {
      removeBtn.addEventListener('click', function () {
        input.value = '';
        setPreview('');
      });
    }
  })();
</script>
</body>
</html>
