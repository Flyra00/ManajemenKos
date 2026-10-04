  <!-- ============================================================
       SIDEBAR — struktur statis. Saat integrasi Laravel, potong
       blok ini menjadi components/sidebar.blade.php. Ganti href
       dengan route Laravel, contoh: href="{{ route('dashboard') }}"
       ============================================================ -->
  <aside class="sidebar" id="sidebar">
    <div class="brand">Kos<span class="brand-accent">Fly</span></div>

    <nav class="side-nav" aria-label="Menu utama KosFly">
      <!-- data-role = daftar role yang boleh melihat menu ini (demo preview saja) -->
      <a class="nav-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h7v9H3z"/><path d="M14 3h7v5h-7z"/><path d="M14 12h7v9h-7z"/><path d="M3 16h7v5H3z"/></svg>
        <span>Dashboard</span>
      </a>

      {{-- Khusus Tenant: Menu Maintenance --}}
      @if(auth()->check() && auth()->user()->hasRole('tenant'))
        <a class="nav-btn {{ request()->routeIs('maintenance.*') ? 'active' : '' }}" href="{{ route('maintenance.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Maintenance</span>
        </a>
      @endif

      {{-- Menu Master Properti & Penghuni: Khusus Admin & Owner (Staff tidak mengelola ini) --}}
      @if(!auth()->check() || (!auth()->user()->hasRole('tenant') && !auth()->user()->hasRole('staff')))
        {{-- Properti Fisik Kos --}}
        <a class="nav-btn {{ request()->routeIs('rooms.*') ? 'active' : '' }}" href="{{ route('rooms.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
          <span>Kelola Kamar</span>
        </a>
        <a class="nav-btn {{ request()->routeIs('facilities.*') ? 'active' : '' }}" href="{{ route('facilities.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><path d="M12 20h.01"/></svg>
          <span>Fasilitas</span>
        </a>

        {{-- Operasional Penghuni & Sewa --}}
        <a class="nav-btn {{ request()->routeIs('tenants.*') ? 'active' : '' }}" href="{{ route('tenants.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Penghuni</span>
        </a>
        <a class="nav-btn {{ request()->routeIs('leases.*') ? 'active' : '' }}" href="{{ route('leases.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
          <span>Kontrak</span>
        </a>
      @endif

      {{-- Menu Operasional, Keuangan & Laporan: Dikelola oleh Admin, Owner, dan Staff --}}
      @if(!auth()->check() || !auth()->user()->hasRole('tenant'))
        <a class="nav-btn {{ request()->routeIs('maintenance.*') ? 'active' : '' }}" href="{{ route('maintenance.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Maintenance</span>
        </a>

        <a class="nav-btn {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h20v12H2z"/><path d="M2 11h20"/><path d="M6 15h4"/></svg>
          <span>Pembayaran</span>
        </a>
        <a class="nav-btn {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 7l-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/></svg>
          <span>Pengeluaran</span>
        </a>
        <a class="nav-btn {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 15l2 2 4-4"/></svg>
          <span>Laporan</span>
        </a>
      @endif

      {{-- Menu Pengaturan Sistem: Khusus Admin & Owner --}}
      @if(!auth()->check() || (!auth()->user()->hasRole('tenant') && !auth()->user()->hasRole('staff')))
        <a class="nav-btn  {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          <span>Pengaturan</span>
        </a>
      @endif

    </nav>

    <div class="side-foot" id="sideFoot">
      @if(auth()->check() && auth()->user()->hasRole('tenant'))
        KosFly Penghuni
      @elseif(auth()->check() && auth()->user()->hasRole('owner'))
        KosFly Owner
      @elseif(auth()->check() && auth()->user()->hasRole('staff'))
        KosFly Staff
      @else
        KosFly Admin
      @endif
    </div>
  </aside>

