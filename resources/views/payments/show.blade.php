@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('payments.index') }}">Pembayaran</a>
            <span class="sep">/</span>
            <span class="current">{{ $payment->invoice_number }}</span>
          </nav>
          <h2 class="page-title">Invoice {{ $payment->invoice_number }}</h2>
          <p class="page-sub">Rincian status pembayaran sewa kos dan bukti transaksi.</p>
        </div>
        <div class="flex head-actions" style="gap: 8px;">
          <a href="{{ route('payments.index') }}" class="btn btn-secondary">Kembali</a>
          <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="btn btn-secondary" title="Cetak Kuitansi / Invoice Resmi">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
            {{ $payment->status === 'paid' ? 'Cetak Kuitansi' : 'Cetak Invoice' }}
          </a>
          @if(!auth()->user() || !auth()->user()->hasRole('owner'))
            @if($payment->status !== 'paid')
              <form id="verifyPaymentForm" method="POST" action="{{ route('payments.verify', $payment) }}" style="display:inline;">
                @csrf
                @method('PUT')
                <button type="button" id="btnVerifyPayment" class="btn btn-primary" style="background:#16a34a; border-color:#16a34a;">
                  ✓ Verifikasi Lunas
                </button>
              </form>
            @endif
            <a href="{{ route('payments.edit', $payment) }}" class="btn btn-secondary">Edit Invoice</a>
          @endif
        </div>


      </section>

      {{-- Stat Cards --}}
      <section class="grid-cols-1 md:grid-cols-3 grid gap-4" aria-label="Ringkasan invoice">
        <div class="card elev-sm">
          <div class="stat-label">Status Tagihan</div>
          <div class="stat-value">
            @if($payment->status === 'paid')
              <span class="tag tag-accent">Lunas</span>
            @elseif($payment->status === 'pending')
              <span class="tag tag-neutral">Menunggu Verifikasi</span>
            @elseif($payment->status === 'overdue')
              <span class="tag tag-outline" style="color:var(--color-accent); border-color:var(--color-accent)">Terlambat (Overdue)</span>
            @else
              <span class="tag tag-outline">Belum Bayar</span>
            @endif
          </div>
          <div class="stat-sub muted">
            Jatuh tempo: {{ $payment->due_date ? $payment->due_date->translatedFormat('d F Y') : '—' }}
          </div>
        </div>

        <div class="card elev-sm">
          <div class="stat-label">Total Tagihan</div>
          <div class="stat-value">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
          <div class="stat-sub muted">
            Periode: {{ $payment->billing_period ? $payment->billing_period->translatedFormat('F Y') : '—' }}
          </div>
        </div>

        <div class="card elev-sm">
          <div class="stat-label">Metode Pembayaran</div>
          <div class="stat-value" style="font-size:1.25rem;">
            @if($payment->payment_method === 'bank_tf')
              Transfer Bank
            @elseif($payment->payment_method === 'e_wallet')
              E-Wallet / QRIS
            @else
              Tunai
            @endif
          </div>
          <div class="stat-sub muted">
            Tgl bayar: {{ $payment->payment_date ? $payment->payment_date->translatedFormat('d M Y') : 'Belum dibayar' }}
          </div>
        </div>
      </section>

      <!-- Informasi Tagihan & Bukti Transfer -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start" aria-label="Detail invoice">
        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Rincian Sewa & Penyewa</h3>
          </div>
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 m-0">
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nama Penghuni</dt>
              <dd class="m-0 font-semibold">
                @if($payment->lease && $payment->lease->tenant)
                  <a href="{{ route('tenants.show', $payment->lease->tenant) }}" class="text-accent hover:underline">
                    {{ $payment->lease->tenant->user->name ?? '—' }}
                  </a>
                @else
                  —
                @endif
              </dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Kamar Kos</dt>
              <dd class="m-0 font-semibold">
                @if($payment->lease && $payment->lease->room)
                  <a href="{{ route('rooms.show', $payment->lease->room) }}" class="text-neutral-800 hover:underline">
                    Kamar {{ $payment->lease->room->room_number }} (Lt. {{ $payment->lease->room->floor }})
                  </a>
                @else
                  —
                @endif
              </dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Nomor Kontrak</dt>
              <dd class="m-0">
                @if($payment->lease)
                  <a href="{{ route('leases.show', $payment->lease) }}" class="text-accent hover:underline">
                    #LS-{{ str_pad($payment->lease->id, 4, '0', STR_PAD_LEFT) }}
                  </a>
                @else
                  —
                @endif
              </dd>
            </div>
            <div>
              <dt class="text-xs text-neutral-600 mb-1">Diverifikasi Oleh</dt>
              <dd class="m-0 font-semibold text-sm">{{ $payment->verifier->name ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-xs text-neutral-600 mb-1">Catatan</dt>
              <dd class="m-0 text-sm text-neutral-700">{{ $payment->notes ?: 'Tidak ada catatan tambahan.' }}</dd>
            </div>
          </dl>
        </div>

        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Bukti Pembayaran / Transfer</h3>
          </div>
          <div>
            @if($payment->proof_img)
              <div class="p-2 border border-neutral-200 rounded-lg text-center">
                <a href="{{ asset('storage/' . $payment->proof_img) }}" target="_blank" title="Klik untuk memperbesar">
                  <img src="{{ asset('storage/' . $payment->proof_img) }}" alt="Bukti transfer {{ $payment->invoice_number }}" class="max-h-80 mx-auto rounded object-contain">
                </a>
                <p class="text-xs text-neutral-500 mt-2">Klik gambar untuk melihat ukuran penuh</p>
              </div>
            @else
              <div class="py-12 text-center text-neutral-500 border border-dashed border-neutral-300 rounded-lg">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 8px; color: var(--color-neutral-400);"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                <p class="text-sm">Belum ada bukti pembayaran yang diunggah.</p>
              </div>
            @endif
          </div>
        </div>
      </section>

    </main>

    <script>
      document.getElementById('btnVerifyPayment')?.addEventListener('click', async function() {
        if (typeof window.showConfirmDialog === 'function') {
          const ok = await window.showConfirmDialog({
            title: 'Verifikasi Pembayaran',
            message: 'Verifikasi pembayaran ini sebagai Lunas? Kontrak sewa akan aktif dan status kamar otomatis menjadi Terisi.',
            confirmText: 'Ya, Verifikasi Lunas',
            cancelText: 'Batal',
            confirmColor: '#16a34a'
          });
          if (ok) {
            document.getElementById('verifyPaymentForm').submit();
          }
        } else {
          if (confirm('Verifikasi pembayaran ini sebagai Lunas? Kontrak sewa akan aktif dan status kamar otomatis menjadi Terisi.')) {
            document.getElementById('verifyPaymentForm').submit();
          }
        }
      });
    </script>
@endsection
