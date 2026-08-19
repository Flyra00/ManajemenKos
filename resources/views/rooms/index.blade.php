@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('rooms.index') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Master Kamar</span>
          </nav>
          <h2 class="page-title">Kelola Kamar</h2>
          <p class="page-sub">Kelola data kamar kos, harga, status, fasilitas, dan foto kamar.</p>
        </div>
        <div class="flex head-actions">
          <a href="{{ route('rooms.create') }}" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Tambah Kamar
          </a>
        </div>
      </section>

      {{-- Flash message (session('success') / session('error')) --}}
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

      {{-- Statistik kamar. Kompatibel untuk $rooms Collection maupun paginator.
           Bila dihitung di controller, ganti dengan variabel yang dikirim
           (mis. $stats['total'], $stats['kosong'], …). --}}
      @php
        $roomList = $rooms instanceof \Illuminate\Pagination\AbstractPaginator ? $rooms->getCollection() : ($rooms ?? collect());
        $statTotal   = $rooms instanceof \Illuminate\Pagination\AbstractPaginator ? $rooms->total() : $roomList->count();
        $statKosong  = $roomList->where('status', 'occupied')->count();
        $statTerisi  = $roomList->where('status', 'available')->count();
        $statPerbaikan = $roomList->where('status', 'maintenance')->count();
        $statLantai  = $roomList->pluck('floor')->unique()->count();
      @endphp
      <section class="grid-4" aria-label="Statistik kamar">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Kamar</div>
          <div class="stat-value">{{ $statTotal }}</div>
          <div class="stat-sub muted">{{ $statLantai }} lantai</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Kamar Kosong</div>
          <div class="stat-value">{{ $statKosong }}</div>
          <div class="stat-sub muted">tersedia untuk disewa</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Kamar Terisi</div>
          <div class="stat-value">{{ $statTerisi }}</div>
          <div class="stat-sub muted">sedang dihuni</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Perbaikan</div>
          <div class="stat-value">{{ $statPerbaikan }}</div>
          <div class="stat-sub muted">sedang dalam perbaikan</div>
        </div>
      </section>

      <!-- Daftar kamar -->
      <section class="card elev-sm section-card" aria-label="Daftar kamar">
        <div class="card-head">
          <h3 class="card-title">Daftar Kamar</h3>
          <span class="small muted">{{ $statTotal }} kamar terdaftar</span>
        </div>

        {{-- Filter dikirim via GET agar Laravel menangani search/filter di server.
             Nama param: search, floor, status, is_active. --}}
        <form method="GET" action="{{ route('rooms.index') }}" class="filter-bar" role="search">
          <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nomor kamar…" aria-label="Cari nomor kamar">
          <select class="input" name="floor" aria-label="Filter lantai">
            <option value="">Semua Lantai</option>
            @for($i = 1; $i <= 3; $i++)
              <option value="{{ $i }}" @selected(request('floor') !== null && (string) request('floor') === (string) $i)>Lantai {{ $i }}</option>
            @endfor
          </select>
          <select class="input" name="status" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="Kosong" @selected(request('status') === 'occupied')>Kosong</option>
            <option value="Terisi" @selected(request('status') === 'available')>Terisi</option>
            <option value="Perbaikan" @selected(request('status') === 'maintenance')>Perbaikan</option>
          </select>
          <select class="input" name="is_active" aria-label="Filter status aktif">
            <option value="">Semua</option>
            <option value="1" @selected(request('is_active') === '1')>Aktif</option>
            <option value="0" @selected(request('is_active') === '0')>Nonaktif</option>
          </select>
          <button type="submit" class="btn btn-secondary">Terapkan</button>
          @if(request()->hasAny(['search', 'floor', 'status', 'is_active']))
            <a href="{{ route('rooms.index') }}" class="btn btn-ghost">Reset</a>
          @endif
        </form>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr>
                <th>Foto</th>
                <th>No. Kamar</th>
                <th>Lantai</th>
                <th>Harga / Bulan</th>
                <th>Status</th>
                <th>Aktif</th>
                <th>Fasilitas</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rooms as $room)
                <tr>
                  <td>
                    @if($room->image)
                      <div class="room-thumb">
                        <img src="{{ asset('storage/' . $room->image) }}" alt="Foto kamar {{ $room->room_number }}" loading="lazy" class="w-32 h-32 object-cover rounded-lg">
                      </div>
                    @else
                      <div class="room-thumb room-thumb-empty" title="Belum ada foto — tambahkan lewat Edit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <span class="thumb-empty-txt">Tanpa Foto</span>
                      </div>
                    @endif
                  </td>
                  <td style="font-weight:600">{{ $room->room_number }}</td>
                  <td class="muted">Lantai {{ $room->floor }}</td>
                  <td>Rp {{ number_format($room->price, 0, ',', '.') }}<span class="muted small">/bln</span></td>
                  <td>
                    <span class="tag {{ $room->status === 'available' ? 'tag-neutral' : ($room->status === 'occupied' ? 'tag-accent' : 'tag-outline') }}">{{ $room->status }}</span>
                  </td>
                  <td>
                    @if($room->is_active)
                      <span class="tag tag-neutral">Aktif</span>
                    @else
                      <span class="tag tag-outline">Nonaktif</span>
                    @endif
                  </td>
                  <td>
                    @if($room->facilities->count())
                      <div class="facility-pills facility-pills--sm">
                        @foreach($room->facilities->take(3) as $facility)
                          <span class="facility-pill">{{ $facility->name }}</span>
                        @endforeach
                        @if($room->facilities->count() > 3)
                          <span class="facility-more">+{{ $room->facilities->count() - 3 }} lagi</span>
                        @endif
                      </div>
                    @else
                      <span class="muted small">—</span>
                    @endif
                  </td>
                  <td>
                    <div class="row-actions">
                      <a class="btn btn-secondary" href="{{ route('rooms.show', $room) }}" title="Detail">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                      </a>
                      <a class="btn btn-secondary" href="{{ route('rooms.edit', $room) }}" title="Edit">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                      </a>
                      <button type="button" class="btn btn-ghost" data-action="room-delete-open"
                              data-url="{{ route('rooms.destroy', $room) }}" data-name="{{ $room->room_number }}" title="Hapus">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11v6M14 11v6"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8">
                    <div class="empty-state">
                      <span class="es-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
                      </span>
                      <h4>Belum ada kamar</h4>
                      <p>Belum ada data kamar yang tersedia.</p>
                      <a href="{{ route('rooms.create') }}" class="btn btn-primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        Tambah kamar pertama
                      </a>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination Laravel — {{ $rooms->links() }} (bukan dummy JS) --}}
        @if($rooms instanceof \Illuminate\Pagination\AbstractPaginator)
          {{ $rooms->links() }}
        @endif
      </section>

    </main>

    <!-- Backdrop navigasi mobile -->
<div class="nav-backdrop" id="navBackdrop"></div>

<!-- ============================================================
     MODAL KONFIRMASI HAPUS — form dikirim ke rooms.destroy
     ============================================================ -->
<div class="dialog-backdrop" id="modalConfirm" hidden data-close="1">
  <div class="dialog">
    <div class="dialog-title">Hapus Kamar?</div>
    <p class="small muted" style="margin:0;line-height:1.6">
      Apakah Anda yakin ingin menghapus kamar <b id="delRoomName">—</b>?
      Tindakan ini tidak dapat dibatalkan.
    </p>
    <form method="POST" id="formDelete" action="">
      @csrf
      @method('DELETE')
      <div class="dialog-actions" style="margin-top:16px">
        <button type="button" class="btn btn-secondary" data-action="close-dialog">Batal</button>
        <button type="submit" class="btn btn-primary">Hapus</button>
      </div>
    </form>
  </div>
</div>
@endsection
