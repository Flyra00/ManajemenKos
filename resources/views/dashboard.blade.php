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
          <p class="page-sub">Ringkasan operasional kos dan aktivitas terkini.</p>
        </div>
        <div>
          @if(auth()->user() && auth()->user()->roles->isNotEmpty())
            <span class="tag tag-accent">{{ auth()->user()->roles->first()->name }}</span>
          @else
            <span class="tag tag-accent">Pengelola</span>
          @endif
        </div>
      </section>

      <!-- 4 Kartu Statistik Ringkasan -->
      <section class="grid-4" aria-label="Statistik">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Kamar</div>
          <div class="stat-value">{{ $totalRooms }}</div>
          <div class="stat-sub muted">{{ $availableRooms }} kamar kosong tersedia</div>
        </div>

        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Penghuni Aktif</div>
          <div class="stat-value">{{ $activeLeases }}</div>
          <div class="stat-sub muted">dari {{ $totalTenants }} total penghuni</div>
        </div>

        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Pendapatan Bulan Ini</div>
          <div class="stat-value">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</div>
          <div class="stat-sub muted">{{ now()->translatedFormat('F Y') }}</div>
        </div>

        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Tagihan Belum Dibayar</div>
          <div class="stat-value">Rp {{ number_format($unpaidSum, 0, ',', '.') }}</div>
          <div class="stat-sub" style="color:var(--color-accent-700)">{{ $unpaidCount }} tagihan menunggu</div>
        </div>
      </section>

      <!-- Chart Pendapatan + Okupansi Donut -->
      <section class="grid-32" aria-label="Grafik operasional">
        <div class="card elev-sm" style="padding:20px">
          <div class="card-head">
            <div>
              <h3 class="card-title">Pendapatan 6 Bulan Terakhir</h3>
              <span class="small muted">Tren penerimaan pembayaran sewa kos</span>
            </div>
            <a href="{{ route('reports.index') }}" class="btn btn-ghost" style="font-size:13px">Lihat Laporan</a>
          </div>

          <div class="chart-bars">
            @foreach($chartMonths as $cm)
              <div class="chart-col">
                <div class="chart-val">
                  @if($cm['amount'] >= 1000000)
                    {{ number_format($cm['amount'] / 1000000, 1) }}jt
                  @elseif($cm['amount'] > 0)
                    {{ number_format($cm['amount'] / 1000, 0) }}rb
                  @else
                    0
                  @endif
                </div>
                <div class="chart-bar" style="height: {{ $cm['pct'] }}%" title="{{ $cm['full'] }}: Rp {{ number_format($cm['amount'], 0, ',', '.') }}"></div>
              </div>
            @endforeach
          </div>

          <div class="chart-labels">
            @foreach($chartMonths as $cm)
              <span>{{ $cm['label'] }}</span>
            @endforeach
          </div>
        </div>

        <!-- Donut Okupansi Kamar -->
        <div class="card elev-sm" style="padding:20px">
          <h3 class="card-title">Okupansi Kamar</h3>
          <div class="flex donut-wrap">
            <svg width="150" height="150" viewBox="0 0 150 150" role="img" aria-label="Okupansi kamar">
              <!-- Background lingkaran dasar -->
              <circle cx="75" cy="75" r="56" fill="none" stroke="var(--color-neutral-200)" stroke-width="22"></circle>
              <!-- Segmen Terisi (Hitam) -->
              <circle cx="75" cy="75" r="56" fill="none" stroke="var(--color-neutral-900)" stroke-width="22"
                      stroke-dasharray="{{ $dashTerisi }} {{ $circumference }}"
                      transform="rotate(-90 75 75)"></circle>
              <!-- Segmen Perbaikan (Merah Akses) jika ada -->
              @if($dashPerbaikan > 0)
                <circle cx="75" cy="75" r="56" fill="none" stroke="var(--color-accent)" stroke-width="22"
                        stroke-dasharray="{{ $dashPerbaikan }} {{ $circumference }}"
                        transform="rotate({{ -90 + ($dashTerisi / $circumference * 360) }} 75 75)"></circle>
              @endif
              <text x="75" y="73" text-anchor="middle" font-family="Archivo, sans-serif" font-weight="800" font-size="26" fill="var(--color-text)">
                {{ $occupancyPct }}%
              </text>
              <text x="75" y="90" text-anchor="middle" font-size="11" fill="var(--color-neutral-600)">terisi</text>
            </svg>

            <div class="legend">
              <div class="flex items-center" style="gap:6px">
                <span class="sw" style="background:var(--color-neutral-900)"></span>
                Terisi — <strong>{{ $occupiedRooms }}</strong>
              </div>
              <div class="flex items-center" style="gap:6px">
                <span class="sw" style="background:var(--color-neutral-200);border:1px solid var(--color-divider)"></span>
                Kosong — <strong>{{ $availableRooms }}</strong>
              </div>
              <div class="flex items-center" style="gap:6px">
                <span class="sw" style="background:var(--color-accent)"></span>
                Perbaikan — <strong>{{ $maintRooms }}</strong>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Pembayaran Terbaru & Maintenance Aktif -->
      <section class="grid-32" aria-label="Aktivitas operasional terkini">
        <div class="card elev-sm" style="padding:20px">
          <div class="card-head">
            <h3 class="card-title">Pembayaran Terbaru</h3>
            <div class="flex" style="gap:8px">
              @if(!auth()->user() || !auth()->user()->hasRole('owner'))
                <a class="btn btn-primary" href="{{ route('payments.create') }}" style="font-size:13px">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                  Catat
                </a>
              @endif
              <a class="btn btn-ghost" href="{{ route('payments.index') }}" style="font-size:13px">Lihat semua</a>
            </div>

          </div>

          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Penghuni</th>
                  <th>Kamar</th>
                  <th>Jumlah</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentPayments as $pay)
                  <tr>
                    <td class="font-semibold">{{ $pay->lease->tenant->user->name ?? '—' }}</td>
                    <td>Kamar {{ $pay->lease->room->room_number ?? '—' }}</td>
                    <td class="font-semibold">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                    <td>
                      @if($pay->status === 'paid')
                        <span class="tag tag-accent">Lunas</span>
                      @elseif($pay->status === 'pending')
                        <span class="tag tag-outline">Menunggu</span>
                      @elseif($pay->status === 'overdue')
                        <span class="tag tag-accent" style="background:#dc2626">Terlambat</span>
                      @else
                        <span class="tag tag-outline">Belum Bayar</span>
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('payments.show', $pay) }}" class="btn btn-ghost btn-sm">Lihat</a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center py-4 text-neutral-500">
                      Belum ada transaksi pembayaran.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="card elev-sm" style="padding:20px">
          <div class="card-head">
            <h3 class="card-title">Maintenance / Keluhan Aktif</h3>
            @if(!auth()->user() || !auth()->user()->hasRole('owner'))
              <a class="btn btn-ghost" href="{{ route('maintenance.create') }}" style="font-size:13px">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Tambah
              </a>
            @endif
          </div>

          <div>
            @forelse($activeComplaints as $complaint)
              <div style="display:flex; justify-content:space-between; align-items:flex-start; padding:12px 0; border-bottom:1px solid var(--color-divider)">
                <div>
                  <div style="font-weight:600; font-size:14px; margin-bottom:2px">
                    <a href="{{ route('maintenance.show', $complaint) }}" style="text-decoration:none; color:inherit">
                      {{ $complaint->title }}
                    </a>
                  </div>
                  <div class="small muted">
                    Kamar {{ $complaint->room->room_number ?? '—' }} · Oleh {{ $complaint->tenant->user->name ?? 'Penghuni' }}
                  </div>
                </div>
                <div>
                  @if($complaint->priority === 'high')
                    <span class="tag tag-accent">Darurat</span>
                  @else
                    <span class="tag tag-outline">{{ ucfirst($complaint->priority) }}</span>
                  @endif
                </div>
              </div>
            @empty
              <p class="small muted py-4 text-center" style="margin:0">
                Tidak ada laporan keluhan yang sedang aktif.
              </p>
            @endforelse
          </div>
        </div>
      </section>

      <!-- Sekilas Kamar Kos (Khusus Admin & Owner) -->
      @if(!auth()->user() || (!auth()->user()->hasRole('staff') && !auth()->user()->hasRole('tenant')))
      <section class="card elev-sm section-card" id="section-kamar" aria-label="Kelola kamar">
        <div class="card-head">
          <div>
            <h3 class="card-title">Sekilas Kamar Kos</h3>
            <p class="small muted" style="margin:0">Daftar kamar kos terdaftar di sistem</p>
          </div>
          <div class="flex" style="gap:8px">
            @if(!auth()->user() || !auth()->user()->hasRole('owner'))
              <a href="{{ route('rooms.create') }}" class="btn btn-primary" style="font-size:13px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Tambah Kamar
              </a>
            @endif
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary" style="font-size:13px">
              Lihat Semua Kamar
            </a>
          </div>
        </div>


        <div class="grid-4" style="gap:16px; margin-top:10px">
          @forelse($rooms as $room)
            <div class="card elev-sm" style="padding:14px; border:1px solid var(--color-divider); display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
                  <strong style="font-size:16px">Kamar {{ $room->room_number }}</strong>
                  @if($room->status === 'occupied')
                    <span class="tag tag-accent">Terisi</span>
                  @elseif($room->status === 'available')
                    <span class="tag tag-outline">Kosong</span>
                  @else
                    <span class="tag tag-outline">Perbaikan</span>
                  @endif
                </div>
                <div class="small muted" style="margin-bottom:6px">Lantai {{ $room->floor }}</div>
                <div style="font-weight:700; font-size:14px; color:var(--color-neutral-900)">
                  Rp {{ number_format($room->price, 0, ',', '.') }}<span class="small muted" style="font-weight:400">/bln</span>
                </div>
              </div>
              <div style="margin-top:12px; padding-top:8px; border-top:1px solid var(--color-divider)">
                <a href="{{ route('rooms.show', $room) }}" class="btn btn-ghost btn-sm" style="width:100%; justify-content:center">
                  Detail Kamar
                </a>
              </div>
            </div>
          @empty
            <p class="small muted col-span-4 text-center py-4">Belum ada data kamar.</p>
          @endforelse
        </div>
      </section>
      @endif

    </main>
@endsection
