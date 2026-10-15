@extends('layouts.app')

@section('content')
<main class="page">

  <!-- Header Halaman -->
  <section class="page-head" aria-label="Judul halaman">
    <div>
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="sep">/</span>
        <span class="current">Pilihan Kamar</span>
      </nav>
      <h2 class="page-title">Pilihan Kamar Tersedia</h2>
      <p class="page-sub">Temukan kamar yang sesuai dengan kebutuhan Anda dan ajukan sewa langsung secara online.</p>
    </div>
  </section>

  <!-- Banner Peringatan Jika Tenant Sudah Memiliki Kamar (Batas 1 Kamar / Tenant) -->
  @if($existingLease)
    <div class="card elev-sm" style="margin-bottom: 20px; padding: 18px 20px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
      <div style="display: flex; align-items: center; gap: 14px;">
        <div style="width: 40px; height: 40px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706; flex-shrink: 0;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
          <strong style="color: #92400e; font-size: 14px; display: block; margin-bottom: 2px;">
            @if($existingLease->status === 'active')
              Anda Sudah Menempati Kamar {{ $existingLease->room->room_number }} (Status: Aktif)
            @else
              Anda Memiliki Pesanan Kamar {{ $existingLease->room->room_number }} (Menunggu Pembayaran)
            @endif
          </strong>
          <span style="color: #b45309; font-size: 13px; line-height: 1.5; display: block;">
            Setiap akun penghuni dibatasi hanya dapat menyewa 1 kamar kos. Anda tidak dapat mengajukan sewa untuk kamar lain secara bersamaan.
          </span>
        </div>
      </div>
      <div>
        @if($existingLease->status === 'active')
          <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="font-weight: 700;">
            Lihat Kamar Saya &rarr;
          </a>
        @else
          @php
            $pendingInv = $existingLease->payments()->whereIn('status', ['unpaid', 'pending', 'overdue'])->first();
          @endphp
          @if($pendingInv)
            <a href="{{ $pendingInv->public_url }}" class="btn btn-primary btn-sm" style="background: #d97706; border-color: #d97706; font-weight: 700;">
              Bayar Tagihan #{{ $pendingInv->invoice_number }} &rarr;
            </a>
          @endif
        @endif
      </div>
    </div>
  @endif

  <!-- Toolbar Filter & Pencarian -->
  <section class="card elev-sm" style="padding: 16px 20px; border: 1px solid var(--color-divider); background: #ffffff; border-radius: 8px;">
    <form method="GET" action="{{ route('public.rooms.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
      
      <div style="flex: 2; min-width: 200px;">
        <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nomor kamar..." aria-label="Cari nomor kamar">
      </div>

      <div style="flex: 1; min-width: 140px;">
        <select class="input" name="floor" aria-label="Filter lantai">
          <option value="semua">Semua Lantai</option>
          @foreach($floors as $fl)
            <option value="{{ $fl }}" @selected(request('floor') == $fl)>Lantai {{ $fl }}</option>
          @endforeach
        </select>
      </div>

      <div style="flex: 1; min-width: 160px;">
        <select class="input" name="max_price" aria-label="Filter harga">
          <option value="">Semua Harga</option>
          <option value="1000000" @selected(request('max_price') == '1000000')>Maks. Rp 1.000.000</option>
          <option value="1500000" @selected(request('max_price') == '1500000')>Maks. Rp 1.500.000</option>
          <option value="2000000" @selected(request('max_price') == '2000000')>Maks. Rp 2.000.000</option>
          <option value="3000000" @selected(request('max_price') == '3000000')>Maks. Rp 3.000.000</option>
        </select>
      </div>

      <div style="display: flex; gap: 8px;">
        <button type="submit" class="btn btn-primary">Filter</button>
        @if(request()->hasAny(['q', 'floor', 'max_price']))
          <a href="{{ route('public.rooms.index') }}" class="btn btn-ghost">Reset</a>
        @endif
      </div>
    </form>
  </section>

  <!-- Grid Daftar Kamar -->
  <section class="grid-3" style="gap: 24px;" aria-label="Daftar kamar kos">
    @forelse($rooms as $room)
      <div class="card elev-sm" style="overflow: hidden; display: flex; flex-direction: column; border: 1px solid var(--color-divider); border-radius: 8px; background: #ffffff;">
        
        <!-- Foto Kamar -->
        <div style="position: relative; height: 190px; background: var(--color-neutral-100); overflow: hidden;">
          @if($room->image)
            <img src="{{ asset('storage/' . $room->image) }}" alt="Foto Kamar {{ $room->room_number }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=600&q=80';" style="width: 100%; height: 100%; object-fit: cover;">
          @else
            <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=600&q=80" alt="Foto Kamar {{ $room->room_number }}" style="width: 100%; height: 100%; object-fit: cover;">
          @endif

          <span class="tag tag-accent" style="position: absolute; top: 12px; left: 12px; font-weight: 700; font-size: 11px; background: rgba(22, 163, 74, 0.95); color: #fff;">
            Tersedia
          </span>

          @if($room->floor)
            <span class="tag tag-neutral" style="position: absolute; top: 12px; right: 12px; font-weight: 600; font-size: 11px; background: rgba(24, 26, 29, 0.75); color: #fff;">
              Lantai {{ $room->floor }}
            </span>
          @endif
        </div>

        <!-- Konten Kartu -->
        <div style="padding: 20px; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
          <div>
            <h3 style="font-size: 20px; font-weight: 800; margin: 0 0 6px; color: var(--color-neutral-900);">
              Kamar {{ $room->room_number }}
            </h3>

            <!-- Tarif Per Bulan -->
            <div style="font-size: 18px; font-weight: 800; color: var(--color-accent); margin-bottom: 14px;">
              Rp {{ number_format($room->price, 0, ',', '.') }}
              <span class="small muted" style="font-size: 12px; font-weight: 400; color: var(--color-neutral-600);">/ bulan</span>
            </div>

            <!-- Fasilitas Kamar (Chip) -->
            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px;">
              @forelse($room->facilities->take(4) as $facility)
                <span class="tag tag-neutral" style="font-size: 11px; padding: 3px 8px; background: var(--color-neutral-100); border: 1px solid var(--color-divider); color: var(--color-neutral-800);">
                  ✓ {{ $facility->name }}
                </span>
              @empty
                <span class="small muted" style="font-size: 12px;">Fasilitas standar kos</span>
              @endforelse
              @if($room->facilities->count() > 4)
                <span class="small muted" style="font-size: 11px; align-self: center;">+{{ $room->facilities->count() - 4 }} lainnya</span>
              @endif
            </div>
          </div>

          <!-- Tombol Aksi Detail & Sewa -->
          <div style="padding-top: 14px; border-top: 1px solid var(--color-divider);">
            @if($existingLease && $existingLease->room_id === $room->id)
              <a href="{{ route('public.rooms.show', $room) }}" class="btn btn-secondary btn-block" style="font-weight: 700; font-size: 13px; text-align: center; color: var(--color-accent); border-color: var(--color-accent);">
                ✓ Kamar Pilihan Anda &rarr;
              </a>
            @elseif($existingLease)
              <a href="{{ route('public.rooms.show', $room) }}" class="btn btn-secondary btn-block" style="font-weight: 600; font-size: 13px; text-align: center;">
                Lihat Spesifikasi Kamar &rarr;
              </a>
            @else
              <a href="{{ route('public.rooms.show', $room) }}" class="btn btn-primary btn-block" style="font-weight: 700; font-size: 13px; text-align: center;">
                Pilih &amp; Ajukan Sewa &rarr;
              </a>
            @endif
          </div>
        </div>

      </div>
    @empty
      <div style="grid-column: 1 / -1; text-align: center; padding: 48px 16px; background: #ffffff; border-radius: 8px; border: 1px dashed var(--color-divider);">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 16px; color: var(--color-neutral-400);"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
        <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 8px;">Tidak Ada Kamar yang Sesuai</h3>
        <p class="muted" style="margin: 0 0 20px;">Silakan ubah kriteria filter atau kata kunci pencarian Anda.</p>
        <a href="{{ route('public.rooms.index') }}" class="btn btn-secondary btn-sm">Reset Filter</a>
      </div>
    @endforelse
  </section>

  <!-- Pagination -->
  @if($rooms->hasPages())
    <div style="margin-top: 24px; display: flex; justify-content: center;">
      {{ $rooms->links() }}
    </div>
  @endif

</main>
@endsection
