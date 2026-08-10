@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ Route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Fasilitas</span>
          </nav>
          <h2 class="page-title">Fasilitas</h2>
          <p class="page-sub">Fasilitas yang tersedia di kos</p>
        </div>
        <div class="flex head-actions">
          <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
          <button class="btn btn-primary" data-action="fac-new">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Fasilitas
          </button>
        </div>
      </section>

      <!-- Kartu statistik -->
      <section class="grid-4" aria-label="Statistik fasilitas">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Fasilitas</div>
          <div class="stat-value" id="statFacTotal">0</div>
          <div class="stat-sub muted" id="statFacTotalSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Tersedia</div>
          <div class="stat-value" id="statFacTersedia">0</div>
          <div class="stat-sub muted" id="statFacTersediaSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Perbaikan</div>
          <div class="stat-value" id="statFacPerbaikan">0</div>
          <div class="stat-sub muted" id="statFacPerbaikanSub">perlu ditangani</div>
        </div>
      </section>

      <!-- Filter + grid fasilitas -->
      <section class="card elev-sm section-card" aria-label="Daftar fasilitas">
        <div class="card-head">
          <h3 class="card-title">Daftar Fasilitas</h3>
          <span class="small muted" id="facilitiesCount"></span>
        </div>

        <div class="filter-bar">
          <input class="input" type="search" name="filterQ" data-filter="facilities-q" placeholder="Cari nama fasilitas…" aria-label="Cari fasilitas">
          <select class="input" name="filterKategori" data-filter="facilities-kategori" aria-label="Filter kategori">
            <option value="Semua">Semua kategori</option>
          </select>
        </div>

        <div class="grid-3" id="facilitiesGrid"></div>
      </section>

    </main>
@endsection
