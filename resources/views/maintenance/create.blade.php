@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('maintenance.index') }}">Maintenance</a>
            <span class="sep">/</span>
            <span class="current">Buat Laporan</span>
          </nav>
          <h2 class="page-title">Buat Laporan Perbaikan</h2>
          <p class="page-sub">Catat keluhan kerusakan fasilitas kamar atau area kos untuk ditindaklanjuti teknisi.</p>
        </div>
      </section>

      {{-- Ringkasan error validasi --}}
      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <form method="POST" action="{{ route('maintenance.store') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <div class="form-layout">

          <!-- ============ FORM UTAMA ============ -->
          <div class="card elev-sm form-card">

            <div class="field">
              <label for="title">Judul Laporan / Kerusakan <span class="text-accent">*</span></label>
              <input class="input @error('title') is-invalid @enderror" type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Contoh: AC Kamar Bocor / Kran Air Patah" required>
              @error('title')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <h3 class="form-section-title">Lokasi & Pelapor</h3>

            @if(auth()->user() && auth()->user()->hasRole('tenant'))
              <input type="hidden" name="tenant_id" value="{{ $selectedTenantId }}">
              <div class="grid-2">
                <div class="field">
                  <label for="room_id">Kamar yang Dihuni <span class="text-accent">*</span></label>
                  <select class="input @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                    @foreach($rooms as $room)
                      <option value="{{ $room->id }}" @selected(old('room_id', $selectedRoomId) == $room->id)>
                        Kamar {{ $room->room_number }} (Lantai {{ $room->floor }})
                      </option>
                    @endforeach
                  </select>
                  @error('room_id')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
                <div class="field">
                  <label>Pelapor</label>
                  <input class="input" type="text" value="{{ auth()->user()->name }} (Saya)" disabled readonly>
                </div>
              </div>

              <div class="field" style="margin-top: 16px;">
                <label for="priority">Tingkat Urgensi / Prioritas <span class="text-accent">*</span></label>
                <select class="input @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                  <option value="high" @selected(old('priority') === 'high')>Tinggi (Darurat / Mendesak)</option>
                  <option value="medium" @selected(old('priority', 'medium') === 'medium')>Sedang (Biasa)</option>
                  <option value="low" @selected(old('priority') === 'low')>Rendah (Dapat Ditunda)</option>
                </select>
                @error('priority')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            @else
              <div class="grid-2">
                <div class="field">
                  <label for="room_id">Kamar / Lokasi Kerusakan <span class="text-accent">*</span></label>
                  <select class="input @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                    <option value="">Pilih Kamar…</option>
                    @foreach($rooms as $room)
                      <option value="{{ $room->id }}" @selected(old('room_id', $selectedRoomId) == $room->id)>
                        Kamar {{ $room->room_number }} (Lantai {{ $room->floor }})
                      </option>
                    @endforeach
                  </select>
                  @error('room_id')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="tenant_id">Dilaporkan Oleh (Penghuni) <span class="text-accent">*</span></label>
                  <select class="input @error('tenant_id') is-invalid @enderror" id="tenant_id" name="tenant_id" required>
                    <option value="">Pilih Penghuni Pelapor…</option>
                    @foreach($tenants as $tenant)
                      <option value="{{ $tenant->id }}" @selected(old('tenant_id', $selectedTenantId) == $tenant->id)>
                        {{ $tenant->user->name ?? 'Penghuni #' . $tenant->id }} ({{ $tenant->ktp_number }})
                      </option>
                    @endforeach
                  </select>
                  @error('tenant_id')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <h3 class="form-section-title">Penanganan & Biaya</h3>

              <div class="grid-3">
                <div class="field">
                  <label for="priority">Prioritas <span class="text-accent">*</span></label>
                  <select class="input @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                    <option value="high" @selected(old('priority') === 'high')>Tinggi (Darurat / Mendesak)</option>
                    <option value="medium" @selected(old('priority', 'medium') === 'medium')>Sedang (Biasa)</option>
                    <option value="low" @selected(old('priority') === 'low')>Rendah (Dapat Ditunda)</option>
                  </select>
                  @error('priority')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="status">Status Penanganan <span class="text-accent">*</span></label>
                  <select class="input @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="reported" @selected(old('status', 'reported') === 'reported')>Baru (Reported)</option>
                    <option value="in_progress" @selected(old('status') === 'in_progress')>Sedang Diproses (In Progress)</option>
                    <option value="completed" @selected(old('status') === 'completed')>Selesai (Completed)</option>
                    <option value="cancelled" @selected(old('status') === 'cancelled')>Dibatalkan</option>
                  </select>
                  @error('status')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="cost">Estimasi Biaya Perbaikan (Rp)</label>
                  <input class="input @error('cost') is-invalid @enderror" type="number" id="cost" name="cost" value="{{ old('cost', 0) }}" min="0" step="5000">
                  @error('cost')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <div class="grid-3">
                <div class="field">
                  <label for="handled_by">Ditangani Oleh (Teknisi)</label>
                  <select class="input @error('handled_by') is-invalid @enderror" id="handled_by" name="handled_by">
                    <option value="">Belum Ditugaskan</option>
                    @foreach($users as $user)
                      <option value="{{ $user->id }}" @selected(old('handled_by') == $user->id)>
                        {{ $user->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('handled_by')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="reported_at">Tanggal Dilaporkan</label>
                  <input class="input @error('reported_at') is-invalid @enderror" type="date" id="reported_at" name="reported_at" value="{{ old('reported_at', date('Y-m-d')) }}">
                  @error('reported_at')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="resolved_at">Tanggal Selesai</label>
                  <input class="input @error('resolved_at') is-invalid @enderror" type="date" id="resolved_at" name="resolved_at" value="{{ old('resolved_at') }}">
                  @error('resolved_at')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            @endif

            <h3 class="form-section-title">Deskripsi Kerusakan</h3>

            <div class="field">
              <label for="description">Detail Masalah <span class="text-accent">*</span></label>
              <textarea class="input @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Jelaskan detail bagian yang rusak, penyebab, atau instruksi perbaikan…" required>{{ old('description') }}</textarea>
              @error('description')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

          </div>

          <!-- ============ SIDE PANEL FOTO KERUSAKAN ============ -->
          <div class="card elev-sm form-card photo-card">
            <h3 class="card-title">Foto Kerusakan</h3>

            <div class="photo-preview" id="maintPhotoPreview" data-empty="1">
              <span class="photo-placeholder">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <span>Belum ada foto</span>
              </span>
            </div>

            <div class="img-upload-actions" style="margin-top:12px">
              <label class="btn btn-secondary" for="image_path" role="button" tabindex="0" title="Pilih foto kerusakan">Pilih Gambar</label>
              <button type="button" class="btn btn-ghost" id="maintPhotoRemove" hidden>Hapus Foto</button>
            </div>
            <input type="file" id="image_path" name="image_path" accept="image/jpeg,image/png,image/webp" hidden>
            <p class="small muted" style="margin:8px 0 0">JPG, PNG, atau WebP (maks 2MB). Foto bukti kerusakan atau bagian yang perlu diperbaiki.</p>
            <p class="form-error" id="maintPhotoError" hidden>Format foto harus JPG, PNG, atau WebP.</p>
            @error('image_path')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

        </div>

        <div class="form-actions" style="margin-top:20px">
          <button type="submit" class="btn btn-primary">Simpan Laporan</button>
          <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">Batal</a>
        </div>
      </form>

    </main>

    <script>
      (function () {
        var input = document.getElementById('image_path');
        var preview = document.getElementById('maintPhotoPreview');
        var removeBtn = document.getElementById('maintPhotoRemove');
        var errMsg = document.getElementById('maintPhotoError');
        if (!input || !preview) return;

        function setPreview(src) {
          if (!src) {
            preview.dataset.empty = '1';
            preview.innerHTML = '<span class="photo-placeholder">' +
              '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>' +
              '<span>Belum ada foto</span></span>';
            if (removeBtn) removeBtn.hidden = true;
            return;
          }
          preview.dataset.empty = '0';
          preview.innerHTML = '<img src="' + src + '" alt="Pratinjau foto kerusakan">';
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

