@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{Route('dashboard')}}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Laporan</span>
          </nav>
          <h2 class="page-title">Laporan</h2>
          <p class="page-sub">Ringkasan dan analisis operasional kos</p>
        </div>
        <div class="flex head-actions">
          <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
          <button class="btn btn-secondary" data-action="report-export-pdf">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
            Export PDF
          </button>
          <button class="btn btn-secondary" data-action="report-export-excel">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
            Export Excel
          </button>
        </div>
      </section>

      <!-- Filter laporan -->
      <section class="card elev-sm section-card" aria-label="Filter laporan">
        <div class="card-head">
          <h3 class="card-title">Filter Laporan</h3>
          <span class="small muted">Data dummy — siap dihubungkan ke backend</span>
        </div>
        <div class="filter-bar">
          <select class="input" name="reportsJenis" data-filter="reports-jenis" aria-label="Jenis laporan">
            <option value="Semua" selected>Semua Laporan</option>
            <option value="pendapatan">Pendapatan</option>
            <option value="pengeluaran">Pengeluaran</option>
            <option value="pembayaran">Pembayaran</option>
            <option value="okupansi">Okupansi Kamar</option>
            <option value="maintenance">Maintenance</option>
          </select>
          <select class="input" name="reportsPeriode" data-filter="reports-periode" aria-label="Periode">
            <option value="Bulan" selected>Bulan</option>
            <option value="Tahun">Tahun</option>
          </select>
          <select class="input" name="reportsBulan" data-filter="reports-bulan" aria-label="Bulan">
            <option value="Januari">Januari</option>
            <option value="Februari">Februari</option>
            <option value="Maret">Maret</option>
            <option value="April">April</option>
            <option value="Mei">Mei</option>
            <option value="Juni">Juni</option>
            <option value="Juli" selected>Juli</option>
            <option value="Agustus">Agustus</option>
            <option value="September">September</option>
            <option value="Oktober">Oktober</option>
            <option value="November">November</option>
            <option value="Desember">Desember</option>
          </select>
          <select class="input" name="reportsTahun" data-filter="reports-tahun" aria-label="Tahun">
            <option value="2025">2025</option>
            <option value="2026" selected>2026</option>
            <option value="2027">2027</option>
          </select>
          <button class="btn btn-primary" data-action="report-apply">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
            Tampilkan Laporan
          </button>
        </div>
      </section>

      <!-- Ringkasan -->
      <section class="grid-4" aria-label="Ringkasan laporan">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Pendapatan</div>
          <div class="stat-value" id="rptPendapatan">Rp 0</div>
          <div class="stat-sub muted" id="rptSubPendapatan">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Pengeluaran</div>
          <div class="stat-value" id="rptPengeluaran">Rp 0</div>
          <div class="stat-sub muted" id="rptSubPengeluaran">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Keuntungan Bersih</div>
          <div class="stat-value" id="rptBersih">Rp 0</div>
          <div class="stat-sub muted" id="rptSubBersih">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Tingkat Hunian</div>
          <div class="stat-value" id="rptHunian">0%</div>
          <div class="stat-sub" id="rptSubHunian" style="color:var(--color-accent-700)">—</div>
        </div>
      </section>

      <!-- Grafik pendapatan vs pengeluaran -->
      <section class="card elev-sm" style="padding:20px" aria-label="Grafik">
        <div class="card-head">
          <div>
            <h3 class="card-title">Pendapatan vs Pengeluaran</h3>
            <span class="small muted">6 bulan terakhir · dalam juta Rp</span>
          </div>
          <div class="chart-legend">
            <span class="flex"><span class="sw" style="background:var(--color-neutral-900)"></span>Pendapatan</span>
            <span class="flex"><span class="sw" style="background:var(--color-accent)"></span>Pengeluaran</span>
          </div>
        </div>
        <div class="chart-bars chart-duo" id="rptChartBars"></div>
        <div class="chart-labels" id="rptChartLabels"></div>
      </section>

      <!-- Tabel laporan -->
      <section class="card elev-sm section-card" aria-label="Tabel laporan">
        <div class="card-head">
          <h3 class="card-title">Tabel Laporan</h3>
          <span class="small muted" id="rptCount"></span>
        </div>

        <div class="report-tabs" role="tablist" aria-label="Jenis laporan">
          <button type="button" class="report-tab active" data-action="report-tab" data-tab="pendapatan" role="tab">Laporan Pendapatan</button>
          <button type="button" class="report-tab" data-action="report-tab" data-tab="pengeluaran" role="tab">Laporan Pengeluaran</button>
          <button type="button" class="report-tab" data-action="report-tab" data-tab="pembayaran" role="tab">Laporan Pembayaran</button>
          <button type="button" class="report-tab" data-action="report-tab" data-tab="okupansi" role="tab">Okupansi Kamar</button>
          <button type="button" class="report-tab" data-action="report-tab" data-tab="maintenance" role="tab">Laporan Maintenance</button>
        </div>

        <!-- Ringkasan okupansi (diisi JS saat tab okupansi aktif) -->
        <div id="rptOkupansi" hidden></div>

        <div class="table-wrap">
          <table class="table table-wide" id="rptTable">
            <thead id="rptThead"></thead>
            <tbody id="rptTbody"></tbody>
          </table>
        </div>
      </section>

    </main>
@endsection
