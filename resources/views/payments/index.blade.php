@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{Route('dashboard')}}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Pembayaran</span>
          </nav>
          <h2 class="page-title">Pembayaran</h2>
          <p class="page-sub">Riwayat pembayaran dan tagihan</p>
        </div>
        <div class="flex head-actions">
          <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
          <button class="btn btn-primary" data-action="pay-new">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Pembayaran
          </button>
        </div>
      </section>

      <!-- Kartu statistik -->
      <section class="grid-5" aria-label="Statistik pembayaran">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Pembayaran</div>
          <div class="stat-value" id="statPayTotal">0</div>
          <div class="stat-sub muted" id="statPayTotalSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Sudah Dibayar</div>
          <div class="stat-value" id="statPayLunas">0</div>
          <div class="stat-sub muted" id="statPayLunasSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Pending</div>
          <div class="stat-value" id="statPayPending">0</div>
          <div class="stat-sub muted" id="statPayPendingSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Belum Dibayar</div>
          <div class="stat-value" id="statPayBelum">0</div>
          <div class="stat-sub muted" id="statPayBelumSub">—</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Overdue</div>
          <div class="stat-value" id="statPayOverdue">0</div>
          <div class="stat-sub muted" id="statPayOverdueSub">—</div>
        </div>
      </section>

      <!-- Filter + tabel pembayaran -->
      <section class="card elev-sm section-card" aria-label="Daftar pembayaran">
        <div class="card-head">
          <h3 class="card-title">Daftar Pembayaran</h3>
          <span class="small muted" id="paymentsCount"></span>
        </div>

        <div class="filter-bar">
          <input class="input" type="search" name="filterQ" data-filter="payments-q" placeholder="Cari invoice, penghuni…" aria-label="Cari pembayaran">
          <select class="input" name="filterStatus" data-filter="payments-status" aria-label="Filter status">
            <option value="Semua">Semua status</option>
            <option value="Lunas">Lunas</option>
            <option value="Menunggu">Pending</option>
            <option value="Belum Bayar">Belum Bayar</option>
            <option value="Terlambat">Overdue</option>
          </select>
          <select class="input" name="filterPeriode" data-filter="payments-periode" aria-label="Filter periode">
            <option value="Semua">Semua periode</option>
          </select>
          <select class="input" name="filterMetode" data-filter="payments-metode" aria-label="Filter metode">
            <option value="Semua">Semua metode</option>
            <option value="Transfer">Transfer</option>
            <option value="QRIS">QRIS</option>
            <option value="Tunai">Tunai</option>
          </select>
        </div>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr><th>Invoice</th><th>Penghuni</th><th>Kamar</th><th>Periode</th><th>Jumlah</th><th>Jatuh Tempo</th><th>Tgl Bayar</th><th>Metode</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody id="paymentsBody"></tbody>
          </table>
        </div>
      </section>

    </main>
@endsection
