@extends('layouts.app')
@section('content')
    <main class="max-w-[1200px] w-full mx-auto p-6 flex flex-col gap-5">

      <section class="flex items-end justify-between gap-4 flex-wrap" aria-label="Judul halaman">
        <div>
          <nav class="flex items-center gap-1.5 flex-wrap text-xs text-neutral-600 mb-1" aria-label="Breadcrumb">
            <a class="no-underline text-ink font-medium hover:text-accent" href="index.html">Dashboard</a>
            <span class="text-neutral-400">/</span>
            <a class="no-underline text-ink font-medium hover:text-accent" href="facility-index.html">Facilities</a>
            <span class="text-neutral-400">/</span>
            <span class="text-neutral-600">Add Facility</span>
          </nav>
          <h2 class="text-[26px]">Add Facility</h2>
          <p class="m-0 mt-1 text-[13px] text-neutral-600">Tambahkan fasilitas baru yang tersedia di kos.</p>
        </div>
        <!-- NANTI HUBUNGKAN KE route('facilities.index') -->
        <a href="facility-index.html" class="btn btn-secondary">Kembali</a>
      </section>

      <!-- Flash message — Blade: @if($errors->any()) … @endif -->
      <div class="hidden items-center gap-2 border-2 border-ink/40 bg-surface px-3 py-2 text-sm" role="alert">
        <span>(contoh tampilan error validation — Blade: @error di setiap field)</span>
      </div>

      <!-- Form tambah facility -->
      <section class="card" aria-label="Form tambah facility">
        <h3 class="card-title">Data Facility</h3>

        <!-- NANTI HUBUNGKAN FORM KE facilities.store
             Blade: <form method="POST" action="{{ route('facilities.store') }}">
             Blade: @csrf (letakkan tepat di bawah tag <form>) -->
        <form method="POST" action="#" class="flex flex-col gap-4">

          <div class="field">
            <label class="field-label" for="name">Facility Name</label>
            <input class="input" type="text" id="name" name="name" placeholder="Mis. WiFi, AC, Kasur" required> value="{{ old('name') }}" (agar isian tetap ada saat validasi gagal)
                 Blade: @error('name') <span class="small text-accent-800">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label class="field-label" for="description">Description</label>
            <textarea class="input" id="description" name="description" rows="5" placeholder="Jelaskan fasilitas ini, misalnya kecepatan, kapasitas, atau lokasinya…"></textarea>
             {{ old('description') }}
                 @error('description') <span class="small text-accent-800">{{ $message }}</span> @enderror
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <!-- NANTI HUBUNGKAN KE route('facilities.index') -->
            <a href="facility-index.html" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Facility</button>
          </div>

        </form>
      </section>

    </main>
@endsection
