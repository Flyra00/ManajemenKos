@extends('layouts.app')

@section('content')
<main class="page">

  <!-- Header Halaman -->
  <section class="page-head" aria-label="Judul halaman">
    <div>
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span class="sep">/</span>
        <a href="{{ route('public.rooms.index') }}">Pilihan Kamar</a>
        <span class="sep">/</span>
        <span class="current">Kamar {{ $room->room_number }}</span>
      </nav>
      <h2 class="page-title">Kamar {{ $room->room_number }}</h2>
      <p class="page-sub">Lantai {{ $room->floor ?? '—' }} · {{ $kosSettings['name'] }}</p>
    </div>
    <div>
      <a href="{{ route('public.rooms.index') }}" class="btn btn-secondary">
        &larr; Kembali ke Pilihan Kamar
      </a>
    </div>
  </section>

  <!-- Notifikasi Error / Flash -->
  @if($errors->any())
    <div class="alert alert-error" role="alert">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
      <div>
        <strong style="display:block; margin-bottom:4px;">Gagal Mengajukan Sewa:</strong>
        <ul style="margin: 0 0 0 16px; padding: 0;">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-error" role="alert">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  <!-- Dua Kolom: Detail Kamar & Formulir Sewa -->
  <div class="grid-2" style="gap: 24px; align-items: flex-start;">

    <!-- ==================== KOLOM KIRI: SPESIFIKASI KAMAR ==================== -->
    <div style="display: flex; flex-direction: column; gap: 24px;">

      <!-- Card Foto & Info Utama -->
      <div class="card elev-sm" style="overflow: hidden; border: 1px solid var(--color-divider); border-radius: 8px; background: #ffffff;">
        <div style="height: 320px; background: var(--color-neutral-100); overflow: hidden; position: relative;">
          @if($room->image)
            <img src="{{ asset('storage/' . $room->image) }}" alt="Foto Kamar {{ $room->room_number }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80';" style="width: 100%; height: 100%; object-fit: cover;">
          @else
            <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80" alt="Foto Kamar {{ $room->room_number }}" style="width: 100%; height: 100%; object-fit: cover;">
          @endif
          <span class="tag tag-accent" style="position: absolute; top: 16px; left: 16px; font-weight: 700; font-size: 12px; background: rgba(22, 163, 74, 0.95); color: #fff;">
            Siap Huni
          </span>
          <span class="tag tag-neutral" style="position: absolute; top: 16px; right: 16px; font-weight: 600; font-size: 12px; background: rgba(24, 26, 29, 0.75); color: #fff;">
            Lantai {{ $room->floor ?? '—' }}
          </span>
        </div>

        <div style="padding: 24px;">
          <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
            <div>
              <h3 style="font-size: 24px; font-weight: 800; margin: 0; color: var(--color-neutral-900);">
                Kamar {{ $room->room_number }}
              </h3>
              <span class="small muted">Lantai {{ $room->floor ?? '—' }} · {{ $kosSettings['name'] }}</span>
            </div>
            <div style="text-align: right;">
              <span class="small muted" style="display: block;">Tarif Sewa:</span>
              <span style="font-size: 22px; font-weight: 800; color: var(--color-accent);">
                Rp {{ number_format($room->price, 0, ',', '.') }}
              </span>
              <span class="small muted">/ bulan</span>
            </div>
          </div>

          <!-- Fasilitas Kamar -->
          <div style="margin-bottom: 24px;">
            <h4 style="font-size: 15px; font-weight: 700; margin: 0 0 10px; color: var(--color-neutral-900);">Fasilitas Kamar</h4>
            @if($room->facilities->isNotEmpty())
              <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                @foreach($room->facilities as $facility)
                  <span class="tag tag-neutral" style="font-size: 12px; padding: 5px 12px; background: var(--color-neutral-100); border: 1px solid var(--color-divider); color: var(--color-neutral-800);">
                    ✓ {{ $facility->name }}
                  </span>
                @endforeach
              </div>
            @else
              <p class="small muted" style="margin: 0;">Fasilitas kamar standar kos.</p>
            @endif
          </div>

          <!-- Deskripsi Kamar -->
          @if($room->description)
            <div style="margin-bottom: 20px;">
              <h4 style="font-size: 15px; font-weight: 700; margin: 0 0 8px; color: var(--color-neutral-900);">Deskripsi Kamar</h4>
              <p style="margin: 0; font-size: 14px; color: var(--color-neutral-700); line-height: 1.6;">
                {{ $room->description }}
              </p>
            </div>
          @endif

          <!-- Kontak Bantuan WhatsApp -->
          <div style="padding-top: 16px; border-top: 1px solid var(--color-divider); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div class="small muted">
              Ada pertanyaan tentang kamar ini?
            </div>
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $kosSettings['phone']) }}" target="_blank" class="btn btn-secondary btn-sm" style="font-weight: 600;">
              Hubungi Pengelola via WA
            </a>
          </div>
        </div>
      </div>

    </div>

    <!-- ==================== KOLOM KANAN: FORM PENGAJUAN SEWA ==================== -->
    <div>
      <div class="card elev-sm" style="padding: 24px; border: 1px solid var(--color-divider); border-radius: 8px; background: #ffffff;">
        <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 6px; color: var(--color-neutral-900);">
          Formulir Pengajuan Sewa
        </h3>
        <p class="small muted" style="margin: 0 0 20px; line-height: 1.5;">
          Lengkapi data sewa kamar di bawah ini untuk menerbitkan invoice tagihan resmi Anda.
        </p>

        <!-- Informasi Akun Penyewa yang Login -->
        <div class="card" style="padding: 14px 16px; background: var(--color-neutral-50); border: 1px solid var(--color-divider); border-radius: 6px; margin-bottom: 20px;">
          <div class="small muted" style="margin-bottom: 2px;">Akun Penyewa:</div>
          <strong style="font-size: 15px; color: var(--color-neutral-900); display: block;">{{ auth()->user()->name }}</strong>
          <div class="small muted">{{ auth()->user()->email }} · {{ auth()->user()->phone ?? 'Belum ada nomor HP' }}</div>
        </div>

        <form method="POST" action="{{ route('public.rooms.book', $room) }}" style="display: flex; flex-direction: column; gap: 16px;">
          @csrf

          <!-- Nomor KTP / NIK -->
          <div class="field">
            <label for="ktp_number" style="font-size: 13px; font-weight: 600;">Nomor KTP / NIK (16 Digit) <span class="text-accent">*</span></label>
            <input class="input @error('ktp_number') is-invalid @enderror" type="text" id="ktp_number" name="ktp_number" value="{{ old('ktp_number', (auth()->user()->tenant && !str_starts_with(auth()->user()->tenant->ktp_number, 'KTP-')) ? auth()->user()->tenant->ktp_number : '') }}" placeholder="16 digit angka sesuai KTP" maxlength="16" pattern="[0-9]{16}" inputmode="numeric" required>
            <span class="small muted" style="font-size: 11px;">Wajib diisi sesuai KTP asli untuk dokumen kontrak hunian resmi.</span>
            @error('ktp_number')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Tanggal Mulai Menghuni -->
          <div class="field">
            <label for="start_date" style="font-size: 13px; font-weight: 600;">Tanggal Mulai Menghuni <span class="text-accent">*</span></label>
            <input class="input @error('start_date') is-invalid @enderror" type="date" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
            @error('start_date')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- Durasi Sewa -->
          <div class="field">
            <label for="duration_months" style="font-size: 13px; font-weight: 600;">Durasi Sewa Awal</label>
            <select class="input" id="duration_months" name="duration_months">
              <option value="1" @selected(old('duration_months') == '1')>1 Bulan (Bulanan)</option>
              <option value="3" @selected(old('duration_months') == '3')>3 Bulan (Triwulan)</option>
              <option value="6" @selected(old('duration_months') == '6')>6 Bulan (Semester)</option>
              <option value="12" @selected(old('duration_months') == '12')>12 Bulan (1 Tahun)</option>
            </select>
          </div>

          <!-- Skema Pembayaran -->
          <div class="field">
            <label for="payment_type" style="font-size: 13px; font-weight: 600;">Skema Pembayaran <span class="text-accent">*</span></label>
            <select class="input" id="payment_type" name="payment_type">
              <option value="full" @selected(old('payment_type') === 'full')>Bayar Penuh 100% (Pelunasan di Awal)</option>
              <option value="deposit_50" @selected(old('payment_type') === 'deposit_50')>Uang Muka / Deposit 50% (Sisa dibayar kemudian)</option>
            </select>
            <span class="small muted" style="font-size: 11px;">Jika memilih Deposit 50%, sisa tagihan sewa berkurang 50% saat pelunasan berikutnya.</span>
          </div>

          <!-- Rangkuman Biaya Real-time -->
          <div style="padding: 16px; background: var(--color-neutral-50); border: 1px solid var(--color-divider); border-radius: 6px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
              <span class="muted">Tarif Kamar:</span>
              <span>Rp {{ number_format($room->price, 0, ',', '.') }} / bln</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
              <span class="muted">Total Nilai Sewa:</span>
              <span id="totalRentDisplay" style="font-weight: 600;">Rp {{ number_format($room->price, 0, ',', '.') }}</span>
            </div>
            <div id="depositRow" style="display: none; justify-content: space-between; margin-bottom: 6px; font-size: 13px; color: var(--color-accent);">
              <span>Deposit (Uang Muka 50%):</span>
              <span id="depositDisplay" style="font-weight: 700;">Rp 0</span>
            </div>
            <div style="border-top: 1px dashed var(--color-divider); margin: 8px 0; padding-top: 8px; display: flex; justify-content: space-between; font-size: 15px; font-weight: 800;">
              <span id="invoiceLabel">Total Tagihan Awal:</span>
              <span id="totalDisplay" style="color: var(--color-accent);">Rp {{ number_format($room->price, 0, ',', '.') }}</span>
            </div>
            <div id="remainingRow" style="display: none; justify-content: space-between; font-size: 12px; color: var(--color-neutral-600); margin-top: 4px;">
              <span>Sisa Pelunasan Nanti:</span>
              <span id="remainingDisplay">Rp 0</span>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block" style="font-weight: 700; padding: 12px; font-size: 15px;">
            Ajukan Sewa &amp; Terbitkan Tagihan &rarr;
          </button>
        </form>
      </div>
    </div>

  </div>

