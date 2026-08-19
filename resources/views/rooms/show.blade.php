@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('rooms.index') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('rooms.index') }}">Master Kamar</a>
            <span class="sep">/</span>
            <span class="current">{{ $room->room_number }}</span>
          </nav>
          <h2 class="page-title">Detail Kamar {{ $room->room_number }}</h2>
          <p class="page-sub">Informasi lengkap kamar kos, status, fasilitas, dan foto.</p>
        </div>
        <div class="flex head-actions">
          <a href="{{ route('rooms.edit', $room) }}" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
            Edit
          </a>
        </div>
      </section>

      <!-- Hero: foto + informasi utama -->
      <section class="card elev-sm detail-hero" aria-label="Ringkasan kamar">
        <div class="detail-img-lg">
          @if($room->image)
            <img src="{{ asset('storage/' . $room->image) }}" alt="Foto kamar {{ $room->room_number }}">
          @else
            <span class="detail-img-ph">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
              <span>Belum ada foto</span>
            </span>
          @endif
        </div>

        <div>
          <h3 class="room-no-lg">{{ $room->room_number }}</h3>
          <p class="room-price">Rp {{ number_format($room->price_per_month, 0, ',', '.') }} <span>/ bulan</span></p>

          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Lantai</span>
              <span class="info-value">Lantai {{ $room->floor }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Status</span>
              <span class="info-value">
                <span class="tag {{ $room->status === 'Terisi' ? 'tag-neutral' : ($room->status === 'Perbaikan' ? 'tag-accent' : 'tag-outline') }}">{{ $room->status }}</span>
              </span>
            </div>
            <div class="info-item">
              <span class="info-label">Status Aktif</span>
              <span class="info-value">
                @if($room->is_active)
                  <span class="tag tag-neutral">Aktif</span>
                @else
                  <span class="tag tag-outline">Nonaktif</span>
                @endif
              </span>
            </div>
            <div class="info-item">
              <span class="info-label">Harga</span>
              <span class="info-value">Rp {{ number_format($room->price_per_month, 0, ',', '.') }} / bulan</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Fasilitas + Deskripsi -->
      <section class="grid-2" aria-label="Fasilitas dan deskripsi kamar">
        <div class="card elev-sm form-card">
          <h3 class="card-title">Fasilitas</h3>
          @forelse($room->facilities as $facility)
            <div class="facility-row">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
              <span>{{ $facility->name }}</span>
            </div>
          @empty
            <p class="small muted" style="margin:0">Belum ada fasilitas untuk kamar ini.</p>
          @endforelse
        </div>

        <div class="card elev-sm form-card">
          <h3 class="card-title">Deskripsi</h3>
          @if($room->description)
            <p style="margin:0;font-size:14px;white-space:pre-line">{{ $room->description }}</p>
          @else
            <p class="small muted" style="margin:0">Belum ada deskripsi untuk kamar ini.</p>
          @endif
        </div>
      </section>

      <div class="form-actions">
        <a href="{{ route('rooms.edit', $room) }}" class="btn btn-primary">Edit Kamar</a>
        <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Kembali</a>
      </div>

    </main>
@endsection
