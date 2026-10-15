@extends('layouts.app')

@section('content')
<main class="page">

  <!-- Header Halaman -->
  <section class="page-head" aria-label="Judul halaman">
    <div>
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="sep">/</span>
        <span class="current">Tagihan #{{ $payment->invoice_number }}</span>
      </nav>
      <h2 class="page-title">Tagihan Sewa #{{ $payment->invoice_number }}</h2>
      <p class="page-sub">Sewa Kamar {{ $payment->lease->room->room_number ?? '—' }} · {{ $kosSettings['name'] }}</p>
    </div>
    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
      <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        &larr; Kembali ke Dashboard
      </a>
      <a href="{{ $payment->public_receipt_url }}" target="_blank" class="btn btn-secondary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
        {{ $payment->status === 'paid' ? 'Cetak Kuitansi' : 'Cetak Invoice' }}
      </a>
    </div>
  </section>

  <!-- Notifikasi Flash -->
  @if(session('success'))
    <div class="alert alert-success" role="status">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if(session('info'))
    <div class="alert alert-info" role="status">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
      <span>{{ session('info') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-error" role="alert">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>Terdapat kendala pada unggahan bukti bayar Anda. Pastikan format gambar JPG/PNG dan ukuran maksimal 4MB.</span>
    </div>
  @endif

  <!-- KARTU INVOICE RESMI -->
  <div class="card elev-sm" style="padding: 32px; border: 1px solid var(--color-divider); border-radius: 8px; background: #ffffff;">
    
    <!-- Header Invoice -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--color-neutral-900); padding-bottom: 24px; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
      <div>
        <div class="brand" style="font-size: 24px; margin-bottom: 4px;">
          Kos<span class="brand-accent">Fly</span>
        </div>
        <div class="small muted">{{ $kosSettings['name'] }} · Kontak: {{ $kosSettings['phone'] }}</div>
      </div>
      <div style="text-align: right;">
        <span class="small muted" style="text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">INVOICE TAGIHAN</span>
        <div style="font-size: 22px; font-weight: 800; font-family: monospace; color: var(--color-neutral-900); margin: 4px 0;">
          {{ $payment->invoice_number }}
        </div>
        <div>
          @if($payment->status === 'paid')
            <span class="tag tag-accent" style="background: #16a34a; color: #fff; font-weight: 700;">LUNAS TERVERIFIKASI</span>
          @elseif($payment->status === 'pending')
            <span class="tag tag-accent" style="background: #d97706; color: #fff; font-weight: 700;">MENUNGGU VERIFIKASI ADMIN</span>
          @elseif($payment->status === 'overdue')
            <span class="tag tag-accent" style="background: #dc2626; color: #fff; font-weight: 700;">KADALUWARSA / OVERDUE</span>
          @else
            <span class="tag tag-outline" style="font-weight: 700; border-color: var(--color-accent); color: var(--color-accent); background: #fff5f5;">BELUM DIBAYAR</span>
          @endif
        </div>
      </div>
    </div>

    <!-- Metadata Penerbitan & Jatuh Tempo -->
    <div class="grid-2" style="gap: 20px; margin-bottom: 24px;">
      <div>
        <span class="small muted" style="display: block; margin-bottom: 4px;">Ditujukan Kepada:</span>
        <strong style="font-size: 16px; color: var(--color-neutral-900);">{{ $payment->lease->tenant->user->name ?? 'Penyewa' }}</strong>
        <div class="small muted">{{ $payment->lease->tenant->user->email ?? '—' }} · {{ $payment->lease->tenant->user->phone ?? '—' }}</div>
      </div>
      <div style="text-align: right;">
        <div style="margin-bottom: 6px;">
          <span class="small muted">Tanggal Terbit:</span>
          <strong>{{ $payment->created_at ? $payment->created_at->translatedFormat('d M Y, H:i') : now()->translatedFormat('d M Y') }}</strong>
        </div>
        <div>
          <span class="small muted">Batas Waktu Bayar:</span>
          <strong style="color: var(--color-accent);">{{ \Carbon\Carbon::parse($payment->due_date)->translatedFormat('d M Y, 23:59') }}</strong>
        </div>
        @if($payment->status === 'unpaid')
          <div class="small muted" style="margin-top: 4px; font-weight: 600; color: var(--color-accent);">
            ⏳ Sisa Waktu: <span id="countdownTimer">Memuat...</span>
          </div>
        @endif
      </div>
    </div>

    <!-- Rincian Item Tagihan -->
    <div class="table-wrap" style="margin-bottom: 24px;">
      <table class="table" style="width: 100%;">
        <thead>
          <tr style="background: var(--color-neutral-50);">
            <th style="text-align: left; padding: 12px 16px;">Keterangan Item</th>
            <th style="text-align: center; padding: 12px 16px;">Kamar</th>
            <th style="text-align: right; padding: 12px 16px;">Total Jumlah</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding: 16px;">
              <strong>Sewa Kamar Kos ({{ \Carbon\Carbon::parse($payment->billing_period)->translatedFormat('F Y') }})</strong>
              <div class="small muted" style="margin-top: 4px;">
                {{ $payment->notes ?: 'Tagihan sewa resmi KosFly Residence.' }}
              </div>
            </td>
            <td style="text-align: center; padding: 16px; font-weight: 700;">
              Kamar {{ $payment->lease->room->room_number ?? '—' }}
            </td>
            <td style="text-align: right; padding: 16px; font-size: 16px; font-weight: 800; color: var(--color-neutral-900);">
              Rp {{ number_format($payment->amount, 0, ',', '.') }}
            </td>
          </tr>
          <tr style="background: var(--color-neutral-50); font-weight: 800;">
            <td colspan="2" style="text-align: right; padding: 14px 16px; font-size: 15px;">TOTAL TAGIHAN:</td>
            <td style="text-align: right; padding: 14px 16px; font-size: 18px; color: var(--color-accent);">
              Rp {{ number_format($payment->amount, 0, ',', '.') }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ==================== BAGIAN STATUS & PEMBAYARAN ==================== -->
    @if($payment->status === 'paid')
      <!-- State 1: LUNAS -->
      <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 24px; border-radius: 8px; text-align: center;">
        <div style="width: 52px; height: 52px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: #16a34a;">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h3 style="font-size: 18px; font-weight: 800; color: #166534; margin: 0 0 6px;">Pembayaran Telah Lunas &amp; Diverifikasi</h3>
        <p class="small muted" style="margin: 0 0 16px; color: #15803d;">
          Terima kasih! Pembayaran tagihan ini telah resmi diterima dan diverifikasi pada <strong>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y, H:i') : '—' }}</strong>.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
          <a href="{{ $payment->public_receipt_url }}" target="_blank" class="btn btn-primary" style="background: #16a34a; border-color: #16a34a; font-weight: 700;">
            Lihat &amp; Cetak Kuitansi Resmi &rarr;
          </a>
          <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            Buka Dashboard Portal
          </a>
        </div>
      </div>

    @elseif($payment->status === 'pending')
      <!-- State 2: MENUNGGU VERIFIKASI ADMIN -->
      <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 24px; border-radius: 8px;">
        <div style="display: flex; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
          <div style="width: 44px; height: 44px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #d97706; flex-shrink: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <div style="flex: 1; min-width: 260px;">
            <h3 style="font-size: 16px; font-weight: 800; color: #92400e; margin: 0 0 4px;">Bukti Pembayaran Sedang Diverifikasi Admin</h3>
            <p class="small muted" style="margin: 0 0 12px; color: #b45309; line-height: 1.5;">
              Bukti transfer Anda telah berhasil kami terima. Pengelola kos sedang memverifikasi mutasi rekening. Halaman ini akan otomatis diperbarui begitu pembayaran disetujui.
            </p>
            @if($payment->proof_img || $payment->proof_image)
              <div style="margin-top: 12px;">
                <span class="small muted" style="display: block; margin-bottom: 6px; font-weight: 600;">Lampiran Bukti Terkirim:</span>
                <a href="{{ route('payments.proof', $payment) }}" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 12px;">
                  Lihat Bukti Transfer &rarr;
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>

    @else
      <!-- State 3: BELUM DIBAYAR (INSTRUKSI PEMBAYARAN) -->
      <div style="border: 1px solid var(--color-divider); border-radius: 8px; overflow: hidden;">
        
        <!-- Tab Pilihan Metode Pembayaran -->
        <div style="display: flex; background: var(--color-neutral-100); border-bottom: 1px solid var(--color-divider);">
          @if(!empty($snapToken))
            <button type="button" id="tabMidtrans" class="btn" style="flex: 1; border-radius: 0; background: #ffffff; border: none; border-bottom: 2px solid var(--color-accent); font-weight: 700; padding: 14px 16px; color: var(--color-neutral-900);">
              💳 Bayar Otomatis (QRIS / Virtual Account / E-Wallet)
            </button>
          @endif
          <button type="button" id="tabManual" class="btn" style="flex: 1; border-radius: 0; background: {{ empty($snapToken) ? '#ffffff' : 'transparent' }}; border: none; border-bottom: {{ empty($snapToken) ? '2px solid var(--color-accent)' : 'none' }}; font-weight: 700; padding: 14px 16px; color: var(--color-neutral-900);">
            🏦 Transfer Bank Manual
          </button>
        </div>

        <div style="padding: 24px;">
          @if(!empty($snapToken))
            <!-- METODE 1: MIDTRANS SNAP -->
            <div id="contentMidtrans">
              <div style="text-align: center; padding: 20px 0;">
                <h4 style="font-size: 16px; font-weight: 800; margin: 0 0 8px;">Pembayaran Instan &amp; Otomatis</h4>
                <p class="small muted" style="margin: 0 0 20px; max-width: 440px; margin-inline: auto;">
                  Bayar secara real-time menggunakan QRIS, GoPay, ShopeePay, OVO, atau Virtual Account BCA, Mandiri, BNI, dan BRI tanpa perlu konfirmasi manual.
                </p>
                <button type="button" id="payButton" class="btn btn-primary" style="font-weight: 800; font-size: 16px; padding: 14px 32px; box-shadow: 0 4px 12px rgba(183, 19, 26, 0.25);">
                  Bayar Sekarang Rp {{ number_format($payment->amount, 0, ',', '.') }} &rarr;
                </button>
              </div>
            </div>
          @endif

          <!-- METODE 2: TRANSFER BANK MANUAL & UPLOAD BUKTI -->
          <div id="contentManual" style="display: {{ empty($snapToken) ? 'block' : 'none' }};">
            <h4 style="font-size: 15px; font-weight: 700; margin: 0 0 12px;">Rekening Bank Tujuan Pengelola Kos:</h4>
            
            <div style="display: flex; gap: 16px; align-items: center; padding: 16px; background: var(--color-neutral-50); border: 1px solid var(--color-divider); border-radius: 6px; margin-bottom: 24px; flex-wrap: wrap;">
              <div style="font-size: 28px; width: 48px; height: 48px; border-radius: 8px; background: #ffffff; border: 1px solid var(--color-divider); display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--color-neutral-800);">
                🏦
              </div>
              <div>
                <span class="small muted" style="text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">{{ $kosSettings['bank_name'] ?? 'BCA' }}</span>
                <div style="font-size: 20px; font-weight: 800; font-family: monospace; letter-spacing: 1px; color: var(--color-neutral-900);">
                  {{ $kosSettings['bank_account_number'] ?? '1234567890' }}
                </div>
                <div class="small muted">Atas Nama: <strong>{{ $kosSettings['bank_account_holder'] ?? 'Pengelola Kos' }}</strong></div>
              </div>
            </div>

            <!-- Form Upload Bukti Transfer -->
            <form method="POST" action="{{ route('invoices.pay', $payment->invoice_number) }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
              @csrf

              <div class="field">
                <label for="payment_method" style="font-size: 13px; font-weight: 600;">Metode Transfer yang Digunakan <span class="text-accent">*</span></label>
                <select class="input" id="payment_method" name="payment_method" required>
                  <option value="bank_tf" @selected(old('payment_method') === 'bank_tf')>Transfer Antar Bank / M-Banking</option>
                  <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                  <option value="e_wallet" @selected(old('payment_method') === 'e_wallet')>E-Wallet (Dana/OVO/GoPay)</option>
                  <option value="cash" @selected(old('payment_method') === 'cash')>Tunai Langsung ke Pengelola</option>
                </select>
              </div>

              <div class="field">
                <label for="proof_image" style="font-size: 13px; font-weight: 600;">Unggah Foto / Screenshot Bukti Transfer <span class="text-accent">*</span></label>
                <input class="input" type="file" id="proof_image" name="proof_image" accept="image/*" required>
                <span class="small muted" style="font-size: 11px;">Format: JPG, JPEG, PNG, atau WebP. Maksimal 4 MB.</span>
              </div>

              <button type="submit" class="btn btn-primary" style="font-weight: 700; padding: 12px 24px; align-self: flex-start;">
                Kirim Bukti Pembayaran &rarr;
              </button>
            </form>
          </div>
        </div>

      </div>
    @endif

  </div>

</main>

@if(!empty($snapToken))
  @php
    $midtransIsProduction = config('midtrans.is_production', false);
    $snapUrl = $midtransIsProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
  @endphp
  <script src="{{ $snapUrl }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
@endif

<script>
  // Tab Switcher Midtrans vs Transfer Bank
  const tabMidtrans = document.getElementById('tabMidtrans');
  const tabManual = document.getElementById('tabManual');
  const contentMidtrans = document.getElementById('contentMidtrans');
  const contentManual = document.getElementById('contentManual');

  if (tabMidtrans && tabManual) {
    tabMidtrans.addEventListener('click', () => {
      tabMidtrans.style.background = '#ffffff';
      tabMidtrans.style.borderBottom = '2px solid var(--color-accent)';
      tabManual.style.background = 'transparent';
      tabManual.style.borderBottom = 'none';
      if (contentMidtrans) contentMidtrans.style.display = 'block';
      if (contentManual) contentManual.style.display = 'none';
    });

    tabManual.addEventListener('click', () => {
      tabManual.style.background = '#ffffff';
      tabManual.style.borderBottom = '2px solid var(--color-accent)';
      tabMidtrans.style.background = 'transparent';
      tabMidtrans.style.borderBottom = 'none';
      if (contentMidtrans) contentMidtrans.style.display = 'none';
      if (contentManual) contentManual.style.display = 'block';
    });
  }

  // Midtrans Snap Pay Trigger
  const payBtn = document.getElementById('payButton');
  if (payBtn) {
    payBtn.addEventListener('click', () => {
      if (typeof snap !== 'undefined') {
        snap.pay('{{ $snapToken }}', {
          onSuccess: function(result) {
            window.location.reload();
          },
          onPending: function(result) {
            window.location.reload();
          },
          onError: function(result) {
            alert('Pembayaran gagal atau dibatalkan. Silakan coba kembali.');
          },
          onClose: function() {
            console.log('Customer closed popup without payment.');
          }
        });
      }
    });
  }

  // Countdown Timer
  const timerElem = document.getElementById('countdownTimer');
  if (timerElem) {
    const dueDate = new Date("{{ \Carbon\Carbon::parse($payment->due_date)->endOfDay()->toIso8601String() }}").getTime();

    function updateTimer() {
      const now = new Date().getTime();
      const diff = dueDate - now;

      if (diff <= 0) {
        timerElem.textContent = 'Batas Waktu Telah Berakhir';
        timerElem.style.color = '#dc2626';
        return;
      }

      const hours = Math.floor(diff / (1000 * 60 * 60));
      const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((diff % (1000 * 60)) / 1000);

      timerElem.textContent = `${hours} jam ${minutes} menit ${seconds} detik`;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
  }

  @if($payment->status === 'pending')
  // Realtime Poller
  (function() {
    const statusUrl = "{{ URL::signedRoute('invoices.status', ['invoice_number' => $payment->invoice_number]) }}";
    let pollCount = 0;
    const maxPoll = 600;
    
    const pollTimer = setInterval(async function() {
      pollCount++;
      if (pollCount > maxPoll) {
        clearInterval(pollTimer);
        return;
      }

      try {
        const res = await fetch(statusUrl, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.is_paid) {
          clearInterval(pollTimer);
          window.location.reload();
        }
      } catch (e) {
        console.debug('Realtime poll:', e);
      }
    }, 3000);
  })();
  @endif
</script>
@endsection
