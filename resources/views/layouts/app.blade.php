<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f3f2f2">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'KosFly' }}</title>
  @include('partials.favicons')

  <!-- Seluruh styling ada di style.css -->
    @vite(['resources/css/app.css',])
    @stack('styles')
</head>
<body data-page="{{ $pageName ?? 'dashboard' }}">

<div class="layout">


    @include('partials.sidebar')
  <!-- ============================================================
       KONTEN UTAMA
       ============================================================ -->
  <div class="main">

    <!-- ============================ TOPBAR ============================ -->
    @include('partials.topbar')

    <!-- ============================ DASHBOARD / MAIN CONTENT ============================ -->
    @yield('content')
    {{ $slot ?? '' }}

  </div>
</div>

<!-- Backdrop navigasi mobile -->
<div class="nav-backdrop" id="navBackdrop"></div>

<!-- ============================================================
     DIALOG ROOT
     Halaman yang butuh modal mendefinisikan markup-nya sendiri di
     dalam #dialogRoot (contoh: rooms/index.blade.php untuk konfirmasi
     hapus). app.js hanya membuka/menutupnya lewat data-action.
     ============================================================ -->
<div id="dialogRoot"></div>

<!-- Toast notifications -->
<div id="toastRoot" aria-live="polite">
  @if(session('success'))
    <div class="toast ok" style="cursor:pointer;" onclick="this.remove()" title="Klik untuk menutup">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
      <span>{{ session('success') }}</span>
    </div>
  @endif
  @if(session('error'))
    <div class="toast err" style="cursor:pointer;" onclick="this.remove()" title="Klik untuk menutup">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
      <span>{{ session('error') }}</span>
    </div>
  @endif
  @if($errors->any())
    @foreach($errors->all() as $error)
      <div class="toast err" style="cursor:pointer;" onclick="this.remove()" title="Klik untuk menutup">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span>{{ $error }}</span>
      </div>
    @endforeach
  @endif
</div>

@vite([ 'resources/js/app.js'])
@stack('scripts')
</body>
</html>
