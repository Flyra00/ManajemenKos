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
            <span class="current">Edit Penghuni</span>
          </nav>
          <h2 class="page-title">Edit Penghuni</h2>
          <p class="page-sub">Perbarui informasi biodata dan kontak darurat penghuni.</p>
        </div>
        <div class="flex head-actions">
          <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
      </section>

      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <section class="card elev-sm section-card" aria-label="Form edit penghuni">
        <div class="card-head">
          <h3 class="card-title">Data Pribadi & Kontak</h3>
        </div>

        <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="flex flex-col gap-4">
          @csrf
          @method('PUT')

          <div class="grid-2">
            <div class="field">
              <label for="name">Nama Lengkap <span class="text-accent">*</span></label>
              <input class="input @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name', $tenant->user->name ?? '') }}" required>
              @error('name')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="email">Alamat Email <span class="text-accent">*</span></label>
              <input class="input @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email', $tenant->user->email ?? '') }}" required>
              @error('email')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid-2">
            <div class="field">
              <label for="phone">Nomor Telepon / WhatsApp</label>
              <input class="input @error('phone') is-invalid @enderror" type="text" id="phone" name="phone" value="{{ old('phone', $tenant->user->phone ?? '') }}">
              @error('phone')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="ktp_number">Nomor KTP (NIK) <span class="text-accent">*</span></label>
              <input class="input @error('ktp_number') is-invalid @enderror" type="text" id="ktp_number" name="ktp_number" value="{{ old('ktp_number', $tenant->ktp_number) }}" maxlength="20" required>
              @error('ktp_number')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="field">
            <label for="job">Pekerjaan / Instansi</label>
            <input class="input @error('job') is-invalid @enderror" type="text" id="job" name="job" value="{{ old('job', $tenant->job) }}">
            @error('job')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <div class="card-head" style="margin-top: 1rem; border-top: 1px solid var(--color-neutral-300); padding-top: 1rem;">
            <h4 class="card-title text-sm">Kontak Darurat (Keluarga / Kerabat)</h4>
          </div>

          <div class="grid-2">
            <div class="field">
              <label for="emergency_name">Nama Kontak Darurat</label>
              <input class="input @error('emergency_name') is-invalid @enderror" type="text" id="emergency_name" name="emergency_name" value="{{ old('emergency_name', $tenant->emergency_name) }}">
              @error('emergency_name')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="emergency_contact">Nomor Telepon Darurat</label>
              <input class="input @error('emergency_contact') is-invalid @enderror" type="text" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', $tenant->emergency_contact) }}">
              @error('emergency_contact')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Perbarui Penghuni</button>
          </div>

        </form>
      </section>

    </main>
@endsection
