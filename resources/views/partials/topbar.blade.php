    <header class="topbar">
      <!-- Tombol menu (mobile) — inline SVG statis -->
      <button class="btn btn-icon btn-secondary hamburger" id="hamburger" data-action="toggle-nav" title="Menu" aria-label="Buka menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
      </button>

      <!-- Pencarian global -->
      <div class="searchbox">
        <svg class="search-ic" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        <input id="globalSearch" class="input" type="search" name="globalSearch" placeholder="Cari kamar, penghuni, pembayaran…" autocomplete="off" aria-label="Cari">
        <div class="search-results" id="searchResults"></div>
      </div>

      <div class="topbar-right">
        <!-- Notifikasi -->
        <div class="notif">
          <button class="btn btn-icon btn-secondary" id="notifBtn" data-action="toggle-notif" title="Notifikasi" aria-label="Notifikasi">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
          </button>
          <span class="notif-dot" id="notifDot" hidden></span>
          <div class="notif-menu" id="notifMenu"></div>
        </div>

        <!-- Profil user (dummy: Admin KosFly) -->
        <div class="userchip" id="userChip" data-action="toggle-user">
          <div class="avatar" id="userAvatar">AK</div>
          <div class="user-meta">
            <b id="userName">Admin KosFly</b>
            <span id="userRole">Admin</span>
          </div>
          <span class="user-caret">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </span>
          <div class="user-menu" id="userMenu">
            <a href="profile.html">Profil</a>
            <a href="settings.html">Pengaturan</a>
            <form method="POST" action="{{ route('logout') }}">
            @csrf
                <button type="submit" href="route('logout')"> Keluar</button>
            </form>
          </div>
        </div>
      </div>
    </header>
