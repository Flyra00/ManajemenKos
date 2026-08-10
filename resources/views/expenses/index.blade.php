@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{Route('dashboard')}}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Pengeluaran</span>
          </nav>
          <h2 class="page-title">Pengeluaran</h2>
          <p class="page-sub">Catatan pengeluaran operasional</p>
        </div>
        <div class="flex head-actions">
          <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
          <button class="btn btn-primary" data-action="exp-new">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Pengeluaran
          </button>
        </div>
      </section>

      <!-- Kartu statistik -->
      <section class="grid-4" aria-label="Statistik pengeluaran">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Pengeluaran</div>
          <div class="stat-value" id="statExTotal">Rp 0</div>
          <div class="stat-sub muted" id="statExTotalSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Bulan Ini</div>
          <div class="stat-value" id="statExBulan">Rp 0</div>
          <div class="stat-sub muted" id="statExBulanSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Pengeluaran Terbesar</div>
          <div class="stat-value" id="statExBesar">Rp 0</div>
          <div class="stat-sub muted" id="statExBesarSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Jumlah Catatan</div>
          <div class="stat-value" id="statExCount">0</div>
          <div class="stat-sub muted" id="statExCountSub">—</div>
        </div>
      </section>

      <!-- Filter + tabel pengeluaran -->
      <section class="card elev-sm section-card" aria-label="Daftar pengeluaran">
        <div class="card-head">
          <h3 class="card-title">Daftar Pengeluaran</h3>
          <span class="small muted" id="expensesCount"></span>
        </div>

        <div class="filter-bar">
          <input class="input" type="search" name="filterQ" data-filter="expenses-q" placeholder="Cari judul, deskripsi…" aria-label="Cari pengeluaran">
          <select class="input" name="filterKategori" data-filter="expenses-kategori" aria-label="Filter kategori">
            <option value="Semua">Semua kategori</option>
            <option value="Listrik">Listrik</option>
            <option value="Air">Air</option>
            <option value="Internet">Internet</option>
            <option value="Kebersihan">Kebersihan</option>
            <option value="Perbaikan">Perbaikan</option>
            <option value="Lainnya">Lainnya</option>
          </select>
          <label class="flex" style="gap:6px">
            <span class="small muted">Bulan:</span>
            <input class="input" type="month" name="filterBulan" data-filter="expenses-bulan" aria-label="Filter bulan pengeluaran">
          </label>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr><th>Judul</th><th>Deskripsi</th><th>Kategori</th><th>Jumlah</th><th>Tanggal</th><th>Dibuat Oleh</th><th>Aksi</th></tr>
            </thead>
            <tbody id="expensesBody"></tbody>
          </table>
        </div>
      </section>

    </main>
@endsection
