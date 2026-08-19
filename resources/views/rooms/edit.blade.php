@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('rooms.index') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('rooms.index') }}">Master Kamar</a>
            <span class="sep">/</span>
            <span class="current">Edit Kamar</span>
          </nav>
          <h2 class="page-title">Edit Kamar {{ $room->room_number }}</h2>
          <p class="page-sub">Perbarui data kamar kos, harga, status, fasilitas, dan foto kamar.</p>
        </div>
      </section>

      {{-- Ringkasan error validasi (Laravel Validation) --}}
      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada form. Periksa kembali isian di bawah.</span>
        </div>
      @endif

      <form method="POST" action="{{ route('rooms.update', $room) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PATCH')

        <div class="form-layout">

          <!-- ============ FORM UTAMA ============ -->
          <div class="card elev-sm form-card">

            <div class="field">
              <label for="room_number">Nomor Kamar</label>
              <input class="input @error('room_number') is-invalid @enderror" type="text" id="room_number"
                     name="room_number" value="{{ old('room_number', $room->room_number) }}"
                     placeholder="contoh: A-01" maxlength="10" required>
              @error('room_number')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="grid-2">
              <div class="field">
                <label for="floor">Lantai</label>
                <select class="input @error('floor') is-invalid @enderror" id="floor" name="floor">
                  <option value="">Pilih lantai</option>
                  @for($i = 1; $i <= 3; $i++)
                    <option value="{{ $i }}" @selected((string) old('floor', $room->floor) === (string) $i)>Lantai {{ $i }}</option>
                  @endfor
                </select>
                @error('floor')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="price">Harga per Bulan (Rp)</label>
                <input class="input @error('price') is-invalid @enderror" type="number" id="price"
                       name="price" value="{{ old('price', $room->price) }}"
                       min="0" step="50000" required>
                @error('price')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="status">Status</label>
                <select class="input @error('status') is-invalid @enderror" id="status" name="status">
                  <option value="occupied" @selected(old('status', $room->status) === 'occupied')>Kosong</option>
                  <option value="available" @selected(old('status', $room->status) === 'available')>Terisi</option>
                  <option value="maintenance" @selected(old('status', $room->status) === 'maintenance')>Perbaikan</option>
                </select>
                @error('status')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="is_active">Status Aktif</label>
                <select class="input @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                  <option value="1" @selected((string) old('is_active', $room->is_active ? '1' : '0') !== '0')>Aktif</option>
                  <option value="0" @selected(old('is_active', $room->is_active ? '1' : '0') === '0')>Nonaktif</option>
                </select>
                @error('is_active')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Fasilitas</h3>
            <div class="field">
              <div class="chk-grid">
                {{-- Fasilitas existing diberi checked via $room->facilities->contains() --}}
                @forelse($facilities as $facility)
                  <label class="chk">
                    <input type="checkbox" name="facilities[]" value="{{ $facility->id }}"
                           @checked(in_array($facility->id, old('facilities', $room->facilities->pluck('id')->all())))>
                    <span>{{ $facility->name }}</span>
                  </label>
                @empty
                  <p class="small muted" style="margin:0">Belum ada fasilitas. Tambahkan lewat menu Fasilitas terlebih dahulu.</p>
                @endforelse
              </div>
              <p class="small muted" style="margin:6px 0 0">Pilih fasilitas yang tersedia di kamar ini (relasi room_facilities).</p>
              @error('facilities')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <h3 class="form-section-title">Deskripsi</h3>
            <div class="field">
              <label for="description">Deskripsi</label>
              <textarea class="input @error('description') is-invalid @enderror" id="description" name="description"
                        rows="3" placeholder="Deskripsi kamar, kondisi, atau keterangan lain…">{{ old('description', $room->description) }}</textarea>
              @error('description')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- ============ PANEL FOTO ============ -->
          <div class="card elev-sm form-card photo-card">
            <h3 class="card-title">Foto Kamar</h3>

            <div class="photo-preview" id="photoPreview" data-empty="{{ $room->image ? '0' : '1' }}">
              @if($room->image)
                <img src="{{ asset('storage/' . $room->image) }}" alt="Foto kamar {{ $room->room_number }}">
              @else
                <span class="photo-placeholder">
                  <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                  <span>Belum ada foto</span>
                </span>
              @endif
            </div>

            <div class="img-upload-actions">
              <label class="btn btn-secondary" for="image" role="button" tabindex="0" title="Pilih gambar kamar">Ganti Gambar</label>
              <button type="button" class="btn btn-ghost" id="photoRemove" data-action="room-photo-remove" {{ $room->image ? '' : 'hidden' }}>Hapus Foto</button>
            </div>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" hidden>
            <p class="small muted" style="margin:0">JPG, JPEG, PNG, atau WebP. Gambar disimpan di Storage Laravel, bukan base64.</p>
            <p class="form-error" id="photoError" hidden>Format foto harus JPG, JPEG, PNG, atau WebP.</p>
            @error('image')
              <p class="form-error">{{ $message }}</p>
            @enderror

            {{-- Hapus foto lama (backend: if($request->has('remove_image')) Storage::delete + set null) --}}
            @if($room->image)
              <label class="chk" style="margin-top:10px">
                <input type="checkbox" name="remove_image" value="1" @checked(old('remove_image') === '1')>
                <span>Hapus foto kamar ini</span>
              </label>
            @endif
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Update Kamar</button>
          <a href="{{ route('rooms.show', $room) }}" class="btn btn-secondary">Batal</a>
        </div>
      </form>

    </main>
<script>
  // UI kecil halaman ini: preview foto baru sebelum submit (tanpa menyimpan data).
  (function () {
    var input = document.getElementById('image');
    var preview = document.getElementById('photoPreview');
    var removeBtn = document.getElementById('photoRemove');
    var errMsg = document.getElementById('photoError');
    if (!input || !preview) return;

    var PLACEHOLDER = '<span class="photo-placeholder">' +
      '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>' +
      '<span>Belum ada foto</span></span>';

    function setPreview(src) {
      if (!src) {
        preview.dataset.empty = '1';
        preview.innerHTML = PLACEHOLDER;
        if (removeBtn) removeBtn.hidden = true;
        return;
      }
      preview.dataset.empty = '0';
      preview.innerHTML = '<img src="' + src + '" alt="Pratinjau foto kamar">';
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
      // Object URL untuk preview — file asli tetap dikirim via <input type="file">.
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
