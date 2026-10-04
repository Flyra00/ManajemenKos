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
            <span class="current">Tambah Kontrak</span>
          </nav>
          <h2 class="page-title">Tambah Kontrak Sewa</h2>
          <p class="page-sub">Buat perjanjian sewa baru antara penghuni dan kamar kos.</p>
        </div>
        <div class="flex head-actions" style="gap: 8px;">
          <a href="{{ route('leases.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" form="lease-form" class="btn btn-primary">Simpan Kontrak</button>
        </div>
      </section>

      {{-- Ringkasan error validasi --}}
      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <form id="lease-form" method="POST" action="{{ route('leases.store') }}" novalidate>
        @csrf

        <div class="form-layout">

          <!-- ============ FORM UTAMA ============ -->
          <div class="card elev-sm form-card">

            <div class="grid-2">
              <div class="field">
                <label for="tenant_id">Penghuni <span class="text-accent">*</span></label>
                <select class="input @error('tenant_id') is-invalid @enderror" id="tenant_id" name="tenant_id" required>
                  <option value="">Pilih Penghuni…</option>
                  @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" @selected(old('tenant_id', $selectedTenantId ?? null) == $tenant->id)>
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
                  <option value="">Pilih Kamar…</option>
                  @foreach($rooms as $room)
                    <option value="{{ $room->id }}" data-price="{{ $room->price }}" @selected(old('room_id', $selectedRoomId ?? null) == $room->id)>
                      Kamar {{ $room->room_number }} (Lantai {{ $room->floor }}) — Rp {{ number_format($room->price, 0, ',', '.') }}/bln {{ $room->status === 'available' ? '[Kosong]' : '[' . ucfirst($room->status) . ']' }}
                    </option>
                  @endforeach
                </select>
                @error('room_id')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Periode & Biaya Sewa</h3>

            <div class="grid-2">
              <div class="field">
                <label for="start_date">Tanggal Mulai Sewa <span class="text-accent">*</span></label>
                <input class="input @error('start_date') is-invalid @enderror" type="date" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                @error('start_date')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="end_date">Tanggal Berakhir Sewa</label>
                <input class="input @error('end_date') is-invalid @enderror" type="date" id="end_date" name="end_date" value="{{ old('end_date') }}">
                <span class="small muted">Kosongkan jika sewa tanpa batas tetap.</span>
                @error('end_date')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="grid-3">
              <div class="field">
                <label for="monthly_price">Harga Bulanan (Rp) <span class="text-accent">*</span></label>
                <input class="input @error('monthly_price') is-invalid @enderror" type="number" id="monthly_price" name="monthly_price" value="{{ old('monthly_price', isset($selectedRoomId) ? ($rooms->firstWhere('id', $selectedRoomId)?->price ?? '') : '') }}" min="0" step="10000" placeholder="1500000" required>
                @error('monthly_price')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="deposit_amount">Uang Deposit (Rp)</label>
                <input class="input @error('deposit_amount') is-invalid @enderror" type="number" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount', 0) }}" min="0" step="10000" placeholder="0">
                @error('deposit_amount')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="status">Status Kontrak <span class="text-accent">*</span></label>
                <select class="input @error('status') is-invalid @enderror" id="status" name="status" required>
                  <option value="active" @selected(old('status', 'active') === 'active')>Aktif (Langsung Dihuni)</option>
                  <option value="pending" @selected(old('status') === 'pending')>Menunggu Verifikasi (Pending)</option>
                  <option value="completed" @selected(old('status') === 'completed')>Selesai</option>
                  <option value="cancelled" @selected(old('status') === 'cancelled')>Dibatalkan</option>
                </select>
                @error('status')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Catatan Tambahan</h3>

            <div class="field">
              <label for="note">Catatan Kontrak (Opsional)</label>
              <textarea class="input @error('note') is-invalid @enderror" id="note" name="note" rows="3" placeholder="Contoh: Termasuk biaya listrik dan air, pembayaran setiap tanggal 5">{{ old('note') }}</textarea>
              @error('note')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

          </div>

          <!-- ============ SIDE PANEL INFORMASI ============ -->
          <div class="card elev-sm form-card photo-card">
            <h3 class="card-title">Ringkasan & Aturan Sewa</h3>
            <div style="font-size:13px; line-height:1.6; color:var(--color-neutral-700)">
              <p style="margin:0 0 10px">
                Saat kontrak disimpan dengan status <strong>Aktif</strong>, status kamar terkait akan otomatis disinkronkan menjadi <strong>Terisi</strong>.
              </p>
              <p style="margin:0 0 10px">
                Tarif sewa bulanan otomatis terisi mengikuti harga bawaan kamar, namun dapat Anda sesuaikan jika terdapat kesepakatan khusus.
              </p>
            </div>
          </div>

        </div>

        <div class="form-actions" style="margin-top:20px">
          <button type="submit" class="btn btn-primary">Simpan Kontrak</button>
          <a href="{{ route('leases.index') }}" class="btn btn-secondary">Batal</a>
        </div>
      </form>

    </main>

    <script>
      function updateRoomPrice(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        const priceInput = document.getElementById('monthly_price');
        if (price && (!priceInput.value || priceInput.value == '0')) {
          priceInput.value = price;
        }
      }
    </script>
@endsection

