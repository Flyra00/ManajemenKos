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
            <span class="current">Buat Tagihan</span>
          </nav>
          <h2 class="page-title">Buat Tagihan / Pembayaran</h2>
          <p class="page-sub">Terbitkan invoice tagihan sewa baru atau catat pembayaran penghuni kos.</p>
        </div>
      </section>

      {{-- Ringkasan error validasi --}}
      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <div class="form-layout">

          <!-- ============ FORM UTAMA ============ -->
          <div class="card elev-sm form-card">

            <div class="grid-2">
              <div class="field">
                <label for="lease_id">Kontrak Sewa (Penghuni & Kamar) <span class="text-accent">*</span></label>
                <select class="input @error('lease_id') is-invalid @enderror" id="lease_id" name="lease_id" onchange="updatePaymentAmount(this)" required>
                  <option value="">Pilih Kontrak Sewa…</option>
                  @foreach($leases as $lease)
                    @php
                      $monthlyPrice = (float) $lease->monthly_price;
                      $deposit = (float) $lease->deposit_amount;
                      $paid = (float) $lease->total_paid;
                      $remaining = (float) $lease->remaining_due;
                    @endphp
                    <option value="{{ $lease->id }}"
                            data-monthly="{{ $monthlyPrice }}"
                            data-deposit="{{ $deposit }}"
                            data-paid="{{ $paid }}"
                            data-remaining="{{ $remaining }}"
                            @selected(old('lease_id', $selectedLeaseId) == $lease->id)>
                      {{ $lease->tenant->user->name ?? 'Penghuni' }} — Kamar {{ $lease->room->room_number ?? '—' }} (Rp {{ number_format($monthlyPrice, 0, ',', '.') }}/bln)
                      @if($deposit > 0)
                        [Ada DP: Rp {{ number_format($deposit, 0, ',', '.') }}]
                      @endif
                    </option>
                  @endforeach
                </select>
                @error('lease_id')
                  <p class="form-error">{{ $message }}</p>
                @enderror

                <!-- Kotak Sinkronisasi Biaya & Deposit -->
                <div id="leaseCostInfoBox" style="display: none; margin-top: 10px; padding: 12px 14px; background: var(--color-neutral-50); border: 1px solid var(--color-divider); border-radius: 6px; font-size: 13px;">
                  <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span class="muted">Tarif Sewa Bulanan:</span>
                    <strong id="infoMonthlyPrice">Rp 0</strong>
                  </div>
                  <div id="infoDepositRow" style="display: none; justify-content: space-between; margin-bottom: 4px; color: var(--color-accent);">
                    <span>Uang Muka (Deposit):</span>
                    <strong id="infoDepositAmount">Rp 0</strong>
                  </div>
                  <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span class="muted">Total Sudah Dibayar:</span>
                    <strong id="infoTotalPaid">Rp 0</strong>
                  </div>
                  <div style="display: flex; justify-content: space-between; padding-top: 6px; border-top: 1px solid var(--color-divider); font-weight: 800;">
                    <span>Sisa Tagihan yang Harus Ditagih:</span>
                    <span id="infoRemainingDue" style="color: var(--color-accent);">Rp 0</span>
                  </div>
                  <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-secondary btn-sm" id="btnSetRemaining" style="font-size: 11px;">Gunakan Sisa Tagihan</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="btnSetMonthly" style="font-size: 11px;">Gunakan Sewa Penuh</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="btnSetDeposit" style="font-size: 11px; display: none;">Gunakan Nominal Deposit</button>
                  </div>
                </div>
              </div>


              <div class="field">
                <label for="invoice_number">Nomor Invoice <span class="text-accent">*</span></label>
                <input class="input @error('invoice_number') is-invalid @enderror" type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $suggestedInvoice) }}" required>
                @error('invoice_number')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Periode & Nominal Tagihan</h3>

            <div class="grid-3">
              <div class="field">
                <label for="billing_period">Periode Tagihan <span class="text-accent">*</span></label>
                <input class="input @error('billing_period') is-invalid @enderror" type="date" id="billing_period" name="billing_period" value="{{ old('billing_period', date('Y-m-01')) }}" required>
                @error('billing_period')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="due_date">Tanggal Jatuh Tempo <span class="text-accent">*</span></label>
                <input class="input @error('due_date') is-invalid @enderror" type="date" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-10')) }}" required>
                @error('due_date')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="amount">Jumlah Tagihan (Rp) <span class="text-accent">*</span></label>
                <input class="input @error('amount') is-invalid @enderror" type="number" id="amount" name="amount" value="{{ old('amount') }}" min="0" step="10000" placeholder="1500000" required>
                @error('amount')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Pembayaran & Status</h3>

            <div class="grid-3">
              <div class="field">
                <label for="payment_method">Metode Pembayaran <span class="text-accent">*</span></label>
                <select class="input @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                  <option value="bank_tf" @selected(old('payment_method') === 'bank_tf')>Transfer Bank</option>
                  <option value="qris" @selected(old('payment_method') === 'qris')>QRIS</option>
                  <option value="e_wallet" @selected(old('payment_method') === 'e_wallet')>E-Wallet (Gopay/OVO/Dana)</option>
                  <option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option>
                </select>
                @error('payment_method')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="status">Status Pembayaran <span class="text-accent">*</span></label>
                <select class="input @error('status') is-invalid @enderror" id="status" name="status" required>
                  <option value="unpaid" @selected(old('status', 'unpaid') === 'unpaid')>Belum Dibayar</option>
                  <option value="pending" @selected(old('status') === 'pending')>Menunggu Verifikasi</option>
                  <option value="paid" @selected(old('status') === 'paid')>Lunas</option>
                  <option value="overdue" @selected(old('status') === 'overdue')>Terlambat (Overdue)</option>
                </select>
                @error('status')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="payment_date">Tanggal Bayar</label>
                <input class="input @error('payment_date') is-invalid @enderror" type="date" id="payment_date" name="payment_date" value="{{ old('payment_date') }}">
                <span class="small muted">Otomatis hari ini jika status Lunas.</span>
                @error('payment_date')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Catatan Tambahan</h3>

            <div class="field">
              <label for="notes">Catatan Pembayaran (Opsional)</label>
              <textarea class="input @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Catatan transaksi, nama pemilik rekening pengirim, atau keterangan lain…">{{ old('notes') }}</textarea>
              @error('notes')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

          </div>

          <!-- ============ SIDE PANEL BUKTI PEMBAYARAN ============ -->
          <div class="card elev-sm form-card photo-card">
            <h3 class="card-title">Bukti Transfer</h3>

            <div class="photo-preview" id="proofPreview" data-empty="1">
              <span class="photo-placeholder">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                <span>Belum ada bukti</span>
              </span>
            </div>

            <div class="img-upload-actions" style="margin-top:12px">
              <label class="btn btn-secondary" for="proof_img" role="button" tabindex="0" title="Pilih bukti transfer">Pilih Gambar</label>
              <button type="button" class="btn btn-ghost" id="proofRemove" hidden>Hapus</button>
            </div>
            <input type="file" id="proof_img" name="proof_img" accept="image/jpeg,image/png,image/webp" hidden>
            <p class="small muted" style="margin:8px 0 0">JPG, PNG, atau WebP (maks 2MB). Bukti transfer/struk pembayaran.</p>
            <p class="form-error" id="proofError" hidden>Format bukti harus JPG, PNG, atau WebP.</p>
            @error('proof_img')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

        </div>

        <div class="form-actions" style="margin-top:20px">
          <button type="submit" class="btn btn-primary">Terbitkan Invoice</button>
          <a href="{{ route('payments.index') }}" class="btn btn-secondary">Batal</a>
        </div>
      </form>

    </main>

    <script>
      function updatePaymentAmount(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
          document.getElementById('leaseCostInfoBox').style.display = 'none';
          return;
        }

        const monthly = parseFloat(selectedOption.getAttribute('data-monthly')) || 0;
        const deposit = parseFloat(selectedOption.getAttribute('data-deposit')) || 0;
        const paid = parseFloat(selectedOption.getAttribute('data-paid')) || 0;
        const remaining = parseFloat(selectedOption.getAttribute('data-remaining')) || 0;

        const infoBox = document.getElementById('leaseCostInfoBox');
        const infoMonthlyPrice = document.getElementById('infoMonthlyPrice');
        const infoDepositRow = document.getElementById('infoDepositRow');
        const infoDepositAmount = document.getElementById('infoDepositAmount');
        const infoTotalPaid = document.getElementById('infoTotalPaid');
        const infoRemainingDue = document.getElementById('infoRemainingDue');
        const btnSetDeposit = document.getElementById('btnSetDeposit');
        const amountInput = document.getElementById('amount');

        infoBox.style.display = 'block';
        infoMonthlyPrice.textContent = 'Rp ' + monthly.toLocaleString('id-ID');
        infoTotalPaid.textContent = 'Rp ' + paid.toLocaleString('id-ID');
        infoRemainingDue.textContent = 'Rp ' + remaining.toLocaleString('id-ID');

        if (deposit > 0) {
          infoDepositRow.style.display = 'flex';
          infoDepositAmount.textContent = 'Rp ' + deposit.toLocaleString('id-ID');
          btnSetDeposit.style.display = 'inline-flex';
        } else {
          infoDepositRow.style.display = 'none';
          btnSetDeposit.style.display = 'none';
        }

        // Default: Utamakan sisa tagihan jika masih ada sisa, jika tidak gunakan harga bulanan
        const suggestedAmount = remaining > 0 ? remaining : monthly;
        if (!amountInput.value || amountInput.value == '0') {
          amountInput.value = suggestedAmount;
        }

        // Tombol Preset
        document.getElementById('btnSetRemaining').onclick = function() {
          amountInput.value = remaining > 0 ? remaining : monthly;
        };
        document.getElementById('btnSetMonthly').onclick = function() {
          amountInput.value = monthly;
        };
        document.getElementById('btnSetDeposit').onclick = function() {
          amountInput.value = deposit;
        };
      }

      document.addEventListener('DOMContentLoaded', function() {
        const leaseSelect = document.getElementById('lease_id');
        if (leaseSelect && leaseSelect.value) {
          updatePaymentAmount(leaseSelect);
        }
      });


      // Preview bukti transfer sebelum submit
      (function () {
        var input = document.getElementById('proof_img');
        var preview = document.getElementById('proofPreview');
        var removeBtn = document.getElementById('proofRemove');
        var errMsg = document.getElementById('proofError');
        if (!input || !preview) return;

        function setPreview(src) {
          if (!src) {
            preview.dataset.empty = '1';
            preview.innerHTML = '<span class="photo-placeholder">' +
              '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>' +
              '<span>Belum ada bukti</span></span>';
            if (removeBtn) removeBtn.hidden = true;
            return;
          }
          preview.dataset.empty = '0';
          preview.innerHTML = '<img src="' + src + '" alt="Pratinjau bukti transfer">';
          if (removeBtn) removeBtn.hidden = false;
        }

        input.addEventListener('change', function () {
          var file = input.files && input.files[0];
          if (!file) return;
          if (errMsg) errMsg.hidden = true;
          if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
            input.value = '';
            setPreview('');
            if (errMsg) errMsg.hidden = false;
            return;
          }
          setPreview(URL.createObjectURL(file));
        });

        if (removeBtn) {
          removeBtn.addEventListener('click', function () {
            input.value = '';
            setPreview('');
          });
        }
      })();
    </script>
@endsection

