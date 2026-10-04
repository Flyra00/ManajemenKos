@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('facilities.index') }}">Fasilitas</a>
            <span class="sep">/</span>
            <span class="current">Tambah Fasilitas</span>
          </nav>
          <h2 class="page-title">Tambah Fasilitas</h2>
          <p class="page-sub">Lengkapi nama dan deskripsi fasilitas baru yang tersedia di kos.</p>
        </div>
      </section>

      {{-- Ringkasan error validasi --}}
      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada form. Periksa kembali isian di bawah.</span>
        </div>
      @endif

      <form method="POST" action="{{ route('facilities.store') }}" novalidate>
        @csrf

        <div class="form-layout">

          <!-- ============ FORM UTAMA ============ -->
          <div class="card elev-sm form-card">
            <div class="field">
              <label for="name">Nama Fasilitas <span class="text-accent">*</span></label>
              <input class="input @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: WiFi Cepat, AC Inverter, Parkir Motor, Dapur Bersama" required>
              @error('name')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <h3 class="form-section-title">Deskripsi Fasilitas</h3>
            <div class="field">
              <label for="description">Deskripsi</label>
              <textarea class="input @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Jelaskan detail fasilitas ini, misalnya kapasitas, aturan pakai, atau spesifikasinya…">{{ old('description') }}</textarea>
              @error('description')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- ============ SIDE PANEL INFORMASI ============ -->
          <div class="card elev-sm form-card photo-card">
            <h3 class="card-title">Informasi & Panduan</h3>
            <div style="font-size:13px; line-height:1.6; color:var(--color-neutral-700)">
              <p style="margin:0 0 10px">
                Fasilitas yang Anda daftarkan di sini nantinya dapat dipilih saat menambahkan atau mengedit data <strong>Kamar Kos</strong>.
              </p>
              <p style="margin:0 0 10px">
                Fasilitas ini juga akan tampil pada halaman profil detail kamar sebagai penunjang informasi bagi calon penghuni kos.
              </p>
            </div>
          </div>

        </div>

        <div class="form-actions" style="margin-top:20px">
          <button type="submit" class="btn btn-primary">Simpan Fasilitas</button>
          <a href="{{ route('facilities.index') }}" class="btn btn-secondary">Batal</a>
        </div>
      </form>

    </main>
@endsection

