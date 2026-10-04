@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Pengaturan</span>
          </nav>
          <h2 class="page-title">Pengaturan & Konfigurasi</h2>
          <p class="page-sub">Kelola informasi operasional kos, tanggal jatuh tempo, profil akun, dan peran pengguna sistem.</p>
        </div>
        <div>
          @if(auth()->user() && auth()->user()->roles->isNotEmpty())
            <span class="tag tag-accent">Role: {{ ucfirst(auth()->user()->roles->first()->name) }}</span>
          @else
            <span class="tag tag-accent">Role: Pengelola</span>
          @endif
        </div>
      </section>

      @if(session('success'))
        <div class="alert alert-success" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <!-- 1. INFORMASI OPERASIONAL KOS -->
      <section class="card elev-sm section-card" aria-label="Informasi operasional kos">
        <div class="card-head">
          <h3 class="card-title">Informasi Operasional Kos</h3>
          <span class="small muted">Data identitas kos, nomor kontak pengelola, dan tanggal jatuh tempo tagihan</span>
        </div>

        <form method="POST" action="{{ route('settings.kos') }}" style="display:flex; flex-direction:column; gap:16px;">
          @csrf
          @method('PUT')

          <div class="grid-2">
            <div class="field">
              <label for="kos_name">Nama Kos <span class="text-accent">*</span></label>
              <input class="input @error('name') is-invalid @enderror" type="text" id="kos_name" name="name" value="{{ old('name', $kosSettings['name']) }}" required>
              @error('name')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="kos_phone">Nomor Telepon / WhatsApp Pengelola</label>
              <input class="input @error('phone') is-invalid @enderror" type="text" id="kos_phone" name="phone" value="{{ old('phone', $kosSettings['phone']) }}">
              @error('phone')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid-2">
            <div class="field">
              <label for="kos_email">Email Resmi Kos</label>
              <input class="input @error('email') is-invalid @enderror" type="email" id="kos_email" name="email" value="{{ old('email', $kosSettings['email']) }}">
              @error('email')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="billing_due">Tanggal Standar Jatuh Tempo Tagihan</label>
              <select class="input @error('billing_due') is-invalid @enderror" id="billing_due" name="billing_due">
                @for($d = 1; $d <= 28; $d++)
                  <option value="{{ $d }}" @selected(old('billing_due', $kosSettings['billing_due']) == $d)>
                    Setiap Tanggal {{ $d }} tiap bulan
                  </option>
                @endfor
              </select>
              @error('billing_due')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="field">
            <label for="kos_address">Alamat Lengkap Kos</label>
            <textarea class="input @error('address') is-invalid @enderror" id="kos_address" name="address" rows="2">{{ old('address', $kosSettings['address']) }}</textarea>
            @error('address')
              <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          <!-- LOKASI PETA INTERAKTIF (LEAFLET.JS) -->
          <div style="border-top: 1px solid var(--color-divider); padding-top: 16px; margin-top: 4px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
              <div>
                <h4 style="font-size: 15px; font-weight: 700; margin: 0 0 2px;">Titik Lokasi Kos di Peta (Leaflet)</h4>
                <p class="small muted" style="margin:0;">Klik pada peta atau geser pin merah untuk menentukan titik lokasi akurat kos Anda.</p>
              </div>
              <button type="button" id="btnUseMyLocation" class="btn btn-secondary btn-sm" style="font-size: 12px; gap: 4px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                Gunakan Lokasi Saya Saat Ini
              </button>
            </div>

            <!-- Wadah Peta Leaflet -->
            <div id="settingsMap" style="height: 320px; width: 100%; border-radius: 8px; border: 1px solid var(--color-divider); margin-bottom: 12px; z-index: 1;"></div>

            <div class="grid-2">
              <div class="field">
                <label for="latitude">Latitude (Lintang)</label>
                <input class="input @error('latitude') is-invalid @enderror" type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude', $kosSettings['latitude'] ?? -6.9740) }}" placeholder="-6.9740" required>
                @error('latitude')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label for="longitude">Longitude (Bujur)</label>
                <input class="input @error('longitude') is-invalid @enderror" type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude', $kosSettings['longitude'] ?? 107.6305) }}" placeholder="107.6305" required>
                @error('longitude')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>
            <input type="hidden" id="map_zoom" name="map_zoom" value="{{ old('map_zoom', $kosSettings['map_zoom'] ?? 16) }}">
          </div>

          @if(!auth()->user() || !auth()->user()->hasRole('owner'))
            <div>
              <button type="submit" class="btn btn-primary">Simpan Informasi Kos</button>
            </div>
          @else
            <div>
              <span class="small muted">Mode Pengawas (Owner): Informasi operasional kos hanya dapat diubah oleh Admin.</span>
            </div>
          @endif

        </form>
      </section>

      <!-- 2. PROFIL AKUN PENGGUNA & UBAH KATA SANDI -->
      <section class="grid-2" style="gap:20px;" aria-label="Profil dan keamanan akun">

        <!-- Form Edit Profil -->
        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Profil Akun Saya</h3>
          </div>

          <form method="POST" action="{{ route('settings.profile') }}" style="display:flex; flex-direction:column; gap:14px;">
            @csrf
            @method('PUT')

            <div class="field">
              <label for="prof_name">Nama Lengkap <span class="text-accent">*</span></label>
              <input class="input @error('name') is-invalid @enderror" type="text" id="prof_name" name="name" value="{{ old('name', $user->name) }}" required>
              @error('name')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="prof_email">Alamat Email Login <span class="text-accent">*</span></label>
              <input class="input @error('email') is-invalid @enderror" type="email" id="prof_email" name="email" value="{{ old('email', $user->email) }}" required>
              @error('email')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="prof_phone">Nomor Telepon / WhatsApp</label>
              <input class="input @error('phone') is-invalid @enderror" type="text" id="prof_phone" name="phone" value="{{ old('phone', $user->phone) }}">
              @error('phone')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <button type="submit" class="btn btn-primary">Perbarui Profil</button>
            </div>
          </form>
        </div>

        <!-- Form Ganti Password -->
        <div class="card elev-sm section-card">
          <div class="card-head">
            <h3 class="card-title">Ubah Kata Sandi</h3>
          </div>

          <form method="POST" action="{{ route('settings.password') }}" style="display:flex; flex-direction:column; gap:14px;">
            @csrf
            @method('PUT')

            <div class="field">
              <label for="cur_password">Kata Sandi Saat Ini <span class="text-accent">*</span></label>
              <input class="input @error('current_password') is-invalid @enderror" type="password" id="cur_password" name="current_password" required>
              @error('current_password')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="new_password">Kata Sandi Baru <span class="text-accent">*</span></label>
              <input class="input @error('password') is-invalid @enderror" type="password" id="new_password" name="password" required>
              @error('password')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <div class="field">
              <label for="new_password_confirmation">Konfirmasi Kata Sandi Baru <span class="text-accent">*</span></label>
              <input class="input" type="password" id="new_password_confirmation" name="password_confirmation" required>
            </div>

            <div>
              <button type="submit" class="btn btn-secondary">Simpan Kata Sandi Baru</button>
            </div>
          </form>
        </div>

      </section>

      <!-- 3. MANAJEMEN PENGGUNA & PERAN (SPATIE PERMISSION) -->
      <section class="card elev-sm section-card" aria-label="Manajemen pengguna dan peran">
        <div class="card-head">
          <h3 class="card-title">Manajemen Akun Pengguna & Hak Akses (Role)</h3>
          <span class="small muted">{{ $users->total() }} akun terdaftar</span>
        </div>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr>
                <th style="width:60px;">No</th>
                <th>Nama Pengguna</th>
                <th>Email</th>
                <th>Nomor Telepon</th>
                <th>Peran Saat Ini</th>
                @if(!auth()->user() || !auth()->user()->hasRole('owner'))
                  <th style="width:240px;">Ubah Peran (Spatie)</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($users as $u)
                @php
                  $currentRole = $u->roles->first()?->name ?? 'tenant';
                @endphp
                <tr>
                  <td class="muted">{{ $users->firstItem() ? $users->firstItem() + $loop->index : $loop->iteration }}</td>
                  <td class="font-semibold">{{ $u->name }}</td>
                  <td>{{ $u->email }}</td>
                  <td>{{ $u->phone ?: '—' }}</td>
                  <td>
                    @if($currentRole === 'admin')
                      <span class="tag tag-accent">Admin</span>
                    @elseif($currentRole === 'owner')
                      <span class="tag tag-outline" style="border-color:var(--color-neutral-900); font-weight:700">Owner</span>
                    @else
                      <span class="tag tag-outline">Tenant</span>
                    @endif
                  </td>
                  @if(!auth()->user() || !auth()->user()->hasRole('owner'))
                    <td>
                      <form method="POST" action="{{ route('settings.users.role', $u) }}" class="flex items-center" style="gap:6px">
                        @csrf
                        @method('PUT')
                        <select class="input py-1 text-xs" name="role" style="min-width:110px">
                          <option value="admin" @selected($currentRole === 'admin')>Admin</option>
                          <option value="owner" @selected($currentRole === 'owner')>Owner</option>
                          <option value="tenant" @selected($currentRole === 'tenant')>Tenant</option>
                        </select>
                        <button type="submit" class="btn btn-secondary btn-sm" title="Terapkan peran baru">
                          Ubah
                        </button>
                      </form>
                    </td>
                  @endif
                </tr>
              @empty

                <tr>
                  <td colspan="6" class="text-center py-6 text-neutral-500">
                    Belum ada data akun pengguna.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($users->hasPages())
          <div class="card-footer flex justify-between items-center py-3 px-4 border-t border-neutral-200">
            <span class="text-sm text-neutral-600">
              Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} akun
            </span>
            <div>
              {{ $users->links() }}
            </div>
          </div>
        @endif
      </section>

    </main>
