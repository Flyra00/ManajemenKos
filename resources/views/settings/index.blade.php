@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{Route('dashboard')}}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Pengaturan</span>
          </nav>
          <h2 class="page-title">Pengaturan</h2>
          <p class="page-sub">Pengaturan aplikasi dan kos</p>
        </div>
        <span class="tag tag-accent" id="roleChip">Preview: Admin</span>
      </section>

      <!-- Informasi kos -->
      <section class="card elev-sm section-card" aria-label="Informasi kos">
        <h3 class="card-title">Informasi Kos</h3>
        <form data-form="kosinfo" novalidate style="display:flex;flex-direction:column;gap:12px">
          <div class="grid-2">
            <div class="field">
              <label for="sNamaKos">Nama Kos</label>
              <input class="input" name="namaKos" id="sNamaKos" required>
            </div>
            <div class="field">
              <label for="sAlamat">Alamat</label>
              <input class="input" name="alamat" id="sAlamat">
            </div>
            <div class="field">
              <label for="sTelp">Nomor Telepon</label>
              <input class="input" name="telp" id="sTelp">
            </div>
            <div class="field">
              <label for="sEmail">Email</label>
              <input class="input" name="email" id="sEmail" type="email">
            </div>
          </div>
          <div class="dialog-actions" style="margin-top:0">
            <button type="submit" class="btn btn-primary">Simpan Informasi</button>
          </div>
        </form>
      </section>

      <!-- Pengaturan pembayaran -->
      <section class="card elev-sm section-card" aria-label="Pengaturan pembayaran">
        <h3 class="card-title">Pengaturan Pembayaran</h3>
        <form data-form="paysettings" novalidate style="display:flex;flex-direction:column;gap:12px">
          <div class="grid-2">
            <div class="field">
              <label for="sJatuhTempo">Jatuh Tempo Tagihan</label>
              <select class="input" name="jatuhTempo" id="sJatuhTempo">
                <option value="5">Tanggal 5</option>
                <option value="10">Tanggal 10</option>
                <option value="15">Tanggal 15</option>
                <option value="akhir">Akhir bulan</option>
              </select>
            </div>
            <div class="field">
              <label for="sMetode">Metode Pembayaran Utama</label>
              <select class="input" name="metode" id="sMetode">
                <option value="Transfer">Transfer</option>
                <option value="QRIS">QRIS</option>
                <option value="Tunai">Tunai</option>
              </select>
            </div>
            <div class="field">
              <label for="sDenda">Denda Keterlambatan per Hari (Rp)</label>
              <input class="input" name="denda" id="sDenda" type="number" min="0" step="5000">
            </div>
          </div>

          <div>
            <div class="form-section-title">Otomatisasi</div>
            <div class="setting-row">
              <div>
                <div class="s-title">Terapkan denda otomatis</div>
                <div class="s-desc">Tambahkan denda saat tagihan melewati jatuh tempo</div>
              </div>
              <label class="switch">
                <input type="checkbox" name="autoDenda" id="sAutoDenda">
                <span class="slider"></span>
              </label>
            </div>
            <div class="setting-row">
              <div>
                <div class="s-title">Kirim pengingat otomatis</div>
                <div class="s-desc">Ingatkan penghuni 3 hari sebelum jatuh tempo</div>
              </div>
              <label class="switch">
                <input type="checkbox" name="reminder" id="sReminder">
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <div class="dialog-actions" style="margin-top:0">
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
          </div>
        </form>
      </section>

      <!-- Pengaturan notifikasi -->
      <section class="card elev-sm section-card" aria-label="Pengaturan notifikasi">
        <h3 class="card-title">Pengaturan Notifikasi</h3>
        <form data-form="notif" novalidate>
          <div class="setting-row">
            <div>
              <div class="s-title">Pembayaran masuk</div>
              <div class="s-desc">Notifikasi saat ada pembayaran baru dicatat</div>
            </div>
            <label class="switch">
              <input type="checkbox" name="payMasuk" id="nPayMasuk">
              <span class="slider"></span>
            </label>
          </div>
          <div class="setting-row">
            <div>
              <div class="s-title">Pengingat tagihan</div>
              <div class="s-desc">Pengingat untuk tagihan yang belum dibayar</div>
            </div>
            <label class="switch">
              <input type="checkbox" name="tagihan" id="nTagihan">
              <span class="slider"></span>
            </label>
          </div>
          <div class="setting-row">
            <div>
              <div class="s-title">Keluhan / maintenance baru</div>
              <div class="s-desc">Notifikasi saat penghuni melaporkan kerusakan</div>
            </div>
            <label class="switch">
              <input type="checkbox" name="keluhan" id="nKeluhan">
              <span class="slider"></span>
            </label>
          </div>
          <div class="setting-row">
            <div>
              <div class="s-title">Kontrak akan berakhir</div>
              <div class="s-desc">Pengingat kontrak sewa yang hampir habis</div>
            </div>
            <label class="switch">
              <input type="checkbox" name="kontrak" id="nKontrak">
              <span class="slider"></span>
            </label>
          </div>
          <div class="dialog-actions" style="margin-top:12px">
            <button type="submit" class="btn btn-primary">Simpan Notifikasi</button>
          </div>
        </form>
      </section>

    </main>
@endsection
