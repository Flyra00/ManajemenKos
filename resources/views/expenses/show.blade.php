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
            <span class="current">Detail Pengeluaran</span>
          </nav>
          <h2 class="page-title">{{ $expense->title }}</h2>
          <p class="page-sub">Rincian catatan pengeluaran operasional kos.</p>
        </div>
        <div class="flex head-actions">
          @if(!auth()->user() || !auth()->user()->hasRole('owner'))
            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary">Edit Pengeluaran</a>
          @endif
          <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Kembali</a>
        </div>

      </section>

      <section class="card elev-sm section-card" aria-label="Rincian pengeluaran">
        <div class="card-head">
          <h3 class="card-title">Informasi Pengeluaran</h3>
        </div>

        <div class="card-body p-4">
          <div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--color-neutral-200);">
            <div class="muted small" style="font-weight: 500;">TOTAL BIAYA:</div>
            <div style="font-size: 28px; font-weight: 700; color: var(--color-neutral-900);">
              Rp {{ number_format($expense->amount, 0, ',', '.') }}
            </div>
          </div>

          <div class="grid-2" style="gap: 20px; margin-bottom: 20px;">
            <div>
              <span class="muted small" style="display:block; margin-bottom:4px">TANGGAL TRANSAKSI</span>
              <strong style="font-size:15px">{{ $expense->expense_date ? $expense->expense_date->translatedFormat('d F Y') : '—' }}</strong>
            </div>
            <div>
              <span class="muted small" style="display:block; margin-bottom:4px">DICATAT OLEH</span>
              <strong style="font-size:15px">{{ $expense->user->name ?? 'Admin' }} ({{ $expense->user->email ?? '—' }})</strong>
            </div>
          </div>

          <div style="margin-bottom: 20px;">
            <span class="muted small" style="display:block; margin-bottom:4px">KETERANGAN / RINCIAN BIAYA</span>
            <div style="background: var(--color-neutral-50, #f9f9f9); padding: 14px 16px; border: 1px solid var(--color-neutral-200); line-height: 1.6; font-size: 14px; white-space: pre-wrap;">{{ $expense->description ?: 'Tidak ada keterangan tambahan.' }}</div>
          </div>

          <div class="grid-2" style="gap: 20px; padding-top: 16px; border-top: 1px solid var(--color-neutral-200); font-size: 13px; color: var(--color-neutral-600);">
            <div>Waktu Input: {{ $expense->created_at ? $expense->created_at->translatedFormat('d M Y, H:i') : '—' }}</div>
            <div>Terakhir Diperbarui: {{ $expense->updated_at ? $expense->updated_at->translatedFormat('d M Y, H:i') : '—' }}</div>
          </div>
        </div>
      </section>

    </main>
@endsection
