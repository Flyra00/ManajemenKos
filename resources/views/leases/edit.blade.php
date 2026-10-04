@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('leases.index') }}">Kontrak Sewa</a>
            <span class="sep">/</span>
            <span class="current">Edit Kontrak #LS-{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }}</span>
          </nav>
          <h2 class="page-title">Edit Kontrak Sewa</h2>
          <p class="page-sub">Perbarui informasi masa sewa, tarif bulanan, kamar, atau status kontrak.</p>
        </div>
        <div class="flex head-actions">
          <a href="{{ route('leases.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
      </section>

      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <section class="card elev-sm section-card" aria-label="Form edit kontrak">
        <div class="card-head">
          <h3 class="card-title">Informasi Kontrak</h3>
        </div>

        <form method="POST" action="{{ route('leases.update', $lease) }}" class="flex flex-col gap-4">
          @csrf
          @method('PUT')

          <div class="grid-2">
            <div class="field">
              <label for="tenant_id">Penghuni <span class="text-accent">*</span></label>
              <select class="input @error('tenant_id') is-invalid @enderror" id="tenant_id" name="tenant_id" required>
                @foreach($tenants as $tenant)
                  <option value="{{ $tenant->id }}" @selected(old('tenant_id', $lease->tenant_id) == $tenant->id)>
                    {{ $tenant->user->name ?? 'Penghuni #' . $tenant->id }} ({{ $tenant->ktp_number }})
                  </option>
                @endforeach
              </select>
              @error('tenant_id')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="room_id">Kamar Kos <span class="text-accent">*</span></label>
              <select class="input @error('room_id') is-invalid @enderror" id="room_id" name="room_id" onchange="updateRoomPrice(this)" required>
                @foreach($rooms as $room)
                  <option value="{{ $room->id }}" data-price="{{ $room->price }}" @selected(old('room_id', $lease->room_id) == $room->id)>
                    Kamar {{ $room->room_number }} (Lantai {{ $room->floor }}) — Rp {{ number_format($room->price, 0, ',', '.') }}/bln {{ $room->status === 'available' ? '[Kosong]' : '[' . ucfirst($room->status) . ']' }}
                  </option>
                @endforeach
              </select>
              @error('room_id')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid-2">
            <div class="field">
              <label for="start_date">Tanggal Mulai Sewa <span class="text-accent">*</span></label>
              <input class="input @error('start_date') is-invalid @enderror" type="date" id="start_date" name="start_date" value="{{ old('start_date', $lease->start_date ? $lease->start_date->format('Y-m-d') : '') }}" required>
              @error('start_date')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="end_date">Tanggal Berakhir Sewa</label>
              <input class="input @error('end_date') is-invalid @enderror" type="date" id="end_date" name="end_date" value="{{ old('end_date', $lease->end_date ? $lease->end_date->format('Y-m-d') : '') }}">
              <span class="text-xs text-neutral-500">Kosongkan jika sewa tanpa batas waktu tertentu.</span>
              @error('end_date')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid-3">
            <div class="field">
              <label for="monthly_price">Harga Bulanan (Rp) <span class="text-accent">*</span></label>
              <input class="input @error('monthly_price') is-invalid @enderror" type="number" id="monthly_price" name="monthly_price" value="{{ old('monthly_price', $lease->monthly_price) }}" min="0" step="10000" required>
              @error('monthly_price')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="deposit_amount">Uang Deposit (Rp)</label>
              <input class="input @error('deposit_amount') is-invalid @enderror" type="number" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount', $lease->deposit_amount) }}" min="0" step="10000">
              @error('deposit_amount')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="status">Status Kontrak <span class="text-accent">*</span></label>
              <select class="input @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="active" @selected(old('status', $lease->status) === 'active')>Aktif</option>
                <option value="pending" @selected(old('status', $lease->status) === 'pending')>Menunggu Verifikasi (Pending)</option>
                <option value="completed" @selected(old('status', $lease->status) === 'completed')>Selesai</option>
                <option value="cancelled" @selected(old('status', $lease->status) === 'cancelled')>Dibatalkan</option>
              </select>
              @error('status')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="field">
            <label for="note">Catatan Tambahan (Opsional)</label>
            <textarea class="input @error('note') is-invalid @enderror" id="note" name="note" rows="3">{{ old('note', $lease->note) }}</textarea>
            @error('note')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('leases.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Perbarui Kontrak</button>
          </div>

        </form>
      </section>

    </main>

    <script>
      async function updateRoomPrice(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        const priceInput = document.getElementById('monthly_price');
        if (price) {
          if (typeof window.showConfirmDialog === 'function') {
            const ok = await window.showConfirmDialog({
              title: 'Sesuaikan Harga Kamar',
              message: 'Sesuaikan harga bulanan dengan harga standar kamar (Rp ' + Number(price).toLocaleString('id-ID') + ')?',
              confirmText: 'Ya, Sesuaikan',
              cancelText: 'Biarkan',
              confirmColor: 'var(--color-primary, #e11d48)'
            });
            if (ok) {
              priceInput.value = price;
              if (typeof window.showToast === 'function') {
                window.showToast('Harga sewa berhasil disesuaikan dengan harga standar kamar.', 'success');
              }
            }
          } else if (confirm('Sesuaikan harga bulanan dengan harga standar kamar (Rp ' + Number(price).toLocaleString('id-ID') + ')?')) {
            priceInput.value = price;
          }
        }
      }
    </script>
@endsection
