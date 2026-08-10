@extends('layouts.app')
@section('content')
    <main class="page" id="page">

      <!-- Judul halaman -->
      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <span class="current">Dashboard</span>
          </nav>
          <h2 class="page-title">Dashboard</h2>
          <p class="page-sub" id="pageSub">Ringkasan operasional</p>
        </div>
        <!-- Role demo — hapus saat pakai auth() di Laravel -->
        <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
      </section>

      <!-- Kartu statistik -->
      <section class="grid-4" aria-label="Statistik">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Kamar</div>
          <div class="stat-value" id="statTotalKamar">0</div>
          <div class="stat-sub muted" id="statSubKamar">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Penghuni Aktif</div>
          <div class="stat-value" id="statPenghuniAktif">0</div>
          <div class="stat-sub muted" id="statSubPenghuni">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Pendapatan Bulan Ini</div>
          <div class="stat-value" id="statPendapatan">Rp 0</div>
          <div class="stat-sub muted" id="statSubPendapatan">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Tagihan Belum Dibayar</div>
          <div class="stat-value" id="statTunggak">Rp 0</div>
          <div class="stat-sub" id="statSubTunggak" style="color:var(--color-accent-700)">—</div>
        </div>
      </section>

      <!-- Chart pendapatan + okupansi -->
      <section class="grid-32" aria-label="Grafik">
        <div class="card elev-sm" style="padding:20px">
          <div class="card-head">
            <h3 class="card-title">Pendapatan 6 Bulan Terakhir</h3>
            <span class="small muted">dalam juta Rp</span>
          </div>
          <div class="chart-bars" id="chartBars"></div>
          <div class="chart-labels" id="chartLabels"></div>
        </div>

        <div class="card elev-sm" style="padding:20px">
          <h3 class="card-title">Okupansi Kamar</h3>
          <div class="flex donut-wrap">
            <!-- Donut: nilai dash diisi oleh app.js -->
            <svg width="150" height="150" viewBox="0 0 150 150" role="img" aria-label="Okupansi kamar">
              <circle cx="75" cy="75" r="56" fill="none" stroke="var(--color-neutral-200)" stroke-width="22"></circle>
              <g transform="rotate(-90 75 75)">
                <circle id="donutTerisi" cx="75" cy="75" r="56" fill="none" stroke="var(--color-neutral-900)" stroke-width="22" stroke-dasharray="0 351.9"></circle>
                <circle id="donutKosong" cx="75" cy="75" r="56" fill="none" stroke="var(--color-neutral-200)" stroke-width="22" stroke-dasharray="0 351.9"></circle>
                <circle id="donutPerbaikan" cx="75" cy="75" r="56" fill="none" stroke="var(--color-accent)" stroke-width="22" stroke-dasharray="0 351.9"></circle>
              </g>
              <text id="donutPct" x="75" y="71" text-anchor="middle" font-family="Archivo" font-weight="800" font-size="26" fill="var(--color-text)">0%</text>
              <text x="75" y="90" text-anchor="middle" font-size="10" fill="var(--color-neutral-600)">terisi</text>
            </svg>
            <div class="legend">
              <div class="flex"><span class="sw" style="background:var(--color-neutral-900)"></span>Terisi — <span id="legendTerisi">0</span></div>
              <div class="flex"><span class="sw" style="background:var(--color-neutral-200);border:1px solid var(--color-divider)"></span>Kosong — <span id="legendKosong">0</span></div>
              <div class="flex"><span class="sw" style="background:var(--color-accent)"></span>Perbaikan — <span id="legendPerbaikan">0</span></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Pembayaran terbaru + maintenance -->
      <section class="grid-32" aria-label="Aktivitas">
        <div class="card elev-sm" style="padding:20px">
          <div class="card-head">
            <h3 class="card-title">Pembayaran Terbaru</h3>
            <div class="flex" style="gap:8px">
              <button class="btn btn-primary" data-action="pay-new" style="font-size:13px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Catat
              </button>
              <a class="btn btn-ghost" href="payments.html" style="font-size:13px;text-decoration:none">Lihat semua</a>
            </div>
          </div>
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr><th>Penghuni</th><th>Kamar</th><th>Bulan</th><th>Tanggal</th><th>Metode</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr>
              </thead>
              <tbody id="paymentsBody"></tbody>
            </table>
          </div>
        </div>

        <div class="card elev-sm" style="padding:20px">
          <div class="card-head">
            <h3 class="card-title">Maintenance / Keluhan</h3>
            <button class="btn btn-ghost" data-action="maint-new" style="font-size:13px;text-decoration:none">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Tambah
            </button>
          </div>
          <div id="complaintsList"></div>
        </div>
      </section>

      <!-- Kelola kamar -->
      <section class="card elev-sm section-card" id="section-kamar" aria-label="Kelola kamar">
        <div class="card-head">
          <div>
            <h3 class="card-title">Kelola Kamar</h3>
            <p class="small muted" id="kamarSub"></p>
          </div>
          <button class="btn btn-primary" data-action="room-new">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Kamar
          </button>
        </div>

        <div class="toolbar">
          <select class="input" data-filter="kamar-status" name="filterStatus" aria-label="Filter status kamar">
            <option value="Semua">Semua status</option>
            <option value="Kosong">Kosong</option>
            <option value="Terisi">Terisi</option>
            <option value="Perbaikan">Perbaikan</option>
          </select>
          <select class="input" data-filter="kamar-tipe" name="filterTipe" aria-label="Filter tipe kamar">
            <option value="Semua">Semua tipe</option>
            <option value="Standar">Standar</option>
            <option value="Deluxe">Deluxe</option>
            <option value="Premium">Premium</option>
          </select>
          <input class="input" data-filter="kamar-q" name="filterQ" placeholder="Cari nomor kamar…" aria-label="Cari kamar">
          <span class="small muted flex-1" id="kamarCount" style="text-align:right"></span>
        </div>

        <div class="grid-4" id="roomsGrid"></div>
      </section>

    </main>
@endsection
