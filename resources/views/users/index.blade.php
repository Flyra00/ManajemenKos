@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Pengguna</span>
          </nav>
          <h2 class="page-title">Manajemen Pengguna</h2>
          <p class="page-sub">Kelola seluruh akun terdaftar, status verifikasi email, dan penetapan peran akses (Role)</p>
        </div>
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

      <!-- Kartu Statistik Pengguna -->
      <section class="grid-4" aria-label="Statistik pengguna">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Akun</div>
          <div class="stat-value">{{ $totalUsers }}</div>
          <div class="stat-sub muted">pengguna terdaftar di sistem</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Admin &amp; Owner</div>
          <div class="stat-value">{{ $totalAdmins + $totalOwners }}</div>
          <div class="stat-sub muted">{{ $totalAdmins }} admin • {{ $totalOwners }} owner</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid #2563eb">
          <div class="stat-label">Staff Kos</div>
          <div class="stat-value">{{ $totalStaff }}</div>
          <div class="stat-sub muted">petugas operasional &amp; teknisi</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid #22c55e">
          <div class="stat-label">Penyewa (Tenant)</div>
          <div class="stat-value">{{ $totalTenants }}</div>
          <div class="stat-sub muted">akun penghuni &amp; pemesan kamar</div>
        </div>
      </section>

      <!-- Filter Pencarian & Peran -->
      <section class="card elev-sm" style="margin-bottom:20px; padding:16px;" aria-label="Filter pengguna">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3">
          <div class="flex-1 min-w-[240px]">
            <input 
              type="text" 
              name="q" 
              class="input text-sm w-full" 
              placeholder="Cari nama, email, atau nomor HP..." 
              value="{{ request('q') }}"
            >
          </div>
          <div style="min-width:160px;">
            <select name="role" class="input text-sm w-full">
              <option value="">Semua Peran</option>
              <option value="admin" @selected(request('role') === 'admin')>Admin</option>
              <option value="owner" @selected(request('role') === 'owner')>Owner</option>
              <option value="staff" @selected(request('role') === 'staff')>Staff</option>
              <option value="tenant" @selected(request('role') === 'tenant')>Tenant</option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
              Filter
            </button>
            @if(request()->hasAny(['q', 'role']))
              <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm" title="Reset filter">
                Reset
              </a>
            @endif
          </div>
        </form>
      </section>

      <!-- Tabel Daftar Pengguna -->
      <section class="card elev-sm" aria-label="Tabel pengguna">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th style="width:60px;">No</th>
                <th>Pengguna</th>
                <th>Nomor Telepon</th>
                <th>Status Verifikasi</th>
                <th>Peran Saat Ini</th>
                <th>Terdaftar</th>
                @if(auth()->check() && auth()->user()->hasRole('admin'))
                  <th style="width:230px;">Kelola Peran</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($users as $u)
                @php
                  $currentRole = $u->roles->first()?->name ?? 'tenant';
                  $isVerified = !is_null($u->email_verified_at) || in_array($currentRole, ['admin', 'owner', 'staff']);
                @endphp
                <tr>
                  <td class="muted">{{ $users->firstItem() ? $users->firstItem() + $loop->index : $loop->iteration }}</td>
                  <td>
                    <div class="font-semibold" style="color:var(--color-neutral-900);">{{ $u->name }}</div>
                    <div class="small muted" style="font-size:12px;">{{ $u->email }}</div>
                  </td>
                  <td>{{ $u->phone ?: '—' }}</td>
                  <td>
                    @if($isVerified)
                      <span class="tag" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; font-size:11px;">
                        ✓ Terverifikasi
                      </span>
                    @else
                      <span class="tag" style="background:#fffbeb; color:#92400e; border:1px solid #fef3c7; font-size:11px;">
                        ⏳ Menunggu Gmail
                      </span>
                    @endif
                  </td>
                  <td>
                    @if($currentRole === 'admin')
                      <span class="tag tag-accent">Admin</span>
                    @elseif($currentRole === 'owner')
                      <span class="tag tag-outline" style="border-color:var(--color-neutral-900); font-weight:700">Owner</span>
                    @elseif($currentRole === 'staff')
                      <span class="tag" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; font-size:11px; font-weight:700">Staff</span>
                    @else
                      <span class="tag tag-outline">Tenant</span>
                    @endif
                  </td>
                  <td class="small muted" style="font-size:12px;">
                    {{ $u->created_at ? $u->created_at->format('d M Y') : '—' }}
                  </td>
                  @if(auth()->check() && auth()->user()->hasRole('admin'))
                    <td>
                      <form method="POST" action="{{ route('users.role', $u) }}" class="flex items-center" style="gap:6px">
                        @csrf
                        @method('PUT')
                        <select class="input py-1 text-xs" name="role" style="min-width:105px;">
                          <option value="admin" @selected($currentRole === 'admin')>Admin</option>
                          <option value="owner" @selected($currentRole === 'owner')>Owner</option>
                          <option value="staff" @selected($currentRole === 'staff')>Staff</option>
                          <option value="tenant" @selected($currentRole === 'tenant')>Tenant</option>
                        </select>
                        <button type="submit" class="btn btn-secondary btn-sm" title="Terapkan peran">
                          Ubah
                        </button>
                      </form>
                    </td>
                  @endif
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-8 text-neutral-500">
                    <div style="font-size:14px; margin-bottom:4px;">Tidak ditemukan data pengguna yang cocok.</div>
                    <div class="small muted">Coba ubah kata kunci pencarian atau reset filter peran.</div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($users->hasPages())
          <div class="card-footer flex justify-between items-center py-3 px-4 border-t border-neutral-200">
            <span class="text-sm text-neutral-600">
              Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} akun
            </span>
            <div>
              {{ $users->links() }}
            </div>
          </div>
        @endif
      </section>

    </main>
@endsection
