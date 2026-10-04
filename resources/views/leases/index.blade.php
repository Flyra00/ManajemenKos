@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Kontrak Sewa</span>
          </nav>
          <h2 class="page-title">Kontrak Sewa</h2>
          <p class="page-sub">Kelola kontrak sewa kamar dan masa berlaku penghuni kos.</p>
        </div>
        @if(!auth()->user() || !auth()->user()->hasRole('owner'))
          <div class="flex head-actions">
            <a href="{{ route('leases.create') }}" class="btn btn-primary">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Tambah Kontrak
            </a>
          </div>
        @endif
      </section>


      @if(session('success'))
        <div class="alert alert-success" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      <!-- Kartu statistik -->
      <section class="grid-4" aria-label="Statistik kontrak">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Kontrak</div>
          <div class="stat-value">{{ $stats['total'] }}</div>
          <div class="stat-sub muted">semua riwayat</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Kontrak Aktif</div>
          <div class="stat-value">{{ $stats['aktif'] }}</div>
          <div class="stat-sub muted">sedang berjalan</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Akan Berakhir</div>
          <div class="stat-value">{{ $stats['segera'] }}</div>
          <div class="stat-sub muted">dalam 30 hari ke depan</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Selesai / Batal</div>
          <div class="stat-value">{{ $stats['selesai'] }}</div>
          <div class="stat-sub muted">kontrak lampau</div>
        </div>
      </section>

      <!-- Filter + tabel kontrak -->
      <section class="card elev-sm section-card" aria-label="Daftar kontrak sewa">
        <div class="card-head">
          <h3 class="card-title">Daftar Kontrak</h3>
          <span class="small muted">{{ $leases->total() }} kontrak terdaftar</span>
        </div>

        <form method="GET" action="{{ route('leases.index') }}" class="filter-bar">
          <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nomor kontrak, penghuni, kamar…" aria-label="Cari kontrak">
          <select class="input" name="status" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="active" @selected(request('status') === 'active')>Aktif</option>
            <option value="pending" @selected(request('status') === 'pending')>Menunggu Verifikasi (Pending)</option>
            <option value="Segera Berakhir" @selected(request('status') === 'Segera Berakhir')>Akan Berakhir</option>
            <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
          </select>
          <label class="flex items-center" style="gap:6px">
            <span class="small muted">Berakhir bulan:</span>
            <input class="input" type="month" name="month" value="{{ request('month') }}" aria-label="Filter bulan berakhir">
          </label>
          <button type="submit" class="btn btn-secondary">Terapkan</button>
          @if(request()->hasAny(['search', 'status', 'month']))
            <a href="{{ route('leases.index') }}" class="btn btn-ghost">Reset</a>
          @endif
        </form>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr>
                <th>No. Kontrak</th>
                <th>Penghuni</th>
                <th>Kamar</th>
                <th>Mulai</th>
                <th>Berakhir</th>
                <th>Harga/Bln</th>
                <th>Deposit</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($leases as $lease)
                <tr>
                  <td class="font-semibold">#LS-{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }}</td>
                  <td>
                    @if($lease->tenant && $lease->tenant->user)
                      <a href="{{ route('tenants.show', $lease->tenant) }}" class="text-accent hover:underline">
                        {{ $lease->tenant->user->name }}
                      </a>
                    @else
                      <span class="muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($lease->room)
                      <a href="{{ route('rooms.show', $lease->room) }}" class="font-semibold text-neutral-800">
                        Kamar {{ $lease->room->room_number }}
                      </a>
                    @else
                      <span class="muted">—</span>
                    @endif
                  </td>
                  <td>{{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->translatedFormat('d M Y') : '—' }}</td>
                  <td>
                    @if($lease->end_date)
                      {{ \Carbon\Carbon::parse($lease->end_date)->translatedFormat('d M Y') }}
                      @if($lease->status === 'active' && $lease->isOverdue())
                        <span class="tag tag-outline text-xs" style="color:var(--color-accent); border-color:var(--color-accent); margin-left:4px" title="Masa sewa telah terlewati">Lewat Batas</span>
                      @elseif($lease->status === 'active' && $lease->isExpiringSoon(14))
                        <span class="tag tag-accent text-xs" style="margin-left:4px" title="Akan berakhir dalam {{ (int) $lease->end_date->diffInDays(now()->startOfDay()) }} hari">H-{{ (int) $lease->end_date->diffInDays(now()->startOfDay()) }}</span>
                      @elseif($lease->status === 'active' && \Carbon\Carbon::parse($lease->end_date)->diffInDays(now(), false) >= -30 && \Carbon\Carbon::parse($lease->end_date)->isFuture())
                        <span class="tag tag-neutral text-xs" style="margin-left:4px">Segera</span>
                      @endif
                    @else
                      <span class="muted">Tanpa batas</span>
                    @endif
                  </td>
                  <td>Rp {{ number_format($lease->monthly_price, 0, ',', '.') }}</td>
                  <td>Rp {{ number_format($lease->deposit_amount, 0, ',', '.') }}</td>
                  <td>
                    @if($lease->status === 'active')
                      <span class="tag tag-accent">Aktif</span>
                    @elseif($lease->status === 'pending')
                      <span class="tag tag-outline" style="color:var(--color-accent-2); border-color:var(--color-accent-2); font-weight:600">Pending</span>
                    @elseif($lease->status === 'completed')
                      <span class="tag tag-neutral">Selesai</span>
                    @else
                      <span class="tag tag-outline">Dibatalkan</span>
                    @endif
                    @if($lease->renewal_count > 0)
                      <span class="tag tag-outline text-xs" style="margin-left:2px" title="Kontrak telah diperpanjang {{ $lease->renewal_count }} kali">+{{ $lease->renewal_count }}x</span>
                    @endif
                  </td>
                  <td>
                    <div class="flex gap-1">
                      <a href="{{ route('leases.show', $lease) }}" class="btn btn-ghost btn-sm" title="Lihat detail kontrak">
                        Detail
                      </a>
                      @if($lease->status === 'completed' && $lease->checkout_date)
                        <a href="{{ route('leases.checkout-receipt', $lease) }}" target="_blank" class="btn btn-ghost btn-sm" title="Berita Acara Check-Out">
                          BA
                        </a>
                      @endif
                      @if(!auth()->user() || !auth()->user()->hasRole('owner'))
                        <a href="{{ route('leases.edit', $lease) }}" class="btn btn-secondary btn-sm" title="Edit kontrak">
                          Edit
                        </a>
                        <button type="button" class="btn btn-ghost btn-sm text-accent"
                                onclick="openDeleteLeaseModal('{{ route('leases.destroy', $lease) }}', '#LS-{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }}', '{{ $lease->tenant->user->name ?? 'Penghuni' }}')">
                          Hapus
                        </button>
                      @endif
                    </div>

                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-6 text-neutral-500">
                    Tidak ada kontrak sewa yang ditemukan.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($leases->hasPages())
          <div class="card-footer flex justify-between items-center py-3 px-4 border-t border-neutral-200">
            <span class="text-sm text-neutral-600">
              Menampilkan {{ $leases->firstItem() }} - {{ $leases->lastItem() }} dari {{ $leases->total() }} kontrak
            </span>
            <div>
              {{ $leases->links() }}
            </div>
          </div>
        @endif
      </section>

    </main>

    <!-- Modal Konfirmasi Hapus Kontrak -->
    <div class="dialog-backdrop" id="deleteLeaseModal" style="display:none; align-items:center; justify-content:center;" data-close="1">
      <div class="dialog" style="margin: auto;">
        <div class="dialog-title">Konfirmasi Hapus Kontrak</div>
        <p class="small muted" style="margin:8px 0 16px;line-height:1.5">
          Apakah Anda yakin ingin menghapus kontrak sewa <strong id="deleteLeaseCode"></strong> atas nama <strong id="deleteLeaseTenant"></strong>? Tindakan ini tidak dapat dibatalkan.
        </p>
        <form id="deleteLeaseForm" method="POST" action="">
          @csrf
          @method('DELETE')
          <div class="dialog-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteLeaseModal()">Batal</button>
            <button type="submit" class="btn btn-primary" style="background-color: var(--color-accent, #e53e3e); border-color: var(--color-accent, #e53e3e);">
              Ya, Hapus Kontrak
            </button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function openDeleteLeaseModal(actionUrl, code, tenant) {
        const modal = document.getElementById('deleteLeaseModal');
        const form = document.getElementById('deleteLeaseForm');
        const codeSpan = document.getElementById('deleteLeaseCode');
        const tenantSpan = document.getElementById('deleteLeaseTenant');

        form.action = actionUrl;
        codeSpan.textContent = code;
        tenantSpan.textContent = tenant;
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
      }

      function closeDeleteLeaseModal() {
        const modal = document.getElementById('deleteLeaseModal');
        modal.style.display = 'none';
      }

      window.addEventListener('click', function(e) {
        const modal = document.getElementById('deleteLeaseModal');
        if (e.target === modal) {
          closeDeleteLeaseModal();
        }
      });
    </script>
@endsection

