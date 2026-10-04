@extends('layouts.app')

@section('content')
  <main class="page" id="page">

    <!-- Header Halaman Profil -->
    <section class="page-head" aria-label="Judul halaman">
      <div>
        <nav class="breadcrumb" aria-label="Breadcrumb">
          <a href="{{ route('dashboard') }}">Dashboard</a>
          <span class="sep">/</span>
          <span class="current">Profil Akun</span>
        </nav>
        <h2 class="page-title">Pengaturan Profil</h2>
        <p class="page-sub">Kelola informasi data pribadi, nomor kontak WhatsApp, nomor KTP, dan keamanan kata sandi akun Anda.</p>
      </div>
    </section>

    @if (session('status') === 'profile-updated')
      <div class="alert alert-success" role="alert" style="margin-bottom: 24px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        <span>Data profil Anda berhasil disimpan dan diperbarui.</span>
      </div>
    @endif

    @if (session('status') === 'password-updated')
      <div class="alert alert-success" role="alert" style="margin-bottom: 24px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        <span>Kata sandi akun Anda berhasil diperbarui.</span>
      </div>
    @endif

    <div style="display: flex; flex-direction: column; gap: 24px; max-width: 720px;">

      <!-- Kartu 1: Informasi Profil, Kontak & KTP -->
      <div class="card elev-sm" style="background: #ffffff; border: 1px solid var(--color-divider); border-radius: 8px; padding: 24px;">
        @include('profile.partials.update-profile-information-form')
      </div>

      <!-- Kartu 2: Ganti Password -->
      <div class="card elev-sm" style="background: #ffffff; border: 1px solid var(--color-divider); border-radius: 8px; padding: 24px;">
        @include('profile.partials.update-password-form')
      </div>

      <!-- Kartu 3: Hapus Akun -->
      <div class="card elev-sm" style="background: #ffffff; border: 1px solid var(--color-divider); border-radius: 8px; padding: 24px;">
        @include('profile.partials.delete-user-form')
      </div>

    </div>

  </main>
@endsection
