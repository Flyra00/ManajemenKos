@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('tenants.index') }}">Penghuni</a>
            <span class="sep">/</span>
            <span class="current">Detail Penghuni</span>
          </nav>
          <h2 class="page-title">{{ $tenant->user->name ?? 'Penghuni' }}</h2>
          <p class="page-sub">Informasi lengkap biodata, status kamar, dan riwayat sewa.</p>
        </div>
        <div class="flex head-actions" style="gap: 8px;">
          <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Kembali</a>
          @if(!auth()->user() || !auth()->user()->hasRole('owner'))
            <a href="{{ route('leases.create', ['tenant_id' => $tenant->id]) }}" class="btn btn-primary" title="Buat Kontrak Sewa Baru untuk Penghuni Ini">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
              Buat Kontrak Sewa
            </a>
          @endif
        </div>

      </section>

      {{-- Ringkasan status --}}
      <section class="grid-cols-1 md:grid-cols-3 grid gap-4" aria-label="Ringkasan status penghuni">
        <div class="card elev-sm">
          <div class="stat-label">Status Sewa</div>
          <div class="stat-value">
            @if($tenant->activeLease)
              <span class="tag tag-accent">Aktif Menghuni</span>
            @else
              <span class="tag tag-neutral">Tidak Aktif</span>
            @endif
          </div>
          <div class="stat-sub muted">
            @if($tenant->activeLease && $tenant->activeLease->room)
              Menempati Kamar {{ $tenant->activeLease->room->room_number }}
            @else
              Belum ada sewa aktif
            @endif
          </div>
        </div>

        <div class="card elev-sm">
          <div class="stat-label">Total Riwayat Sewa</div>
          <div class="stat-value">{{ $tenant->leases->count() }}</div>
          <div class="stat-sub muted">kontrak tercatat</div>
        </div>

        <div class="card elev-sm">
          <div class="stat-label">Keluhan / Maintenance</div>
          <div class="stat-value">{{ $tenant->maintenanceRequests->count() }}</div>
          <div class="stat-sub muted">laporan diajukan</div>
        </div>
      </section>

      <!-- Informasi Biodata -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start" aria-label="Detail data penghuni">
        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Biodata Pribadi</h3>
          </div>
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 m-0">
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nama Lengkap</dt>
              <dd class="m-0 font-semibold">{{ $tenant->user->name ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nomor KTP (NIK)</dt>
              <dd class="m-0 font-mono text-neutral-600" title="Data NIK dilindungi hak privasi penghuni">
                •••••••••••••••• <span class="tag tag-neutral text-xs" style="font-size: 10px; padding: 1px 6px;">Privat</span>
              </dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Email</dt>
              <dd class="m-0">{{ $tenant->user->email ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nomor Telepon</dt>
              <dd class="m-0">{{ $tenant->user->phone ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Pekerjaan</dt>
              <dd class="m-0">{{ $tenant->job ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Terdaftar Sejak</dt>
              <dd class="m-0 text-sm text-neutral-600">{{ $tenant->created_at ? $tenant->created_at->translatedFormat('d F Y') : '—' }}</dd>
            </div>
          </dl>
        </div>

        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Kontak Darurat</h3>
          </div>
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 m-0">
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nama Kontak</dt>
              <dd class="m-0 font-semibold">{{ $tenant->emergency_name ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nomor Telepon</dt>
              <dd class="m-0 font-semibold">{{ $tenant->emergency_contact ?? '—' }}</dd>
            </div>
          </dl>
        </div>
      </section>

      <!-- Riwayat Sewa -->
      <section class="card elev-sm section-card" aria-label="Riwayat sewa kamar">
        <div class="card-head">
          <h3 class="card-title">Riwayat Kontrak Sewa</h3>
          <span class="small muted">{{ $tenant->leases->count() }} kontrak</span>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Kamar</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Biaya Sewa</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($tenant->leases as $lease)
                <tr>
                  <td class="font-semibold">
                    {{ $lease->room ? 'Kamar ' . $lease->room->room_number : '—' }}
                  </td>
                  <td>{{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->translatedFormat('d M Y') : '—' }}</td>
                  <td>{{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->translatedFormat('d M Y') : 'Belum selesai' }}</td>
                  <td>Rp {{ number_format($lease->m_price ?? $lease->monthly_price ?? 0, 0, ',', '.') }}<span class="small muted">/bln</span></td>
                  <td>
                    @if($lease->status === 'active')
                      <span class="tag tag-accent">Aktif</span>
                    @elseif($lease->status === 'completed')
                      <span class="tag tag-neutral">Selesai</span>
                    @else
                      <span class="tag tag-outline">Dibatalkan</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-6 text-neutral-500">
                    Belum ada riwayat kontrak sewa untuk penghuni ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>

    </main>
@endsection
