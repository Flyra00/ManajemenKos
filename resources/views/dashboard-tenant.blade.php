@extends('layouts.app')
@section('content')
  <main class="page" id="page">

    <!-- Header Sambutan Personal Tenant -->
    <section class="page-head" aria-label="Judul halaman">
      <div>
        <nav class="breadcrumb" aria-label="Breadcrumb">
          <span class="current">Portal Penghuni</span>
        </nav>
        <h2 class="page-title">Halo, {{ $user->name }}!</h2>
        <p class="page-sub">Selamat datang di portal hunian Anda di {{ $kosSettings['name'] }}.</p>
      </div>
      <div>
        <span class="tag tag-accent" style="font-weight:700;">Penghuni Kos</span>
      </div>
    </section>

    <!-- BANNER PERINGATAN TAGIHAN (Jika ada tagihan belum dibayar) -->
    @if($unpaidPayments->isNotEmpty())
      @php
        $duePayment = $unpaidPayments->first();
      @endphp
      <div class="card elev-sm" style="margin-bottom: 24px; background: #fffbeb; border: 1px solid #fde68a; padding: 20px; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706; flex-shrink: 0;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
              <h3 style="font-size: 16px; font-weight: 800; color: #92400e; margin: 0 0 4px;">
                Ada Tagihan Sewa Menunggu Pembayaran
              </h3>
              <p class="small muted" style="margin: 0; color: #b45309;">
                Invoice <strong>#{{ $duePayment->invoice_number }}</strong> sebesar <strong>Rp {{ number_format($duePayment->amount, 0, ',', '.') }}</strong> jatuh tempo pada <strong>{{ \Carbon\Carbon::parse($duePayment->due_date)->translatedFormat('d F Y') }}</strong>.
              </p>
            </div>
          </div>
          <div>
            <a href="{{ $duePayment->public_url }}" class="btn btn-primary" style="background: #d97706; border-color: #d97706;">
              Bayar Sekarang / Lihat Invoice &rarr;
            </a>
          </div>
        </div>
      </div>
    @elseif($activeLease && $remainingDays <= 14 && $remainingDays > 0)
      <div class="card elev-sm" style="margin-bottom: 24px; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 20px; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; color: #16a34a; flex-shrink: 0;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
              <h3 style="font-size: 16px; font-weight: 800; color: #166534; margin: 0 0 4px;">
                Masa Aktif Kamar: Tersisa {{ $remainingDays }} Hari Lagi
              </h3>
              <p class="small muted" style="margin: 0; color: #15803d;">
                Masa aktif sewa Anda berakhir pada <strong>{{ \Carbon\Carbon::parse($activeLease->end_date)->translatedFormat('d M Y') }}</strong>. Terbitkan tagihan sewa bulan berikutnya sekarang untuk menambah masa aktif <strong>+30 hari</strong> secara otomatis.
              </p>
            </div>
          </div>
          <div>
            <form method="POST" action="{{ route('tenant.leases.request-bill', $activeLease) }}">
              @csrf
              <button type="submit" class="btn btn-primary" style="background: #16a34a; border-color: #16a34a; font-weight: 700;">
                Perpanjang Masa Aktif (+30 Hari) &rarr;
              </button>
            </form>
          </div>
        </div>
      </div>
    @elseif($activeLease && $remainingDays == 0)
      <div class="card elev-sm" style="margin-bottom: 24px; background: #fef2f2; border: 1px solid #fecaca; padding: 20px; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
              <h3 style="font-size: 16px; font-weight: 800; color: #991b1b; margin: 0 0 4px;">
                Masa Aktif Kamar Anda Telah Habis (0 Hari)
              </h3>
              <p class="small muted" style="margin: 0; color: #b91c1c;">
                Masa sewa telah jatuh tempo. Segera terbitkan dan bayar invoice sewa untuk mengaktifkan kembali kamar Anda (+30 hari).
              </p>
            </div>
          </div>
          <div>
            <form method="POST" action="{{ route('tenant.leases.request-bill', $activeLease) }}">
              @csrf
              <button type="submit" class="btn btn-primary" style="background: #dc2626; border-color: #dc2626; font-weight: 700;">
                Aktifkan Kembali (+30 Hari) &rarr;
              </button>
            </form>
          </div>
        </div>
      </div>
    @endif

    <!-- ============================================================
         1. KARTU UTAMA HUNIAN & MASA AKTIF (HERO STATUS CARD)
         ============================================================ -->
    <div class="card elev-sm" style="margin-bottom: 24px; padding: 24px; border: 1px solid var(--color-divider); background: #ffffff; border-radius: 8px;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 24px;">

        <!-- Sisi Kiri: Foto, Nama Kamar, Lantai, Tarif -->
        <div style="display: flex; gap: 20px; align-items: center; flex: 1; min-width: 280px;">
          <div style="width: 110px; height: 88px; border-radius: 8px; overflow: hidden; background: var(--color-neutral-100); flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
            @if($activeLease && $activeLease->room && $activeLease->room->image)
              <img src="{{ asset('storage/' . $activeLease->room->image) }}" alt="Kamar {{ $activeLease->room->room_number }}" style="width: 100%; height: 100%; object-fit: cover;">
            @else
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--color-neutral-400);"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
            @endif
          </div>

          <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
              <h3 style="font-size: 22px; font-weight: 800; margin: 0; color: var(--color-neutral-900);">
                @if($activeLease && $activeLease->room)
                  Kamar {{ $activeLease->room->room_number }}
                @else
                  Belum Ada Kamar Aktif
                @endif
              </h3>
              @if($activeLease)
                <span class="tag tag-accent" style="font-size: 11px; padding: 2px 8px;">{{ ucfirst($activeLease->status) }}</span>
              @endif
            </div>

            <div class="small muted" style="margin-bottom: 6px;">
              @if($activeLease && $activeLease->room)
                Lantai {{ $activeLease->room->floor }} · {{ $kosSettings['name'] }}
              @else
                Pilih kamar dari katalog untuk mulai menyewa
              @endif
            </div>

            @if($activeLease)
              <div style="font-size: 16px; font-weight: 800; color: var(--color-neutral-900);">
                Rp {{ number_format($activeLease->monthly_price, 0, ',', '.') }}
                <span class="small muted" style="font-weight: 400; font-size: 12px;">/ bulan</span>
              </div>
            @else
              <div style="margin-top: 10px;">
                <a href="{{ route('public.rooms.index') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700; padding: 8px 18px;">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
                  <span>Pilih Kamar Dari Katalog &rarr;</span>
                </a>
              </div>
            @endif
          </div>
        </div>

      </div>

      <!-- Fasilitas Kamar (Chip Netral Bersih) -->
      @if($activeLease && $activeLease->room && $activeLease->room->facilities->isNotEmpty())
        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--color-divider); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
          <span class="small muted" style="font-size: 12px; margin-right: 4px;">Fasilitas Kamar:</span>
          @foreach($activeLease->room->facilities as $facility)
            <span class="tag tag-neutral" style="font-size: 11px; font-weight: 500; background: var(--color-neutral-100); border: 1px solid var(--color-divider); color: var(--color-neutral-700);">
              ✓ {{ $facility->name }}
            </span>
          @endforeach
        </div>
      @endif
    </div>

    <!-- ============================================================
         KATALOG PILIHAN KAMAR SIAP HUNI (JIKA BELUM ADA KAMAR AKTIF)
         ============================================================ -->
    @if(!$activeLease && isset($availableRooms) && $availableRooms->isNotEmpty())
      <div class="card elev-sm" style="margin-bottom: 24px; padding: 24px; border: 1px solid var(--color-divider); background: #ffffff; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
          <div>
            <h3 style="font-size: 18px; font-weight: 800; margin: 0; color: var(--color-neutral-900);">
              Pilih Kamar Kos Siap Huni
            </h3>
            <p class="small muted" style="margin: 4px 0 0;">
              Tersedia {{ $availableRooms->count() }} kamar kosong. Pilih kamar favorit Anda dan ajukan sewa secara mandiri.
            </p>
          </div>
          <a href="{{ route('public.rooms.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 600;">
            Lihat Semua Kamar (Katalog Lengkap) &rarr;
          </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 20px;">
          @foreach($availableRooms as $availRoom)
            <div class="card elev-sm" style="border: 1px solid var(--color-divider); border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; background: #ffffff;">
              <div style="height: 160px; background: var(--color-neutral-100); position: relative; overflow: hidden;">
                @if($availRoom->image)
                  <img src="{{ asset('storage/' . $availRoom->image) }}" alt="Kamar {{ $availRoom->room_number }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                  <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--color-neutral-400);">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
                  </div>
                @endif
                <span class="tag tag-accent" style="position: absolute; top: 10px; right: 10px; font-size: 11px; font-weight: 700; background: #22c55e; color: #fff;">
                  Tersedia
                </span>
                <span class="tag tag-neutral" style="position: absolute; top: 10px; left: 10px; font-size: 11px; font-weight: 600; background: rgba(0,0,0,0.65); color: #fff;">
                  Lantai {{ $availRoom->floor }}
                </span>
              </div>

              <div style="padding: 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                  <h4 style="font-size: 18px; font-weight: 800; margin: 0 0 8px; color: var(--color-neutral-900);">
                    Kamar {{ $availRoom->room_number }}
                  </h4>

                  @if($availRoom->facilities->isNotEmpty())
                    <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 14px;">
                      @foreach($availRoom->facilities->take(3) as $fac)
                        <span style="font-size: 11px; padding: 2px 7px; background: var(--color-neutral-100); border-radius: 4px; color: var(--color-neutral-700); border: 1px solid var(--color-divider);">
                          ✓ {{ $fac->name }}
                        </span>
                      @endforeach
                      @if($availRoom->facilities->count() > 3)
                        <span style="font-size: 11px; padding: 2px 6px; color: var(--color-neutral-500);">
                          +{{ $availRoom->facilities->count() - 3 }}
                        </span>
                      @endif
                    </div>
                  @endif
                </div>

                <div>
                  <div style="font-size: 17px; font-weight: 800; color: var(--color-primary); margin-bottom: 12px;">
                    Rp {{ number_format($availRoom->price, 0, ',', '.') }}
                    <span style="font-size: 12px; font-weight: 400; color: var(--color-text-muted);">/ bulan</span>
                  </div>

                  <a href="{{ route('public.rooms.show', $availRoom) }}" class="btn btn-primary btn-block" style="justify-content: center; font-weight: 700;">
                    Pilih &amp; Sewa Kamar Ini &rarr;
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <!-- ============================================================
         2. DUA KOLOM LAPANG (TAGIHAN & KELUHAN)
         ============================================================ -->
    <div class="grid-2" style="gap: 24px; align-items: flex-start;">

      <!-- ==================== KOLOM KIRI: STATUS TAGIHAN & KONTAK ==================== -->
      <div style="display: flex; flex-direction: column; gap: 24px;">

        <!-- STATUS TAGIHAN SEWA SAYA -->
        <div class="card elev-sm section-card">
          <div class="card-head" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Status Tagihan Sewa</h3>
            @if($unpaidPayments->isNotEmpty())
              <span class="tag tag-accent" style="font-size: 11px; font-weight: 700;">Tagihan Sewa Belum Lunas</span>
            @elseif($activeLease)
              <span class="tag tag-accent" style="background: #16a34a; color: #fff; font-size: 11px; font-weight: 700;">Lunas ✓</span>
            @else
              <span class="tag tag-neutral" style="font-size: 11px;">Belum Ada Tagihan</span>
            @endif
          </div>

          <!-- Daftar Tagihan Aktif / Tertunggak -->
          @if($unpaidPayments->isNotEmpty())
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px;">
              @foreach($unpaidPayments as $bill)
                <div style="padding: 16px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                  <div>
                    <span class="small muted" style="font-family: monospace; font-weight: 700;">#{{ $bill->invoice_number }}</span>
                    <div style="font-size: 16px; font-weight: 800; color: var(--color-neutral-900);">
                      Rp {{ number_format($bill->amount, 0, ',', '.') }}
                    </div>
                    <div class="small text-accent" style="font-weight: 600;">
                      Jatuh Tempo: {{ \Carbon\Carbon::parse($bill->due_date)->translatedFormat('d M Y') }}
                    </div>
                  </div>
                  <div>
                    <a href="{{ $bill->public_url }}" class="btn btn-primary btn-sm">
                      Bayar Sekarang &rarr;
                    </a>
                  </div>
                </div>
              @endforeach
            </div>
          @endif

          <!-- Riwayat Pembayaran Terakhir -->
          <div>
            <span class="small muted" style="display: block; margin-bottom: 8px; font-weight: 600;">Riwayat Pembayaran Terakhir:</span>
            @if($recentPayments->isNotEmpty())
              <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($recentPayments->take(3) as $pay)
                  <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: var(--color-neutral-50); border: 1px solid var(--color-divider); border-radius: 6px; font-size: 13px;">
                    <div>
                      <span style="font-family: monospace; font-weight: 700;">#{{ $pay->invoice_number }}</span>
                      <div class="small muted">{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->translatedFormat('d M Y') : 'Belum dibayar' }}</div>
                    </div>
                    <div style="text-align: right;">
                      <div style="font-weight: 700;">Rp {{ number_format($pay->amount, 0, ',', '.') }}</div>
                      <div>
                        @if($pay->status === 'paid')
                          <span class="tag tag-accent" style="background: #16a34a; color: #fff; font-size: 10px; padding: 2px 6px;">Lunas</span>
                        @elseif($pay->status === 'pending')
                          <span class="tag tag-accent" style="background: #d97706; color: #fff; font-size: 10px; padding: 2px 6px;">Menunggu Verifikasi</span>
                        @else
                          <span class="tag tag-outline" style="font-size: 10px; padding: 2px 6px;">Belum Bayar</span>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <p class="small muted" style="margin: 0;">Belum ada catatan riwayat transaksi pembayaran.</p>
            @endif
          </div>
        </div>

        <!-- PUSAT BANTUAN & KONTAK PENGELOLA KOS -->
        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Pusat Bantuan & Kontak Kos</h3>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 0;">
            <div>
              <strong style="font-size: 14px; display: block;">Butuh bantuan mendesak?</strong>
              <span class="small muted">Hubungi pengelola {{ $kosSettings['name'] ?? 'kos' }} langsung via WhatsApp</span>
            </div>
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $kosSettings['phone']) }}" target="_blank" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              Chat WhatsApp
            </a>
          </div>
        </div>

      </div>

      <!-- ==================== KOLOM KANAN: KELUHAN & PERAWATAN KAMAR ==================== -->
      <div>

        <div class="card elev-sm section-card">
          <div class="card-head" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Keluhan & Perawatan Kamar</h3>
            <a href="{{ route('maintenance.create') }}" class="btn btn-primary btn-sm" style="font-size: 12px;">
              + Laporkan Kerusakan
            </a>
          </div>

          @if($maintenanceRequests->isNotEmpty())
            <div class="table-wrap">
              <table class="table" style="width: 100%;">
                <thead>
                  <tr style="background: var(--color-neutral-50);">
                    <th>Keluhan</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Tanggal Lapor</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($maintenanceRequests as $req)
                    <tr>
                      <td>
                        <strong>{{ $req->title }}</strong>
                        <div class="small muted">{{ Str::limit($req->description, 35) }}</div>
                      </td>
                      <td>
                        @if($req->priority === 'high')
                          <span class="tag tag-accent" style="font-size: 10px;">Tinggi</span>
                        @elseif($req->priority === 'medium')
                          <span class="tag tag-outline" style="font-size: 10px;">Sedang</span>
                        @else
                          <span class="tag tag-neutral" style="font-size: 10px;">Rendah</span>
                        @endif
                      </td>
                      <td>
                        @if($req->status === 'resolved')
                          <span class="tag tag-accent" style="background: #16a34a; color: #fff; font-size: 10px;">Selesai</span>
                        @elseif($req->status === 'in_progress')
                          <span class="tag tag-accent" style="background: #d97706; color: #fff; font-size: 10px;">Diproses</span>
                        @else
                          <span class="tag tag-outline" style="font-size: 10px;">Baru</span>
                        @endif
                      </td>
                      <td class="small muted">
                        {{ \Carbon\Carbon::parse($req->reported_at)->translatedFormat('d M Y') }}
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div style="margin-top: 16px; text-align: right;">
              <a href="{{ route('maintenance.index') }}" class="btn btn-ghost btn-sm" style="font-size: 12px;">
                Lihat Semua Tiket Keluhan &rarr;
              </a>
            </div>
          @else
            <div class="text-center py-6" style="padding: 32px 16px;">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 12px; color: var(--color-neutral-400);"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
              <h4 style="font-size: 15px; font-weight: 700; margin: 0 0 6px;">Kamar Nyaman &amp; Terawat</h4>
              <p class="small muted" style="margin: 0 0 16px;">Belum ada laporan keluhan kerusakan atau kendala fasilitas.</p>
              <a href="{{ route('maintenance.create') }}" class="btn btn-secondary btn-sm">
                Ajukan Laporan Perbaikan
              </a>
            </div>
          @endif
        </div>

      </div>

    </div>

  </main>
@endsection
