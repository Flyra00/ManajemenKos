@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Laporan</span>
          </nav>
          <h2 class="page-title">Laporan & Analisis Operasional</h2>
          <p class="page-sub">Ringkasan arus kas keuangan, efisiensi operasional, dan tingkat hunian kos.</p>
        </div>
        <div class="flex head-actions" style="gap: 8px;">
          <a href="{{ route('reports.export', request()->query()) }}" class="btn btn-secondary" title="Unduh data laporan ke format spreadsheet CSV">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Unduh CSV / Excel
          </a>
          <button class="btn btn-primary" onclick="window.print()" title="Cetak halaman laporan">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
            Cetak / Export PDF
          </button>
        </div>
      </section>

      <!-- Filter Periode Laporan -->
      <section class="card elev-sm section-card" aria-label="Filter laporan">
        <div class="card-head">
          <h3 class="card-title">Filter Periode Laporan</h3>
          <span class="small muted">Pilih rentang waktu untuk mengkalkulasi ulang data</span>
        </div>
        <form method="GET" action="{{ route('reports.index') }}" class="filter-bar">
          <input type="hidden" name="tab" value="{{ $tab }}">

          <label class="flex items-center" style="gap:6px">
            <span class="small muted">Tahun:</span>
            <select class="input" name="year">
              @for($y = date('Y') + 1; $y >= 2024; $y--)
                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
              @endfor
            </select>
          </label>

          <label class="flex items-center" style="gap:6px">
            <span class="small muted">Bulan:</span>
            <select class="input" name="month">
              <option value="all" @selected($month === 'all')>Semua Bulan (1 Tahun Penuh)</option>
              @foreach([
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
              ] as $mNum => $mName)
                <option value="{{ $mNum }}" @selected($month == $mNum)>{{ $mName }}</option>
              @endforeach
            </select>
          </label>

          <button type="submit" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
            Tampilkan Laporan
          </button>

          @if(request()->hasAny(['year', 'month']))
            <a href="{{ route('reports.index', ['tab' => $tab]) }}" class="btn btn-ghost">Reset Filter</a>
          @endif
        </form>
      </section>

      <!-- 4 Kartu Ringkasan Keuangan & Hunian -->
      <section class="grid-4" aria-label="Ringkasan laporan">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Pendapatan</div>
          <div class="stat-value">Rp {{ number_format($stats['total_income'], 0, ',', '.') }}</div>
          <div class="stat-sub muted">pembayaran terverifikasi</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Pengeluaran</div>
          <div class="stat-value">Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}</div>
          <div class="stat-sub muted">biaya operasional & utilitas</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid {{ $stats['net_profit'] >= 0 ? 'var(--color-neutral-900)' : 'var(--color-accent)' }}">
          <div class="stat-label">Keuntungan Bersih</div>
          <div class="stat-value" style="color: {{ $stats['net_profit'] >= 0 ? 'inherit' : 'var(--color-accent)' }}">
            Rp {{ number_format($stats['net_profit'], 0, ',', '.') }}
          </div>
          <div class="stat-sub muted">pendapatan - pengeluaran</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Tingkat Okupansi</div>
          <div class="stat-value">{{ $stats['occupancy_rate'] }}%</div>
          <div class="stat-sub muted">{{ $stats['occupied_rooms'] }} dari {{ $stats['total_rooms'] }} kamar terisi</div>
        </div>
      </section>

      <!-- Grafik Arus Kas: Pendapatan vs Pengeluaran 6 Bulan Terakhir -->
      <section class="card elev-sm" style="padding:20px" aria-label="Grafik arus kas">
        <div class="card-head">
          <div>
            <h3 class="card-title">Tren Arus Kas (6 Bulan Terakhir)</h3>
            <span class="small muted">Perbandingan pendapatan sewa vs pengeluaran operasional kos</span>
          </div>
          <div class="chart-legend">
            <span class="flex items-center" style="gap:5px"><span class="sw" style="background:var(--color-neutral-900)"></span>Pendapatan</span>
            <span class="flex items-center" style="gap:5px"><span class="sw" style="background:var(--color-accent)"></span>Pengeluaran</span>
          </div>
        </div>

        <div class="chart-bars chart-duo">
          @foreach($chartMonths as $cm)
            <div class="chart-col">
              <div class="chart-val">
                @if($cm['income'] >= 1000000)
                  {{ number_format($cm['income'] / 1000000, 1) }}jt
                @elseif($cm['income'] > 0)
                  {{ number_format($cm['income'] / 1000, 0) }}rb
                @else
                  0
                @endif
              </div>
              <div class="chart-group">
                <div class="chart-bar masuk" style="height: {{ $cm['income_pct'] }}%" title="Pendapatan {{ $cm['label'] }}: Rp {{ number_format($cm['income'], 0, ',', '.') }}"></div>
                <div class="chart-bar keluar" style="height: {{ $cm['expense_pct'] }}%" title="Pengeluaran {{ $cm['label'] }}: Rp {{ number_format($cm['expense'], 0, ',', '.') }}"></div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="chart-labels">
          @foreach($chartMonths as $cm)
            <span>{{ $cm['label'] }}</span>
          @endforeach
        </div>
      </section>

      <!-- Tabel Data Rincian per Kategori -->
      <section class="card elev-sm section-card" aria-label="Tabel rincian laporan">
        <div class="card-head">
          <h3 class="card-title">Data Rincian Laporan</h3>
          <span class="small muted">{{ $tableData ? $tableData->total() : 0 }} catatan</span>
        </div>

        <div class="report-tabs" role="tablist" aria-label="Tab jenis laporan">
          <a href="{{ route('reports.index', array_merge(request()->query(), ['tab' => 'all'])) }}"
             class="report-tab {{ in_array($tab, ['all', 'income']) ? 'active' : '' }}">
            Pendapatan Sewa
          </a>
          <a href="{{ route('reports.index', array_merge(request()->query(), ['tab' => 'expenses'])) }}"
             class="report-tab {{ $tab === 'expenses' ? 'active' : '' }}">
            Pengeluaran Operasional
          </a>
          <a href="{{ route('reports.index', array_merge(request()->query(), ['tab' => 'occupancy'])) }}"
             class="report-tab {{ $tab === 'occupancy' ? 'active' : '' }}">
            Okupansi Kamar
          </a>
          <a href="{{ route('reports.index', array_merge(request()->query(), ['tab' => 'maintenance'])) }}"
             class="report-tab {{ $tab === 'maintenance' ? 'active' : '' }}">
            Biaya Maintenance
          </a>
        </div>

        <div class="table-wrap">
          @if(in_array($tab, ['all', 'income']))
            {{-- TABEL PENDAPATAN --}}
            <table class="table table-wide">
              <thead>
                <tr>
                  <th style="width:60px;">No</th>
                  <th>No. Invoice</th>
                  <th>Penghuni</th>
                  <th>Kamar</th>
                  <th>Periode Tagihan</th>
                  <th>Tanggal Bayar</th>
                  <th>Metode</th>
                  <th>Jumlah Diterima</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tableData as $pay)
                  <tr>
                    <td class="muted">{{ $tableData->firstItem() ? $tableData->firstItem() + $loop->index : $loop->iteration }}</td>
                    <td class="font-semibold">{{ $pay->invoice_number }}</td>
                    <td>{{ $pay->lease->tenant->user->name ?? '—' }}</td>
                    <td>Kamar {{ $pay->lease->room->room_number ?? '—' }}</td>
                    <td>{{ $pay->billing_period ? $pay->billing_period->translatedFormat('F Y') : '—' }}</td>
                    <td>{{ $pay->payment_date ? $pay->payment_date->translatedFormat('d M Y') : '—' }}</td>
                    <td>
                      @if($pay->payment_method === 'bank_tf')
                        <span class="tag tag-outline">Transfer</span>
                      @elseif($pay->payment_method === 'e_wallet')
                        <span class="tag tag-outline">E-Wallet</span>
                      @else
                        <span class="tag tag-outline">Tunai</span>
                      @endif
                    </td>
                    <td class="font-semibold text-neutral-900">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="text-center py-6 text-neutral-500">
                      Tidak ada data penerimaan pembayaran pada periode ini.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>

          @elseif($tab === 'expenses')
            {{-- TABEL PENGELUARAN --}}
            <table class="table table-wide">
              <thead>
                <tr>
                  <th style="width:60px;">No</th>
                  <th>Judul Pengeluaran</th>
                  <th>Keterangan</th>
                  <th>Tanggal</th>
                  <th>Dicatat Oleh</th>
                  <th>Jumlah Biaya</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tableData as $exp)
                  <tr>
                    <td class="muted">{{ $tableData->firstItem() ? $tableData->firstItem() + $loop->index : $loop->iteration }}</td>
                    <td class="font-semibold">{{ $exp->title }}</td>
                    <td class="text-neutral-600">{{ $exp->description ?: '—' }}</td>
                    <td>{{ $exp->expense_date ? $exp->expense_date->translatedFormat('d M Y') : '—' }}</td>
                    <td>{{ $exp->user->name ?? 'Admin' }}</td>
                    <td class="font-semibold text-neutral-900">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center py-6 text-neutral-500">
                      Tidak ada catatan pengeluaran pada periode ini.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>

          @elseif($tab === 'occupancy')
            {{-- TABEL OKUPANSI KAMAR --}}
            <table class="table table-wide">
              <thead>
                <tr>
                  <th style="width:60px;">No</th>
                  <th>No. Kamar</th>
                  <th>Lantai</th>
                  <th>Harga Standar</th>
                  <th>Status Hunian</th>
                  <th>Penghuni Saat Ini</th>
                  <th>Periode Kontrak</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tableData as $room)
                  @php
                    $activeLease = $room->leases->first();
                  @endphp
                  <tr>
                    <td class="muted">{{ $tableData->firstItem() ? $tableData->firstItem() + $loop->index : $loop->iteration }}</td>
                    <td class="font-semibold">Kamar {{ $room->room_number }}</td>
                    <td>Lantai {{ $room->floor }}</td>
                    <td>Rp {{ number_format($room->price, 0, ',', '.') }}/bln</td>
                    <td>
                      @if($room->status === 'occupied')
                        <span class="tag tag-accent">Terisi</span>
                      @elseif($room->status === 'available')
                        <span class="tag tag-outline">Kosong</span>
                      @else
                        <span class="tag tag-outline">Perbaikan</span>
                      @endif
                    </td>
                    <td>
                      {{ $activeLease && $activeLease->tenant && $activeLease->tenant->user ? $activeLease->tenant->user->name : '—' }}
                    </td>
                    <td>
                      @if($activeLease)
                        {{ $activeLease->start_date ? $activeLease->start_date->translatedFormat('d M Y') : '—' }}
                        s/d
                        {{ $activeLease->end_date ? $activeLease->end_date->translatedFormat('d M Y') : 'Fleksibel' }}
                      @else
                        —
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-6 text-neutral-500">
                      Belum ada data kamar terdaftar.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>

          @elseif($tab === 'maintenance')
            {{-- TABEL MAINTENANCE --}}
            <table class="table table-wide">
              <thead>
                <tr>
                  <th style="width:60px;">No</th>
                  <th>Judul Kerusakan</th>
                  <th>Kamar</th>
                  <th>Pelapor</th>
                  <th>Prioritas</th>
                  <th>Status Penanganan</th>
                  <th>Realisasi Biaya</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tableData as $maint)
                  <tr>
                    <td class="muted">{{ $tableData->firstItem() ? $tableData->firstItem() + $loop->index : $loop->iteration }}</td>
                    <td class="font-semibold">{{ $maint->title }}</td>
                    <td>Kamar {{ $maint->room->room_number ?? '—' }}</td>
                    <td>{{ $maint->tenant->user->name ?? 'Penghuni' }}</td>
                    <td>
                      @if($maint->priority === 'high')
                        <span class="tag tag-accent">Tinggi</span>
                      @elseif($maint->priority === 'medium')
                        <span class="tag tag-outline">Sedang</span>
                      @else
                        <span class="tag tag-outline">Rendah</span>
                      @endif
                    </td>
                    <td>
                      @if($maint->status === 'completed')
                        <span class="tag tag-outline">Selesai</span>
                      @elseif($maint->status === 'in_progress')
                        <span class="tag tag-accent">Diproses</span>
                      @elseif($maint->status === 'cancelled')
                        <span class="tag tag-outline">Dibatalkan</span>
                      @else
                        <span class="tag tag-outline">Baru</span>
                      @endif
                    </td>
                    <td class="font-semibold text-neutral-900">
                      Rp {{ number_format($maint->cost ?? 0, 0, ',', '.') }}
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-6 text-neutral-500">
                      Tidak ada catatan tiket maintenance.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          @endif
        </div>

        @if($tableData && $tableData->hasPages())
          <div class="card-footer flex justify-between items-center py-3 px-4 border-t border-neutral-200">
            <span class="text-sm text-neutral-600">
              Menampilkan {{ $tableData->firstItem() }} - {{ $tableData->lastItem() }} dari {{ $tableData->total() }} catatan
            </span>
            <div>
              {{ $tableData->links() }}
            </div>
          </div>
        @endif
      </section>

    </main>
@endsection
