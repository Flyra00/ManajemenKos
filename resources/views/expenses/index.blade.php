@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Pengeluaran</span>
          </nav>
          <h2 class="page-title">Pengeluaran Operasional</h2>
          <p class="page-sub">Kelola dan pantau seluruh pos biaya operasional, utilitas, dan perawatan kos.</p>
        </div>
        @if(!auth()->user() || !auth()->user()->hasRole('owner'))
          <div class="flex head-actions">
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Tambah Pengeluaran
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

      @if(session('error'))
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      <!-- Kartu statistik -->
      <section class="grid-4" aria-label="Statistik pengeluaran">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Pengeluaran</div>
          <div class="stat-value">Rp {{ number_format($stats['total'], 0, ',', '.') }}</div>
          <div class="stat-sub muted">akumulasi semua waktu</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Bulan Ini</div>
          <div class="stat-value">Rp {{ number_format($stats['this_month'], 0, ',', '.') }}</div>
          <div class="stat-sub muted">{{ now()->translatedFormat('F Y') }}</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Pengeluaran Terbesar</div>
          <div class="stat-value">Rp {{ number_format($stats['max'], 0, ',', '.') }}</div>
          <div class="stat-sub muted">pos pengeluaran tertinggi</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Jumlah Catatan</div>
          <div class="stat-value">{{ $stats['count'] }}</div>
          <div class="stat-sub muted">transaksi pengeluaran</div>
        </div>
      </section>

      <!-- Filter + tabel pengeluaran -->
      <section class="card elev-sm section-card" aria-label="Daftar pengeluaran">
        <div class="card-head">
          <h3 class="card-title">Daftar Biaya Operasional</h3>
          <span class="small muted">{{ $expenses->total() }} transaksi</span>
        </div>

        <form method="GET" action="{{ route('expenses.index') }}" class="filter-bar">
          <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari judul, keterangan…" aria-label="Cari pengeluaran">
          <label class="flex items-center" style="gap:6px">
            <span class="small muted">Bulan:</span>
            <input class="input" type="month" name="month" value="{{ request('month') }}" aria-label="Filter bulan pengeluaran">
          </label>
          <button type="submit" class="btn btn-secondary">Terapkan</button>
          @if(request()->hasAny(['search', 'month']))
            <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Reset</a>
          @endif
        </form>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr>
                <th style="width:60px;">No</th>
                <th>Judul Pengeluaran</th>
                <th>Keterangan</th>
                <th>Jumlah (Biaya)</th>
                <th>Tanggal</th>
                <th>Dicatat Oleh</th>
                <th style="width:160px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($expenses as $expense)
                <tr>
                  <td class="muted">{{ $expenses->firstItem() ? $expenses->firstItem() + $loop->index : $loop->iteration }}</td>
                  <td class="font-semibold">{{ $expense->title }}</td>
                  <td class="text-neutral-600">{{ $expense->description ?: '—' }}</td>
                  <td class="font-semibold text-neutral-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                  <td>{{ $expense->expense_date ? $expense->expense_date->translatedFormat('d M Y') : '—' }}</td>
                  <td>{{ $expense->user->name ?? '—' }}</td>
                  <td>
                    <div class="flex gap-1">
                      <a href="{{ route('expenses.show', $expense) }}" class="btn btn-ghost btn-sm" title="Lihat detail">
                        Detail
                      </a>
                      @if(!auth()->user() || !auth()->user()->hasRole('owner'))
                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-secondary btn-sm" title="Edit pengeluaran">
                          Edit
                        </a>
                        <button type="button" class="btn btn-ghost btn-sm text-accent"
                                onclick="openDeleteExpenseModal('{{ route('expenses.destroy', $expense) }}', '{{ $expense->title }}', '{{ number_format($expense->amount, 0, ',', '.') }}')">
                          Hapus
                        </button>
                      @endif
                    </div>

                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-6 text-neutral-500">
                    Tidak ada catatan pengeluaran yang ditemukan.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($expenses->hasPages())
          <div class="card-footer flex justify-between items-center py-3 px-4 border-t border-neutral-200">
            <span class="text-sm text-neutral-600">
              Menampilkan {{ $expenses->firstItem() }} - {{ $expenses->lastItem() }} dari {{ $expenses->total() }} transaksi
            </span>
            <div>
              {{ $expenses->links() }}
            </div>
          </div>
        @endif
      </section>

    </main>

    <!-- Modal Konfirmasi Hapus Pengeluaran -->
    <div class="dialog-backdrop" id="deleteExpenseModal" style="display:none; align-items:center; justify-content:center;" data-close="1">
      <div class="dialog" style="margin: auto;">
        <div class="dialog-title">Konfirmasi Hapus Pengeluaran</div>
        <p class="small muted" style="margin:8px 0 16px;line-height:1.5">
          Apakah Anda yakin ingin menghapus catatan pengeluaran <strong id="deleteExpenseTitle"></strong> sebesar <strong id="deleteExpenseAmount"></strong>? Tindakan ini tidak dapat dibatalkan.
        </p>
        <form id="deleteExpenseForm" method="POST" action="">
          @csrf
          @method('DELETE')
          <div class="dialog-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteExpenseModal()">Batal</button>
            <button type="submit" class="btn btn-primary" style="background-color: var(--color-accent, #e53e3e); border-color: var(--color-accent, #e53e3e);">
              Ya, Hapus
            </button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function openDeleteExpenseModal(actionUrl, title, amount) {
        const modal = document.getElementById('deleteExpenseModal');
        const form = document.getElementById('deleteExpenseForm');
        const titleSpan = document.getElementById('deleteExpenseTitle');
        const amountSpan = document.getElementById('deleteExpenseAmount');

        form.action = actionUrl;
        titleSpan.textContent = title;
        amountSpan.textContent = 'Rp ' + amount;
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
      }

      function closeDeleteExpenseModal() {
        const modal = document.getElementById('deleteExpenseModal');
        modal.style.display = 'none';
      }

      window.addEventListener('click', function(e) {
        const modal = document.getElementById('deleteExpenseModal');
        if (e.target === modal) {
          closeDeleteExpenseModal();
        }
      });
    </script>
@endsection

