@extends('layouts.app')
@section('content')
    <main class="page">

      <!-- Judul halaman -->
      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Kamar</span>
          </nav>
          <h2 class="page-title">Kamar</h2>
          <p class="page-sub">Kelola kamar kos, harga, dan status hunian</p>
        </div>
        <div class="flex head-actions">
          <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
          <button class="btn btn-primary" data-action="room-new">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Kamar
          </button>
        </div>
      </section>

      <!-- Kartu statistik -->
      <section class="grid-4" aria-label="Statistik kamar">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Kamar</div>
          <div class="stat-value" id="statRoomsTotal">0</div>
          <div class="stat-sub muted" id="statRoomsTotalSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Kamar Tersedia</div>
          <div class="stat-value" id="statRoomsKosong">0</div>
          <div class="stat-sub muted" id="statRoomsKosongSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Kamar Terisi</div>
          <div class="stat-value" id="statRoomsTerisi">0</div>
          <div class="stat-sub muted" id="statRoomsTerisiSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Kamar Maintenance</div>
          <div class="stat-value" id="statRoomsPerbaikan">0</div>
          <div class="stat-sub muted" id="statRoomsPerbaikanSub">—</div>
        </div>
      </section>

      <!-- Filter + tabel kamar -->
      <section class="card elev-sm section-card" aria-label="Daftar kamar">
        <div class="card-head">
          <h3 class="card-title">Daftar Kamar</h3>
          <span class="small muted" id="roomsCount"></span>
        </div>

        <div class="filter-bar">
          <input class="input" type="search" name="filterQ" data-filter="rooms-q" placeholder="Cari nomor kamar atau penghuni…" aria-label="Cari kamar">
          <select class="input" name="filterStatus" data-filter="rooms-status" aria-label="Filter status">
            <option value="Semua">Semua status</option>
            <option value="Kosong">Kosong</option>
            <option value="Terisi">Terisi</option>
            <option value="Perbaikan">Perbaikan</option>
          </select>
          <select class="input" name="filterLantai" data-filter="rooms-lantai" aria-label="Filter lantai">
            <option value="Semua">Semua lantai</option>
            <option value="1">Lantai 1</option>
            <option value="2">Lantai 2</option>
            <option value="3">Lantai 3</option>
          </select>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr><th>Nomor</th><th>Lantai</th><th>Harga/Bulan</th><th>Status</th><th>Penghuni</th><th>Aksi</th></tr>
            </thead>
            <tbody id="roomsBody"></tbody>
          </table>
        </div>
      </section>

    </main>
  </div>
</div>

<!-- Backdrop navigasi mobile -->
<div class="nav-backdrop" id="navBackdrop"></div>

<!-- ============================================================
     MODAL — struktur statis di HTML agar mudah dipindah ke Blade.
     app.js hanya membuka/menutup dan mengisi nilai.
     ============================================================ -->
<div id="dialogRoot">

  <!-- Modal: Kamar (tambah/edit) -->
  <div class="dialog-backdrop" id="modalRoom" hidden data-close="1">
    <div class="dialog">
      <form data-form="room" novalidate>
        <div class="dialog-title" id="roomModalTitle">Tambah Kamar</div>
        <div class="grid-2">
          <div class="field">
            <label for="fRoomNo">Nomor Kamar</label>
            <input class="input" name="no" id="fRoomNo" placeholder="A-09" required>
          </div>
          <div class="field">
            <label for="fRoomLantai">Lantai</label>
            <select class="input" name="lantai" id="fRoomLantai">
              <option value="1">Lantai 1</option>
              <option value="2">Lantai 2</option>
              <option value="3">Lantai 3</option>
            </select>
          </div>
          <div class="field">
            <label for="fRoomTipe">Tipe</label>
            <select class="input" name="tipe" id="fRoomTipe">
              <option value="Standar">Standar</option>
              <option value="Deluxe">Deluxe</option>
              <option value="Premium">Premium</option>
            </select>
          </div>
          <div class="field">
            <label for="fRoomHarga">Harga per Bulan (Rp)</label>
            <input class="input" name="harga" id="fRoomHarga" type="number" min="0" step="50000" required>
          </div>
          <div class="field">
            <label for="fRoomStatus">Status</label>
            <select class="input" name="status" id="fRoomStatus">
              <option value="Kosong">Kosong</option>
              <option value="Terisi">Terisi</option>
              <option value="Perbaikan">Perbaikan</option>
            </select>
          </div>
          <div class="field">
            <label for="fRoomPenghuni">Penghuni (jika Terisi)</label>
            <input class="input" name="penghuni" id="fRoomPenghuni" placeholder="Nama penghuni">
          </div>
        </div>
        <div class="field">
          <label for="fRoomCatatan">Catatan</label>
          <textarea class="input" name="catatan" id="fRoomCatatan" placeholder="Fasilitas kamar, catatan…" rows="2"></textarea>
        </div>
        <div class="dialog-actions">
          <button type="button" class="btn btn-secondary" data-action="close-dialog">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Detail -->
  <div class="dialog-backdrop" id="modalDetail" hidden data-close="1">
    <div class="dialog">
      <div class="dialog-title" id="detailTitle">Detail</div>
      <dl class="detail-list" id="detailBody"></dl>
      <div class="dialog-actions">
        <button type="button" class="btn btn-secondary" data-action="close-dialog">Tutup</button>
      </div>
    </div>
  </div>

  <!-- Modal: Konfirmasi -->
  <div class="dialog-backdrop" id="modalConfirm" hidden data-close="1">
    <div class="dialog">
      <div class="dialog-title" id="confirmTitle">Konfirmasi</div>
      <p class="small muted" id="confirmMsg" style="margin:8px 0 0;line-height:1.5"></p>
      <div class="dialog-actions" style="margin-top:16px">
        <button type="button" class="btn btn-secondary" data-action="close-dialog">Batal</button>
        <button type="button" class="btn btn-primary" data-action="confirm-ok" id="confirmOkBtn">Hapus</button>
      </div>
    </div>
  </div>

</div>

<!-- Toast notifications -->
<div id="toa
@endsection
