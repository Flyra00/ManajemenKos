@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Penghuni</span>
          </nav>
          <h2 class="page-title">Penghuni</h2>
          <p class="page-sub">Data penghuni kos dan status tinggal</p>
        </div>
        @if(!auth()->user() || !auth()->user()->hasRole('owner'))
          <div class="flex head-actions">
            <a href="{{ route('tenants.create') }}" class="btn btn-primary">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Tambah Penghuni
            </a>
          </div>
        @endif
      </section>


      {{-- Flash Message --}}
      @if(session('success'))
        <div class="alert alert-success" role="status">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif
      @if(session('error'))
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      <!-- Kartu statistik -->
      <section class="grid-4" aria-label="Statistik penghuni">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Penghuni</div>
          <div class="stat-value">{{ $stats['total'] }}</div>
          <div class="stat-sub muted">terdaftar di sistem</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Penghuni Aktif</div>
          <div class="stat-value">{{ $stats['aktif'] }}</div>
          <div class="stat-sub muted">sedang menempati kamar</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Penghuni Baru</div>
          <div class="stat-value">{{ $stats['baru'] }}</div>
          <div class="stat-sub muted">bulan ini</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Sudah Keluar</div>
          <div class="stat-value">{{ $stats['keluar'] }}</div>
          <div class="stat-sub muted">riwayat sewa selesai</div>
        </div>
      </section>

      <!-- Filter + tabel penghuni -->
      <section class="card elev-sm section-card" aria-label="Daftar penghuni">
        <div class="card-head">
          <h3 class="card-title">Daftar Penghuni</h3>
          <span class="small muted">{{ $tenants->total() }} penghuni</span>
        </div>

        <form method="GET" action="{{ route('tenants.index') }}" class="filter-bar" role="search">
          <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, KTP, pekerjaan, kontak…" aria-label="Cari penghuni">
          <select class="input" name="status" aria-label="Filter status">
            <option value="">Semua status</option>
            <option value="Aktif" @selected(request('status') === 'Aktif')>Aktif</option>
            <option value="Keluar" @selected(request('status') === 'Keluar')>Keluar</option>
          </select>
          <button type="submit" class="btn btn-secondary">Terapkan</button>
          @if(request('search') || request('status'))
            <a href="{{ route('tenants.index') }}" class="btn btn-ghost">Reset</a>
          @endif
        </form>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama</th>
                <th>KTP</th>
                <th>Telepon</th>
                <th>Kamar</th>
                <th>Pekerjaan</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($tenants as $tenant)
                <tr>
                  <td class="muted">{{ $tenants->firstItem() ? $tenants->firstItem() + $loop->index : $loop->iteration }}</td>
                  <td class="font-semibold">{{ $tenant->user->name ?? '—' }}</td>
                  <td>
                    <span class="muted font-mono" title="Data NIK disembunyikan demi privasi penghuni">••••••••••••••••</span>
                    <span class="tag tag-neutral text-xs" style="font-size: 10px; padding: 1px 6px;">Privat</span>
                  </td>
                  <td>{{ $tenant->user->phone ?? '—' }}</td>
                  <td>
                    @if($tenant->activeLease && $tenant->activeLease->room)
                      <span class="font-medium">Kamar {{ $tenant->activeLease->room->room_number }}</span>
                    @else
                      <span class="muted">—</span>
                    @endif
                  </td>
                  <td>{{ $tenant->job ?? '—' }}</td>
                  <td>
                    @if($tenant->activeLease)
                      <span class="tag tag-accent">Aktif</span>
                    @else
                      <span class="tag tag-neutral">Nonaktif</span>
                    @endif
                  </td>
                  <td>
                    <div class="flex gap-1 justify-center">
                      <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-ghost px-2 py-1.5" title="Lihat Detail Penghuni">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span class="text-xs">Detail</span>
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-6 text-neutral-500">
                    Belum ada data penghuni.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($tenants->hasPages())
          <div class="pt-3">
            {{ $tenants->links() }}
          </div>
        @endif
      </section>

    </main>
@endsection

