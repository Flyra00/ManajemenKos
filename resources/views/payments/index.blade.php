@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Pembayaran</span>
          </nav>
          <h2 class="page-title">Pembayaran & Tagihan</h2>
          <p class="page-sub">Kelola invoice tagihan sewa kos, konfirmasi pembayaran, dan pantau piutang.</p>
        </div>
        @if(!auth()->user() || !auth()->user()->hasRole('owner'))
          <div class="flex head-actions" style="gap: 8px;">
            <button type="button" class="btn btn-secondary" onclick="openGenerateBillsModal()" title="Otomatis terbitkan invoice sewa untuk semua kontrak aktif">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
              Terbitkan Tagihan Bulanan
            </button>
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Tambah Pembayaran
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

      @if(session('info'))
        <div class="alert alert-info" role="alert" style="background: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; padding: 12px 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <span style="color: #1d4ed8; font-size: 13px;">{{ session('info') }}</span>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      <!-- Kartu statistik -->
      <section class="grid-5" aria-label="Statistik pembayaran">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Invoice</div>
          <div class="stat-value">{{ $stats['total'] }}</div>
          <div class="stat-sub muted">semua transaksi</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Sudah Dibayar</div>
          <div class="stat-value">{{ $stats['paid'] }}</div>
          <div class="stat-sub muted">Rp {{ number_format($stats['nominal'], 0, ',', '.') }}</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Menunggu Verifikasi</div>
          <div class="stat-value">{{ $stats['pending'] }}</div>
          <div class="stat-sub muted">perlu dicek</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Belum Dibayar</div>
          <div class="stat-value">{{ $stats['unpaid'] }}</div>
          <div class="stat-sub muted">belum lunas</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Terlambat (Overdue)</div>
          <div class="stat-value">{{ $stats['overdue'] }}</div>
          <div class="stat-sub muted">lewat jatuh tempo</div>
        </div>
      </section>

      <!-- Filter + tabel pembayaran -->
      <section class="card elev-sm section-card" aria-label="Daftar pembayaran">
        <div class="card-head">
          <h3 class="card-title">Daftar Tagihan & Pembayaran</h3>
          <span class="small muted">{{ $payments->total() }} transaksi</span>
        </div>

        <form method="GET" action="{{ route('payments.index') }}" class="filter-bar">
          <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari invoice, penghuni, kamar…" aria-label="Cari pembayaran">
          <select class="input" name="status" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="paid" @selected(request('status') === 'paid')>Lunas</option>
            <option value="pending" @selected(request('status') === 'pending')>Menunggu Verifikasi</option>
            <option value="unpaid" @selected(request('status') === 'unpaid')>Belum Bayar</option>
            <option value="overdue" @selected(request('status') === 'overdue')>Terlambat (Overdue)</option>
          </select>
          <select class="input" name="payment_method" aria-label="Filter metode">
            <option value="">Semua Metode</option>
            <option value="bank_tf" @selected(request('payment_method') === 'bank_tf')>Transfer Bank</option>
            <option value="e_wallet" @selected(request('payment_method') === 'e_wallet')>E-Wallet / QRIS</option>
            <option value="cash" @selected(request('payment_method') === 'cash')>Tunai</option>
          </select>
          <label class="flex items-center" style="gap:6px">
            <span class="small muted">Periode:</span>
            <input class="input" type="month" name="month" value="{{ request('month') }}" aria-label="Filter periode bulan">
          </label>
          <button type="submit" class="btn btn-secondary">Terapkan</button>
          @if(request()->hasAny(['search', 'status', 'payment_method', 'month']))
            <a href="{{ route('payments.index') }}" class="btn btn-ghost">Reset</a>
          @endif
        </form>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Penghuni</th>
                <th>Kamar</th>
                <th>Periode</th>
                <th>Jumlah</th>
                <th>Jatuh Tempo</th>
                <th>Tgl Bayar</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($payments as $payment)
                <tr>
                  <td class="font-semibold">{{ $payment->invoice_number }}</td>
                  <td>
                    @if($payment->lease && $payment->lease->tenant && $payment->lease->tenant->user)
                      <a href="{{ route('tenants.show', $payment->lease->tenant) }}" class="text-accent hover:underline">
                        {{ $payment->lease->tenant->user->name }}
                      </a>
                    @else
                      <span class="muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($payment->lease && $payment->lease->room)
                      <a href="{{ route('rooms.show', $payment->lease->room) }}" class="font-semibold text-neutral-800">
                        Kamar {{ $payment->lease->room->room_number }}
                      </a>
                    @else
                      <span class="muted">—</span>
                    @endif
                  </td>
                  <td>{{ $payment->billing_period ? \Carbon\Carbon::parse($payment->billing_period)->translatedFormat('M Y') : '—' }}</td>
                  <td class="font-semibold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                  <td>{{ $payment->due_date ? \Carbon\Carbon::parse($payment->due_date)->translatedFormat('d M Y') : '—' }}</td>
                  <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d M Y') : '—' }}</td>
                  <td>
                    @if($payment->payment_method === 'bank_tf')
                      Transfer
                    @elseif($payment->payment_method === 'e_wallet')
                      E-Wallet
                    @else
                      Tunai
                    @endif
                  </td>
                  <td>
                    @if($payment->status === 'paid')
                      <span class="tag tag-accent">Lunas</span>
                    @elseif($payment->status === 'pending')
                      <span class="tag tag-neutral">Pending</span>
                    @elseif($payment->status === 'overdue')
                      <span class="tag tag-outline" style="color:var(--color-accent); border-color:var(--color-accent)">Terlambat</span>
                    @else
                      <span class="tag tag-outline">Belum Bayar</span>
                    @endif
                  </td>
                  <td>
                    <div class="flex gap-1" style="align-items: center;">
                      <a href="{{ route('payments.show', $payment) }}" class="btn btn-ghost btn-sm" title="Lihat detail invoice">
                        Detail
                      </a>
                      @if($payment->status !== 'paid')
                        @php
                          $waUrl = isset($billingService) ? $billingService->buildWhatsAppReminderUrl($payment) : null;
                        @endphp
                        @if($waUrl)
                          <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                             class="btn btn-sm"
                             style="background:#16a34a; color:#ffffff; padding: 3px 8px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; border: none; border-radius: 4px; font-weight: 600;"
                             title="Kirim pengingat tagihan via WhatsApp">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                            WA
                          </a>
                        @endif
                      @endif
                      @if(!auth()->user() || !auth()->user()->hasRole('owner'))
                        <a href="{{ route('payments.edit', $payment) }}" class="btn btn-secondary btn-sm" title="Edit invoice">
                          Edit
                        </a>
                        <button type="button" class="btn btn-ghost btn-sm text-accent"
                                onclick="openDeletePaymentModal('{{ route('payments.destroy', $payment) }}', '{{ $payment->invoice_number }}', '{{ $payment->lease->tenant->user->name ?? 'Penghuni' }}')">
                          Hapus
                        </button>
                      @endif
                    </div>

                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="text-center py-6 text-neutral-500">
                    Tidak ada data tagihan/pembayaran yang sesuai kriteria.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($payments->hasPages())
          <div class="card-footer flex justify-between items-center py-3 px-4 border-t border-neutral-200">
            <span class="text-sm text-neutral-600">
              Menampilkan {{ $payments->firstItem() }} - {{ $payments->lastItem() }} dari {{ $payments->total() }} pembayaran
            </span>
            <div>
              {{ $payments->links() }}
            </div>
          </div>
        @endif
      </section>

    </main>

    <!-- Modal Konfirmasi Hapus Pembayaran -->
    <div class="dialog-backdrop" id="deletePaymentModal" style="display:none; align-items:center; justify-content:center;" data-close="1">
      <div class="dialog" style="margin: auto;">
        <div class="dialog-title">Konfirmasi Hapus Pembayaran</div>
        <p class="small muted" style="margin:8px 0 16px;line-height:1.5">
          Apakah Anda yakin ingin menghapus tagihan/pembayaran nomor <strong id="deletePaymentInvoice"></strong> atas nama <strong id="deletePaymentTenant"></strong>? Tindakan ini tidak dapat dibatalkan.
        </p>
        <form id="deletePaymentForm" method="POST" action="">
          @csrf
          @method('DELETE')
          <div class="dialog-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeletePaymentModal()">Batal</button>
            <button type="submit" class="btn btn-primary" style="background-color: var(--color-accent, #e53e3e); border-color: var(--color-accent, #e53e3e);">
              Ya, Hapus
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Terbitkan Tagihan Sewa Bulanan Otomatis -->
    <div class="dialog-backdrop" id="generateBillsModal" style="display:none; align-items:center; justify-content:center;" data-close="1">
      <div class="dialog" style="max-width: 480px; margin: auto;">
        <div class="dialog-title">Terbitkan Tagihan Sewa Bulanan</div>
        <p class="small muted" style="margin:8px 0 16px;line-height:1.5">
          Sistem akan memeriksa seluruh <strong>kontrak sewa aktif</strong> dan membuatkan invoice tagihan sewa secara otomatis. Kontrak yang sudah memiliki tagihan pada periode tersebut akan otomatis dilewati agar tidak terjadi duplikasi.
        </p>
        <form id="generateBillsForm" method="POST" action="{{ route('payments.generate_bills') }}">
          @csrf
          <div class="field" style="margin-bottom: 16px;">
            <label for="generateMonthInput">Pilih Periode Tagihan</label>
            <input type="month" name="month" id="generateMonthInput" class="input" value="{{ date('Y-m') }}" required>
            <span class="small muted" style="margin-top: 4px; display: block;">Default: Bulan berjalan ({{ now()->translatedFormat('F Y') }}).</span>
          </div>

          <div class="dialog-actions">
            <button type="button" class="btn btn-secondary" onclick="closeGenerateBillsModal()">Batal</button>
            <button type="submit" class="btn btn-primary">
              ⚡ Proses Sekarang
            </button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function openDeletePaymentModal(actionUrl, invoice, tenant) {
        const modal = document.getElementById('deletePaymentModal');
        const form = document.getElementById('deletePaymentForm');
        const invoiceSpan = document.getElementById('deletePaymentInvoice');
        const tenantSpan = document.getElementById('deletePaymentTenant');

        form.action = actionUrl;
        invoiceSpan.textContent = invoice;
        tenantSpan.textContent = tenant;
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
      }

      function closeDeletePaymentModal() {
        const modal = document.getElementById('deletePaymentModal');
        modal.style.display = 'none';
      }

      function openGenerateBillsModal() {
        const modal = document.getElementById('generateBillsModal');
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
      }

      function closeGenerateBillsModal() {
        const modal = document.getElementById('generateBillsModal');
        modal.style.display = 'none';
      }

      window.addEventListener('click', function(e) {
        const delModal = document.getElementById('deletePaymentModal');
        if (e.target === delModal) {
          closeDeletePaymentModal();
        }
        const genModal = document.getElementById('generateBillsModal');
        if (e.target === genModal) {
          closeGenerateBillsModal();
        }
      });
    </script>
@endsection

