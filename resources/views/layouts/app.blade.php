<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f3f2f2">
  <meta name="mobile-web-app-capable" content="yes">
  <title>{{ $title ?? 'KosFly' }}</title>

  <!-- Seluruh styling ada di style.css -->
    @vite(['resources/css/app.css',])
</head>
<body data-page="dashboard">

<div class="layout">


    @include('partials.sidebar')
  <!-- ============================================================
       KONTEN UTAMA
       ============================================================ -->
  <div class="main">

    <!-- ============================ TOPBAR ============================ -->
    @include('partials.topbar')

    <!-- ============================ DASHBOARD ============================ -->
    @yield('content')

  </div>
</div>

<!-- Backdrop navigasi mobile -->
<div class="nav-backdrop" id="navBackdrop"></div>

<!-- ============================================================
     MODAL — struktur statis di HTML agar mudah dipindah ke Blade.
     app.js hanya membuka/menutup dan mengisi nilai.
     ============================================================ -->
<div id="dialogRoot">

  <!-- Modal: Kamar (tambah/edit) -->
  <div class="dialog-backdrop" id="modalRoom" hidden data-close="1">
    <div class="dialog">
      <form data-form="room" novalidate>
        <div class="dialog-title" id="roomModalTitle">Tambah Kamar</div>
        <div class="grid-2">
          <div class="field">
            <label for="fRoomNo">Nomor Kamar</label>
            <input class="input" name="no" id="fRoomNo" placeholder="A-09" required>
          </div>
          <div class="field">
            <label for="fRoomLantai">Lantai</label>
            <select class="input" name="lantai" id="fRoomLantai">
              <option value="1">Lantai 1</option>
              <option value="2">Lantai 2</option>
              <option value="3">Lantai 3</option>
            </select>
          </div>
          <div class="field">
            <label for="fRoomTipe">Tipe</label>
            <select class="input" name="tipe" id="fRoomTipe">
              <option value="Standar">Standar</option>
              <option value="Deluxe">Deluxe</option>
              <option value="Premium">Premium</option>
            </select>
          </div>
          <div class="field">
            <label for="fRoomHarga">Harga per Bulan (Rp)</label>
            <input class="input" name="harga" id="fRoomHarga" type="number" min="0" step="50000" required>
          </div>
          <div class="field">
            <label for="fRoomStatus">Status</label>
            <select class="input" name="status" id="fRoomStatus">
              <option value="Kosong">Kosong</option>
              <option value="Terisi">Terisi</option>
              <option value="Perbaikan">Perbaikan</option>
            </select>
          </div>
          <div class="field">
            <label for="fRoomPenghuni">Penghuni (jika Terisi)</label>
            <input class="input" name="penghuni" id="fRoomPenghuni" placeholder="Nama penghuni">
          </div>
        </div>
        <div class="field">
          <label for="fRoomCatatan">Catatan</label>
          <textarea class="input" name="catatan" id="fRoomCatatan" placeholder="Fasilitas kamar, catatan…" rows="2"></textarea>
        </div>
        <div class="dialog-actions">
          <button type="button" class="btn btn-secondary" data-action="close-dialog">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Pembayaran (tambah/edit) -->
  <div class="dialog-backdrop" id="modalPay" hidden data-close="1">
    <div class="dialog">
      <form data-form="pay" novalidate>
        <div class="dialog-title" id="payModalTitle">Catat Pembayaran</div>
        <div class="grid-2">
          <div class="field">
            <label for="fPayPenghuni">Penghuni</label>
            <select class="input" name="penghuni" id="fPayPenghuni"></select>
          </div>
          <div class="field">
            <label for="fPayKamar">Kamar</label>
            <input class="input" name="kamar" id="fPayKamar" readonly>
          </div>
          <div class="field">
            <label for="fPayPeriode">Periode Tagihan</label>
            <select class="input" name="periode" id="fPayPeriode"></select>
          </div>
          <div class="field">
            <label for="fPayJumlah">Jumlah (Rp)</label>
            <input class="input" name="jumlah" id="fPayJumlah" type="number" min="0" step="50000" required>
          </div>
          <div class="field">
            <label for="fPayJatuh">Jatuh Tempo</label>
            <input class="input" name="jatuhTempo" id="fPayJatuh" type="date">
          </div>
          <div class="field">
            <label for="fPayTgl">Tanggal Bayar</label>
            <input class="input" name="tgl" id="fPayTgl" type="date">
          </div>
          <div class="field">
            <label for="fPayMetode">Metode</label>
            <select class="input" name="metode" id="fPayMetode">
              <option value="Transfer">Transfer</option>
              <option value="QRIS">QRIS</option>
              <option value="Tunai">Tunai</option>
            </select>
          </div>
          <div class="field">
            <label for="fPayStatus">Status</label>
            <select class="input" name="status" id="fPayStatus">
              <option value="Lunas">Lunas</option>
              <option value="Menunggu">Pending</option>
              <option value="Belum Bayar">Belum Bayar</option>
              <option value="Terlambat">Overdue</option>
            </select>
          </div>
        </div>
        <div class="dialog-actions">
          <button type="button" class="btn btn-secondary" data-action="close-dialog">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Maintenance / Keluhan (tambah/edit) -->
  <div class="dialog-backdrop" id="modalMaint" hidden data-close="1">
    <div class="dialog">
      <form data-form="maint" novalidate>
        <div class="dialog-title" id="maintModalTitle">Tambah Keluhan</div>
        <div class="field">
          <label for="fMtJudul">Judul Laporan</label>
          <input class="input" name="judul" id="fMtJudul" placeholder="Mis. AC tidak dingin" required>
        </div>
        <div class="grid-2">
          <div class="field">
            <label for="fMtKamar">Kamar / Lokasi</label>
            <select class="input" name="kamar" id="fMtKamar"></select>
          </div>
          <div class="field">
            <label for="fMtPenghuni">Dilaporkan oleh</label>
            <select class="input" name="penghuni" id="fMtPenghuni"></select>
          </div>
          <div class="field">
            <label for="fMtPrioritas">Prioritas</label>
            <select class="input" name="prioritas" id="fMtPrioritas">
              <option value="Tinggi">Tinggi</option>
              <option value="Sedang">Sedang</option>
              <option value="Rendah">Rendah</option>
            </select>
          </div>
          <div class="field">
            <label for="fMtStatus">Status</label>
            <select class="input" name="status" id="fMtStatus">
              <option value="Baru">Reported</option>
              <option value="Diproses">In Progress</option>
              <option value="Selesai">Completed</option>
              <option value="Dibatalkan">Cancelled</option>
            </select>
          </div>
          <div class="field">
            <label for="fMtBiaya">Biaya (Rp)</label>
            <input class="input" name="biaya" id="fMtBiaya" type="number" min="0" step="10000" placeholder="0">
          </div>
          <div class="field">
            <label for="fMtDitangani">Ditangani oleh</label>
            <input class="input" name="ditangani" id="fMtDitangani" placeholder="Nama teknisi">
          </div>
        </div>
        <div class="dialog-actions">
          <button type="button" class="btn btn-secondary" data-action="close-dialog">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Detail -->
  <div class="dialog-backdrop" id="modalDetail" hidden data-close="1">
    <div class="dialog">
      <div class="dialog-title" id="detailTitle">Detail</div>
      <dl class="detail-list" id="detailBody"></dl>
      <div class="dialog-actions">
        <button type="button" class="btn btn-secondary" data-action="close-dialog">Tutup</button>
      </div>
    </div>
  </div>

  <!-- Modal: Konfirmasi -->
  <div class="dialog-backdrop" id="modalConfirm" hidden data-close="1">
    <div class="dialog">
      <div class="dialog-title" id="confirmTitle">Konfirmasi</div>
      <p class="small muted" id="confirmMsg" style="margin:8px 0 0;line-height:1.5"></p>
      <div class="dialog-actions" style="margin-top:16px">
        <button type="button" class="btn btn-secondary" data-action="close-dialog">Batal</button>
        <button type="button" class="btn btn-primary" data-action="confirm-ok" id="confirmOkBtn">Hapus</button>
      </div>
    </div>
  </div>

</div>

<!-- Toast notifications -->
<div id="toastRoot" aria-live="polite"></div>
@vite([ 'resources/js/app.js'])
</body>
</html>
