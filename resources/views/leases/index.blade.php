@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{Route('dashboard')}}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Kontrak Sewa</span>
          </nav>
          <h2 class="page-title">Kontrak Sewa</h2>
          <p class="page-sub">Kontrak sewa dan masa berlaku</p>
        </div>
        <div class="flex head-actions">
          <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
          <button class="btn btn-primary" data-action="lease-new">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Kontrak
          </button>
        </div>
      </section>

      <!-- Kartu statistik -->
      <section class="grid-4" aria-label="Statistik kontrak">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Kontrak</div>
          <div class="stat-value" id="statLsTotal">0</div>
          <div class="stat-sub muted" id="statLsTotalSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Kontrak Aktif</div>
          <div class="stat-value" id="statLsAktif">0</div>
          <div class="stat-sub muted" id="statLsAktifSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Akan Berakhir</div>
          <div class="stat-value" id="statLsSegera">0</div>
          <div class="stat-sub muted" id="statLsSegeraSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Berakhir</div>
          <div class="stat-value" id="statLsBerakhir">0</div>
          <div class="stat-sub muted" id="statLsBerakhirSub">—</div>
        </div>
      </section>

      <!-- Filter + tabel kontrak -->
      <section class="card elev-sm section-card" aria-label="Daftar kontrak sewa">
        <div class="card-head">
          <h3 class="card-title">Daftar Kontrak</h3>
          <span class="small muted" id="leasesCount"></span>
        </div>

        <div class="filter-bar">
          <input class="input" type="search" name="filterQ" data-filter="leases-q" placeholder="Cari nomor kontrak, penghuni…" aria-label="Cari kontrak">
          <select class="input" name="filterStatus" data-filter="leases-status" aria-label="Filter status">
            <option value="Semua">Semua status</option>
            <option value="Aktif">Aktif</option>
            <option value="Segera Berakhir">Akan Berakhir</option>
            <option value="Berakhir">Berakhir</option>
          </select>
          <label class="flex" style="gap:6px">
            <span class="small muted">Berakhir bulan:</span>
            <input class="input" type="month" name="filterBulan" data-filter="leases-bulan" aria-label="Filter bulan berakhir">
          </label>
        </div>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr><th>No. Kontrak</th><th>Penghuni</th><th>Kamar</th><th>Mulai</th><th>Berakhir</th><th>Harga/Bln</th><th>Deposit</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody id="leasesBody"></tbody>
          </table>
        </div>
      </section>

    </main>
@endsection
