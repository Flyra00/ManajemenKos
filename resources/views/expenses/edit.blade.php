@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('expenses.index') }}">Pengeluaran</a>
            <span class="sep">/</span>
            <span class="current">Edit Pengeluaran</span>
          </nav>
          <h2 class="page-title">Edit Catatan Pengeluaran</h2>
          <p class="page-sub">Perbarui informasi data transaksi pengeluaran operasional.</p>
        </div>
      </section>

      {{-- Ringkasan error validasi --}}
      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <form method="POST" action="{{ route('expenses.update', $expense) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="form-layout">

          <!-- ============ FORM UTAMA ============ -->
          <div class="card elev-sm form-card">

            <div class="field">
              <label for="title">Judul Pengeluaran <span class="text-accent">*</span></label>
              <input class="input @error('title') is-invalid @enderror" type="text" id="title" name="title" value="{{ old('title', $expense->title) }}" placeholder="Contoh: Tagihan Listrik PLN Bulan September" required>
              @error('title')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <h3 class="form-section-title">Rincian Biaya & Waktu</h3>

            <div class="grid-2">
              <div class="field">
                <label for="amount">Jumlah Biaya (Rp) <span class="text-accent">*</span></label>
                <input class="input @error('amount') is-invalid @enderror" type="number" id="amount" name="amount" value="{{ old('amount', (int)$expense->amount) }}" min="0" step="1000" placeholder="500000" required>
                @error('amount')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="expense_date">Tanggal Pengeluaran <span class="text-accent">*</span></label>
                <input class="input @error('expense_date') is-invalid @enderror" type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d')) }}" required>
                @error('expense_date')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Keterangan Tambahan</h3>

            <div class="field">
              <label for="description">Keterangan / Rincian Biaya (Opsional)</label>
              <textarea class="input @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Rincian pembelian, nomor token listrik, vendor, atau catatan pendukung lainnya…">{{ old('description', $expense->description) }}</textarea>
              @error('description')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

          </div>

          <!-- ============ SIDE PANEL INFORMASI ============ -->
          <div class="card elev-sm form-card photo-card">
            <h3 class="card-title">Informasi Catatan</h3>
            <div style="font-size:13px; line-height:1.6; color:var(--color-neutral-700)">
              <p style="margin:0 0 10px">
                Dicatat pertama kali oleh: <strong>{{ $expense->user->name ?? 'Admin' }}</strong>
              </p>
              <p style="margin:0 0 10px">
                Dibuat pada: {{ $expense->created_at ? $expense->created_at->translatedFormat('d F Y, H:i') : '—' }}
              </p>
              <p style="margin:0">
                Terakhir diubah: {{ $expense->updated_at ? $expense->updated_at->translatedFormat('d F Y, H:i') : '—' }}
              </p>
            </div>
          </div>

        </div>

        <div class="form-actions" style="margin-top:20px">
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Batal</a>
        </div>
      </form>

    </main>
@endsection
