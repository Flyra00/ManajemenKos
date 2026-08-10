@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{Route('dashboard')}}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Penghuni</span>
          </nav>
          <h2 class="page-title">Penghuni</h2>
          <p class="page-sub">Data penghuni kos dan status tinggal</p>
        </div>
        <div class="flex head-actions">
          <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
          <button class="btn btn-primary" data-action="tenant-new">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Penghuni
          </button>
        </div>
      </section>

      <!-- Kartu statistik -->
      <section class="grid-4" aria-label="Statistik penghuni">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Penghuni</div>
          <div class="stat-value" id="statTenTotal">0</div>
          <div class="stat-sub muted" id="statTenTotalSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Penghuni Aktif</div>
          <div class="stat-value" id="statTenAktif">0</div>
          <div class="stat-sub muted" id="statTenAktifSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Penghuni Baru</div>
          <div class="stat-value" id="statTenBaru">0</div>
          <div class="stat-sub muted" id="statTenBaruSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Sudah Keluar</div>
          <div class="stat-value" id="statTenKeluar">0</div>
          <div class="stat-sub muted" id="statTenKeluarSub">—</div>
        </div>
      </section>

      <!-- Filter + tabel penghuni -->
      <section class="card elev-sm section-card" aria-label="Daftar penghuni">
        <div class="card-head">
          <h3 class="card-title">Daftar Penghuni</h3>
          <span class="small muted" id="tenantsCount"></span>
        </div>

        <div class="filter-bar">
          <input class="input" type="search" name="filterQ" data-filter="tenants-q" placeholder="Cari nama, KTP, pekerjaan…" aria-label="Cari penghuni">
          <select class="input" name="filterStatus" data-filter="tenants-status" aria-label="Filter status">
            <option value="Semua">Semua status</option>
            <option value="Aktif">Aktif</option>
            <option value="Keluar">Keluar</option>
          </select>
          <select class="input" name="filterKamar" data-filter="tenants-kamar" aria-label="Filter kamar">
            <option value="Semua">Semua kamar</option>
          </select>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr><th>Nama</th><th>KTP</th><th>Telepon</th><th>Kamar</th><th>Pekerjaan</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody id="tenantsBody"></tbody>
          </table>
        </div>
      </section>

    </main>
@endsection