</main>

<script>
  // Kalkulator dinamis biaya sewa & deposit 50%
  const monthlyPrice = {{ (int)$room->price }};
  const durationSelect = document.getElementById('duration_months');
  const paymentTypeSelect = document.getElementById('payment_type');
  const totalRentDisplay = document.getElementById('totalRentDisplay');
  const depositRow = document.getElementById('depositRow');
  const depositDisplay = document.getElementById('depositDisplay');
  const invoiceLabel = document.getElementById('invoiceLabel');
  const totalDisplay = document.getElementById('totalDisplay');
  const remainingRow = document.getElementById('remainingRow');
  const remainingDisplay = document.getElementById('remainingDisplay');

  function calculateCost() {
    const months = parseInt(durationSelect ? durationSelect.value : 1, 10) || 1;
    const totalRent = monthlyPrice * months;
    const isDeposit = paymentTypeSelect && paymentTypeSelect.value === 'deposit_50';

    if (totalRentDisplay) {
      totalRentDisplay.textContent = 'Rp ' + totalRent.toLocaleString('id-ID');
    }

    if (isDeposit) {
      const deposit = Math.round(totalRent * 0.5);
      const remaining = totalRent - deposit;

      if (depositRow) depositRow.style.display = 'flex';
      if (depositDisplay) depositDisplay.textContent = 'Rp ' + deposit.toLocaleString('id-ID');
      if (invoiceLabel) invoiceLabel.textContent = 'Tagihan Invoice Awal (DP 50%):';
      if (totalDisplay) totalDisplay.textContent = 'Rp ' + deposit.toLocaleString('id-ID');
      if (remainingRow) remainingRow.style.display = 'flex';
      if (remainingDisplay) remainingDisplay.textContent = 'Rp ' + remaining.toLocaleString('id-ID');
    } else {
      if (depositRow) depositRow.style.display = 'none';
      if (invoiceLabel) invoiceLabel.textContent = 'Total Tagihan Penuh (100%):';
      if (totalDisplay) totalDisplay.textContent = 'Rp ' + totalRent.toLocaleString('id-ID');
      if (remainingRow) remainingRow.style.display = 'none';
    }
  }

  if (durationSelect) durationSelect.addEventListener('change', calculateCost);
  if (paymentTypeSelect) paymentTypeSelect.addEventListener('change', calculateCost);
  calculateCost();
</script>
@endsection
