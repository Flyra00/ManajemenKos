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
            <span class="current">Edit Laporan #{{ $maintenance->id }}</span>
          </nav>
          <h2 class="page-title">Edit Laporan Perbaikan</h2>
          <p class="page-sub">Perbarui status pengerjaan teknisi, biaya realisasi, atau foto perbaikan.</p>
        </div>
        <div class="flex head-actions">
          <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
      </section>

      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <form method="POST" action="{{ route('maintenance.update', $maintenance) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-layout">

          <!-- ============ FORM UTAMA ============ -->
          <div class="card elev-sm form-card">
            <h3 class="card-title" style="margin-bottom: 16px;">Edit Laporan: {{ $maintenance->title }}</h3>

            <div class="field">
              <label for="title">Judul Laporan / Kerusakan <span class="text-accent">*</span></label>
              <input class="input @error('title') is-invalid @enderror" type="text" id="title" name="title" value="{{ old('title', $maintenance->title) }}" required>
              @error('title')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <h3 class="form-section-title">Lokasi & Pelapor</h3>

            @if(auth()->user() && auth()->user()->hasRole('tenant'))
              <input type="hidden" name="tenant_id" value="{{ $maintenance->tenant_id }}">
              <div class="grid-2">
                <div class="field">
                  <label for="room_id">Kamar yang Dihuni <span class="text-accent">*</span></label>
                  <select class="input @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                    @foreach($rooms as $room)
                      <option value="{{ $room->id }}" @selected(old('room_id', $maintenance->room_id) == $room->id)>
                        Kamar {{ $room->room_number }} (Lantai {{ $room->floor }})
                      </option>
                    @endforeach
                  </select>
                  @error('room_id')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
                <div class="field">
                  <label for="priority">Prioritas <span class="text-accent">*</span></label>
                  <select class="input @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                    <option value="high" @selected(old('priority', $maintenance->priority) === 'high')>Tinggi (Darurat / Mendesak)</option>
                    <option value="medium" @selected(old('priority', $maintenance->priority) === 'medium')>Sedang (Biasa)</option>
                    <option value="low" @selected(old('priority', $maintenance->priority) === 'low')>Rendah (Dapat Ditunda)</option>
                  </select>
                  @error('priority')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            @else
              <div class="grid-2">
                <div class="field">
                  <label for="room_id">Kamar / Lokasi Kerusakan <span class="text-accent">*</span></label>
                  <select class="input @error('room_id') is-invalid @enderror" id="room_id" name="room_id" required>
                    @foreach($rooms as $room)
                      <option value="{{ $room->id }}" @selected(old('room_id', $maintenance->room_id) == $room->id)>
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
                    @foreach($tenants as $tenant)
                      <option value="{{ $tenant->id }}" @selected(old('tenant_id', $maintenance->tenant_id) == $tenant->id)>
                        {{ $tenant->user->name ?? 'Penghuni #' . $tenant->id }} ({{ $tenant->ktp_number }})
                      </option>
                    @endforeach
                  </select>
                  @error('tenant_id')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <h3 class="form-section-title">Status & Penanganan</h3>

              <div class="grid-3">
                <div class="field">
                  <label for="priority">Prioritas <span class="text-accent">*</span></label>
                  <select class="input @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                    <option value="high" @selected(old('priority', $maintenance->priority) === 'high')>Tinggi (Darurat / Mendesak)</option>
                    <option value="medium" @selected(old('priority', $maintenance->priority) === 'medium')>Sedang (Biasa)</option>
                    <option value="low" @selected(old('priority', $maintenance->priority) === 'low')>Rendah (Dapat Ditunda)</option>
                  </select>
                  @error('priority')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="status">Status Penanganan <span class="text-accent">*</span></label>
                  <select class="input @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="reported" @selected(old('status', $maintenance->status) === 'reported')>Baru (Reported)</option>
                    <option value="in_progress" @selected(old('status', $maintenance->status) === 'in_progress')>Sedang Diproses (In Progress)</option>
                    <option value="completed" @selected(old('status', $maintenance->status) === 'completed')>Selesai (Completed)</option>
                    <option value="cancelled" @selected(old('status', $maintenance->status) === 'cancelled')>Dibatalkan</option>
                  </select>
                  @error('status')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="cost">Biaya Perbaikan (Rp)</label>
                  <input class="input @error('cost') is-invalid @enderror" type="number" id="cost" name="cost" value="{{ old('cost', $maintenance->cost) }}" min="0" step="5000">
                  @error('cost')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <div class="grid-3">
                <div class="field">
                  <label for="handled_by">Ditangani Oleh (Teknisi / Pengelola)</label>
                  <select class="input @error('handled_by') is-invalid @enderror" id="handled_by" name="handled_by">
                    <option value="">Belum Ditugaskan</option>
                    @foreach($users as $user)
                      <option value="{{ $user->id }}" @selected(old('handled_by', $maintenance->handled_by) == $user->id)>
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
                  <input class="input @error('reported_at') is-invalid @enderror" type="date" id="reported_at" name="reported_at" value="{{ old('reported_at', $maintenance->reported_at ? $maintenance->reported_at->format('Y-m-d') : '') }}">
                  @error('reported_at')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>

                <div class="field">
                  <label for="resolved_at">Tanggal Selesai</label>
                  <input class="input @error('resolved_at') is-invalid @enderror" type="date" id="resolved_at" name="resolved_at" value="{{ old('resolved_at', $maintenance->resolved_at ? $maintenance->resolved_at->format('Y-m-d') : '') }}">
                  @error('resolved_at')
                    <p class="form-error">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            @endif

            <h3 class="form-section-title">Deskripsi Kerusakan</h3>

            <div class="field">
              <label for="description">Deskripsi Kerusakan <span class="text-accent">*</span></label>
              <textarea class="input @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description', $maintenance->description) }}</textarea>
              @error('description')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

          </div>

          <!-- ============ SIDE PANEL FOTO KERUSAKAN ============ -->
          <div class="card elev-sm form-card photo-card">
            <h3 class="card-title">Foto Kerusakan</h3>

            <div class="photo-preview" id="maintPhotoPreview" data-empty="{{ $maintenance->image_path ? '0' : '1' }}">
              @if($maintenance->image_path)
                <img id="currentPhotoImg" src="{{ asset('storage/' . $maintenance->image_path) }}" alt="Foto Kerusakan #{{ $maintenance->id }}">
              @else
                <span class="photo-placeholder" id="placeholderSpan">
                  <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                  <span>Belum ada foto</span>
                </span>
              @endif
            </div>

            @if($maintenance->image_path)
              <div id="photoStatusBadge" style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: rgba(34, 197, 94, 0.1); color: #16a34a; border-radius: 6px; font-size: 12px; font-weight: 600;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Foto Saat Ini Tersimpan
              </div>
              <p class="small muted" id="photoHelpText" style="margin: 6px 0 0;">Foto sudah tersimpan di server. Anda tidak perlu mengunggah ulang jika tidak ingin mengganti.</p>
            @endif

            <div class="img-upload-actions" style="margin-top: 12px;">
              <label class="btn btn-secondary" for="image_path" role="button" tabindex="0" title="Pilih foto pengganti">
                {{ $maintenance->image_path ? 'Ganti Foto' : 'Pilih Foto' }}
              </label>
              <button type="button" class="btn btn-ghost" id="maintPhotoReset" style="display: none;">Batal Ganti</button>
            </div>
            <input type="file" id="image_path" name="image_path" accept="image/jpeg,image/png,image/webp" hidden>
            <p class="small muted" style="margin: 8px 0 0;">Format: JPG, PNG, atau WebP (maks 2MB).</p>
            <p class="form-error" id="maintPhotoError" hidden>Format foto harus JPG, PNG, atau WebP.</p>
            @error('image_path')
              <p class="form-error">{{ $message }}</p>
            @enderror

            @if($maintenance->image_path)
              <div style="margin-top: 16px; padding-top: 12px; border-top: 1px dashed var(--color-divider);">
                <label class="chk" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #dc2626; cursor: pointer;">
                  <input type="checkbox" name="remove_image" id="remove_image" value="1">
                  <span>Hapus foto saat ini</span>
                </label>
              </div>
            @endif
          </div>

        </div>

        <div class="form-actions" style="margin-top: 20px;">
          <button type="submit" class="btn btn-primary">Perbarui Laporan</button>
          <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">Batal</a>
        </div>

      </form>

    </main>

    <script>
      (function () {
        var input = document.getElementById('image_path');
        var preview = document.getElementById('maintPhotoPreview');
        var resetBtn = document.getElementById('maintPhotoReset');
        var removeChk = document.getElementById('remove_image');
        var errMsg = document.getElementById('maintPhotoError');
        var badge = document.getElementById('photoStatusBadge');
        var helpText = document.getElementById('photoHelpText');

        if (!input || !preview) return;

        var originalHTML = preview.innerHTML;
        var originalEmpty = preview.dataset.empty;

        function restoreOriginal() {
          input.value = '';
          preview.innerHTML = originalHTML;
          preview.dataset.empty = originalEmpty;
          preview.style.opacity = '1';
          if (resetBtn) resetBtn.style.display = 'none';
          if (badge) {
            badge.style.display = 'inline-flex';
            badge.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Foto Saat Ini Tersimpan';
            badge.style.color = '#16a34a';
            badge.style.background = 'rgba(34, 197, 94, 0.1)';
          }
          if (helpText) helpText.textContent = 'Foto sudah tersimpan di server. Anda tidak perlu mengunggah ulang jika tidak ingin mengganti.';
        }

        input.addEventListener('change', function () {
          var file = input.files && input.files[0];
          if (!file) return;

          if (errMsg) errMsg.hidden = true;

          if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
            input.value = '';
            restoreOriginal();
            if (errMsg) errMsg.hidden = false;
            return;
          }

          if (removeChk) {
            removeChk.checked = false;
          }

          preview.dataset.empty = '0';
          preview.style.opacity = '1';
          preview.innerHTML = '<img src="' + URL.createObjectURL(file) + '" alt="Pratinjau foto baru">';

          if (resetBtn) resetBtn.style.display = 'inline-flex';

          if (badge) {
            badge.style.display = 'inline-flex';
            badge.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Foto Baru Dipilih (Belum Disimpan)';
            badge.style.color = '#ca8a04';
            badge.style.background = 'rgba(234, 179, 8, 0.1)';
          }
          if (helpText) helpText.textContent = 'Klik "Perbarui Laporan" di bawah untuk menyimpan perubahan foto ini ke server.';
        });

        if (resetBtn) {
          resetBtn.addEventListener('click', function () {
            restoreOriginal();
          });
        }

        if (removeChk) {
          removeChk.addEventListener('change', function () {
            if (removeChk.checked) {
              input.value = '';
              if (resetBtn) resetBtn.style.display = 'none';
              preview.style.opacity = '0.35';
              if (badge) {
                badge.style.display = 'inline-flex';
                badge.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Foto Akan Dihapus';
                badge.style.color = '#dc2626';
                badge.style.background = 'rgba(239, 68, 68, 0.1)';
              }
              if (helpText) helpText.textContent = 'Foto ini akan dihapus dari server saat Anda mengklik "Perbarui Laporan".';
            } else {
              restoreOriginal();
            }
          });
        }
      })();
    </script>
  </main>
@endsection
