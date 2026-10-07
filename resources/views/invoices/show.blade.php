<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice #{{ $payment->invoice_number }} — KosFly</title>
  <meta name="description" content="Tagihan pembayaran sewa kamar kos {{ $payment->invoice_number }}">
  @include('partials.favicons')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="{{ asset('style.css') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="public-page">

  <!-- NAVBAR -->
  <header class="site-nav">
    <div class="container">
      <a class="brand" href="{{ route('home') }}" aria-label="KosFly — Beranda">
        <span class="brand-mark">K</span>
        Kos<span class="brand-accent">Fly</span>
      </a>

      <div class="nav-cta" style="display: flex; gap: 8px; align-items: center;">
        <a class="btn btn-secondary btn-sm" href="{{ $payment->public_receipt_url }}" target="_blank" title="Cetak Kuitansi / Invoice Resmi">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
          {{ $payment->status === 'paid' ? 'Cetak Kuitansi' : 'Cetak Invoice' }}
        </a>
        @auth
          <a class="btn btn-primary btn-sm" href="{{ route('dashboard') }}">Dashboard Saya</a>
        @else
          <a class="btn btn-secondary btn-sm" href="{{ route('login') }}">Masuk</a>
        @endauth
      </div>
    </div>
  </header>

  <main style="padding: 40px 0 80px;">
    <div class="container" style="max-width: 860px; margin: 0 auto;">

      <!-- Notifikasi Flash -->
      @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 24px;" role="status">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if(session('info'))
        <div class="alert alert-info" style="margin-bottom: 24px;" role="status">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <span>{{ session('info') }}</span>
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-error" style="margin-bottom: 24px;" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>Terdapat kendala pada unggahan bukti bayar Anda. Pastikan format gambar JPG/PNG dan ukuran maksimal 4MB.</span>
        </div>
      @endif

      <!-- KARTU INVOICE RESMI -->
      <div class="card elev-sm" style="padding: 32px; border: 1px solid var(--color-divider); border-radius: 8px; background: #fff;">
        
        <!-- Header Invoice -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--color-neutral-900); padding-bottom: 24px; margin-bottom: 24px;">
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
                <span class="tag tag-accent" style="font-weight: 700;">MENUNGGU VERIFIKASI ADMIN</span>
              @elseif($payment->status === 'overdue')
                <span class="tag tag-accent" style="background: #dc2626; color: #fff; font-weight: 700;">KADALUWARSA / OVERDUE</span>
              @else
                <span class="tag tag-outline" style="font-weight: 700; border-color: var(--color-accent); color: var(--color-accent);">BELUM DIBAYAR</span>
              @endif
            </div>
          </div>
        </div>

        <!-- Metadata Penerbitan & Jatuh Tempo -->
        <div class="grid-2" style="gap: 20px; margin-bottom: 24px;">
          <div>
            <span class="small muted" style="display: block; margin-bottom: 4px;">Ditujukan Kepada:</span>
            <strong style="font-size: 16px;">{{ $payment->lease->tenant->user->name ?? 'Penyewa' }}</strong>
            <div class="small muted">{{ $payment->lease->tenant->user->email ?? '—' }} · {{ $payment->lease->tenant->user->phone ?? '—' }}</div>
          </div>
          <div style="text-align: right;">
            <div style="margin-bottom: 6px;">
              <span class="small muted">Tanggal Terbit:</span>
              <strong>{{ $payment->created_at ? $payment->created_at->translatedFormat('d M Y, H:i') : now()->translatedFormat('d M Y') }}</strong>
            </div>
            <div>
              <span class="small muted">Batas Waktu Bayar (1x24 Jam):</span>
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
                <th>Deskripsi Sewa</th>
                <th>Kamar</th>
                <th>Masa Sewa</th>
                <th style="text-align: right;">Jumlah</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <strong>Sewa Kamar Kos</strong>
                  <div class="small muted">{{ $payment->notes ?: 'Tagihan sewa bulanan kamar kos' }}</div>
                </td>
                <td>
                  <strong>Kamar {{ $payment->lease->room->room_number ?? '—' }}</strong>
                  <div class="small muted">Lantai {{ $payment->lease->room->floor ?? '—' }}</div>
                </td>
                <td>
                  {{ \Carbon\Carbon::parse($payment->lease->start_date)->translatedFormat('d M Y') }}
                  <span class="muted">s/d</span>
                  {{ \Carbon\Carbon::parse($payment->lease->end_date)->translatedFormat('d M Y') }}
                </td>
                <td style="text-align: right; font-weight: 700; font-size: 15px;">
                  Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr style="border-top: 2px solid var(--color-neutral-900);">
                <td colspan="3" style="text-align: right; font-weight: 800; font-size: 16px;">TOTAL TAGIHAN:</td>
                <td style="text-align: right; font-weight: 800; font-size: 20px; color: var(--color-neutral-900);">
                  Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- OPSI PEMBAYARAN: PEMBAYARAN ONLINE (OTOMATIS) -->
        @if($payment->status === 'paid')
          <div class="card" style="padding: 24px; background: #f0fdf4; border: 1px solid #bbf7d0; text-align: center;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 12px;"><path d="M20 6 9 17l-5-5"/></svg>
            <h3 style="font-size: 18px; font-weight: 800; color: #166534; margin: 0 0 6px;">Pembayaran Terverifikasi Lunas!</h3>
            <p class="small muted" style="margin: 0 0 16px; color: #15803d;">
              Kamar kos Anda resmi terdaftar. Anda dapat langsung mengakses dashboard untuk memantau kontrak dan fasilitas.
            </p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary" style="display: inline-flex;">Buka Dashboard Saya</a>
          </div>

        @elseif($payment->status === 'pending')
          <div class="card" style="padding: 24px; background: #fffbeb; border: 1px solid #fde68a; text-align: center;">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 12px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <h3 style="font-size: 18px; font-weight: 800; color: #92400e; margin: 0 0 6px;">Menunggu Penyelesaian Pembayaran</h3>
            <p class="small muted" style="margin: 0 0 14px; color: #b45309;">
              Transaksi sedang diproses. Begitu transaksi Anda selesai, status tagihan akan otomatis berubah menjadi Lunas.
            </p>
            <div style="margin-bottom: 16px; display: inline-flex; align-items: center; gap: 8px; background: rgba(217, 119, 6, 0.12); padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; color: #92400e;">
              <span style="width: 8px; height: 8px; background: #16a34a; border-radius: 50%; display: inline-block; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.25);"></span>
              <span>Memantau verifikasi otomatis secara real-time...</span>
            </div>
          </div>

        @else
          <!-- Pembayaran Online Otomatis (QRIS, VA, E-Wallet) -->
          <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 2px solid var(--color-neutral-900); border-radius: 8px; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
              <div>
                <span class="tag tag-accent" style="margin-bottom: 8px; background: var(--color-neutral-900); color: #fff; font-size: 11px;">PEMBAYARAN INSTAN</span>
                <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 4px; color: var(--color-neutral-900);">
                  Pembayaran Online
                </h3>
                <p class="small muted" style="margin: 0;">
                  Bayar mudah melalui <strong>QRIS (GoPay, OVO, ShopeePay, Dana)</strong> atau <strong>Virtual Account Bank (BCA, Mandiri, BNI, BRI)</strong>. Otomatis lunas seketika tanpa perlu konfirmasi manual!
                </p>
              </div>

              <div>
                <button type="button" id="payMidtransBtn" class="btn btn-primary" style="padding: 14px 28px; font-size: 15px; font-weight: 800; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 8px;">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                  <span>Bayar Online Sekarang</span>
                </button>
              </div>
            </div>
          </div>
        @endif

      </div>

      <!-- Tombol Kembali & Cetak -->
      <div style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('public.rooms.index') }}" class="btn btn-secondary">
          &larr; Kembali ke Pilihan Kamar
        </a>
        <button onclick="window.print()" class="btn btn-ghost">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Cetak Invoice
        </button>
      </div>

    </div>
  </main>

  <!-- FOOTER -->
  <footer class="site-foot" style="background: var(--color-neutral-900); color: #fff; padding: 32px 0; border-top: 1px solid var(--color-divider); margin-top: 40px;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
      <div class="brand" style="color: #fff;">
        Kos<span class="brand-accent">Fly</span>
      </div>
      <p class="small" style="color: var(--color-neutral-400); margin: 0;">
        &copy; {{ date('Y') }} KosFly. Manajemen Kos & Sewa Online Terpadu.
      </p>
    </div>
  </footer>

  <!-- Midtrans Snap JS -->
  <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

  <script>
    // Handle Bayar via Midtrans Snap
    const payBtn = document.getElementById('payMidtransBtn');
    if (payBtn) {
      payBtn.addEventListener('click', async function() {
        const originalText = payBtn.innerHTML;
        payBtn.disabled = true;
        payBtn.innerHTML = `
          <svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/></svg>
          <span>Menghubungkan ke Midtrans...</span>
        `;

        try {
          let token = "{{ $snapToken ?? '' }}";

          // Jika token belum di-generate di server awal, ambil via AJAX
          if (!token) {
            const tokenUrl = "{{ URL::signedRoute('invoices.snap-token', ['invoice_number' => $payment->invoice_number]) }}";
            const res = await fetch(tokenUrl, {
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              }
            });
            const data = await res.json();
            if (!data.success) {
              throw new Error(data.message || 'Gagal memuat Snap Token dari server.');
            }
            token = data.snap_token;
          }

          if (typeof window.snap === 'undefined') {
            throw new Error('Midtrans Snap SDK gagal dimuat. Periksa koneksi internet Anda.');
          }

          window.snap.pay(token, {
            onSuccess: function(result) {
              if (typeof window.showToast === 'function') {
                window.showToast('Pembayaran Anda berhasil diproses! Memperbarui status...', 'success');
              }
              setTimeout(() => window.location.reload(), 1500);
            },
            onPending: function(result) {
              if (typeof window.showToast === 'function') {
                window.showToast('Menunggu pembayaran diselesaikan...', 'info');
              }
              setTimeout(() => window.location.reload(), 2000);
            },
            onError: function(result) {
              if (typeof window.showToast === 'function') {
                window.showToast('Pembayaran gagal atau dibatalkan.', 'error');
              }
              payBtn.disabled = false;
              payBtn.innerHTML = originalText;
            },
            onClose: function() {
              payBtn.disabled = false;
              payBtn.innerHTML = originalText;
            }
          });

        } catch (err) {
          console.error('Midtrans error:', err);
          if (typeof window.showToast === 'function') {
            window.showToast(err.message || 'Terjadi kesalahan saat memproses Midtrans.', 'error');
          } else {
            alert(err.message || 'Terjadi kesalahan saat memproses Midtrans.');
          }
          payBtn.disabled = false;
          payBtn.innerHTML = originalText;
        }
      });
    }

    // Countdown Timer 1x24 Jam
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
    // Realtime Verification Poller
    (function() {
      const statusUrl = "{{ URL::signedRoute('invoices.status', ['invoice_number' => $payment->invoice_number]) }}";
      let pollCount = 0;
      const maxPoll = 600; // Aktif hingga 30 menit
      
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
            if (typeof window.showToast === 'function') {
              window.showToast('Pembayaran Anda berhasil diverifikasi oleh pengelola! 🎉 Memperbarui halaman...', 'success');
            }
            setTimeout(function() {
              window.location.reload();
            }, 1200);
          }
        } catch (e) {
          console.debug('Realtime status poll:', e);
        }
      }, 3000);
    })();
    @endif
  </script>

  <!-- Toast notifications container -->
  <div id="toastRoot" aria-live="polite"></div>
</body>
</html>

