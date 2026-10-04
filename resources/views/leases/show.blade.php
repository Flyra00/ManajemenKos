@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('leases.index') }}">Kontrak Sewa</a>
            <span class="sep">/</span>
            <span class="current">#LS-{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }}</span>
          </nav>
          <h2 class="page-title">Kontrak Sewa #LS-{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }}</h2>
          <p class="page-sub">Rincian perjanjian sewa, status pembayaran, dan data penghuni.</p>
        </div>
        <div class="flex head-actions" style="gap: 8px;">
          <a href="{{ route('leases.index') }}" class="btn btn-secondary">Kembali</a>
          <a href="{{ route('leases.contract', $lease) }}" target="_blank" class="btn btn-secondary" title="Cetak Surat Perjanjian Sewa (SPK)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
            Cetak Perjanjian (SPK)
          </a>
          @if($lease->status === 'completed' && $lease->checkout_date)
            <a href="{{ route('leases.checkout-receipt', $lease) }}" target="_blank" class="btn btn-secondary" title="Cetak Berita Acara Check-Out">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              Berita Acara Check-Out
            </a>
          @endif
          @if(!auth()->user() || !auth()->user()->hasRole('owner'))
            @if($lease->status === 'active')
              <button type="button" class="btn btn-secondary" onclick="openRenewalModal()" title="Perpanjang jangka waktu sewa">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                Perpanjang Sewa
              </button>
              <button type="button" class="btn btn-secondary text-accent" onclick="openCheckoutModal()" title="Proses pengakhiran sewa & serah terima kamar">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Check-Out
              </button>
            @endif
            <a href="{{ route('leases.edit', $lease) }}" class="btn btn-primary">Edit Kontrak</a>
          @endif
        </div>

      </section>

      @if(session('success'))
        <div class="alert alert-success" role="alert" style="margin-bottom: 16px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-error" role="alert" style="margin-bottom: 16px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-error" role="alert" style="margin-bottom: 16px;">
          <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if($lease->status === 'active' && $lease->isOverdue())
        <div class="alert alert-error" style="margin-bottom: 16px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;">
          <strong>Perhatian:</strong> Masa sewa kontrak ini telah berakhir pada {{ $lease->end_date->translatedFormat('d F Y') }}. Silakan lakukan <strong>Perpanjangan Sewa</strong> atau proses <strong>Check-Out</strong>.
        </div>
      @elseif($lease->status === 'active' && $lease->isExpiringSoon(14))
        <div class="alert alert-warning" style="margin-bottom: 16px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e;">
          <strong>Pemberitahuan:</strong> Masa sewa kontrak ini akan berakhir dalam {{ (int) $lease->end_date->diffInDays(now()->startOfDay()) }} hari lagi ({{ $lease->end_date->translatedFormat('d F Y') }}).
        </div>
      @endif

      {{-- Stat Cards --}}
      <section class="grid-cols-1 md:grid-cols-4 grid gap-4" aria-label="Ringkasan kontrak">
        <div class="card elev-sm">
          <div class="stat-label">Status Kontrak</div>
          <div class="stat-value">
            @if($lease->status === 'active')
              <span class="tag tag-accent">Aktif Menghuni</span>
            @elseif($lease->status === 'completed')
              <span class="tag tag-neutral">Selesai</span>
            @else
              <span class="tag tag-outline">{{ ucfirst($lease->status) }}</span>
            @endif
            @if($lease->renewal_count > 0)
              <span class="tag tag-outline text-xs" style="margin-left: 4px;" title="Kontrak telah diperpanjang {{ $lease->renewal_count }} kali">
                +{{ $lease->renewal_count }}x Perpanjang
              </span>
            @endif
          </div>
          <div class="stat-sub muted">
            {{ $lease->start_date ? $lease->start_date->translatedFormat('d M Y') : '—' }} s/d {{ $lease->end_date ? $lease->end_date->translatedFormat('d M Y') : 'Fleksibel' }}
          </div>
        </div>

        <div class="card elev-sm">
          <div class="stat-label">Tarif Sewa</div>
          <div class="stat-value">Rp {{ number_format($lease->monthly_price, 0, ',', '.') }}</div>
          <div class="stat-sub muted">per bulan</div>
        </div>

        <div class="card elev-sm">
          <div class="stat-label">Uang Muka / Deposit</div>
          <div class="stat-value">Rp {{ number_format($lease->deposit_amount, 0, ',', '.') }}</div>
          <div class="stat-sub muted">
            @if($lease->deposit_paid)
              <span style="color: #16a34a; font-weight:700;">Sudah Dibayar ✓</span>
            @elseif($lease->deposit_amount > 0)
              <span class="text-accent" style="font-weight:700;">Belum Dibayar</span>
            @else
              tanpa deposit
            @endif
          </div>
        </div>

        <div class="card elev-sm">
          <div class="stat-label">Sisa Tagihan Pelunasan</div>
          <div class="stat-value" style="color: {{ $lease->remaining_due > 0 ? 'var(--color-accent)' : '#16a34a' }}">
            Rp {{ number_format($lease->remaining_due, 0, ',', '.') }}
          </div>
          <div class="stat-sub muted">
            Total masuk: Rp {{ number_format($lease->total_paid, 0, ',', '.') }}
          </div>
        </div>
      </section>


      <!-- Informasi Penghuni & Kamar -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start" aria-label="Detail pihak sewa">
        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Data Penghuni</h3>
            @if($lease->tenant)
              <a href="{{ route('tenants.show', $lease->tenant) }}" class="small text-accent hover:underline">Lihat Profil &rarr;</a>
            @endif
          </div>
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 m-0">
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nama Penghuni</dt>
              <dd class="m-0 font-semibold">{{ $lease->tenant->user->name ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nomor KTP (NIK)</dt>
              <dd class="m-0 font-semibold">{{ $lease->tenant->ktp_number ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nomor Telepon</dt>
              <dd class="m-0">{{ $lease->tenant->user->phone ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Email</dt>
              <dd class="m-0">{{ $lease->tenant->user->email ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Pekerjaan</dt>
              <dd class="m-0">{{ $lease->tenant->job ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Kontak Darurat</dt>
              <dd class="m-0 text-sm">{{ $lease->tenant->emergency_name ?? '—' }} ({{ $lease->tenant->emergency_contact ?? '—' }})</dd>
            </div>
          </dl>
        </div>

        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Kamar & Ketentuan</h3>
            @if($lease->room)
              <a href="{{ route('rooms.show', $lease->room) }}" class="small text-accent hover:underline">Lihat Kamar &rarr;</a>
            @endif
          </div>
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 m-0">
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nomor Kamar</dt>
              <dd class="m-0 font-semibold">Kamar {{ $lease->room->room_number ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Lantai</dt>
              <dd class="m-0">Lantai {{ $lease->room->floor ?? '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Mulai Sewa</dt>
              <dd class="m-0">{{ $lease->start_date ? $lease->start_date->translatedFormat('d F Y') : '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Berakhir Sewa</dt>
              <dd class="m-0">{{ $lease->end_date ? $lease->end_date->translatedFormat('d F Y') : 'Tanpa Batas Waktu' }}</dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-xs text-neutral-600 mb-1">Catatan Perjanjian</dt>
              <dd class="m-0 text-sm text-neutral-700">{{ $lease->note ?: 'Tidak ada catatan tambahan.' }}</dd>
            </div>
          </dl>
        </div>
      </section>

      @if($lease->status === 'completed' && $lease->checkout_date)
        <!-- Rincian Serah Terima Check-Out & Deposit -->
        <section class="card elev-sm section-card" aria-label="Rincian check-out dan deposit" style="border-left: 4px solid var(--color-neutral-800);">
          <div class="card-head">
            <div>
              <h3 class="card-title">Rincian Check-Out &amp; Penyelesaian Uang Jaminan (Deposit)</h3>
              <p class="small muted">Penghuni resmi check-out pada {{ $lease->checkout_date->translatedFormat('d F Y') }}</p>
            </div>
            <a href="{{ route('leases.checkout-receipt', $lease) }}" target="_blank" class="btn btn-secondary btn-sm" title="Cetak Berita Acara Serah Terima">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
              Cetak Berita Acara &rarr;
            </a>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4" style="background: var(--color-neutral-100); border-radius: 6px;">
            <div>
              <div class="text-xs text-neutral-600 mb-1">Kondisi Kamar Saat Serah Terima</div>
              <div class="font-semibold">
                @if($lease->room_condition === 'good')
                  <span class="tag tag-accent">Kondisi Baik</span>
                @elseif($lease->room_condition === 'needs_cleaning')
                  <span class="tag tag-neutral">Perlu Pembersihan</span>
                @else
                  <span class="tag tag-outline" style="color:var(--color-accent); border-color:var(--color-accent)">Ada Kerusakan</span>
                @endif
              </div>
            </div>
            <div>
              <div class="text-xs text-neutral-600 mb-1">Uang Muka / Deposit Awal</div>
              <div class="font-semibold">Rp {{ number_format($lease->deposit_amount, 0, ',', '.') }}</div>
            </div>
            <div>
              <div class="text-xs text-neutral-600 mb-1">Potongan Kerusakan / Denda</div>
              <div class="font-semibold text-accent">- Rp {{ number_format($lease->deposit_deduction, 0, ',', '.') }}</div>
            </div>
            <div>
              <div class="text-xs text-neutral-600 mb-1">Sisa Deposit Dikembalikan</div>
              <div class="font-semibold" style="color: #16a34a; font-size: 15px;">Rp {{ number_format($lease->deposit_refunded, 0, ',', '.') }}</div>
            </div>
          </div>
          @if($lease->checkout_notes)
            <div style="margin-top: 12px; padding: 0 4px; font-size: 13px; color: var(--color-neutral-700);">
              <strong>Catatan Serah Terima:</strong> {{ $lease->checkout_notes }}
            </div>
          @endif
        </section>
      @endif

      <!-- Riwayat Pembayaran Tagihan -->
      <section class="card elev-sm section-card" aria-label="Riwayat pembayaran sewa">
        <div class="card-head" style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h3 class="card-title" style="margin: 0;">Riwayat Pembayaran Tagihan</h3>
            <span class="small muted">{{ $lease->payments->count() }} invoice</span>
          </div>
          @if(!auth()->user() || !auth()->user()->hasRole('owner'))
            <a href="{{ route('payments.create', ['lease_id' => $lease->id]) }}" class="btn btn-secondary btn-sm" title="Catat pembayaran baru untuk kontrak ini">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Catat Pembayaran
            </a>
          @endif
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>No. Invoice</th>
                <th>Periode Tagihan</th>
                <th>Jumlah</th>
                <th>Jatuh Tempo</th>
                <th>Tgl Bayar</th>
                <th>Metode</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($lease->payments as $payment)
                <tr>
                  <td class="font-semibold">{{ $payment->invoice_number }}</td>
                  <td>{{ $payment->billing_period }}</td>
                  <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                  <td>{{ $payment->due_date ? \Carbon\Carbon::parse($payment->due_date)->translatedFormat('d M Y') : '—' }}</td>
                  <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d M Y') : '—' }}</td>
                  <td>{{ $payment->payment_method ? ucfirst($payment->payment_method) : '—' }}</td>
                  <td>
                    @if($payment->status === 'paid')
                      <span class="tag tag-accent">Lunas</span>
                    @elseif($payment->status === 'pending')
                      <span class="tag tag-neutral">Menunggu</span>
                    @elseif($payment->status === 'cancelled')
                      <span class="tag tag-outline">Dibatalkan</span>
                    @else
                      <span class="tag tag-outline">{{ ucfirst($payment->status) }}</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-6 text-neutral-500">
                    Belum ada riwayat tagihan/pembayaran untuk kontrak sewa ini.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>

    </main>

    @if(!auth()->user() || !auth()->user()->hasRole('owner'))
      @if($lease->status === 'active')
        <!-- Modal Perpanjangan Sewa -->
        <div class="dialog-backdrop" id="renewalModal" style="display:none;" data-close="1">
          <div class="dialog" style="max-width: 520px; width: 100%;">
            <div class="dialog-title">Perpanjang Kontrak Sewa</div>
            <p class="small muted" style="margin:4px 0 16px;line-height:1.4">
              Perpanjang masa berlaku sewa untuk penghuni <strong>{{ $lease->tenant->user->name ?? 'Penghuni' }}</strong> (Kamar {{ $lease->room->room_number ?? '—' }}).
            </p>
            <form method="POST" action="{{ route('leases.renew', $lease) }}">
              @csrf
              <div class="form-group mb-3">
                <label class="label">Pilihan Durasi Tambahan</label>
                <div class="grid grid-cols-2 gap-2" style="margin-top: 6px;">
                  <label class="btn btn-secondary btn-sm" style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:normal;">
                    <input type="radio" name="duration_type" value="1_month" onchange="handleDurationChange('1_month')"> +1 Bulan
                  </label>
                  <label class="btn btn-secondary btn-sm" style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:normal;">
                    <input type="radio" name="duration_type" value="3_months" checked onchange="handleDurationChange('3_months')"> +3 Bulan
                  </label>
                  <label class="btn btn-secondary btn-sm" style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:normal;">
                    <input type="radio" name="duration_type" value="6_months" onchange="handleDurationChange('6_months')"> +6 Bulan
                  </label>
                  <label class="btn btn-secondary btn-sm" style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:normal;">
                    <input type="radio" name="duration_type" value="12_months" onchange="handleDurationChange('12_months')"> +1 Tahun
                  </label>
                </div>
                <div style="margin-top: 8px;">
                  <label class="btn btn-secondary btn-sm" style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-weight:normal; width:100%;">
                    <input type="radio" name="duration_type" value="custom" onchange="handleDurationChange('custom')"> Tanggal Berakhir Kustom
                  </label>
                </div>
              </div>

              <div class="form-group mb-3" id="customDateGroup" style="display: none;">
                <label class="label" for="new_end_date">Tanggal Akhir Baru <span class="text-accent">*</span></label>
                <input class="input" type="date" id="new_end_date" name="new_end_date"
                       min="{{ $lease->end_date && $lease->end_date->isFuture() ? $lease->end_date->copy()->addDay()->format('Y-m-d') : now()->addDay()->format('Y-m-d') }}">
              </div>

              <div class="form-group mb-3">
                <div class="small muted" id="datePreviewText" style="padding: 8px 12px; background: var(--color-neutral-100); border-radius: 4px; font-weight: 500;">
                  <!-- Preview tanggal baru dihitung otomatis -->
                </div>
              </div>

              <div class="form-group mb-3">
                <label class="label" for="renewal_monthly_price">Tarif Sewa Bulanan (Rp) <span class="text-accent">*</span></label>
                <input class="input" type="number" id="renewal_monthly_price" name="monthly_price" value="{{ (int) $lease->monthly_price }}" min="0" required>
              </div>

              <div class="form-group mb-3">
                <label class="flex items-center" style="gap: 8px; cursor: pointer; font-size: 13px;">
                  <input type="checkbox" name="generate_invoice" value="1" checked>
                  <span>Otomatis terbitkan invoice tagihan sewa untuk periode perpanjangan</span>
                </label>
              </div>

              <div class="form-group mb-4">
                <label class="label" for="renewal_note">Catatan Tambahan (Opsional)</label>
                <input class="input" type="text" id="renewal_note" name="renewal_note" placeholder="Misal: Perpanjangan semester ganjil">
              </div>

              <div class="dialog-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRenewalModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Konfirmasi Perpanjang</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Modal Check-Out & Serah Terima Kamar -->
        <div class="dialog-backdrop" id="checkoutModal" style="display:none;" data-close="1">
          <div class="dialog" style="max-width: 540px; width: 100%;">
            <div class="dialog-title">Proses Check-Out &amp; Serah Terima Kamar</div>
            <p class="small muted" style="margin:4px 0 16px;line-height:1.4">
              Penyelesaian kontrak sewa Kamar <strong>{{ $lease->room->room_number ?? '—' }}</strong> atas nama <strong>{{ $lease->tenant->user->name ?? 'Penghuni' }}</strong>.
            </p>
            <form method="POST" action="{{ route('leases.checkout', $lease) }}">
              @csrf
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                <div class="form-group">
                  <label class="label" for="checkout_date">Tanggal Check-Out <span class="text-accent">*</span></label>
                  <input class="input" type="date" id="checkout_date" name="checkout_date" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                  <label class="label" for="room_condition">Kondisi Kamar <span class="text-accent">*</span></label>
                  <select class="input" id="room_condition" name="room_condition" required onchange="handleConditionChange(this.value)">
                    <option value="good">Kondisi Baik &amp; Bersih</option>
                    <option value="needs_cleaning">Perlu Pembersihan Ringan</option>
                    <option value="damaged">Ada Kerusakan / Perbaikan</option>
                  </select>
                </div>
              </div>

              <div class="card p-3 mb-3" style="background: var(--color-neutral-100); border: 1px solid var(--color-neutral-200);">
                <div class="small font-semibold mb-2" style="text-transform:uppercase; letter-spacing:0.5px; color:var(--color-neutral-700)">
                  Penyelesaian Uang Muka / Deposit
                </div>
                <div class="flex justify-between items-center mb-2 text-sm">
                  <span class="text-neutral-600">Total Uang Jaminan (Awal):</span>
                  <span class="font-bold">Rp {{ number_format($lease->deposit_amount, 0, ',', '.') }}</span>
                </div>
                <div class="form-group mb-2">
                  <label class="label text-xs" for="deposit_deduction">Potongan Biaya Kerusakan / Denda / Kebersihan (Rp)</label>
                  <input class="input" type="number" id="deposit_deduction" name="deposit_deduction"
                         value="0" min="0" max="{{ (int) $lease->deposit_amount }}"
                         oninput="calculateRefund({{ (int) $lease->deposit_amount }})">
                </div>
                <div class="flex justify-between items-center text-sm pt-2" style="border-top:1px dashed var(--color-neutral-300);">
                  <span class="font-semibold text-neutral-800">Sisa Dikembalikan ke Penghuni:</span>
                  <span class="font-bold" id="refundPreview" style="color: #16a34a; font-size:15px;">
                    Rp {{ number_format($lease->deposit_amount, 0, ',', '.') }}
                  </span>
                  <input type="hidden" id="deposit_refunded" name="deposit_refunded" value="{{ (int) $lease->deposit_amount }}">
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                <div class="form-group">
                  <label class="label" for="room_status">Status Kamar Selanjutnya <span class="text-accent">*</span></label>
                  <select class="input" id="room_status" name="room_status" required>
                    <option value="available">Tersedia (Siap Disewa Kembali)</option>
                    <option value="maintenance">Perbaikan (Maintenance)</option>
                  </select>
                </div>
                <div class="form-group flex items-end">
                  <label class="flex items-center text-xs" style="gap: 6px; cursor: pointer; padding-bottom: 8px;">
                    <input type="checkbox" name="record_expense" value="1" checked>
                    <span>Catat sisa refund deposit ke kas Pengeluaran</span>
                  </label>
                </div>
              </div>

              <div class="form-group mb-4">
                <label class="label" for="checkout_notes">Catatan Serah Terima / Hasil Inspeksi</label>
                <textarea class="input" id="checkout_notes" name="checkout_notes" rows="2" placeholder="Kunci kamar telah diterima, remote AC lengkap, tidak ada barang tertinggal..."></textarea>
              </div>

              <div class="dialog-actions">
                <button type="button" class="btn btn-secondary" onclick="closeCheckoutModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background-color: var(--color-accent); border-color: var(--color-accent);">
                  Konfirmasi Selesai Sewa
                </button>
              </div>
            </form>
          </div>
        </div>

        <script>
          const baseDateStr = '{{ $lease->end_date && $lease->end_date->isFuture() ? $lease->end_date->format('Y-m-d') : now()->format('Y-m-d') }}';
          const baseDate = new Date(baseDateStr);

          function openRenewalModal() {
            document.getElementById('renewalModal').style.display = 'flex';
            handleDurationChange('3_months');
          }

          function closeRenewalModal() {
            document.getElementById('renewalModal').style.display = 'none';
          }

          function openCheckoutModal() {
            document.getElementById('checkoutModal').style.display = 'flex';
          }

          function closeCheckoutModal() {
            document.getElementById('checkoutModal').style.display = 'none';
          }

          function handleDurationChange(type) {
            const customGroup = document.getElementById('customDateGroup');
            const previewEl = document.getElementById('datePreviewText');
            const customInput = document.getElementById('new_end_date');

            if (type === 'custom') {
              customGroup.style.display = 'block';
              previewEl.innerHTML = 'Pilih tanggal berakhir di input kalender di atas.';
              return;
            }

            customGroup.style.display = 'none';
            let targetDate = new Date(baseDate);

            if (type === '1_month') {
              targetDate.setMonth(targetDate.getMonth() + 1);
            } else if (type === '3_months') {
              targetDate.setMonth(targetDate.getMonth() + 3);
            } else if (type === '6_months') {
              targetDate.setMonth(targetDate.getMonth() + 6);
            } else if (type === '12_months') {
              targetDate.setFullYear(targetDate.getFullYear() + 1);
            }

            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const formatted = targetDate.toLocaleDateString('id-ID', options);
            previewEl.innerHTML = 'Masa sewa baru akan berlaku s/d: <strong>' + formatted + '</strong>';
          }

          function calculateRefund(totalDeposit) {
            const deductionInput = document.getElementById('deposit_deduction');
            const preview = document.getElementById('refundPreview');
            const hiddenRefund = document.getElementById('deposit_refunded');

            let deduction = parseFloat(deductionInput.value) || 0;
            if (deduction > totalDeposit) {
              deduction = totalDeposit;
              deductionInput.value = totalDeposit;
            }

            const refund = Math.max(0, totalDeposit - deduction);
            preview.textContent = 'Rp ' + refund.toLocaleString('id-ID');
            hiddenRefund.value = refund;
          }

          function handleConditionChange(val) {
            const roomStatusSelect = document.getElementById('room_status');
            if (val === 'damaged') {
              roomStatusSelect.value = 'maintenance';
            } else {
              roomStatusSelect.value = 'available';
            }
          }

          window.addEventListener('click', function(e) {
            const rModal = document.getElementById('renewalModal');
            const cModal = document.getElementById('checkoutModal');
            if (e.target === rModal) closeRenewalModal();
            if (e.target === cModal) closeCheckoutModal();
          });
        </script>
      @endif
    @endif

@endsection
