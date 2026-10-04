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
            <span class="current">Edit Invoice {{ $payment->invoice_number }}</span>
          </nav>
          <h2 class="page-title">Edit Tagihan / Pembayaran</h2>
          <p class="page-sub">Perbarui status pelunasan, nominal, tanggal bayar, atau unggah bukti transfer.</p>
        </div>
        <div class="flex head-actions">
          <a href="{{ route('payments.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
      </section>

      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <section class="card elev-sm section-card" aria-label="Form edit tagihan">
        <div class="card-head">
          <h3 class="card-title">Rincian Invoice {{ $payment->invoice_number }}</h3>
        </div>

        <form method="POST" action="{{ route('payments.update', $payment) }}" enctype="multipart/form-data" class="flex flex-col gap-4">
          @csrf
          @method('PUT')

          <div class="grid-2">
            <div class="field">
              <label for="lease_id">Kontrak Sewa (Penghuni & Kamar) <span class="text-accent">*</span></label>
              <select class="input @error('lease_id') is-invalid @enderror" id="lease_id" name="lease_id" required>
                @foreach($leases as $lease)
                  <option value="{{ $lease->id }}" @selected(old('lease_id', $payment->lease_id) == $lease->id)>
                    {{ $lease->tenant->user->name ?? 'Penghuni' }} — Kamar {{ $lease->room->room_number ?? '—' }} (Rp {{ number_format($lease->monthly_price, 0, ',', '.') }}/bln)
                  </option>
                @endforeach
              </select>
              @error('lease_id')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="invoice_number">Nomor Invoice <span class="text-accent">*</span></label>
              <input class="input @error('invoice_number') is-invalid @enderror" type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $payment->invoice_number) }}" required>
              @error('invoice_number')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid-3">
            <div class="field">
              <label for="billing_period">Periode Tagihan <span class="text-accent">*</span></label>
              <input class="input @error('billing_period') is-invalid @enderror" type="date" id="billing_period" name="billing_period" value="{{ old('billing_period', $payment->billing_period ? $payment->billing_period->format('Y-m-d') : '') }}" required>
              @error('billing_period')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="due_date">Tanggal Jatuh Tempo <span class="text-accent">*</span></label>
              <input class="input @error('due_date') is-invalid @enderror" type="date" id="due_date" name="due_date" value="{{ old('due_date', $payment->due_date ? $payment->due_date->format('Y-m-d') : '') }}" required>
              @error('due_date')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="amount">Jumlah Tagihan (Rp) <span class="text-accent">*</span></label>
              <input class="input @error('amount') is-invalid @enderror" type="number" id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" min="0" step="10000" required>
              @error('amount')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid-3">
            <div class="field">
              <label for="payment_method">Metode Pembayaran <span class="text-accent">*</span></label>
              <select class="input @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                <option value="bank_tf" @selected(old('payment_method', $payment->payment_method) === 'bank_tf')>Transfer Bank</option>
                <option value="qris" @selected(old('payment_method', $payment->payment_method) === 'qris')>QRIS</option>
                <option value="e_wallet" @selected(old('payment_method', $payment->payment_method) === 'e_wallet')>E-Wallet (Gopay/OVO/Dana)</option>
                <option value="cash" @selected(old('payment_method', $payment->payment_method) === 'cash')>Tunai</option>
              </select>
              @error('payment_method')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="status">Status Pembayaran <span class="text-accent">*</span></label>
              <select class="input @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="unpaid" @selected(old('status', $payment->status) === 'unpaid')>Belum Dibayar</option>
                <option value="pending" @selected(old('status', $payment->status) === 'pending')>Menunggu Verifikasi</option>
                <option value="paid" @selected(old('status', $payment->status) === 'paid')>Lunas</option>
                <option value="overdue" @selected(old('status', $payment->status) === 'overdue')>Terlambat (Overdue)</option>
              </select>
              @error('status')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="payment_date">Tanggal Bayar</label>
              <input class="input @error('payment_date') is-invalid @enderror" type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '') }}">
              @error('payment_date')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="field">
            <label for="proof_img">Bukti Pembayaran / Transfer</label>
            @if($payment->proof_img)
              <div style="margin-bottom: 0.5rem;">
                <a href="{{ asset('storage/' . $payment->proof_img) }}" target="_blank" class="small text-accent hover:underline flex items-center gap-1">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                  Lihat bukti transfer yang tersimpan saat ini
                </a>
              </div>
            @endif
            <input class="input @error('proof_img') is-invalid @enderror" type="file" id="proof_img" name="proof_img" accept="image/jpeg,image/png,image/webp">
            <span class="text-xs text-neutral-500">Pilih file baru jika ingin mengganti bukti sebelumnya.</span>
            @error('proof_img')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="field">
            <label for="notes">Catatan Tambahan (Opsional)</label>
            <textarea class="input @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes', $payment->notes) }}</textarea>
            @error('notes')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Perbarui Invoice</button>
          </div>

        </form>
      </section>

    </main>
@endsection
