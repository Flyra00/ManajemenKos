@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('maintenance.index') }}">Maintenance</a>
            <span class="sep">/</span>
            <span class="current">Tiket #{{ $maintenance->id }}</span>
          </nav>
          <h2 class="page-title">{{ $maintenance->title }}</h2>
          <p class="page-sub">Rincian laporan kerusakan dan riwayat pengerjaan teknisi.</p>
        </div>
        <div class="flex head-actions">
          <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">Kembali</a>
          @if(!auth()->user() || (!auth()->user()->hasRole('owner') && !auth()->user()->hasRole('tenant')))
            <a href="{{ route('maintenance.edit', $maintenance) }}" class="btn btn-primary">Edit Laporan</a>
          @elseif(auth()->user() && auth()->user()->hasRole('tenant') && $maintenance->status === 'reported')
            <a href="{{ route('maintenance.edit', $maintenance) }}" class="btn btn-primary">Edit Laporan</a>
          @endif
        </div>

      </section>

      {{-- Stat Cards --}}
      <section class="grid-cols-1 md:grid-cols-3 grid gap-4" aria-label="Ringkasan tiket">
        <div class="card elev-sm">
          <div class="stat-label">Status Pengerjaan</div>
          <div class="stat-value">
            @if($maintenance->status === 'reported')
              <span class="tag tag-accent">Reported</span>
            @elseif($maintenance->status === 'in_progress')
              <span class="tag tag-neutral">Sedang Diproses</span>
            @elseif($maintenance->status === 'completed')
              <span class="tag tag-outline" style="color:var(--color-neutral-800); font-weight:600">Selesai</span>
            @else
              <span class="tag tag-outline">Dibatalkan</span>
            @endif
          </div>
          <div class="stat-sub muted">
            @if($maintenance->status === 'completed' && $maintenance->resolved_at)
              Diselesaikan: {{ $maintenance->resolved_at->translatedFormat('d M Y') }}
            @else
              Dilaporkan: {{ $maintenance->reported_at ? $maintenance->reported_at->translatedFormat('d M Y') : '—' }}
            @endif
          </div>
        </div>

        <div class="card elev-sm">
          <div class="stat-label">Tingkat Prioritas</div>
          <div class="stat-value">
            @if($maintenance->priority === 'high')
              <span class="tag tag-accent">Tinggi (Mendesak)</span>
            @elseif($maintenance->priority === 'medium')
              <span class="tag tag-neutral">Sedang (Biasa)</span>
            @else
              <span class="tag tag-outline">Rendah</span>
            @endif
          </div>
          <div class="stat-sub muted">
            Lokasi: Kamar {{ $maintenance->room->room_number ?? '—' }} (Lt. {{ $maintenance->room->floor ?? '—' }})
          </div>
        </div>

        @if(!auth()->user() || !auth()->user()->hasRole('tenant'))
          <div class="card elev-sm">
            <div class="stat-label">Total Biaya Perbaikan</div>
            <div class="stat-value">Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</div>
            <div class="stat-sub muted">
              Ditangani oleh: {{ $maintenance->handler->name ?? 'Belum ditentukan' }}
            </div>
          </div>
        @else
          <div class="card elev-sm">
            <div class="stat-label">Penanganan Teknisi</div>
            <div class="stat-value" style="font-size: 18px;">
              {{ $maintenance->handler->name ?? 'Menunggu Penugasan' }}
            </div>
            <div class="stat-sub muted">
              Status: {{ $maintenance->status === 'reported' ? 'Menunggu verifikasi' : ($maintenance->status === 'in_progress' ? 'Sedang diperbaiki' : 'Selesai') }}
            </div>
          </div>
        @endif
      </section>

      <!-- Informasi Laporan & Foto -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start" aria-label="Detail tiket maintenance">
        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Rincian Tiket Kerusakan</h3>
          </div>
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 m-0">
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Kamar / Lokasi</dt>
              <dd class="m-0 font-semibold">
                @if($maintenance->room)
                  @if(auth()->user() && auth()->user()->hasRole('tenant'))
                    <span class="text-neutral-800">Kamar {{ $maintenance->room->room_number }} (Lantai {{ $maintenance->room->floor }})</span>
                  @else
                    <a href="{{ route('rooms.show', $maintenance->room) }}" class="text-accent hover:underline">
                      Kamar {{ $maintenance->room->room_number }} (Lantai {{ $maintenance->room->floor }})
                    </a>
                  @endif
                @else
                  —
                @endif
              </dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Pelapor (Penghuni)</dt>
              <dd class="m-0 font-semibold">
                @if($maintenance->tenant && $maintenance->tenant->user)
                  @if(auth()->user() && auth()->user()->hasRole('tenant'))
                    <span class="text-neutral-800">{{ $maintenance->tenant->user->name }}</span>
                  @else
                    <a href="{{ route('tenants.show', $maintenance->tenant) }}" class="text-accent hover:underline">
                      {{ $maintenance->tenant->user->name }}
                    </a>
                  @endif
                @else
                  —
                @endif
              </dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Waktu Pelaporan</dt>
              <dd class="m-0">{{ $maintenance->reported_at ? $maintenance->reported_at->translatedFormat('d F Y') : '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Waktu Penyelesaian</dt>
              <dd class="m-0">{{ $maintenance->resolved_at ? $maintenance->resolved_at->translatedFormat('d F Y') : 'Belum selesai' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Teknisi Penanggung Jawab</dt>
              <dd class="m-0 font-semibold">{{ $maintenance->handler->name ?? 'Belum ditugaskan' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Kontak Teknisi</dt>
              <dd class="m-0 text-sm">{{ $maintenance->handler->phone ?? ($maintenance->handler->email ?? '—') }}</dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-xs text-neutral-600 mb-1">Deskripsi Kerusakan & Catatan</dt>
              <dd class="m-0 text-sm text-neutral-800" style="white-space: pre-line; line-height: 1.6;">{{ $maintenance->description }}</dd>
            </div>
          </dl>
        </div>

        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Dokumentasi & Bukti Pengerjaan</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <div class="text-xs font-semibold text-neutral-700 mb-2">Foto Kerusakan (Dilaporkan)</div>
              @if($maintenance->image_path)
                <div class="p-2 border border-neutral-200 rounded-lg text-center bg-neutral-50">
                  <a href="{{ asset('storage/' . $maintenance->image_path) }}" target="_blank" title="Klik untuk melihat foto penuh">
                    <img src="{{ asset('storage/' . $maintenance->image_path) }}" alt="Foto kerusakan {{ $maintenance->title }}" class="max-h-64 mx-auto rounded object-contain">
                  </a>
                  <p class="text-xs text-neutral-500 mt-2">Klik gambar untuk melihat resolusi penuh</p>
                </div>
              @else
                <div class="py-10 text-center text-neutral-500 border border-dashed border-neutral-300 rounded-lg">
                  <p class="text-xs">Belum ada foto kerusakan awal yang diunggah.</p>
                </div>
              @endif
            </div>

            <div>
              <div class="text-xs font-semibold text-neutral-700 mb-2">Bukti Foto Penyelesaian (Teknisi)</div>
              @if($maintenance->completion_image)
                <div class="p-2 border border-neutral-200 rounded-lg text-center bg-neutral-50">
                  <a href="{{ asset('storage/' . $maintenance->completion_image) }}" target="_blank" title="Klik untuk melihat bukti penyelesaian">
                    <img src="{{ asset('storage/' . $maintenance->completion_image) }}" alt="Bukti penyelesaian {{ $maintenance->title }}" class="max-h-64 mx-auto rounded object-contain">
                  </a>
                  <p class="text-xs text-neutral-500 mt-2">Klik gambar untuk melihat resolusi penuh</p>
                </div>
              @else
                <div class="py-10 text-center text-neutral-500 border border-dashed border-neutral-300 rounded-lg">
                  <p class="text-xs">Belum ada foto bukti penyelesaian.</p>
                </div>
              @endif
            </div>
          </div>
        </div>
      </section>

    </main>
@endsection
