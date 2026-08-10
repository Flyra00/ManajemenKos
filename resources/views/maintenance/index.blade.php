@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{Route('dashboard')}}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Maintenance</span>
          </nav>
          <h2 class="page-title">Maintenance</h2>
          <p class="page-sub">Laporan kerusakan dan perbaikan</p>
        </div>
        <div class="flex head-actions">
          <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
          <button class="btn btn-primary" data-action="maint-new">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Laporan
          </button>
        </div>
      </section>

      <!-- Kartu statistik -->
      <section class="grid-5" aria-label="Statistik maintenance">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Laporan</div>
          <div class="stat-value" id="statMtTotal">0</div>
          <div class="stat-sub muted" id="statMtTotalSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Reported</div>
          <div class="stat-value" id="statMtBaru">0</div>
          <div class="stat-sub muted" id="statMtBaruSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">In Progress</div>
          <div class="stat-value" id="statMtProses">0</div>
          <div class="stat-sub muted" id="statMtProsesSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Completed</div>
          <div class="stat-value" id="statMtSelesai">0</div>
          <div class="stat-sub muted" id="statMtSelesaiSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Cancelled</div>
          <div class="stat-value" id="statMtBatal">0</div>
          <div class="stat-sub muted" id="statMtBatalSub">—</div>
        </div>
      </section>

      <!-- Filter + tabel maintenance -->
      <section class="card elev-sm section-card" aria-label="Daftar laporan maintenance">
        <div class="card-head">
          <h3 class="card-title">Daftar Laporan</h3>
          <span class="small muted" id="maintenanceCount"></span>
        </div>

        <div class="filter-bar">
          <input class="input" type="search" name="filterQ" data-filter="maintenance-q" placeholder="Cari judul, kamar, penghuni…" aria-label="Cari laporan">
          <select class="input" name="filterPrioritas" data-filter="maintenance-prioritas" aria-label="Filter prioritas">
            <option value="Semua">Semua prioritas</option>
            <option value="Tinggi">Tinggi</option>
            <option value="Sedang">Sedang</option>
            <option value="Rendah">Rendah</option>
          </select>
          <select class="input" name="filterStatus" data-filter="maintenance-status" aria-label="Filter status">
            <option value="Semua">Semua status</option>
            <option value="Baru">Reported</option>
            <option value="Diproses">In Progress</option>
            <option value="Selesai">Completed</option>
            <option value="Dibatalkan">Cancelled</option>
          </select>
        </div>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr><th>Judul</th><th>Kamar</th><th>Penghuni</th><th>Prioritas</th><th>Status</th><th>Dilaporkan</th><th>Biaya</th><th>Ditangani</th><th>Aksi</th></tr>
            </thead>
            <tbody id="maintenanceBody"></tbody>
          </table>
        </div>
      </section>

    </main>
@endsection
