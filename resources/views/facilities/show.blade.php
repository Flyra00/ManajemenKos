@extends('layouts.app')
@section('content')
    <main class="max-w-[1200px] w-full mx-auto p-6 flex flex-col gap-5">

      <section class="flex items-end justify-between gap-4 flex-wrap" aria-label="Judul halaman">
        <div>
          <nav class="flex items-center gap-1.5 flex-wrap text-xs text-neutral-600 mb-1" aria-label="Breadcrumb">
            <a class="no-underline text-ink font-medium hover:text-accent" href="{{ route('dashboard') }}">Dashboard</a>
            <span class="text-neutral-400">/</span>
            <a class="no-underline text-ink font-medium hover:text-accent" href="{{ route('facilities.index') }}">Facilities</a>
            <span class="text-neutral-400">/</span>
            <span class="text-neutral-600">Facility Detail</span>
          </nav>
          <h2 class="text-[26px]">{{ $facility->name }}</h2>
          <p class="m-0 mt-1 text-[13px] text-neutral-600">Detail facility dan daftar kamar yang menggunakannya.</p>
        </div>
        <div class="flex items-center gap-2.5">
          <a href="{{ route('facilities.index') }}" class="btn btn-secondary">Kembali</a>
          @if(!auth()->user() || !auth()->user()->hasRole('owner'))
            <a href="{{ route('facilities.edit', $facility) }}" class="btn btn-primary">Edit Facility</a>
          @endif
        </div>

      </section>

      <!-- Info facility -->
      <section class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-4 items-start" aria-label="Info facility">
        <div class="card">
          <h3 class="card-title">Informasi Facility</h3>
          <dl class="m-0 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <dt class="text-xs text-ink/70 mb-1">Facility Name</dt>
              <dd class="m-0 font-semibold">{{ $facility->name }}</dd>
            </div>
            <div>
              <dt class="text-xs text-ink/70 mb-1">Description</dt>
              <dd class="m-0 text-sm text-neutral-600">{{ $facility->description ?? '—' }}</dd>
            </div>
          </dl>
        </div>

        <div class="card justify-center" style="border-top:2px solid var(--accent, #ec3013)">
          <div class="text-xs text-ink/70">Total Rooms</div>
          <div class="font-extrabold text-[32px] leading-none">{{ $facility->rooms_count ?? $facility->rooms->count() }}</div>
          <div class="text-xs text-neutral-600">kamar menggunakan facility ini</div>
        </div>
      </section>

      <!-- Rooms Using This Facility -->
      <section class="card" aria-label="Rooms using this facility">
        <div class="flex items-center justify-between">
          <h3 class="card-title">Rooms Using This Facility</h3>
          <span class="small muted">{{ $facility->rooms->count() }} kamar</span>
        </div>

        <div class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th>Room Number</th>
                <th>Floor</th>
                <th>Price</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($facility->rooms as $room)
                <tr>
                  <td class="font-semibold">{{ $room->room_number }}</td>
                  <td class="muted">Lantai {{ $room->floor ?? '—' }}</td>
                  <td>Rp {{ number_format($room->price, 0, ',', '.') }}<span class="small muted">/bln</span></td>
                  <td>
                    @if($room->status === 'occupied')
                      <span class="tag tag-neutral">Terisi</span>
                    @elseif($room->status === 'available')
                      <span class="tag tag-outline">Kosong</span>
                    @else
                      <span class="tag tag-accent">Perbaikan</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center py-6 text-neutral-500">
                    Belum ada kamar yang menggunakan fasilitas ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>

    </main>
@endsection
