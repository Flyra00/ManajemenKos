@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Fasilitas</span>
          </nav>
          <h2 class="page-title">Fasilitas Kos</h2>
          <p class="page-sub">Kelola data fasilitas kos seperti WiFi, AC, kasur, kamar mandi dalam, dan lainnya.</p>
        </div>
        @if(!auth()->user() || !auth()->user()->hasRole('owner'))
          <div class="flex head-actions" style="gap: 8px;">
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary" title="Kembali ke Kelola Kamar">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
              Kelola Kamar
            </a>
            <a href="{{ route('facilities.create') }}" class="btn btn-primary">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Tambah Fasilitas
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
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      <!-- Kartu statistik fasilitas -->
      <section class="grid-3" aria-label="Statistik fasilitas">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Fasilitas</div>
          <div class="stat-value">{{ $stats['total'] ?? $facilities->total() }}</div>
          <div class="stat-sub muted">jenis fasilitas terdaftar</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Fasilitas Digunakan</div>
          <div class="stat-value">{{ $stats['used'] ?? 0 }}</div>
          <div class="stat-sub muted">terpasang di minimal 1 kamar</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Total Alokasi Kamar</div>
          <div class="stat-value">{{ $stats['assignments'] ?? 0 }}</div>
          <div class="stat-sub muted">total relasi kamar & fasilitas</div>
        </div>
      </section>

      <!-- Filter + tabel fasilitas -->
      <section class="card elev-sm section-card" aria-label="Daftar fasilitas">
        <div class="card-head">
          <h3 class="card-title">Daftar Fasilitas Kos</h3>
          <span class="small muted">{{ $facilities->total() }} fasilitas terdaftar</span>
        </div>

        <!-- Filter pencarian -->
        <form method="GET" action="{{ route('facilities.index') }}" class="filter-bar" role="search">
          <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama fasilitas atau deskripsi…" aria-label="Cari fasilitas">
          <button type="submit" class="btn btn-secondary">Terapkan</button>
          @if(request('search'))
            <a href="{{ route('facilities.index') }}" class="btn btn-ghost">Reset</a>
          @endif
        </form>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr>
                <th style="width: 60px;">No</th>
                <th>Nama Fasilitas</th>
                <th>Deskripsi</th>
                <th>Kamar Terpasang</th>
                <th style="width: 160px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($facilities as $facility)
                <tr>
                  <td class="muted">{{ $facilities->firstItem() ? $facilities->firstItem() + $loop->index : $loop->iteration }}</td>
                  <td class="font-semibold">{{ $facility->name }}</td>
                  <td class="text-neutral-600">{{ $facility->description ?: '—' }}</td>
                  <td>
                    @if(($facility->rooms_count ?? 0) > 0)
                      <span class="tag tag-accent">{{ $facility->rooms_count }} Kamar</span>
                    @else
                      <span class="tag tag-outline">Belum dipakai</span>
                    @endif
                  </td>
                  <td>
                    <div class="flex gap-1">
                      <a href="{{ route('facilities.show', $facility) }}" class="btn btn-ghost btn-sm" title="Lihat detail fasilitas">
                        Detail
                      </a>
                      @if(!auth()->user() || !auth()->user()->hasRole('owner'))
                        <a href="{{ route('facilities.edit', $facility) }}" class="btn btn-secondary btn-sm" title="Edit fasilitas">
                          Edit
                        </a>
                        <button type="button" class="btn btn-ghost btn-sm text-accent"
                                onclick="openDeleteFacilityModal('{{ route('facilities.destroy', $facility) }}', '{{ $facility->name }}')">
                          Hapus
                        </button>
                      @endif
                    </div>

                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-6 text-neutral-500">
                    Belum ada fasilitas yang terdaftar.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($facilities->hasPages())
          <div class="card-footer flex justify-between items-center py-3 px-4 border-t border-neutral-200">
            <span class="text-sm text-neutral-600">
              Menampilkan {{ $facilities->firstItem() }} - {{ $facilities->lastItem() }} dari {{ $facilities->total() }} fasilitas
            </span>
            <div>
              {{ $facilities->links() }}
            </div>
          </div>
        @endif
      </section>

    </main>

    <!-- Modal Konfirmasi Hapus Fasilitas -->
    <div class="dialog-backdrop" id="deleteFacilityModal" style="display:none; align-items:center; justify-content:center;" data-close="1">
      <div class="dialog" style="margin: auto;">
        <div class="dialog-title">Konfirmasi Hapus Fasilitas</div>
        <p class="small muted" style="margin:8px 0 16px;line-height:1.5">
          Apakah Anda yakin ingin menghapus fasilitas <strong id="deleteFacilityName"></strong>? Tindakan ini tidak dapat dibatalkan.
        </p>
        <form id="deleteFacilityForm" method="POST" action="">
          @csrf
          @method('DELETE')
          <div class="dialog-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteFacilityModal()">Batal</button>
            <button type="submit" class="btn btn-primary" style="background-color: var(--color-accent, #e53e3e); border-color: var(--color-accent, #e53e3e);">
              Ya, Hapus
            </button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function openDeleteFacilityModal(actionUrl, name) {
        const modal = document.getElementById('deleteFacilityModal');
        const form = document.getElementById('deleteFacilityForm');
        const nameSpan = document.getElementById('deleteFacilityName');

        form.action = actionUrl;
        nameSpan.textContent = name;
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
      }

      function closeDeleteFacilityModal() {
        const modal = document.getElementById('deleteFacilityModal');
        modal.style.display = 'none';
      }

      window.addEventListener('click', function(e) {
        const modal = document.getElementById('deleteFacilityModal');
        if (e.target === modal) {
          closeDeleteFacilityModal();
        }
      });
    </script>
@endsection


