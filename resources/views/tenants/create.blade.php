@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <a href="{{ route('tenants.index') }}">Penghuni</a>
            <span class="sep">/</span>
            <span class="current">Tambah Penghuni</span>
          </nav>
          <h2 class="page-title">Tambah Penghuni</h2>
          <p class="page-sub">Daftarkan data identitas penghuni baru dan akun pengguna kos.</p>
        </div>
      </section>

      {{-- Ringkasan error validasi --}}
      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <form method="POST" action="{{ route('tenants.store') }}" novalidate>
        @csrf

        <div class="form-layout">

          <!-- ============ FORM UTAMA ============ -->
          <div class="card elev-sm form-card">

            <div class="field">
              <label for="name">Nama Lengkap <span class="text-accent">*</span></label>
              <input class="input @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
              @error('name')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="grid-2">
              <div class="field">
                <label for="email">Alamat Email <span class="text-accent">*</span></label>
                <input class="input @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="budi@example.com" required>
                @error('email')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="phone">Nomor Telepon / WhatsApp</label>
                <input class="input @error('phone') is-invalid @enderror" type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="081234567890">
                @error('phone')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Identitas & Pekerjaan</h3>

            <div class="grid-2">
              <div class="field">
                <label for="ktp_number">Nomor KTP (NIK) <span class="text-accent">*</span></label>
                <input class="input @error('ktp_number') is-invalid @enderror" type="text" id="ktp_number" name="ktp_number" value="{{ old('ktp_number') }}" maxlength="20" placeholder="16 digit NIK KTP" required>
                @error('ktp_number')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="job">Pekerjaan / Instansi</label>
                <input class="input @error('job') is-invalid @enderror" type="text" id="job" name="job" value="{{ old('job') }}" placeholder="Contoh: Mahasiswa UI / Karyawan Swasta">
                @error('job')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Kontak Darurat (Keluarga / Kerabat)</h3>

            <div class="grid-2">
              <div class="field">
                <label for="emergency_name">Nama Kontak Darurat</label>
                <input class="input @error('emergency_name') is-invalid @enderror" type="text" id="emergency_name" name="emergency_name" value="{{ old('emergency_name') }}" placeholder="Nama orang tua / wali / kerabat">
                @error('emergency_name')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="emergency_contact">Nomor Telepon Darurat</label>
                <input class="input @error('emergency_contact') is-invalid @enderror" type="text" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="08xxxxxxxxxx">
                @error('emergency_contact')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <h3 class="form-section-title">Akun Login Penghuni</h3>

            <div class="grid-2">
              <div class="field">
                <label for="password">Password Akun <span class="text-accent">*</span></label>
                <input class="input @error('password') is-invalid @enderror" type="password" id="password" name="password" autocomplete="new-password" required>
                @error('password')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="password_confirmation">Ulangi Password <span class="text-accent">*</span></label>
                <input class="input @error('password_confirmation') is-invalid @enderror" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                @error('password_confirmation')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

          </div>

          <!-- ============ SIDE PANEL INFORMASI ============ -->
          <div class="card elev-sm form-card photo-card">
            <h3 class="card-title">Informasi Akun Penghuni</h3>
            <div style="font-size:13px; line-height:1.6; color:var(--color-neutral-700)">
              <p style="margin:0 0 10px">
                Sistem otomatis membuatkan akun user (role: <strong>tenant</strong>) dengan password yang Anda tentukan sendiri pada bagian <strong>Akun Login Penghuni</strong>.
              </p>
              <p style="margin:0 0 10px">
                Catat dan sampaikan password tersebut hanya kepada penghuni yang bersangkutan — password tidak dapat dilihat kembali setelah data disimpan.
              </p>
              <p style="margin:0 0 10px">
                Penghuni dapat menggunakan akun ini untuk login ke aplikasi kos guna memantau tagihan, mengunggah bukti pembayaran, dan mengirim laporan perbaikan kamar.
              </p>
            </div>
          </div>

        </div>

        <div class="form-actions" style="margin-top:20px">
          <button type="submit" class="btn btn-primary">Simpan Penghuni</button>
          <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Batal</a>
        </div>
      </form>

    </main>
@endsection