@endsection

@push('styles')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@push('scripts')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const latInput = document.getElementById('latitude');
      const lngInput = document.getElementById('longitude');
      const zoomInput = document.getElementById('map_zoom');
      const btnLoc = document.getElementById('btnUseMyLocation');

      if (!latInput || !lngInput || !document.getElementById('settingsMap')) return;

      let currentLat = parseFloat(latInput.value) || -6.9740;
      let currentLng = parseFloat(lngInput.value) || 107.6305;
      let currentZoom = parseInt(zoomInput ? zoomInput.value : 16) || 16;

      const map = L.map('settingsMap').setView([currentLat, currentLng], currentZoom);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }).addTo(map);

      // Marker draggable
      let marker = L.marker([currentLat, currentLng], { draggable: true }).addTo(map);
      marker.bindPopup('<b>Titik Lokasi Kos</b><br>Geser pin ini ke lokasi tepat gedung kos.').openPopup();

      function updateMarker(lat, lng, zoom) {
        marker.setLatLng([lat, lng]);
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        if (zoomInput && zoom) {
          zoomInput.value = zoom;
        }
      }

      // Drag event
      marker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        updateMarker(pos.lat, pos.lng, map.getZoom());
      });

      // Click on map event
      map.on('click', function(e) {
        updateMarker(e.latlng.lat, e.latlng.lng, map.getZoom());
      });

      // Zoom change
      map.on('zoomend', function() {
        if (zoomInput) zoomInput.value = map.getZoom();
      });

      // Manual input change
      function onManualChange() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (!isNaN(lat) && !isNaN(lng)) {
          marker.setLatLng([lat, lng]);
          map.panTo([lat, lng]);
        }
      }
      latInput.addEventListener('change', onManualChange);
      lngInput.addEventListener('change', onManualChange);

      // Gunakan lokasi saat ini (Geolocation API browser)
      if (btnLoc) {
        btnLoc.addEventListener('click', function() {
          if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung deteksi lokasi.');
            return;
          }
          btnLoc.disabled = true;
          btnLoc.textContent = 'Mendeteksi lokasi GPS...';

          navigator.geolocation.getCurrentPosition(
            function(pos) {
              const lat = pos.coords.latitude;
              const lng = pos.coords.longitude;
              map.setView([lat, lng], 17);
              updateMarker(lat, lng, 17);
              btnLoc.disabled = false;
              btnLoc.innerHTML = '✓ Lokasi Terdeteksi';
              setTimeout(() => {
                btnLoc.innerHTML = `
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                  Gunakan Lokasi Saya Saat Ini
                `;
              }, 2500);
            },
            function(err) {
              alert('Gagal mendeteksi lokasi: ' + (err.message || 'Izin akses lokasi ditolak.'));
              btnLoc.disabled = false;
              btnLoc.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                Gunakan Lokasi Saya Saat Ini
              `;
            },
            { enableHighAccuracy: true, timeout: 10000 }
          );
        });
      }
    });
  </script>
@endpush
