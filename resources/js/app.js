

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/* ============================================================================
   KosFly — app.js (Dashboard index.html + halaman: Kamar, Penghuni, Kontrak,
   Pembayaran, Maintenance, Pengeluaran, Fasilitas, Profil, Pengaturan, Laporan)
   ----------------------------------------------------------------------------
   Prinsip (sama seperti dashboard):
   - HTML di tiap file halaman = struktur utama. app.js HANYA interaksi.
   - Tidak ada SPA router, tidak ada PAGES object, tidak ada template literal
     untuk seluruh halaman. Yang dirender JS hanya: daftar tabel/kartu
     (data dummy), menu notifikasi, hasil pencarian, chart, dsb.
   - Modal bersifat reusable: form modal, detail modal, konfirmasi modal.
   - Toast: showToast(message, type) — type: 'success' | 'error' | 'warning'.

   INTEGRASI LARAVEL NANTI:
   1. Ganti demoData dengan data backend (mis. @json($data)).
   2. currentRole hanya untuk preview role-based UI.
   3. Tabel yang dirender JS bisa diganti dengan @foreach di Blade.
   4. Sidebar/topbar/form sudah statis di HTML — tinggal potong ke Blade.
   ========================================================================== */

'use strict';

/* ---------------------- DETEKSI JENIS HALAMAN ---------------------- */
// app.js gabungan dashboard + public UI (landing/login/register).
// Halaman public memakai <body class="public-page">.
function isPublicPage() {
  return !!(document.body && document.body.classList && document.body.classList.contains('public-page'));
}

/* ------------------------------ ROLE DEMO ------------------------------ */
// Role dummy untuk preview UI: 'owner' | 'admin' | 'tenant'
const currentRole = 'admin';

/* ------------------------------ DATA DUMMY ------------------------------ */
// Pisahkan data dummy dari logika UI — nanti diganti data backend.
const demoData = {
  user: { nama: 'Admin KosFly', role: 'Admin' },
  pengaturan: {
    namaKos: 'KosFly',
    pemilik: 'Budi Santoso',
    alamat: 'Jl. Melati No. 12, Yogyakarta',
    telp: '0812-3456-7890',
    email: 'admin@kosfly.id',
    tarif: { Standar: 900000, Deluxe: 1100000, Premium: 1250000 },
    pembayaran: { jatuhTempo: '10', metode: 'Transfer', denda: 50000, autoDenda: true, reminder: true },
    notifikasi: { payMasuk: true, tagihan: true, keluhan: true, kontrak: true }
  },
  bulanan: ['Februari 2026', 'Maret 2026', 'April 2026', 'Mei 2026', 'Juni 2026', 'Juli 2026'],
  grafik: [
    { bln: 'Februari 2026', masuk: 15200000 },
    { bln: 'Maret 2026', masuk: 16100000 },
    { bln: 'April 2026', masuk: 14800000 },
    { bln: 'Mei 2026', masuk: 17300000 },
    { bln: 'Juni 2026', masuk: 17000000 },
    { bln: 'Juli 2026', masuk: 18450000 }
  ],

  kamar: [
    { id: 'k01', no: 'A-01', lantai: 1, tipe: 'Standar', harga: 900000, status: 'Terisi', penghuni: 'Rina Wulandari', catatan: '' },
    { id: 'k02', no: 'A-02', lantai: 1, tipe: 'Standar', harga: 900000, status: 'Terisi', penghuni: 'Sari Melati', catatan: '' },
    { id: 'k03', no: 'A-03', lantai: 1, tipe: 'Standar', harga: 900000, status: 'Kosong', penghuni: '', catatan: 'Siap disewa' },
    { id: 'k04', no: 'A-05', lantai: 1, tipe: 'Standar', harga: 900000, status: 'Terisi', penghuni: 'Lina Marlina', catatan: '' },
    { id: 'k05', no: 'A-07', lantai: 1, tipe: 'Standar', harga: 900000, status: 'Perbaikan', penghuni: '', catatan: 'Cat dinding ulang' },
    { id: 'k06', no: 'B-02', lantai: 2, tipe: 'Deluxe', harga: 1100000, status: 'Terisi', penghuni: 'Dedi Kurniawan', catatan: '' },
    { id: 'k07', no: 'B-05', lantai: 2, tipe: 'Deluxe', harga: 1100000, status: 'Kosong', penghuni: '', catatan: 'Siap disewa' },
    { id: 'k08', no: 'B-08', lantai: 2, tipe: 'Deluxe', harga: 1100000, status: 'Terisi', penghuni: 'Agus Prasetyo', catatan: '' },
    { id: 'k09', no: 'B-12', lantai: 2, tipe: 'Deluxe', harga: 1100000, status: 'Terisi', penghuni: 'Maya Sari', catatan: '' },
    { id: 'k10', no: 'C-02', lantai: 3, tipe: 'Premium', harga: 1250000, status: 'Kosong', penghuni: '', catatan: 'Siap disewa' },
    { id: 'k11', no: 'C-04', lantai: 3, tipe: 'Premium', harga: 1250000, status: 'Terisi', penghuni: 'Fajar Nugroho', catatan: '' },
    { id: 'k12', no: 'C-06', lantai: 3, tipe: 'Premium', harga: 1250000, status: 'Perbaikan', penghuni: '', catatan: 'Ganti kloset' }
  ],

  penghuni: [
    { id: 'h01', nama: 'Rina Wulandari', ktp: '3312056511920001', hp: '0857-9988-1122', kamar: 'A-01', pekerjaan: 'Mahasiswa', tglMasuk: '2025-03-15', status: 'Aktif' },
    { id: 'h02', nama: 'Sari Melati', ktp: '3403045201950002', hp: '0838-7766-5544', kamar: 'A-02', pekerjaan: 'Karyawan Swasta', tglMasuk: '2025-09-20', status: 'Aktif' },
    { id: 'h03', nama: 'Lina Marlina', ktp: '3171054703980003', hp: '0821-4455-6677', kamar: 'A-05', pekerjaan: 'Guru', tglMasuk: '2025-06-01', status: 'Aktif' },
    { id: 'h04', nama: 'Dedi Kurniawan', ktp: '3273011205950004', hp: '0821-3344-5566', kamar: 'B-02', pekerjaan: 'Freelancer', tglMasuk: '2025-05-02', status: 'Aktif' },
    { id: 'h05', nama: 'Agus Prasetyo', ktp: '3515062101900005', hp: '0819-1122-3344', kamar: 'B-08', pekerjaan: 'Karyawan Swasta', tglMasuk: '2026-01-11', status: 'Aktif' },
    { id: 'h06', nama: 'Maya Sari', ktp: '3273204508960006', hp: '0812-6677-8899', kamar: 'B-12', pekerjaan: 'Mahasiswa', tglMasuk: '2025-11-05', status: 'Aktif' },
    { id: 'h07', nama: 'Fajar Nugroho', ktp: '3404061501970007', hp: '0856-2233-4455', kamar: 'C-04', pekerjaan: 'Barista', tglMasuk: '2026-01-11', status: 'Aktif' },
    { id: 'h08', nama: 'Dewi Lestari', ktp: '3374056206930008', hp: '0813-9988-7766', kamar: 'A-04', pekerjaan: 'Desainer Grafis', tglMasuk: '2025-02-01', status: 'Keluar' }
  ],

  kontrak: [
    { id: 'ks01', no: 'KS-2026-001', penghuni: 'Rina Wulandari', kamar: 'A-01', mulai: '2025-03-15', sampai: '2026-08-15', harga: 900000, deposit: 900000, status: 'Aktif' },
    { id: 'ks02', no: 'KS-2026-002', penghuni: 'Sari Melati', kamar: 'A-02', mulai: '2025-09-20', sampai: '2026-09-20', harga: 900000, deposit: 900000, status: 'Aktif' },
    { id: 'ks03', no: 'KS-2026-003', penghuni: 'Lina Marlina', kamar: 'A-05', mulai: '2025-06-01', sampai: '2026-08-01', harga: 900000, deposit: 900000, status: 'Segera Berakhir' },
    { id: 'ks04', no: 'KS-2026-004', penghuni: 'Dedi Kurniawan', kamar: 'B-02', mulai: '2025-05-02', sampai: '2026-08-02', harga: 1100000, deposit: 1100000, status: 'Segera Berakhir' },
    { id: 'ks05', no: 'KS-2026-005', penghuni: 'Agus Prasetyo', kamar: 'B-08', mulai: '2026-01-11', sampai: '2026-10-11', harga: 1100000, deposit: 1100000, status: 'Aktif' },
    { id: 'ks06', no: 'KS-2026-006', penghuni: 'Maya Sari', kamar: 'B-12', mulai: '2025-11-05', sampai: '2026-11-05', harga: 1100000, deposit: 1100000, status: 'Aktif' },
    { id: 'ks07', no: 'KS-2026-007', penghuni: 'Fajar Nugroho', kamar: 'C-04', mulai: '2026-01-11', sampai: '2027-01-11', harga: 1250000, deposit: 1250000, status: 'Aktif' },
    { id: 'ks08', no: 'KS-2025-023', penghuni: 'Dewi Lestari', kamar: 'A-04', mulai: '2025-02-01', sampai: '2026-02-01', harga: 900000, deposit: 900000, status: 'Berakhir' }
  ],

  pembayaran: [
    { id: 'b01', inv: 'INV-2026-015', penghuni: 'Rina Wulandari', kamar: 'A-01', periode: 'Juli 2026', jumlah: 900000, jatuhTempo: '2026-07-10', tgl: '2026-07-02', metode: 'Transfer', status: 'Lunas' },
    { id: 'b02', inv: 'INV-2026-016', penghuni: 'Dedi Kurniawan', kamar: 'B-02', periode: 'Juli 2026', jumlah: 1100000, jatuhTempo: '2026-07-10', tgl: '2026-07-01', metode: 'QRIS', status: 'Lunas' },
    { id: 'b03', inv: 'INV-2026-017', penghuni: 'Sari Melati', kamar: 'A-02', periode: 'Juli 2026', jumlah: 900000, jatuhTempo: '2026-07-10', tgl: '2026-07-03', metode: 'Transfer', status: 'Lunas' },
    { id: 'b04', inv: 'INV-2026-018', penghuni: 'Lina Marlina', kamar: 'A-05', periode: 'Juli 2026', jumlah: 900000, jatuhTempo: '2026-07-10', tgl: '', metode: 'Tunai', status: 'Menunggu' },
    { id: 'b05', inv: 'INV-2026-019', penghuni: 'Maya Sari', kamar: 'B-12', periode: 'Juli 2026', jumlah: 1100000, jatuhTempo: '2026-07-10', tgl: '2026-07-06', metode: 'Transfer', status: 'Lunas' },
    { id: 'b06', inv: 'INV-2026-020', penghuni: 'Fajar Nugroho', kamar: 'C-04', periode: 'Juli 2026', jumlah: 1250000, jatuhTempo: '2026-07-10', tgl: '', metode: 'QRIS', status: 'Menunggu' },
    { id: 'b07', inv: 'INV-2026-021', penghuni: 'Agus Prasetyo', kamar: 'B-08', periode: 'Juli 2026', jumlah: 1100000, jatuhTempo: '2026-07-10', tgl: '', metode: 'Transfer', status: 'Belum Bayar' },
    { id: 'b08', inv: 'INV-2026-022', penghuni: 'Rina Wulandari', kamar: 'A-01', periode: 'Juni 2026', jumlah: 900000, jatuhTempo: '2026-06-10', tgl: '', metode: 'Transfer', status: 'Terlambat' },
    { id: 'b09', inv: 'INV-2026-023', penghuni: 'Lina Marlina', kamar: 'A-05', periode: 'Juni 2026', jumlah: 900000, jatuhTempo: '2026-06-10', tgl: '', metode: 'Tunai', status: 'Terlambat' },
    { id: 'b10', inv: 'INV-2026-024', penghuni: 'Sari Melati', kamar: 'A-02', periode: 'Juni 2026', jumlah: 900000, jatuhTempo: '2026-06-10', tgl: '2026-06-05', metode: 'Transfer', status: 'Lunas' }
  ],

  maintenance: [
    { id: 'm01', judul: 'AC kamar B-02 tidak dingin', kamar: 'B-02', penghuni: 'Dedi Kurniawan', prioritas: 'Tinggi', status: 'Baru', dilaporkan: '2026-07-08', biaya: 0, ditangani: '—' },
    { id: 'm02', judul: 'Keran kamar mandi bocor', kamar: 'A-05', penghuni: 'Lina Marlina', prioritas: 'Sedang', status: 'Diproses', dilaporkan: '2026-07-05', biaya: 50000, ditangani: 'Teknisi Budi' },
    { id: 'm03', judul: 'Lampu koridor lantai 2 mati', kamar: 'Koridor Lt.2', penghuni: '—', prioritas: 'Rendah', status: 'Diproses', dilaporkan: '2026-07-04', biaya: 0, ditangani: '—' },
    { id: 'm04', judul: 'WiFi lambat di lantai 3', kamar: 'Lantai 3', penghuni: '—', prioritas: 'Sedang', status: 'Selesai', dilaporkan: '2026-07-01', biaya: 0, ditangani: 'Teknisi Jaringan' },
    { id: 'm05', judul: 'Pintu kamar C-02 macet', kamar: 'C-02', penghuni: '—', prioritas: 'Tinggi', status: 'Baru', dilaporkan: '2026-07-09', biaya: 0, ditangani: '—' },
    { id: 'm06', judul: 'Water heater tidak menyala', kamar: 'B-05', penghuni: '—', prioritas: 'Rendah', status: 'Dibatalkan', dilaporkan: '2026-06-20', biaya: 0, ditangani: '—' }
  ],

  pengeluaran: [
    { id: 'e01', judul: 'Listrik bulan Juni', deskripsi: 'Tagihan listrik seluruh kos', jumlah: 1250000, tgl: '2026-07-05', kategori: 'Listrik', dibuatOleh: 'Admin KosFly' },
    { id: 'e02', judul: 'Air PDAM Juni', deskripsi: 'Tagihan air bulanan', jumlah: 350000, tgl: '2026-07-05', kategori: 'Air', dibuatOleh: 'Admin KosFly' },
    { id: 'e03', judul: 'Internet Juni', deskripsi: 'Langganan fiber 100 Mbps', jumlah: 400000, tgl: '2026-07-06', kategori: 'Internet', dibuatOleh: 'Admin KosFly' },
    { id: 'e04', judul: 'Upah teknisi AC', deskripsi: 'Servis AC kamar B-02', jumlah: 500000, tgl: '2026-07-08', kategori: 'Perbaikan', dibuatOleh: 'Admin KosFly' },
    { id: 'e05', judul: 'Sabun & pembersih', deskripsi: 'Perlengkapan kebersihan lobi', jumlah: 180000, tgl: '2026-07-10', kategori: 'Kebersihan', dibuatOleh: 'Admin KosFly' },
    { id: 'e06', judul: 'Cat dinding A-07', deskripsi: 'Cat tembok kamar perbaikan', jumlah: 450000, tgl: '2026-07-12', kategori: 'Perbaikan', dibuatOleh: 'Admin KosFly' },
    { id: 'e07', judul: 'Servis water heater', deskripsi: 'Jasa servis pemanas air', jumlah: 200000, tgl: '2026-07-14', kategori: 'Perbaikan', dibuatOleh: 'Admin KosFly' },
    { id: 'e08', judul: 'Biaya aplikasi bulanan', deskripsi: 'Langganan software manajemen', jumlah: 99000, tgl: '2026-07-15', kategori: 'Lainnya', dibuatOleh: 'Admin KosFly' }
  ],

  fasilitas: [
    { id: 'f01', nama: 'WiFi 100 Mbps', kategori: 'Umum', jumlah: '3 titik', status: 'Tersedia', icon: 'wifi' },
    { id: 'f02', nama: 'AC', kategori: 'Kamar', jumlah: '8 unit', status: 'Tersedia', icon: 'wind' },
    { id: 'f03', nama: 'Kamar Mandi Dalam', kategori: 'Kamar', jumlah: '12 kamar', status: 'Tersedia', icon: 'drop' },
    { id: 'f04', nama: 'Lemari Pakaian', kategori: 'Kamar', jumlah: '12 unit', status: 'Tersedia', icon: 'box' },
    { id: 'f05', nama: 'Kasur', kategori: 'Kamar', jumlah: '12 unit', status: 'Tersedia', icon: 'bed' },
    { id: 'f06', nama: 'Meja Belajar', kategori: 'Kamar', jumlah: '10 unit', status: 'Perbaikan', icon: 'table' },
    { id: 'f07', nama: 'TV LCD Lobby', kategori: 'Lobby', jumlah: '1 unit', status: 'Tersedia', icon: 'tv' },
    { id: 'f08', nama: 'CCTV', kategori: 'Umum', jumlah: '6 titik', status: 'Tersedia', icon: 'camera' },
    { id: 'f09', nama: 'Dapur & Kulkas Bersama', kategori: 'Umum', jumlah: '1 set', status: 'Tersedia', icon: 'fridge' }
  ]
};

/* ------------------------------ STATE UI ------------------------------ */
const state = {
  page: (document.body && document.body.dataset.page) || '',
  notifRead: false,
  reportTab: 'pendapatan',  // tab aktif di halaman Laporan
  filters: {}   // key = nilai data-filter, contoh: 'rooms-status' -> 'Semua'
};

/* ------------------------------ HELPERS ------------------------------ */
const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));

function esc(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function fmtRp(n) {
  const v = Number(n) || 0;
  return 'Rp ' + v.toLocaleString('id-ID');
}

function fmtTanggal(iso) {
  if (!iso) return '—';
  const d = new Date(iso + 'T00:00:00');
  if (isNaN(d)) return iso;
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Konversi nama bulan Indonesia -> kode ISO bulan, contoh: 'Juli 2026' -> '2026-07'
const BULAN_ISO = { Januari: '01', Februari: '02', Maret: '03', April: '04', Mei: '05', Juni: '06', Juli: '07', Agustus: '08', September: '09', Oktober: '10', November: '11', Desember: '12' };
function bulanIniIso() {
  const [nama, tahun] = String(demoData.bulanan[demoData.bulanan.length - 1] || '').split(' ');
  return (BULAN_ISO[nama] && tahun) ? tahun + '-' + BULAN_ISO[nama] : '';
}

function uid(prefix) {
  return prefix + Math.random().toString(36).slice(2, 8);
}

function initials(nama) {
  return String(nama || '').trim().split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase() || '—';
}

// Nilai filter halaman ini dengan default.
function F(key, def) {
  return key in state.filters ? state.filters[key] : def;
}

function tagClass(status) {
  const map = {
    'Lunas': 'tag-neutral', 'Menunggu': 'tag-outline',
    'Belum Bayar': 'tag-outline', 'Terlambat': 'tag-accent',
    'Terisi': 'tag-neutral', 'Kosong': 'tag-outline', 'Perbaikan': 'tag-accent',
    'Baru': 'tag-accent', 'Diproses': 'tag-outline', 'Selesai': 'tag-neutral', 'Dibatalkan': 'tag-outline',
    'Tinggi': 'tag-accent', 'Sedang': 'tag-outline', 'Rendah': 'tag-neutral',
    'Aktif': 'tag-neutral', 'Keluar': 'tag-outline', 'Segera Berakhir': 'tag-accent', 'Berakhir': 'tag-outline',
    'Tersedia': 'tag-neutral'
  };
  return map[status] || 'tag-outline';
}

/* ------------------------------- ICON ------------------------------- */
// Ikon untuk konten yang dirender JS (aksi tabel, notifikasi, dll).
const ICONS = {
  kamar: 'M3 21V8l9-5 9 5v13|M9 21v-8h6v8',
  penghuni: 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2|M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8',
  keluhan: 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z',
  bell: 'M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9|M13.7 21a2 2 0 0 1-3.4 0',
  edit: 'M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z',
  trash: 'M3 6h18|M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6|M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2|M10 11v6M14 11v6',
  check: 'M20 6 9 17l-5-5',
  alert: 'M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z|M12 9v4|M12 17h.01',
  eye: 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z|M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6',
  plus: 'M12 5v14|M5 12h14',
  wrench: 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z'
};

const FAC_ICONS = {
  wifi: 'M5 12.55a11 11 0 0 1 14.08 0|M1.42 9a16 16 0 0 1 21.16 0|M8.53 16.11a6 6 0 0 1 6.95 0|M12 20h.01',
  wind: 'M9.59 4.59A2 2 0 1 1 11 8H2|M9.59 19.41A2 2 0 1 0 11 16H2|M17.73 7.73A2.5 2.5 0 1 1 19.5 12H2',
  drop: 'M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z',
  box: 'M21 8l-9-5-9 5v8l9 5 9-5z|M3 8l9 5 9-5|M12 13v8',
  bed: 'M2 4v16|M2 8h18a2 2 0 0 1 2 2v10|M2 17h20|M6 8v9',
  table: 'M2 21h20|M2 17h20|M5 3v14|M19 3v14|M12 3v14',
  tv: 'M2 5h20v12H2z|M8 21h8|M12 17v4',
  fridge: 'M5 3h14v18H5z|M5 10h14|M9 6h.01|M9 13h.01',
  camera: 'M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z|M12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8'
};

function icon(name, size) {
  const s = size || 16;
  const d = ICONS[name] || FAC_ICONS[name] || ICONS.keluhan;
  const paths = d.split('|').map(p => `<path d="${p}"></path>`).join('');
  return `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex:none">${paths}</svg>`;
}

/* --------------------------- ROLE-BASED UI (demo) --------------------------- */
// Hanya untuk preview. Saat integrasi Laravel: hapus fungsi ini dan gunakan
// @if(auth()->user()->hasRole('...')) di Blade.
function applyRole() {
  $$('[data-role]').forEach(el => {
    const roles = el.dataset.role.split(' ');
    el.hidden = !roles.includes(currentRole);
  });
  const chip = $('#roleChip');
  if (chip) chip.textContent = 'Preview: ' + currentRole.charAt(0).toUpperCase() + currentRole.slice(1);
}

/* --------------------------- HEADER & NOTIFIKASI --------------------------- */
function renderHeader() {
  const u = demoData.user;
  const p = demoData.pengaturan;
  $('#userAvatar').textContent = initials(u.nama);
  $('#userName').textContent = u.nama;
  $('#userRole').textContent = u.role;
  $('#sideFoot').textContent = p.namaKos + ' Admin v2.0';

  // Badge laporan baru di sidebar (halaman maintenance)
  const badge = $('#navKeluhanBadge');
  if (badge) {
    const baru = demoData.maintenance.filter(k => k.status === 'Baru').length;
    badge.textContent = baru;
    badge.hidden = baru === 0;
  }

  // Dot notifikasi
  const adaBaru = demoData.maintenance.some(k => k.status === 'Baru') ||
    demoData.pembayaran.some(b => b.status === 'Terlambat' || b.status === 'Belum Bayar');
  $('#notifDot').hidden = state.notifRead || !adaBaru;
}

function renderNotifMenu() {
  const mBaru = demoData.maintenance.filter(k => k.status === 'Baru');
  const tagihan = demoData.pembayaran.filter(b => b.status === 'Terlambat' || b.status === 'Belum Bayar');
  const items = [];
  mBaru.slice(0, 3).forEach(k => items.push(`
    <button class="notif-item" data-action="go" data-href="maintenance.html">
      ${icon('keluhan', 14)} ${esc(k.judul)}<div class="ni-meta">${esc(k.kamar)} · ${fmtTanggal(k.dilaporkan)}</div>
    </button>`));
  tagihan.slice(0, 3).forEach(b => items.push(`
    <button class="notif-item" data-action="go" data-href="payments.html">
      ${icon('alert', 14)} Pembayaran ${b.status.toLowerCase()} — ${esc(b.penghuni)}<div class="ni-meta">${esc(b.periode)} · ${fmtRp(b.jumlah)}</div>
    </button>`));
  $('#notifMenu').innerHTML = items.length
    ? items.join('')
    : '<div class="notif-empty">Tidak ada notifikasi baru 🎉</div>';
}

/* --------------------------- TOAST --------------------------- */
// showToast(message, type) — type: 'success' | 'error' | 'warning' | 'info'
function showToast(msg, type) {
  const el = document.createElement('div');
  const cls = { success: 'ok', error: 'err', warning: 'warn' }[type] || 'info';
  el.className = 'toast ' + cls;
  const ic = type === 'success' ? 'check' : (type === 'error' || type === 'warning') ? 'alert' : 'bell';
  el.innerHTML = icon(ic, 15) + '<span>' + esc(msg) + '</span>';
  $('#toastRoot').appendChild(el);
  setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; }, 2600);
  setTimeout(() => el.remove(), 2950);
}

/* --------------------------- MODAL (reusable) --------------------------- */
function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.hidden = false;
}

function closeDialog() {
  $$('#dialogRoot .dialog-backdrop').forEach(d => { d.hidden = true; });
}

// confirmDlg(title, pesan, labelTombolOk, callback)
function confirmDlg(title, msg, btn, onOk) {
  $('#confirmTitle').textContent = title;
  $('#confirmMsg').textContent = msg;
  $('#confirmOkBtn').textContent = btn || 'Hapus';
  $('#dialogRoot')._onOk = onOk;
  openModal('modalConfirm');
}

// openDetail(judul, rows) — rows: [{ label, value, tag? }]
function openDetail(title, rows) {
  $('#detailTitle').textContent = title;
  $('#detailBody').innerHTML = rows.map(r => {
    const val = r.tag
      ? `<span class="tag ${tagClass(r.tag)}">${esc(r.value)}</span>`
      : esc(r.value);
    return `<dt>${esc(r.label)}</dt><dd>${val}</dd>`;
  }).join('');
  openModal('modalDetail');
}

/* --------------------------- SIDEBAR COLLAPSE --------------------------- */
// Satu tombol toggle (#hamburger) di topbar:
//   - layar ≥641px → ciutkan / lebarkan sidebar (class body.sidebar-collapsed)
//   - layar ≤640px → buka / tutup drawer off-canvas (class body.nav-open)
// State collapse disimpan di localStorage agar persisten antar halaman.
const SIDEBAR_STORAGE = 'kosfly_sidebar_collapsed';

function isMobileNav() {
  return window.matchMedia('(max-width: 640px)').matches;
}

// setSidebarState(collapsed) — terapkan class + simpan ke localStorage + sinkron label tombol.
function setSidebarState(collapsed) {
  const apply = !!collapsed;
  document.body.classList.toggle('sidebar-collapsed', apply);
  // Hanya tulis ke localStorage jika berubah (hindari write pada tiap load)
  let stored = null;
  try { stored = localStorage.getItem(SIDEBAR_STORAGE) === '1'; } catch (e) { /* private mode */ }
  if (stored !== apply) {
    try { localStorage.setItem(SIDEBAR_STORAGE, apply ? '1' : '0'); } catch (e) { /* private mode */ }
  }
  const h = $('#hamburger');
  if (h && !isMobileNav()) {
    h.setAttribute('aria-label', apply ? 'Perluas sidebar' : 'Ciutkan sidebar');
    h.setAttribute('title', apply ? 'Perluas sidebar' : 'Ciutkan sidebar');
  }
}

function toggleSidebar() {
  setSidebarState(!document.body.classList.contains('sidebar-collapsed'));
}

function toggleMobileNav() {
  const open = document.body.classList.toggle('nav-open');
  const h = $('#hamburger');
  if (h) {
    h.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    h.setAttribute('title', open ? 'Tutup menu' : 'Buka menu');
  }
}

function initSidebarToggle() {
  // Tooltip + aria-label untuk ikon menu saat sidebar collapsed
  $$('.side-nav .nav-btn').forEach(b => {
    const t = b.querySelector('span');
    if (t) {
      const label = t.textContent.trim();
      if (!b.getAttribute('title')) b.title = label;
      if (!b.getAttribute('aria-label')) b.setAttribute('aria-label', label);
    }
  });
  let saved = null;
  try { saved = localStorage.getItem(SIDEBAR_STORAGE); } catch (e) { /* ignore */ }
  setSidebarState(saved === '1');
  window.addEventListener('resize', () => {
    if (!isMobileNav()) document.body.classList.remove('nav-open');
  });
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: KAMAR (rooms.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderRoomStats() {
  const kamar = demoData.kamar;
  const terisi = kamar.filter(k => k.status === 'Terisi').length;
  const kosong = kamar.filter(k => k.status === 'Kosong').length;
  const perbaikan = kamar.filter(k => k.status === 'Perbaikan').length;
  $('#statRoomsTotal').textContent = kamar.length;
  $('#statRoomsTotalSub').textContent = `${[...new Set(kamar.map(k => k.lantai))].length} lantai`;
  $('#statRoomsKosong').textContent = kosong;
  $('#statRoomsKosongSub').textContent = 'tersedia untuk disewa';
  $('#statRoomsTerisi').textContent = terisi;
  $('#statRoomsTerisiSub').textContent = Math.round(terisi / (kamar.length || 1) * 100) + '% okupansi';
  $('#statRoomsPerbaikan').textContent = perbaikan;
  $('#statRoomsPerbaikanSub').textContent = 'sedang dalam perbaikan';
}

function renderRoomsTable() {
  const q = F('rooms-q', '').trim().toLowerCase();
  const s = F('rooms-status', 'Semua');
  const l = F('rooms-lantai', 'Semua');
  const list = demoData.kamar.filter(k =>
    (s === 'Semua' || k.status === s) &&
    (l === 'Semua' || String(k.lantai) === String(l)) &&
    (!q || (k.no + ' ' + k.tipe + ' ' + k.penghuni).toLowerCase().includes(q))
  );
  $('#roomsCount').textContent = list.length + ' kamar ditampilkan';

  $('#roomsBody').innerHTML = list.length ? list.map(k => `
    <tr>
      <td style="font-weight:600">${esc(k.no)}</td>
      <td class="muted">Lantai ${k.lantai}</td>
      <td>${fmtRp(k.harga)}<span class="muted small">/bln</span></td>
      <td><span class="tag ${tagClass(k.status)}">${k.status}</span></td>
      <td>${esc(k.penghuni || '—')}</td>
      <td><div class="row-actions">
        <button class="btn btn-secondary" data-action="room-detail" data-id="${k.id}" title="Detail">${icon('eye', 13)}</button>
        <button class="btn btn-secondary" data-action="room-edit" data-id="${k.id}" title="Edit">${icon('edit', 13)}</button>
        <button class="btn btn-ghost" data-action="room-delete" data-id="${k.id}" title="Hapus">${icon('trash', 13)}</button>
      </div></td>
    </tr>`).join('')
    : '<tr><td colspan="6" class="empty">Tidak ada kamar yang cocok dengan filter</td></tr>';
}

function openRoomModal(id) {
  const form = $('#modalRoom form');
  const k = id ? demoData.kamar.find(x => x.id === id) : null;
  form.dataset.id = id || '';
  $('#roomModalTitle').textContent = k ? 'Edit Kamar ' + k.no : 'Tambah Kamar';
  if (k) {
    $('#fRoomNo').value = k.no; $('#fRoomLantai').value = k.lantai; $('#fRoomTipe').value = k.tipe;
    $('#fRoomHarga').value = k.harga; $('#fRoomStatus').value = k.status;
    $('#fRoomPenghuni').value = k.penghuni || ''; $('#fRoomCatatan').value = k.catatan || '';
  } else {
    form.reset();
    $('#fRoomTipe').value = 'Standar'; $('#fRoomStatus').value = 'Kosong';
    $('#fRoomHarga').value = demoData.pengaturan.tarif.Standar;
  }
  openModal('modalRoom');
}

function saveRoom(form) {
  const d = Object.fromEntries(new FormData(form));
  const id = form.dataset.id;
  const no = d.no.trim().toUpperCase();
  if (!no) return showToast('Nomor kamar wajib diisi', 'error');
  const harga = Number(d.harga) || 0;
  if (harga <= 0) return showToast('Harga kamar tidak valid', 'error');
  const dupe = demoData.kamar.find(k => k.no.toUpperCase() === no && k.id !== id);
  if (dupe) return showToast('Nomor kamar ' + no + ' sudah dipakai', 'error');
  if (d.status === 'Terisi' && !d.penghuni.trim()) return showToast('Kamar Terisi wajib punya nama penghuni', 'error');

  const rec = {
    id: id || uid('k'), no, lantai: Number(d.lantai) || 1, tipe: d.tipe, harga, status: d.status,
    penghuni: d.status === 'Terisi' ? d.penghuni.trim() : '', catatan: d.catatan.trim()
  };
  if (id) {
    const i = demoData.kamar.findIndex(x => x.id === id);
    demoData.kamar[i] = { ...demoData.kamar[i], ...rec };
  } else {
    demoData.kamar.push(rec);
  }
  closeDialog(); refresh();
  showToast('Kamar ' + no + (id ? ' diperbarui' : ' ditambahkan'), 'success');
}

function deleteRoom(id) {
  const k = demoData.kamar.find(x => x.id === id);
  if (!k) return;
  confirmDlg('Hapus Kamar ' + k.no + '?', 'Kamar ini akan dihapus permanen dari daftar.', 'Hapus', () => {
    demoData.kamar = demoData.kamar.filter(x => x.id !== id);
    refresh();
    showToast('Kamar ' + k.no + ' dihapus', 'success');
  });
}

function roomDetail(id) {
  const k = demoData.kamar.find(x => x.id === id);
  if (!k) return;
  openDetail('Detail Kamar ' + k.no, [
    { label: 'Nomor', value: k.no },
    { label: 'Lantai', value: 'Lantai ' + k.lantai },
    { label: 'Tipe', value: k.tipe },
    { label: 'Harga', value: fmtRp(k.harga) + ' /bulan' },
    { label: 'Status', value: k.status, tag: k.status },
    { label: 'Penghuni', value: k.penghuni || '—' },
    { label: 'Catatan', value: k.catatan || '—' }
  ]);
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: PENGHUNI (tenants.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderTenantStats() {
  const semua = demoData.penghuni;
  const aktif = semua.filter(p => p.status === 'Aktif');
  const bln = bulanIniIso();
  const namaBln = demoData.bulanan[demoData.bulanan.length - 1];
  const baru = aktif.filter(p => (p.tglMasuk || '').startsWith(bln)).length;
  const keluar = semua.filter(p => p.status === 'Keluar').length;
  $('#statTenTotal').textContent = semua.length;
  $('#statTenTotalSub').textContent = 'total terdaftar';
  $('#statTenAktif').textContent = aktif.length;
  $('#statTenAktifSub').textContent = 'sedang tinggal';
  $('#statTenBaru').textContent = baru;
  $('#statTenBaruSub').textContent = 'penghuni baru ' + namaBln;
  $('#statTenKeluar').textContent = keluar;
  $('#statTenKeluarSub').textContent = 'sudah keluar';
}

function renderTenantsTable() {
  const q = F('tenants-q', '').trim().toLowerCase();
  const s = F('tenants-status', 'Semua');
  const km = F('tenants-kamar', 'Semua');
  const list = demoData.penghuni.filter(p =>
    (s === 'Semua' || p.status === s) &&
    (km === 'Semua' || p.kamar === km) &&
    (!q || (p.nama + ' ' + p.ktp + ' ' + p.kamar + ' ' + p.pekerjaan).toLowerCase().includes(q))
  );
  $('#tenantsCount').textContent = list.length + ' penghuni ditampilkan';

  $('#tenantsBody').innerHTML = list.length ? list.map(p => `
    <tr>
      <td style="font-weight:600">${esc(p.nama)}</td>
      <td class="muted">${esc(p.ktp)}</td>
      <td>${esc(p.hp)}</td>
      <td>${esc(p.kamar)}</td>
      <td class="muted">${esc(p.pekerjaan)}</td>
      <td><span class="tag ${tagClass(p.status)}">${p.status}</span></td>
      <td><div class="row-actions">
        <button class="btn btn-secondary" data-action="tenant-detail" data-id="${p.id}" title="Detail">${icon('eye', 13)}</button>
        <button class="btn btn-secondary" data-action="tenant-edit" data-id="${p.id}" title="Edit">${icon('edit', 13)}</button>
        <button class="btn btn-ghost" data-action="tenant-delete" data-id="${p.id}" title="Hapus">${icon('trash', 13)}</button>
      </div></td>
    </tr>`).join('')
    : '<tr><td colspan="7" class="empty">Tidak ada penghuni yang cocok dengan filter</td></tr>';
}

function openTenantModal(id) {
  const form = $('#modalTenant form');
  const p = id ? demoData.penghuni.find(x => x.id === id) : null;
  form.dataset.id = id || '';
  $('#tenantModalTitle').textContent = p ? 'Edit Penghuni' : 'Tambah Penghuni';
  if (p) {
    $('#fTenNama').value = p.nama; $('#fTenKtp').value = p.ktp; $('#fTenHp').value = p.hp;
    $('#fTenKamar').value = p.kamar; $('#fTenKerja').value = p.pekerjaan; $('#fTenStatus').value = p.status;
  } else {
    form.reset();
    $('#fTenStatus').value = 'Aktif';
  }
  openModal('modalTenant');
}

function saveTenant(form) {
  const d = Object.fromEntries(new FormData(form));
  const id = form.dataset.id;
  const nama = d.nama.trim();
  if (!nama) return showToast('Nama penghuni wajib diisi', 'error');
  if (!/^\d{16}$/.test(d.ktp.trim())) return showToast('Nomor KTP harus 16 digit', 'error');
  if (!d.hp.trim()) return showToast('Nomor telepon wajib diisi', 'error');
  if (!d.kamar) return showToast('Kamar wajib dipilih', 'error');

  const rec = {
    id: id || uid('h'), nama, ktp: d.ktp.trim(), hp: d.hp.trim(), kamar: d.kamar,
    pekerjaan: d.pekerjaan.trim(), status: d.status,
    tglMasuk: id ? (demoData.penghuni.find(x => x.id === id) || {}).tglMasuk || new Date().toISOString().slice(0, 10)
                  : new Date().toISOString().slice(0, 10)
  };
  if (id) {
    const i = demoData.penghuni.findIndex(x => x.id === id);
    demoData.penghuni[i] = { ...demoData.penghuni[i], ...rec };
  } else {
    demoData.penghuni.push(rec);
  }
  closeDialog(); refresh();
  showToast('Penghuni ' + nama + (id ? ' diperbarui' : ' ditambahkan'), 'success');
}

function deleteTenant(id) {
  const p = demoData.penghuni.find(x => x.id === id);
  if (!p) return;
  confirmDlg('Hapus Penghuni ' + p.nama + '?', 'Data penghuni akan dihapus permanen dari daftar.', 'Hapus', () => {
    demoData.penghuni = demoData.penghuni.filter(x => x.id !== id);
    refresh();
    showToast('Penghuni ' + p.nama + ' dihapus', 'success');
  });
}

function tenantDetail(id) {
  const p = demoData.penghuni.find(x => x.id === id);
  if (!p) return;
  openDetail('Detail Penghuni', [
    { label: 'Nama', value: p.nama },
    { label: 'KTP', value: p.ktp },
    { label: 'Telepon', value: p.hp },
    { label: 'Kamar', value: p.kamar },
    { label: 'Pekerjaan', value: p.pekerjaan },
    { label: 'Masuk', value: fmtTanggal(p.tglMasuk) },
    { label: 'Status', value: p.status, tag: p.status }
  ]);
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: KONTRAK SEWA (leases.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderLeaseStats() {
  const semua = demoData.kontrak;
  const aktif = semua.filter(k => k.status === 'Aktif').length;
  const segera = semua.filter(k => k.status === 'Segera Berakhir').length;
  const berakhir = semua.filter(k => k.status === 'Berakhir').length;
  $('#statLsTotal').textContent = semua.length;
  $('#statLsTotalSub').textContent = 'total kontrak';
  $('#statLsAktif').textContent = aktif;
  $('#statLsAktifSub').textContent = 'kontrak berjalan';
  $('#statLsSegera').textContent = segera;
  $('#statLsSegeraSub').textContent = 'berakhir bulan ini';
  $('#statLsBerakhir').textContent = berakhir;
  $('#statLsBerakhirSub').textContent = 'sudah berakhir';
}

function renderLeasesTable() {
  const q = F('leases-q', '').trim().toLowerCase();
  const s = F('leases-status', 'Semua');
  const bln = F('leases-bulan', '');
  const list = demoData.kontrak.filter(k =>
    (s === 'Semua' || k.status === s) &&
    (!bln || (k.sampai || '').startsWith(bln)) &&
    (!q || (k.no + ' ' + k.penghuni + ' ' + k.kamar).toLowerCase().includes(q))
  );
  $('#leasesCount').textContent = list.length + ' kontrak ditampilkan';

  $('#leasesBody').innerHTML = list.length ? list.map(k => `
    <tr>
      <td style="font-weight:600">${esc(k.no)}</td>
      <td>${esc(k.penghuni)}</td>
      <td>${esc(k.kamar)}</td>
      <td class="muted">${fmtTanggal(k.mulai)}</td>
      <td class="muted">${fmtTanggal(k.sampai)}</td>
      <td>${fmtRp(k.harga)}</td>
      <td class="muted">${fmtRp(k.deposit)}</td>
      <td><span class="tag ${tagClass(k.status)}">${k.status}</span></td>
      <td><div class="row-actions">
        <button class="btn btn-secondary" data-action="lease-detail" data-id="${k.id}" title="Detail">${icon('eye', 13)}</button>
        <button class="btn btn-secondary" data-action="lease-edit" data-id="${k.id}" title="Edit">${icon('edit', 13)}</button>
        <button class="btn btn-ghost" data-action="lease-delete" data-id="${k.id}" title="Hapus">${icon('trash', 13)}</button>
      </div></td>
    </tr>`).join('')
    : '<tr><td colspan="9" class="empty">Tidak ada kontrak yang cocok dengan filter</td></tr>';
}

function openLeaseModal(id) {
  const form = $('#modalLease form');
  const k = id ? demoData.kontrak.find(x => x.id === id) : null;
  form.dataset.id = id || '';
  $('#leaseModalTitle').textContent = k ? 'Edit Kontrak ' + k.no : 'Tambah Kontrak';
  if (k) {
    $('#fLsPenghuni').value = k.penghuni; $('#fLsKamar').value = k.kamar;
    $('#fLsMulai').value = k.mulai; $('#fLsSampai').value = k.sampai;
    $('#fLsHarga').value = k.harga; $('#fLsDeposit').value = k.deposit; $('#fLsStatus').value = k.status;
  } else {
    form.reset();
    $('#fLsStatus').value = 'Aktif';
    $('#fLsHarga').value = demoData.pengaturan.tarif.Standar;
    $('#fLsDeposit').value = demoData.pengaturan.tarif.Standar;
  }
  openModal('modalLease');
}

function saveLease(form) {
  const d = Object.fromEntries(new FormData(form));
  const id = form.dataset.id;
  if (!d.penghuni) return showToast('Penghuni wajib dipilih', 'error');
  if (!d.kamar) return showToast('Kamar wajib dipilih', 'error');
  const harga = Number(d.harga) || 0;
  if (harga <= 0) return showToast('Harga bulanan tidak valid', 'error');
  if (!d.mulai || !d.sampai) return showToast('Tanggal mulai dan berakhir wajib diisi', 'error');
  if (d.sampai < d.mulai) return showToast('Tanggal berakhir harus setelah tanggal mulai', 'error');

  const no = id ? (demoData.kontrak.find(x => x.id === id) || {}).no
    : 'KS-' + new Date().getFullYear() + '-' + String(demoData.kontrak.length + 1).padStart(3, '0');
  const rec = {
    id: id || uid('ks'), no, penghuni: d.penghuni, kamar: d.kamar,
    mulai: d.mulai, sampai: d.sampai, harga, deposit: Number(d.deposit) || 0, status: d.status
  };
  if (id) {
    const i = demoData.kontrak.findIndex(x => x.id === id);
    demoData.kontrak[i] = { ...demoData.kontrak[i], ...rec };
  } else {
    demoData.kontrak.push(rec);
  }
  closeDialog(); refresh();
  showToast('Kontrak ' + no + (id ? ' diperbarui' : ' ditambahkan'), 'success');
}

function deleteLease(id) {
  const k = demoData.kontrak.find(x => x.id === id);
  if (!k) return;
  confirmDlg('Hapus Kontrak ' + k.no + '?', 'Kontrak ini akan dihapus permanen dari daftar.', 'Hapus', () => {
    demoData.kontrak = demoData.kontrak.filter(x => x.id !== id);
    refresh();
    showToast('Kontrak ' + k.no + ' dihapus', 'success');
  });
}

function leaseDetail(id) {
  const k = demoData.kontrak.find(x => x.id === id);
  if (!k) return;
  openDetail('Detail Kontrak ' + k.no, [
    { label: 'Nomor Kontrak', value: k.no },
    { label: 'Penghuni', value: k.penghuni },
    { label: 'Kamar', value: k.kamar },
    { label: 'Mulai', value: fmtTanggal(k.mulai) },
    { label: 'Berakhir', value: fmtTanggal(k.sampai) },
    { label: 'Harga / Bulan', value: fmtRp(k.harga) },
    { label: 'Deposit', value: fmtRp(k.deposit) },
    { label: 'Status', value: k.status, tag: k.status }
  ]);
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: PEMBAYARAN (payments.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderPaymentStats() {
  const semua = demoData.pembayaran;
  const lunas = semua.filter(b => b.status === 'Lunas').length;
  const pending = semua.filter(b => b.status === 'Menunggu').length;
  const belum = semua.filter(b => b.status === 'Belum Bayar').length;
  const terlambat = semua.filter(b => b.status === 'Terlambat').length;
  $('#statPayTotal').textContent = semua.length;
  $('#statPayTotalSub').textContent = 'total catatan pembayaran';
  $('#statPayLunas').textContent = lunas;
  $('#statPayLunasSub').textContent = 'pembayaran selesai';
  $('#statPayPending').textContent = pending;
  $('#statPayPendingSub').textContent = 'menunggu verifikasi';
  $('#statPayBelum').textContent = belum;
  $('#statPayBelumSub').textContent = 'belum dibayar';
  $('#statPayOverdue').textContent = terlambat;
  $('#statPayOverdueSub').textContent = 'melewati jatuh tempo';
}

function renderPaymentsTable() {
  const q = F('payments-q', '').trim().toLowerCase();
  const s = F('payments-status', 'Semua');
  const pr = F('payments-periode', 'Semua');
  const mt = F('payments-metode', 'Semua');
  const list = demoData.pembayaran.filter(b =>
    (s === 'Semua' || b.status === s) &&
    (pr === 'Semua' || b.periode === pr) &&
    (mt === 'Semua' || b.metode === mt) &&
    (!q || (b.inv + ' ' + b.penghuni + ' ' + b.kamar + ' ' + b.periode).toLowerCase().includes(q))
  ).sort((a, b) => (b.jatuhTempo || '').localeCompare(a.jatuhTempo || ''));
  $('#paymentsCount').textContent = list.length + ' pembayaran ditampilkan';

  $('#paymentsBody').innerHTML = list.length ? list.map(b => `
    <tr>
      <td style="font-weight:600">${esc(b.inv)}</td>
      <td>${esc(b.penghuni)}</td>
      <td>${esc(b.kamar)}</td>
      <td class="muted">${esc(b.periode)}</td>
      <td style="font-weight:600">${fmtRp(b.jumlah)}</td>
      <td class="muted">${fmtTanggal(b.jatuhTempo)}</td>
      <td class="muted">${fmtTanggal(b.tgl)}</td>
      <td class="muted">${esc(b.metode)}</td>
      <td><span class="tag ${tagClass(b.status)}">${b.status}</span></td>
      <td><div class="row-actions">
        ${b.status !== 'Lunas' ? `<button class="btn btn-secondary" data-action="pay-verify" data-id="${b.id}" title="Verifikasi">${icon('check', 13)} Verifikasi</button>` : ''}
        <button class="btn btn-secondary" data-action="pay-detail" data-id="${b.id}" title="Detail">${icon('eye', 13)}</button>
        <button class="btn btn-secondary" data-action="pay-edit" data-id="${b.id}" title="Edit">${icon('edit', 13)}</button>
        <button class="btn btn-ghost" data-action="pay-delete" data-id="${b.id}" title="Hapus">${icon('trash', 13)}</button>
      </div></td>
    </tr>`).join('')
    : '<tr><td colspan="10" class="empty">Tidak ada pembayaran yang cocok dengan filter</td></tr>';
}

function openPayModal(id) {
  const form = $('#modalPay form');
  const b = id ? demoData.pembayaran.find(x => x.id === id) : null;
  form.dataset.id = id || '';
  $('#payModalTitle').textContent = b ? 'Edit Pembayaran ' + b.inv : 'Tambah Pembayaran';
  if (b) {
    $('#fPayPenghuni').value = b.penghuni; $('#fPayKamar').value = b.kamar;
    $('#fPayPeriode').value = b.periode; $('#fPayJumlah').value = b.jumlah;
    $('#fPayJatuh').value = b.jatuhTempo; $('#fPayTgl').value = b.tgl;
    $('#fPayMetode').value = b.metode; $('#fPayStatus').value = b.status;
  } else {
    form.reset();
    $('#fPayPeriode').value = demoData.bulanan[demoData.bulanan.length - 1];
    $('#fPayJatuh').value = new Date().toISOString().slice(0, 10);
    $('#fPayStatus').value = 'Lunas';
    $('#fPayMetode').value = demoData.pengaturan.pembayaran.metode;
    fillPayFromTenant();
  }
  openModal('modalPay');
}

function fillPayFromTenant() {
  const sel = $('#fPayPenghuni');
  if (!sel) return;
  const p = demoData.penghuni.find(x => x.nama === sel.value);
  $('#fPayKamar').value = p ? p.kamar : '';
  if (p) {
    const k = demoData.kamar.find(x => x.no === p.kamar);
    if (k) $('#fPayJumlah').value = k.harga;
  }
}

function savePay(form) {
  const d = Object.fromEntries(new FormData(form));
  const id = form.dataset.id;
  const jumlah = Number(d.jumlah) || 0;
  if (!d.penghuni) return showToast('Penghuni wajib dipilih', 'error');
  if (jumlah <= 0) return showToast('Jumlah pembayaran tidak valid', 'error');
  const inv = id ? (demoData.pembayaran.find(x => x.id === id) || {}).inv
    : 'INV-' + new Date().getFullYear() + '-' + String(demoData.pembayaran.length + 1).padStart(3, '0');
  const rec = {
    id: id || uid('b'), inv, penghuni: d.penghuni, kamar: d.kamar || '—',
    periode: d.periode, jumlah, jatuhTempo: d.jatuhTempo,
    tgl: d.tgl || '', metode: d.metode, status: d.status
  };
  if (id) {
    const i = demoData.pembayaran.findIndex(x => x.id === id);
    demoData.pembayaran[i] = { ...demoData.pembayaran[i], ...rec };
  } else {
    demoData.pembayaran.push(rec);
  }
  closeDialog(); refresh();
  showToast('Pembayaran ' + inv + ' tersimpan', 'success');
}

function payVerify(id) {
  const b = demoData.pembayaran.find(x => x.id === id);
  if (!b) return;
  confirmDlg('Verifikasi Pembayaran', 'Tandai ' + b.inv + ' (' + b.penghuni + ' · ' + b.periode + ') sebagai Lunas?', 'Verifikasi', () => {
    b.status = 'Lunas';
    b.tgl = b.tgl || new Date().toISOString().slice(0, 10);
    refresh();
    showToast(b.inv + ' diverifikasi & ditandai Lunas', 'success');
  });
}

function deletePay(id) {
  const b = demoData.pembayaran.find(x => x.id === id);
  if (!b) return;
  confirmDlg('Hapus catatan pembayaran?', b.inv + ' · ' + b.penghuni + ' · ' + fmtRp(b.jumlah), 'Hapus', () => {
    demoData.pembayaran = demoData.pembayaran.filter(x => x.id !== id);
    refresh();
    showToast('Catatan pembayaran dihapus', 'success');
  });
}

function payDetail(id) {
  const b = demoData.pembayaran.find(x => x.id === id);
  if (!b) return;
  openDetail('Detail Pembayaran ' + b.inv, [
    { label: 'Invoice', value: b.inv },
    { label: 'Penghuni', value: b.penghuni },
    { label: 'Kamar', value: b.kamar },
    { label: 'Periode', value: b.periode },
    { label: 'Jumlah', value: fmtRp(b.jumlah) },
    { label: 'Jatuh Tempo', value: fmtTanggal(b.jatuhTempo) },
    { label: 'Tgl Bayar', value: fmtTanggal(b.tgl) },
    { label: 'Metode', value: b.metode },
    { label: 'Status', value: b.status, tag: b.status }
  ]);
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: MAINTENANCE (maintenance.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderMaintenanceStats() {
  const semua = demoData.maintenance;
  const baru = semua.filter(m => m.status === 'Baru').length;
  const proses = semua.filter(m => m.status === 'Diproses').length;
  const selesai = semua.filter(m => m.status === 'Selesai').length;
  const batal = semua.filter(m => m.status === 'Dibatalkan').length;
  $('#statMtTotal').textContent = semua.length;
  $('#statMtTotalSub').textContent = 'total laporan';
  $('#statMtBaru').textContent = baru;
  $('#statMtBaruSub').textContent = 'menunggu tindakan';
  $('#statMtProses').textContent = proses;
  $('#statMtProsesSub').textContent = 'sedang dikerjakan';
  $('#statMtSelesai').textContent = selesai;
  $('#statMtSelesaiSub').textContent = 'sudah selesai';
  $('#statMtBatal').textContent = batal;
  $('#statMtBatalSub').textContent = 'dibatalkan';
}

function renderMaintenanceTable() {
  const q = F('maintenance-q', '').trim().toLowerCase();
  const pr = F('maintenance-prioritas', 'Semua');
  const s = F('maintenance-status', 'Semua');
  const list = demoData.maintenance.filter(m =>
    (pr === 'Semua' || m.prioritas === pr) &&
    (s === 'Semua' || m.status === s) &&
    (!q || (m.judul + ' ' + m.kamar + ' ' + m.penghuni).toLowerCase().includes(q))
  ).sort((a, b) => (b.dilaporkan || '').localeCompare(a.dilaporkan || ''));
  $('#maintenanceCount').textContent = list.length + ' laporan ditampilkan';

  $('#maintenanceBody').innerHTML = list.length ? list.map(m => `
    <tr>
      <td style="font-weight:600">${esc(m.judul)}</td>
      <td>${esc(m.kamar)}</td>
      <td>${esc(m.penghuni)}</td>
      <td><span class="tag ${tagClass(m.prioritas)}">${m.prioritas}</span></td>
      <td><span class="tag ${tagClass(m.status)}">${m.status}</span></td>
      <td class="muted">${fmtTanggal(m.dilaporkan)}</td>
      <td class="muted">${m.biaya ? fmtRp(m.biaya) : '—'}</td>
      <td class="muted">${esc(m.ditangani)}</td>
      <td><div class="row-actions">
        ${m.status === 'Baru' ? `<button class="btn btn-secondary" data-action="maint-next" data-id="${m.id}" title="Proses">${icon('wrench', 13)} Proses</button>` : ''}
        ${m.status === 'Diproses' ? `<button class="btn btn-secondary" data-action="maint-next" data-id="${m.id}" title="Selesai">${icon('check', 13)} Selesai</button>` : ''}
        <button class="btn btn-secondary" data-action="maint-detail" data-id="${m.id}" title="Detail">${icon('eye', 13)}</button>
        <button class="btn btn-secondary" data-action="maint-edit" data-id="${m.id}" title="Edit">${icon('edit', 13)}</button>
        <button class="btn btn-ghost" data-action="maint-delete" data-id="${m.id}" title="Hapus">${icon('trash', 13)}</button>
      </div></td>
    </tr>`).join('')
    : '<tr><td colspan="9" class="empty">Tidak ada laporan yang cocok dengan filter</td></tr>';
}

function openMaintModal(id) {
  const form = $('#modalMaint form');
  const m = id ? demoData.maintenance.find(x => x.id === id) : null;
  form.dataset.id = id || '';
  $('#maintModalTitle').textContent = m ? 'Edit Laporan' : 'Tambah Laporan';
  if (m) {
    $('#fMtJudul').value = m.judul; $('#fMtKamar').value = m.kamar;
    $('#fMtPenghuni').value = m.penghuni; $('#fMtPrioritas').value = m.prioritas;
    $('#fMtStatus').value = m.status; $('#fMtBiaya').value = m.biaya || '';
    $('#fMtDitangani').value = m.ditangani === '—' ? '' : m.ditangani;
  } else {
    form.reset();
    $('#fMtPrioritas').value = 'Sedang';
    $('#fMtStatus').value = 'Baru';
  }
  openModal('modalMaint');
}

function saveMaint(form) {
  const d = Object.fromEntries(new FormData(form));
  const id = form.dataset.id;
  if (!d.judul.trim()) return showToast('Judul laporan wajib diisi', 'error');
  if (!d.kamar) return showToast('Kamar / lokasi wajib dipilih', 'error');
  const lama = id ? (demoData.maintenance.find(x => x.id === id) || {}) : {};
  const rec = {
    id: id || uid('m'), judul: d.judul.trim(), kamar: d.kamar,
    penghuni: d.penghuni || '—', prioritas: d.prioritas, status: d.status,
    dilaporkan: lama.dilaporkan || new Date().toISOString().slice(0, 10),
    biaya: Number(d.biaya) || 0, ditangani: d.ditangani.trim() || '—'
  };
  if (id) {
    const i = demoData.maintenance.findIndex(x => x.id === id);
    demoData.maintenance[i] = { ...demoData.maintenance[i], ...rec };
  } else {
    demoData.maintenance.unshift(rec);
  }
  closeDialog(); refresh();
  showToast(id ? 'Laporan diperbarui' : 'Laporan baru ditambahkan', 'success');
}

function maintNext(id) {
  const m = demoData.maintenance.find(x => x.id === id);
  if (!m) return;
  if (m.status === 'Baru') { m.status = 'Diproses'; showToast('Laporan masuk antrean proses', 'success'); }
  else if (m.status === 'Diproses') { m.status = 'Selesai'; showToast('Perbaikan selesai 🎉', 'success'); }
  refresh();
}

function deleteMaint(id) {
  const m = demoData.maintenance.find(x => x.id === id);
  if (!m) return;
  confirmDlg('Hapus laporan?', m.judul, 'Hapus', () => {
    demoData.maintenance = demoData.maintenance.filter(x => x.id !== id);
    refresh();
    showToast('Laporan dihapus', 'success');
  });
}

function maintDetail(id) {
  const m = demoData.maintenance.find(x => x.id === id);
  if (!m) return;
  openDetail('Detail Laporan', [
    { label: 'Judul', value: m.judul },
    { label: 'Kamar / Lokasi', value: m.kamar },
    { label: 'Penghuni', value: m.penghuni },
    { label: 'Prioritas', value: m.prioritas, tag: m.prioritas },
    { label: 'Status', value: m.status, tag: m.status },
    { label: 'Dilaporkan', value: fmtTanggal(m.dilaporkan) },
    { label: 'Biaya', value: m.biaya ? fmtRp(m.biaya) : '—' },
    { label: 'Ditangani oleh', value: m.ditangani }
  ]);
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: PENGELUARAN (expenses.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderExpenseStats() {
  const semua = demoData.pengeluaran;
  const total = semua.reduce((s, e) => s + e.jumlah, 0);
  const namaBln = demoData.bulanan[demoData.bulanan.length - 1];
  const bulanIni = semua.filter(e => (e.tgl || '').startsWith(bulanIniIso())).reduce((s, e) => s + e.jumlah, 0);
  const terbesar = semua.reduce((a, b) => (b.jumlah > (a ? a.jumlah : 0) ? b : a), null);
  $('#statExTotal').textContent = fmtRp(total);
  $('#statExTotalSub').textContent = 'total semua pengeluaran';
  $('#statExBulan').textContent = fmtRp(bulanIni);
  $('#statExBulanSub').textContent = 'bulan ' + namaBln;
  $('#statExBesar').textContent = terbesar ? fmtRp(terbesar.jumlah) : '—';
  $('#statExBesarSub').textContent = terbesar ? terbesar.judul : '—';
  $('#statExCount').textContent = semua.length;
  $('#statExCountSub').textContent = 'total catatan';
}

function renderExpensesTable() {
  const q = F('expenses-q', '').trim().toLowerCase();
  const kat = F('expenses-kategori', 'Semua');
  const bln = F('expenses-bulan', '');
  const list = demoData.pengeluaran.filter(e =>
    (kat === 'Semua' || e.kategori === kat) &&
    (!bln || (e.tgl || '').startsWith(bln)) &&
    (!q || (e.judul + ' ' + e.deskripsi + ' ' + e.kategori).toLowerCase().includes(q))
  ).sort((a, b) => (b.tgl || '').localeCompare(a.tgl || ''));
  $('#expensesCount').textContent = list.length + ' pengeluaran ditampilkan';

  $('#expensesBody').innerHTML = list.length ? list.map(e => `
    <tr>
      <td style="font-weight:600">${esc(e.judul)}</td>
      <td class="muted">${esc(e.deskripsi)}</td>
      <td><span class="tag ${tagClass(e.kategori)}">${esc(e.kategori)}</span></td>
      <td style="font-weight:600">${fmtRp(e.jumlah)}</td>
      <td class="muted">${fmtTanggal(e.tgl)}</td>
      <td class="muted">${esc(e.dibuatOleh)}</td>
      <td><div class="row-actions">
        <button class="btn btn-secondary" data-action="exp-detail" data-id="${e.id}" title="Detail">${icon('eye', 13)}</button>
        <button class="btn btn-secondary" data-action="exp-edit" data-id="${e.id}" title="Edit">${icon('edit', 13)}</button>
        <button class="btn btn-ghost" data-action="exp-delete" data-id="${e.id}" title="Hapus">${icon('trash', 13)}</button>
      </div></td>
    </tr>`).join('')
    : '<tr><td colspan="7" class="empty">Tidak ada pengeluaran yang cocok dengan filter</td></tr>';
}

function openExpModal(id) {
  const form = $('#modalExp form');
  const e = id ? demoData.pengeluaran.find(x => x.id === id) : null;
  form.dataset.id = id || '';
  $('#expModalTitle').textContent = e ? 'Edit Pengeluaran' : 'Tambah Pengeluaran';
  if (e) {
    $('#fExJudul').value = e.judul; $('#fExDeskripsi').value = e.deskripsi;
    $('#fExJumlah').value = e.jumlah; $('#fExTgl').value = e.tgl; $('#fExKategori').value = e.kategori;
  } else {
    form.reset();
    $('#fExTgl').value = new Date().toISOString().slice(0, 10);
    $('#fExKategori').value = 'Listrik';
  }
  openModal('modalExp');
}

function saveExp(form) {
  const d = Object.fromEntries(new FormData(form));
  const id = form.dataset.id;
  if (!d.judul.trim()) return showToast('Judul pengeluaran wajib diisi', 'error');
  const jumlah = Number(d.jumlah) || 0;
  if (jumlah <= 0) return showToast('Jumlah pengeluaran tidak valid', 'error');
  const rec = {
    id: id || uid('e'), judul: d.judul.trim(), deskripsi: d.deskripsi.trim(),
    jumlah, tgl: d.tgl || new Date().toISOString().slice(0, 10),
    kategori: d.kategori, dibuatOleh: demoData.user.nama
  };
  if (id) {
    const i = demoData.pengeluaran.findIndex(x => x.id === id);
    demoData.pengeluaran[i] = { ...demoData.pengeluaran[i], ...rec };
  } else {
    demoData.pengeluaran.push(rec);
  }
  closeDialog(); refresh();
  showToast('Pengeluaran ' + (id ? 'diperbarui' : 'ditambahkan'), 'success');
}

function deleteExp(id) {
  const e = demoData.pengeluaran.find(x => x.id === id);
  if (!e) return;
  confirmDlg('Hapus pengeluaran?', e.judul + ' · ' + fmtRp(e.jumlah), 'Hapus', () => {
    demoData.pengeluaran = demoData.pengeluaran.filter(x => x.id !== id);
    refresh();
    showToast('Pengeluaran dihapus', 'success');
  });
}

function expDetail(id) {
  const e = demoData.pengeluaran.find(x => x.id === id);
  if (!e) return;
  openDetail('Detail Pengeluaran', [
    { label: 'Judul', value: e.judul },
    { label: 'Deskripsi', value: e.deskripsi || '—' },
    { label: 'Kategori', value: e.kategori },
    { label: 'Jumlah', value: fmtRp(e.jumlah) },
    { label: 'Tanggal', value: fmtTanggal(e.tgl) },
    { label: 'Dibuat oleh', value: e.dibuatOleh }
  ]);
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: FASILITAS (facilities.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderFacilityStats() {
  const semua = demoData.fasilitas;
  const tersedia = semua.filter(f => f.status === 'Tersedia').length;
  const perbaikan = semua.filter(f => f.status === 'Perbaikan').length;
  $('#statFacTotal').textContent = semua.length;
  $('#statFacTotalSub').textContent = 'jenis fasilitas';
  $('#statFacTersedia').textContent = tersedia;
  $('#statFacTersediaSub').textContent = 'dalam kondisi baik';
  $('#statFacPerbaikan').textContent = perbaikan;
  $('#statFacPerbaikanSub').textContent = 'perlu ditangani';
}

function renderFacilitiesGrid() {
  const q = F('facilities-q', '').trim().toLowerCase();
  const kat = F('facilities-kategori', 'Semua');
  const list = demoData.fasilitas.filter(f =>
    (kat === 'Semua' || f.kategori === kat) &&
    (!q || (f.nama + ' ' + f.kategori).toLowerCase().includes(q))
  );
  $('#facilitiesCount').textContent = list.length + ' fasilitas ditampilkan';

  $('#facilitiesGrid').innerHTML = list.length ? list.map(f => `
    <div class="card elev-sm facility-card" style="border-top:2px solid ${f.status === 'Perbaikan' ? 'var(--color-accent)' : 'var(--color-neutral-900)'}">
      <div class="flex-between">
        <div class="fac-icon">${icon(f.icon || 'box', 18)}</div>
        <span class="tag ${tagClass(f.status)}">${f.status}</span>
      </div>
      <div class="fac-name">${esc(f.nama)}</div>
      <div class="fac-meta">${esc(f.kategori)} · ${esc(f.jumlah)}</div>
      <div class="room-actions" style="border-top:1px solid var(--color-divider);padding-top:10px">
        <button class="btn btn-secondary" data-action="fac-edit" data-id="${f.id}">${icon('edit', 13)} Edit</button>
        <button class="btn btn-ghost" data-action="fac-delete" data-id="${f.id}">${icon('trash', 13)} Hapus</button>
      </div>
    </div>`).join('')
    : '<div class="card elev-sm"><div class="empty">Tidak ada fasilitas yang cocok dengan filter</div></div>';
}

function openFacModal(id) {
  const form = $('#modalFac form');
  const f = id ? demoData.fasilitas.find(x => x.id === id) : null;
  form.dataset.id = id || '';
  $('#facModalTitle').textContent = f ? 'Edit Fasilitas' : 'Tambah Fasilitas';
  if (f) {
    $('#fFacNama').value = f.nama; $('#fFacKategori').value = f.kategori;
    $('#fFacJumlah').value = f.jumlah; $('#fFacStatus').value = f.status;
  } else {
    form.reset();
    $('#fFacStatus').value = 'Tersedia';
    $('#fFacKategori').value = 'Umum';
  }
  openModal('modalFac');
}

function saveFac(form) {
  const d = Object.fromEntries(new FormData(form));
  const id = form.dataset.id;
  if (!d.nama.trim()) return showToast('Nama fasilitas wajib diisi', 'error');
  const rec = {
    id: id || uid('f'), nama: d.nama.trim(), kategori: d.kategori,
    jumlah: d.jumlah.trim() || '—', status: d.status,
    icon: (id ? (demoData.fasilitas.find(x => x.id === id) || {}) : {}).icon || 'box'
  };
  if (id) {
    const i = demoData.fasilitas.findIndex(x => x.id === id);
    demoData.fasilitas[i] = { ...demoData.fasilitas[i], ...rec };
  } else {
    demoData.fasilitas.push(rec);
  }
  closeDialog(); refresh();
  showToast('Fasilitas ' + (id ? 'diperbarui' : 'ditambahkan'), 'success');
}

function deleteFac(id) {
  const f = demoData.fasilitas.find(x => x.id === id);
  if (!f) return;
  confirmDlg('Hapus fasilitas ' + f.nama + '?', 'Fasilitas ini akan dihapus permanen dari daftar.', 'Hapus', () => {
    demoData.fasilitas = demoData.fasilitas.filter(x => x.id !== id);
    refresh();
    showToast('Fasilitas ' + f.nama + ' dihapus', 'success');
  });
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: PROFIL (profile.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderProfile() {
  const u = demoData.user;
  $('#profileAvatar').textContent = initials(u.nama);
  $('#profileName').textContent = u.nama;
  $('#profileEmail').textContent = demoData.pengaturan.email;
  $('#profilePhone').textContent = demoData.pengaturan.telp;
  $('#profileRole').textContent = u.role;
  $('#prNama').value = u.nama;
  $('#prEmail').value = demoData.pengaturan.email;
  $('#prHp').value = demoData.pengaturan.telp;
}

function saveProfile(form) {
  const d = Object.fromEntries(new FormData(form));
  if (!d.nama.trim()) return showToast('Nama wajib diisi', 'error');
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(d.email.trim())) return showToast('Format email tidak valid', 'error');
  demoData.user.nama = d.nama.trim();
  demoData.pengaturan.email = d.email.trim();
  demoData.pengaturan.telp = d.hp.trim();
  renderHeader(); renderProfile();
  showToast('Informasi profil diperbarui', 'success');
}

function savePassword(form) {
  const d = Object.fromEntries(new FormData(form));
  if (!d.lama) return showToast('Password lama wajib diisi', 'error');
  if (d.baru.length < 6) return showToast('Password baru minimal 6 karakter', 'error');
  if (d.baru !== d.ulang) return showToast('Konfirmasi password tidak cocok', 'error');
  form.reset();
  showToast('Password berhasil diubah', 'success');
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: PENGATURAN (settings.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderSettings() {
  const p = demoData.pengaturan;
  $('#sNamaKos').value = p.namaKos;
  $('#sAlamat').value = p.alamat;
  $('#sTelp').value = p.telp;
  $('#sEmail').value = p.email;
  $('#sJatuhTempo').value = p.pembayaran.jatuhTempo;
  $('#sMetode').value = p.pembayaran.metode;
  $('#sDenda').value = p.pembayaran.denda;
  $('#sAutoDenda').checked = p.pembayaran.autoDenda;
  $('#sReminder').checked = p.pembayaran.reminder;
  $('#nPayMasuk').checked = p.notifikasi.payMasuk;
  $('#nTagihan').checked = p.notifikasi.tagihan;
  $('#nKeluhan').checked = p.notifikasi.keluhan;
  $('#nKontrak').checked = p.notifikasi.kontrak;
}

function saveKosInfo(form) {
  const d = Object.fromEntries(new FormData(form));
  if (!d.namaKos.trim()) return showToast('Nama kos wajib diisi', 'error');
  demoData.pengaturan.namaKos = d.namaKos.trim();
  demoData.pengaturan.alamat = d.alamat.trim();
  demoData.pengaturan.telp = d.telp.trim();
  demoData.pengaturan.email = d.email.trim();
  renderHeader();
  showToast('Informasi kos diperbarui', 'success');
}

function savePaySettings(form) {
  const d = Object.fromEntries(new FormData(form));
  demoData.pengaturan.pembayaran = {
    jatuhTempo: d.jatuhTempo, metode: d.metode,
    denda: Number(d.denda) || 0, autoDenda: d.autoDenda === 'on', reminder: d.reminder === 'on'
  };
  showToast('Pengaturan pembayaran tersimpan', 'success');
}

function saveNotifSettings(form) {
  const d = Object.fromEntries(new FormData(form));
  demoData.pengaturan.notifikasi = {
    payMasuk: d.payMasuk === 'on', tagihan: d.tagihan === 'on',
    keluhan: d.keluhan === 'on', kontrak: d.kontrak === 'on'
  };
  showToast('Pengaturan notifikasi tersimpan', 'success');
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: DASHBOARD (index.html)
   ════════════════════════════════════════════════════════════════════════ */
function renderStats() {
  const kamar = demoData.kamar;
  const terisi = kamar.filter(k => k.status === 'Terisi').length;
  const kosong = kamar.filter(k => k.status === 'Kosong').length;
  const perbaikan = kamar.filter(k => k.status === 'Perbaikan').length;
  const aktif = demoData.penghuni.filter(p => p.status === 'Aktif').length;

  const g = demoData.grafik;
  const blnAkhir = g[g.length - 1];
  const blnSebelum = g[g.length - 2] || {};
  const naik = blnSebelum.masuk
    ? Math.round((blnAkhir.masuk - blnSebelum.masuk) / blnSebelum.masuk * 100) : 0;

  const tunggak = demoData.pembayaran.filter(b => b.status === 'Belum Bayar' || b.status === 'Terlambat');
  const tunggakTotal = tunggak.reduce((s, b) => s + b.jumlah, 0);

  $('#pageSub').textContent = 'Ringkasan operasional — ' + blnAkhir.bln;
  $('#statTotalKamar').textContent = kamar.length;
  $('#statSubKamar').textContent = `${terisi} terisi · ${kosong} kosong · ${perbaikan} perbaikan`;
  $('#statPenghuniAktif').textContent = aktif;
  $('#statSubPenghuni').textContent = '+2 bulan ini';
  $('#statPendapatan').textContent = fmtRp(blnAkhir.masuk);
  $('#statSubPendapatan').textContent = `${naik >= 0 ? '+' : ''}${naik}% dari ${blnSebelum.bln || 'bulan lalu'}`;
  $('#statTunggak').textContent = fmtRp(tunggakTotal);
  $('#statSubTunggak').textContent = `${tunggak.length} tagihan belum dibayar`;
}

function renderChart() {
  const maxM = Math.max(...demoData.grafik.map(b => b.masuk), 1);
  const bars = demoData.grafik.map((b, i) => {
    const hPx = Math.max(10, Math.round(b.masuk / maxM * 150));
    const hl = i === demoData.grafik.length - 1 ? ' hl' : '';
    const amt = (b.masuk / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 });
    return `<div class="chart-col">
      <div class="chart-val">${amt}</div>
      <div class="chart-bar${hl}" style="height:${hPx}px"></div>
    </div>`;
  }).join('');
  const labels = demoData.grafik.map(b => `<span>${b.bln.slice(0, 3)}</span>`).join('');
  $('#chartBars').innerHTML = bars;
  $('#chartLabels').innerHTML = labels;
}

function renderDonut() {
  const kamar = demoData.kamar;
  const total = kamar.length;
  const terisi = kamar.filter(k => k.status === 'Terisi').length;
  const kosong = kamar.filter(k => k.status === 'Kosong').length;
  const perbaikan = kamar.filter(k => k.status === 'Perbaikan').length;
  const C = 351.86;
  const l1 = total ? terisi / total * C : 0;
  const l2 = total ? kosong / total * C : 0;
  const l3 = total ? perbaikan / total * C : 0;
  const pct = total ? Math.round(terisi / total * 100) : 0;

  const t = $('#donutTerisi'), k = $('#donutKosong'), p = $('#donutPerbaikan');
  t.setAttribute('stroke-dasharray', `${l1.toFixed(1)} ${C.toFixed(1)}`);
  k.setAttribute('stroke-dasharray', `${l2.toFixed(1)} ${C.toFixed(1)}`);
  k.setAttribute('stroke-dashoffset', `${(-l1).toFixed(1)}`);
  p.setAttribute('stroke-dasharray', `${l3.toFixed(1)} ${C.toFixed(1)}`);
  p.setAttribute('stroke-dashoffset', `${(-(l1 + l2)).toFixed(1)}`);
  $('#donutPct').textContent = pct + '%';
  $('#legendTerisi').textContent = terisi;
  $('#legendKosong').textContent = kosong;
  $('#legendPerbaikan').textContent = perbaikan;
}

function renderPayments() {
  // Pembayaran yang sudah ada tanggal bayar tampil di atas, sisanya urut jatuh tempo
  const list = [...demoData.pembayaran]
    .sort((a, b) => {
      const hasA = !!a.tgl, hasB = !!b.tgl;
      if (hasA !== hasB) return hasA ? -1 : 1;
      return (b.tgl || b.jatuhTempo || '').localeCompare(a.tgl || a.jatuhTempo || '');
    })
    .slice(0, 6);

  $('#paymentsBody').innerHTML = list.length ? list.map(b => `
    <tr>
      <td style="font-weight:600">${esc(b.penghuni)}</td>
      <td>${esc(b.kamar)}</td>
      <td>${esc(b.periode)}</td>
      <td class="muted">${fmtTanggal(b.tgl)}</td>
      <td class="muted">${esc(b.metode)}</td>
      <td style="font-weight:600">${fmtRp(b.jumlah)}</td>
      <td><span class="tag ${tagClass(b.status)}">${b.status}</span></td>
      <td><div class="row-actions">
        ${b.status !== 'Lunas'
          ? `<button class="btn btn-secondary" data-action="pay-verify" data-id="${b.id}" title="Tandai lunas">${icon('check', 12)} Lunas</button>`
          : ''}
        <button class="btn btn-secondary" data-action="pay-edit" data-id="${b.id}" title="Edit">${icon('edit', 12)}</button>
        <button class="btn btn-ghost" data-action="pay-delete" data-id="${b.id}" title="Hapus">${icon('trash', 12)}</button>
      </div></td>
    </tr>`).join('')
    : '<tr><td colspan="8" class="empty">Belum ada pembayaran</td></tr>';
}

function renderComplaints() {
  const list = demoData.maintenance.slice(0, 4);

  $('#complaintsList').innerHTML = list.length ? list.map(m => `
    <div class="complaint-row">
      <div class="flex-between">
        <span style="font-size:14px;font-weight:600">${esc(m.judul)}</span>
        <span class="tag ${tagClass(m.status)}">${m.status}</span>
      </div>
      <div class="complaint-meta">
        <span>${icon('kamar', 13)} ${esc(m.kamar)}</span>
        <span>${icon('penghuni', 13)} ${esc(m.penghuni)}</span>
        <span>${fmtTanggal(m.dilaporkan)}</span>
        <span class="tag ${tagClass(m.prioritas)}">${m.prioritas}</span>
      </div>
      <div class="complaint-actions">
        ${m.status === 'Baru' ? `<button class="btn btn-secondary" data-action="maint-next" data-id="${m.id}" style="font-size:12px">Proses</button>` : ''}
        ${m.status === 'Diproses' ? `<button class="btn btn-secondary" data-action="maint-next" data-id="${m.id}" style="font-size:12px">${icon('check', 12)} Tandai Selesai</button>` : ''}
        <button class="btn btn-ghost" data-action="maint-delete" data-id="${m.id}" style="font-size:12px">${icon('trash', 12)}</button>
      </div>
    </div>`).join('')
    : '<div class="empty">Belum ada keluhan — semua beres 👍</div>';
}

function renderRooms() {
  const s = F('kamar-status', 'Semua');
  const t = F('kamar-tipe', 'Semua');
  const q = F('kamar-q', '').trim().toLowerCase();
  const list = demoData.kamar.filter(k =>
    (s === 'Semua' || k.status === s) &&
    (t === 'Semua' || k.tipe === t) &&
    (!q || (k.no + k.tipe + k.penghuni).toLowerCase().includes(q))
  );
  const lantai = [...new Set(demoData.kamar.map(k => k.lantai))].sort();
  $('#kamarSub').textContent = `${demoData.kamar.length} kamar · ${lantai.length} lantai`;
  $('#kamarCount').textContent = `${list.length} kamar ditampilkan`;

  $('#roomsGrid').innerHTML = list.length ? list.map(k => `
    <div class="card elev-sm room-card" style="border-top:2px solid ${k.status === 'Terisi' ? 'var(--color-neutral-900)' : k.status === 'Kosong' ? 'var(--color-neutral-300)' : 'var(--color-accent)'}">
      <div class="flex-between">
        <div class="room-no">${esc(k.no)}</div>
        <span class="tag ${tagClass(k.status)}">${k.status}</span>
      </div>
      <div class="small muted">${esc(k.tipe)} · Lantai ${k.lantai}</div>
      <div style="font-size:15px;font-weight:600">${fmtRp(k.harga)}<span style="font-size:11px;font-weight:400;color:var(--color-neutral-600)"> /bulan</span></div>
      <div style="font-size:12px;min-height:18px;color:${k.penghuni ? 'var(--color-neutral-700)' : 'var(--color-neutral-500)'}">${esc(k.penghuni || '— tersedia')}</div>
      <div class="room-actions">
        <button class="btn btn-secondary" data-action="room-edit" data-id="${k.id}">${icon('edit', 13)} Edit</button>
        <button class="btn btn-ghost" data-action="room-delete" data-id="${k.id}">${icon('trash', 13)} Hapus</button>
      </div>
    </div>`).join('')
    : '<div class="card elev-sm"><div class="empty">Tidak ada kamar yang cocok dengan filter</div></div>';
}

/* --------------------------- PENCARIAN GLOBAL --------------------------- */
function searchAll(q) {
  const s = q.toLowerCase();
  if (!s) return [];
  const out = [];
  demoData.kamar.forEach(k => {
    if ((k.no + ' ' + k.tipe + ' ' + k.penghuni).toLowerCase().includes(s))
      out.push({ label: k.no + ' — ' + k.tipe, sub: k.penghuni || 'tersedia', href: 'rooms.html' });
  });
  demoData.penghuni.forEach(p => {
    if ((p.nama + ' ' + p.kamar).toLowerCase().includes(s))
      out.push({ label: p.nama, sub: 'Kamar ' + p.kamar, href: 'tenants.html' });
  });
  demoData.kontrak.forEach(k => {
    if ((k.no + ' ' + k.penghuni).toLowerCase().includes(s))
      out.push({ label: k.no, sub: k.penghuni, href: 'leases.html' });
  });
  demoData.pembayaran.forEach(b => {
    if ((b.inv + ' ' + b.penghuni + ' ' + b.periode).toLowerCase().includes(s))
      out.push({ label: b.inv, sub: b.penghuni + ' · ' + fmtRp(b.jumlah), href: 'payments.html' });
  });
  demoData.maintenance.forEach(m => {
    if ((m.judul + ' ' + m.kamar).toLowerCase().includes(s))
      out.push({ label: m.judul, sub: m.kamar, href: 'maintenance.html' });
  });
  demoData.pengeluaran.forEach(e => {
    if ((e.judul + ' ' + e.kategori).toLowerCase().includes(s))
      out.push({ label: e.judul, sub: fmtRp(e.jumlah), href: 'expenses.html' });
  });
  demoData.fasilitas.forEach(f => {
    if (f.nama.toLowerCase().includes(s))
      out.push({ label: f.nama, sub: f.kategori, href: 'facilities.html' });
  });
  return out.slice(0, 8);
}

function runSearch() {
  const q = $('#globalSearch').value.trim();
  const res = searchAll(q);
  if (!q || !res.length) {
    $('#searchResults').classList.remove('show');
    $('#searchResults').innerHTML = '';
    return;
  }
  $('#searchResults').innerHTML = res.map(r =>
    `<button type="button" class="sr-item" data-action="go" data-href="${esc(r.href)}">
      <div>${esc(r.label)}</div><div class="sr-sub">${esc(r.sub)}</div>
    </button>`).join('');
  $('#searchResults').classList.add('show');
}

/* --------------------------- MENU & NAV --------------------------- */
function closeAllMenus() {
  $('#notifMenu').classList.remove('show');
  $('#userMenu').classList.remove('show');
  $('#searchResults').classList.remove('show');
}

function go(href) {
  closeAllMenus();
  document.body.classList.remove('nav-open');
  $('#globalSearch').value = '';
  if (href && href !== '#') window.location.href = href;
}

// Menu sidebar yang belum punya halaman (akan dihubungkan ke route Laravel).
function navPlaceholder(label) {
  closeAllMenus();
  document.body.classList.remove('nav-open');
  $('#globalSearch').value = '';
  showToast('Halaman ' + (label || '') + ' akan dihubungkan ke Laravel — ganti href dengan route', 'info');
}

/* --------------------------- REFRESH SEMUA --------------------------- */
function refresh() {
  renderHeader();
  renderNotifMenu();
  switch (state.page) {
    case 'dashboard': renderStats(); renderChart(); renderDonut(); renderPayments(); renderComplaints(); renderRooms(); break;
    case 'rooms': renderRoomStats(); renderRoomsTable(); break;
    case 'tenants': renderTenantStats(); renderTenantsTable(); break;
    case 'leases': renderLeaseStats(); renderLeasesTable(); break;
    case 'payments': renderPaymentStats(); renderPaymentsTable(); break;
    case 'maintenance': renderMaintenanceStats(); renderMaintenanceTable(); break;
    case 'expenses': renderExpenseStats(); renderExpensesTable(); break;
    case 'facilities': renderFacilityStats(); renderFacilitiesGrid(); break;
    case 'reports': renderReports(); break;
  }
}

/* ══════════════════════════════════════════════════════════════════════════
   HALAMAN: LAPORAN (reports.html)
   ════════════════════════════════════════════════════════════════════════ */
// Data grafik 6 bulan (nilai pendapatan konsisten dengan grafik dashboard).
const reportChart = [
  { bln: 'Februari 2026', masuk: 15200000, keluar: 6100000 },
  { bln: 'Maret 2026', masuk: 16100000, keluar: 5400000 },
  { bln: 'April 2026', masuk: 14800000, keluar: 6900000 },
  { bln: 'Mei 2026', masuk: 17300000, keluar: 5800000 },
  { bln: 'Juni 2026', masuk: 17000000, keluar: 7200000 },
  { bln: 'Juli 2026', masuk: 18450000, keluar: 6300000 }
];

// 'Juli 2026' atau '2026-07-05' → kunci periode '2026-07' (atau '' jika tidak dikenal).
function reportKey(p) {
  const s = String(p || '');
  if (/^\d{4}-\d{2}/.test(s)) return s.slice(0, 7);
  const [bln, thn] = s.split(' ');
  return (BULAN_ISO[bln] && thn) ? thn + '-' + BULAN_ISO[bln] : '';
}

// Apakah record masuk dalam periode filter laporan saat ini?
function inReportPeriod(p) {
  const pr = F('reports-periode', 'Bulan');
  const thn = F('reports-tahun', '2026');
  const bln = F('reports-bulan', 'Juli');
  const key = reportKey(p);
  if (!key) return true;
  if (pr === 'Tahun') return key.slice(0, 4) === String(thn);
  return key === String(thn) + '-' + BULAN_ISO[bln];
}

function renderReports() {
  // Filter "Jenis laporan" ikut memindahkan tab aktif
  const jenis = F('reports-jenis', 'Semua');
  if (['pendapatan', 'pengeluaran', 'pembayaran', 'okupansi', 'maintenance'].includes(jenis)) {
    state.reportTab = jenis;
  }
  renderReportSummary();
  renderReportChart();
  renderReportTabs();
  renderReportTable();
}

function renderReportSummary() {
  const pay = demoData.pembayaran.filter(b => inReportPeriod(b.periode));
  const pendapatan = pay.filter(b => b.status === 'Lunas').reduce((s, b) => s + b.jumlah, 0);
  const pengeluaran = demoData.pengeluaran.filter(e => inReportPeriod(e.tgl)).reduce((s, e) => s + e.jumlah, 0);
  const bersih = pendapatan - pengeluaran;
  const kamar = demoData.kamar;
  const terisi = kamar.filter(k => k.status === 'Terisi').length;
  const okupansi = Math.round(terisi / (kamar.length || 1) * 1000) / 10;

  $('#rptPendapatan').textContent = fmtRp(pendapatan);
  $('#rptPengeluaran').textContent = fmtRp(pengeluaran);
  $('#rptBersih').textContent = bersih < 0 ? '−' + fmtRp(Math.abs(bersih)) : fmtRp(bersih);
  $('#rptHunian').textContent = okupansi + '%';
  $('#rptSubPendapatan').textContent = pay.length + ' transaksi tercatat';
  $('#rptSubPengeluaran').textContent = 'periode terpilih';
  $('#rptSubBersih').textContent = bersih < 0 ? 'perlu perhatian' : 'margin positif';
  $('#rptSubHunian').textContent = terisi + ' dari ' + kamar.length + ' kamar terisi';
}

function renderReportChart() {
  const maxV = Math.max(...reportChart.map(d => Math.max(d.masuk, d.keluar)), 1);
  const bars = reportChart.map(d => {
    const hIn = Math.max(8, Math.round(d.masuk / maxV * 140));
    const hOut = Math.max(8, Math.round(d.keluar / maxV * 140));
    const amt = (d.masuk / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 });
    return `<div class="chart-col">
      <div class="chart-val">${amt}</div>
      <div class="chart-group">
        <div class="chart-bar masuk" style="height:${hIn}px" title="Pendapatan ${fmtRp(d.masuk)}"></div>
        <div class="chart-bar keluar" style="height:${hOut}px" title="Pengeluaran ${fmtRp(d.keluar)}"></div>
      </div>
    </div>`;
  }).join('');
  $('#rptChartBars').innerHTML = bars;
  $('#rptChartLabels').innerHTML = reportChart.map(d => `<span>${d.bln.slice(0, 3)}</span>`).join('');
}

function renderReportTabs() {
  $$('.report-tab').forEach(t => {
    const on = t.dataset.tab === state.reportTab;
    t.classList.toggle('active', on);
    t.setAttribute('aria-selected', on ? 'true' : 'false');
  });
}

function renderReportTable() {
  const thead = $('#rptThead');
  const tbody = $('#rptTbody');
  const okup = $('#rptOkupansi');
  const count = $('#rptCount');
  if (!thead || !tbody) return;
  const empty = (colspan, msg) => `<tr><td colspan="${colspan}" class="empty">${msg}</td></tr>`;

  // --- Okupansi Kamar: ringkasan + tabel kamar ---
  if (state.reportTab === 'okupansi') {
    const kamar = demoData.kamar;
    const terisi = kamar.filter(k => k.status === 'Terisi').length;
    const kosong = kamar.filter(k => k.status === 'Kosong').length;
    const perbaikan = kamar.filter(k => k.status === 'Perbaikan').length;
    const pct = Math.round(terisi / (kamar.length || 1) * 1000) / 10;
    okup.hidden = false;
    okup.innerHTML = `
      <div class="grid-5" style="margin-bottom:16px">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)"><div class="stat-label">Total Kamar</div><div class="stat-value">${kamar.length}</div><div class="stat-sub muted">seluruh kos</div></div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)"><div class="stat-label">Kamar Terisi</div><div class="stat-value">${terisi}</div><div class="stat-sub muted">penghuni aktif</div></div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)"><div class="stat-label">Kamar Kosong</div><div class="stat-value">${kosong}</div><div class="stat-sub muted">siap disewa</div></div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)"><div class="stat-label">Kamar Maintenance</div><div class="stat-value">${perbaikan}</div><div class="stat-sub" style="color:var(--color-accent-700)">dalam perbaikan</div></div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)"><div class="stat-label">Persentase Okupansi</div><div class="stat-value">${pct}%</div><div class="stat-sub muted">${terisi}/${kamar.length} terisi</div></div>
      </div>`;
    thead.innerHTML = '<tr><th>Kamar</th><th>Tipe</th><th>Lantai</th><th>Status</th><th>Penghuni</th><th>Harga / Bulan</th></tr>';
    tbody.innerHTML = kamar.map(k => `
      <tr>
        <td style="font-weight:600">${esc(k.no)}</td>
        <td>${esc(k.tipe)}</td>
        <td class="muted">Lantai ${k.lantai}</td>
        <td><span class="tag ${tagClass(k.status)}">${k.status}</span></td>
        <td>${esc(k.penghuni || '—')}</td>
        <td>${fmtRp(k.harga)}</td>
      </tr>`).join('');
    if (count) count.textContent = kamar.length + ' kamar';
    return;
  }
  okup.hidden = true;

  // --- Pengeluaran ---
  if (state.reportTab === 'pengeluaran') {
    const list = demoData.pengeluaran.filter(e => inReportPeriod(e.tgl));
    thead.innerHTML = '<tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Jumlah</th><th>Dibuat oleh</th></tr>';
    tbody.innerHTML = list.length ? list.map(e => `
      <tr>
        <td class="muted">${fmtTanggal(e.tgl)}</td>
        <td><span class="tag tag-outline">${esc(e.kategori)}</span></td>
        <td>${esc(e.judul)}<div class="small muted">${esc(e.deskripsi || '')}</div></td>
        <td style="font-weight:600">${fmtRp(e.jumlah)}</td>
        <td class="muted">${esc(e.dibuatOleh)}</td>
      </tr>`).join('') : empty(5, 'Tidak ada pengeluaran pada periode ini');
    if (count) count.textContent = list.length + ' baris';
    return;
  }

  // --- Pembayaran ---
  if (state.reportTab === 'pembayaran') {
    const list = demoData.pembayaran.filter(b => inReportPeriod(b.periode))
      .sort((a, b) => (b.tgl || '').localeCompare(a.tgl || ''));
    thead.innerHTML = '<tr><th>Tanggal</th><th>Invoice</th><th>Penghuni</th><th>Kamar</th><th>Periode</th><th>Metode</th><th>Jumlah</th><th>Status</th></tr>';
    tbody.innerHTML = list.length ? list.map(b => `
      <tr>
        <td class="muted">${fmtTanggal(b.tgl || b.jatuhTempo)}</td>
        <td style="font-weight:600">${esc(b.inv)}</td>
        <td>${esc(b.penghuni)}</td>
        <td>${esc(b.kamar)}</td>
        <td class="muted">${esc(b.periode)}</td>
        <td class="muted">${esc(b.metode)}</td>
        <td style="font-weight:600">${fmtRp(b.jumlah)}</td>
        <td><span class="tag ${tagClass(b.status)}">${b.status}</span></td>
      </tr>`).join('') : empty(8, 'Tidak ada pembayaran pada periode ini');
    if (count) count.textContent = list.length + ' baris';
    return;
  }

  // --- Maintenance ---
  if (state.reportTab === 'maintenance') {
    const list = demoData.maintenance.filter(m => inReportPeriod(m.dilaporkan));
    thead.innerHTML = '<tr><th>Tanggal</th><th>Judul Laporan</th><th>Kamar / Lokasi</th><th>Prioritas</th><th>Status</th><th>Biaya</th></tr>';
    tbody.innerHTML = list.length ? list.map(m => `
      <tr>
        <td class="muted">${fmtTanggal(m.dilaporkan)}</td>
        <td style="font-weight:600">${esc(m.judul)}</td>
        <td>${esc(m.kamar)}</td>
        <td><span class="tag ${tagClass(m.prioritas)}">${m.prioritas}</span></td>
        <td><span class="tag ${tagClass(m.status)}">${m.status}</span></td>
        <td>${m.biaya ? fmtRp(m.biaya) : '<span class="muted">—</span>'}</td>
      </tr>`).join('') : empty(6, 'Tidak ada laporan maintenance pada periode ini');
    if (count) count.textContent = list.length + ' baris';
    return;
  }

  // --- Default: Pendapatan ---
  const list = demoData.pembayaran.filter(b => inReportPeriod(b.periode))
    .sort((a, b) => (b.tgl || '').localeCompare(a.tgl || ''));
  thead.innerHTML = '<tr><th>Tanggal</th><th>Invoice</th><th>Penghuni</th><th>Kamar</th><th>Periode</th><th>Jumlah</th><th>Status</th></tr>';
  tbody.innerHTML = list.length ? list.map(b => `
    <tr>
      <td class="muted">${fmtTanggal(b.tgl || b.jatuhTempo)}</td>
      <td style="font-weight:600">${esc(b.inv)}</td>
      <td>${esc(b.penghuni)}</td>
      <td>${esc(b.kamar)}</td>
      <td class="muted">${esc(b.periode)}</td>
      <td style="font-weight:600">${fmtRp(b.jumlah)}</td>
      <td><span class="tag ${tagClass(b.status)}">${b.status}</span></td>
    </tr>`).join('') : empty(7, 'Tidak ada data pendapatan pada periode ini');
  if (count) count.textContent = list.length + ' baris';
}

/* --------------------------- POPULATE SELECT --------------------------- */
function populateSelects() {
  const p = state.page;
  const aktif = demoData.penghuni.filter(x => x.status === 'Aktif');

  if (p === 'tenants') {
    const kmSel = $('[data-filter="tenants-kamar"]');
    if (kmSel) kmSel.innerHTML = '<option value="Semua">Semua kamar</option>' +
      demoData.kamar.map(k => `<option value="${esc(k.no)}">${esc(k.no)}</option>`).join('');
  }

  // Modal pembayaran juga dipakai di dashboard (index.html)
  if (p === 'payments' || p === 'dashboard') {
    const flt = $('[data-filter="payments-periode"]');
    if (flt) flt.innerHTML = '<option value="Semua">Semua periode</option>' +
      demoData.bulanan.map(b => `<option value="${esc(b)}">${esc(b)}</option>`).join('');
    $('#fPayPenghuni').innerHTML = aktif
      .map(x => `<option value="${esc(x.nama)}">${esc(x.nama)} — ${esc(x.kamar)}</option>`).join('');
    $('#fPayPeriode').innerHTML = demoData.bulanan
      .map(b => `<option value="${esc(b)}">${esc(b)}</option>`).join('');
  }

  if (p === 'leases') {
    // Semua penghuni (aktif duluan) agar kontrak lama tetap bisa diedit
    const urut = [...demoData.penghuni].sort((a, b) => (a.status === 'Aktif' ? 0 : 1) - (b.status === 'Aktif' ? 0 : 1));
    $('#fLsPenghuni').innerHTML = urut
      .map(x => `<option value="${esc(x.nama)}">${esc(x.nama)} — ${esc(x.kamar)}${x.status !== 'Aktif' ? ' (' + x.status + ')' : ''}</option>`).join('');
    $('#fLsKamar').innerHTML = demoData.kamar
      .map(k => `<option value="${esc(k.no)}">${esc(k.no)} — ${esc(k.tipe)}</option>`).join('');
  }

  // Modal maintenance juga dipakai di dashboard (index.html)
  if (p === 'maintenance' || p === 'dashboard') {
    const lokasi = ['Koridor Lt.1', 'Koridor Lt.2', 'Koridor Lt.3', 'Lantai 1', 'Lantai 2', 'Lantai 3', 'Depan Kos', '—'];
    $('#fMtKamar').innerHTML = lokasi.map(l => `<option value="${esc(l)}">${esc(l)}</option>`).join('') +
      demoData.kamar.map(k => `<option value="${esc(k.no)}">${esc(k.no)}</option>`).join('');
    $('#fMtPenghuni').innerHTML = '<option value="—">—</option>' +
      aktif.map(x => `<option value="${esc(x.nama)}">${esc(x.nama)}</option>`).join('');
  }

  if (p === 'facilities') {
    const kat = [...new Set(demoData.fasilitas.map(f => f.kategori))];
    const flt = $('[data-filter="facilities-kategori"]');
    if (flt) flt.innerHTML = '<option value="Semua">Semua kategori</option>' +
      kat.map(k => `<option value="${esc(k)}">${esc(k)}</option>`).join('');
  }
}

/* --------------------------- EVENT DELEGATION --------------------------- */
document.addEventListener('click', (e) => {
  // Menu sidebar placeholder (href="#")
  const nav = e.target.closest('.nav-btn');
  if (nav && nav.getAttribute('href') === '#') {
    e.preventDefault();
    const labelEl = nav.querySelector('span');
    navPlaceholder((labelEl ? labelEl.textContent : nav.textContent).trim());
    return;
  }

  const el = e.target.closest('[data-action]');
  if (el) {
    const a = el.dataset.action;
    const id = el.dataset.id;
    const actions = {
      'close-dialog': closeDialog,
      'toggle-nav': () => { if (isMobileNav()) toggleMobileNav(); else toggleSidebar(); },
      'toggle-notif': (ev) => {
        ev.stopPropagation();
        state.notifRead = true;
        renderNotifMenu();
        $('#notifMenu').classList.toggle('show');
        $('#notifDot').hidden = true;
        $('#userMenu').classList.remove('show');
      },
      'toggle-user': (ev) => {
        ev.stopPropagation();
        $('#userMenu').classList.toggle('show');
        $('#notifMenu').classList.remove('show');
      },
      logout: () => {
        $('#userMenu').classList.remove('show');
        showToast('Fitur login akan dihubungkan ke Laravel nanti', 'info');
      },
      'nav-placeholder': () => navPlaceholder(el.dataset.label),
      go: () => go(el.dataset.href),
      'confirm-ok': () => {
        const fn = $('#dialogRoot')._onOk;
        closeDialog();
        if (fn) fn();
      },

      'room-new': () => openRoomModal(null),
      'room-edit': () => openRoomModal(id),
      'room-delete': () => deleteRoom(id),
      'room-detail': () => roomDetail(id),

      'tenant-new': () => openTenantModal(null),
      'tenant-edit': () => openTenantModal(id),
      'tenant-delete': () => deleteTenant(id),
      'tenant-detail': () => tenantDetail(id),

      'lease-new': () => openLeaseModal(null),
      'lease-edit': () => openLeaseModal(id),
      'lease-delete': () => deleteLease(id),
      'lease-detail': () => leaseDetail(id),

      'pay-new': () => openPayModal(null),
      'pay-edit': () => openPayModal(id),
      'pay-delete': () => deletePay(id),
      'pay-detail': () => payDetail(id),
      'pay-verify': () => payVerify(id),

      'maint-new': () => openMaintModal(null),
      'maint-edit': () => openMaintModal(id),
      'maint-delete': () => deleteMaint(id),
      'maint-detail': () => maintDetail(id),
      'maint-next': () => maintNext(id),

      'exp-new': () => openExpModal(null),
      'exp-edit': () => openExpModal(id),
      'exp-delete': () => deleteExp(id),
      'exp-detail': () => expDetail(id),

      'fac-new': () => openFacModal(null),
      'fac-edit': () => openFacModal(id),
      'fac-delete': () => deleteFac(id),

      'report-tab': () => { state.reportTab = el.dataset.tab; renderReports(); },
      'report-apply': () => {
        const pr = F('reports-periode', 'Bulan');
        const bln = F('reports-bulan', 'Juli');
        const thn = F('reports-tahun', '2026');
        renderReports();
        showToast('Laporan ' + (pr === 'Bulan' ? bln + ' ' : 'tahun ') + thn + ' ditampilkan', 'success');
      },
      'report-export-pdf': () => showToast('Fitur export akan tersedia setelah integrasi backend', 'info'),
      'report-export-excel': () => showToast('Fitur export akan tersedia setelah integrasi backend', 'info')
    };
    if (actions[a]) actions[a](e);
  }

  // Tutup dropdown saat klik di luar (null-safe untuk halaman tanpa elemen ini)
  const mNotif = $('#notifMenu'), mUser = $('#userMenu'), mSearch = $('#searchResults');
  if (mNotif && !e.target.closest('.notif')) mNotif.classList.remove('show');
  if (mUser && !e.target.closest('.userchip')) mUser.classList.remove('show');
  if (mSearch && !e.target.closest('.searchbox')) mSearch.classList.remove('show');

  // Tutup dialog saat klik backdrop
  if (e.target.getAttribute && e.target.getAttribute('data-close') === '1') closeDialog();

  // Tutup drawer navigasi mobile
  if (e.target.id === 'navBackdrop') document.body.classList.remove('nav-open');
});

document.addEventListener('submit', (e) => {
  const form = e.target.closest('[data-form]');
  if (!form) return;
  e.preventDefault();
  switch (form.dataset.form) {
    case 'room': saveRoom(form); break;
    case 'tenant': saveTenant(form); break;
    case 'lease': saveLease(form); break;
    case 'pay': savePay(form); break;
    case 'maint': saveMaint(form); break;
    case 'exp': saveExp(form); break;
    case 'fac': saveFac(form); break;
    case 'profile': saveProfile(form); break;
    case 'password': savePassword(form); break;
    case 'kosinfo': saveKosInfo(form); break;
    case 'paysettings': savePaySettings(form); break;
    case 'notif': saveNotifSettings(form); break;
  }
});

document.addEventListener('change', (e) => {
  if (e.target.id === 'fPayPenghuni') fillPayFromTenant();

  const f = e.target.dataset.filter;
  if (f) {
    state.filters[f] = e.target.value;
    refresh();
  }
});

document.addEventListener('input', (e) => {
  const f = e.target.dataset.filter;
  if (f) {
    state.filters[f] = e.target.value;
    refresh();
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return;
  const dlg = $('#dialogRoot');
  if (dlg && dlg.querySelector('.dialog-backdrop:not([hidden])')) {
    closeDialog();
    return;
  }
  if (document.body.classList.contains('nav-open')) document.body.classList.remove('nav-open');
  else {
    const nm = $('#notifMenu');
    const um = $('#userMenu');
    if (nm) nm.classList.remove('show');
    if (um) um.classList.remove('show');
  }
});

/* --------------------- GESTUR SENTUH (HP) --------------------- */
let touchStartX = null;
document.addEventListener('touchstart', (e) => {
  if (isPublicPage()) return; // gesture hanya untuk dashboard
  touchStartX = e.touches[0].clientX;
}, { passive: true });

document.addEventListener('touchend', (e) => {
  if (isPublicPage()) return;
  if (touchStartX === null || !isMobileNav()) return;
  const dx = e.changedTouches[0].clientX - touchStartX;
  const sx = touchStartX;
  touchStartX = null;
  const terbuka = document.body.classList.contains('nav-open');
  if (e.target.closest && e.target.closest('.table-wrap, .search-results, .notif-menu, .user-menu')) return;
  if (!terbuka && sx < 32 && dx > 70) document.body.classList.add('nav-open');
  else if (terbuka && dx < -70) document.body.classList.remove('nav-open');
}, { passive: true });

/* --------------------- PENCARIAN & INISIALISASI --------------------- */
if (!isPublicPage()) {
  $('#globalSearch').addEventListener('input', runSearch);
  $('#globalSearch').addEventListener('blur', () => setTimeout(() => $('#searchResults').classList.remove('show'), 180));
}

function init() {
  initSidebarToggle();
  applyRole();
  populateSelects();
  if (state.page === 'profile') renderProfile();
  if (state.page === 'settings') renderSettings();
  refresh();
}

if (!isPublicPage()) init();

/* ============================================================
   KOSFLY — PUBLIC UI (Landing · Login · Register)
   Bagian ini hanya aktif pada halaman dengan <body class="public-page">
   sehingga tidak mengganggu halaman dashboard.
   ============================================================ */
(function () {
  'use strict';

  var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); };

  /* ------------------------------------------------------------
     NAVBAR MOBILE (drawer + backdrop)
     ------------------------------------------------------------ */
  function initMobileNav() {
    var hamburger = $('.hamburger');
    var backdrop = $('.nav-backdrop');
    if (!hamburger) return;

    function open() {
      document.body.classList.add('nav-open');
      hamburger.setAttribute('aria-expanded', 'true');
    }
    function close() {
      document.body.classList.remove('nav-open');
      hamburger.setAttribute('aria-expanded', 'false');
    }

    hamburger.addEventListener('click', function () {
      if (document.body.classList.contains('nav-open')) close();
      else open();
    });

    if (backdrop) backdrop.addEventListener('click', close);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.body.classList.contains('nav-open')) close();
    });

    // Tutup drawer saat link diklik
    $$('.nav-drawer a').forEach(function (link) {
      link.addEventListener('click', close);
    });

    hamburger.setAttribute('aria-expanded', 'false');
    hamburger.setAttribute('aria-controls', 'navDrawer');
  }

  /* ------------------------------------------------------------
     SMOOTH SCROLL untuk link anchor (Beranda / Fitur / Tentang),
     termasuk link seperti index.html#fitur saat berada di index
     ------------------------------------------------------------ */
  function initSmoothScroll() {
    var currentFile = location.pathname.split('/').pop() || 'index.html';
    $$('a[href*="#"]').forEach(function (link) {
      link.addEventListener('click', function (e) {
        var href = link.getAttribute('href');
        var hashAt = href.indexOf('#');
        if (hashAt === -1) return;
        var file = href.slice(0, hashAt);
        var targetId = href.slice(hashAt);
        // Bila menunjuk file lain, biarkan navigasi normal
        if (file && file !== currentFile) return;
        var target = $(targetId);
        if (!target) return;
        e.preventDefault();
        var top = target.getBoundingClientRect().top + window.pageYOffset - 76;
        window.scrollTo({ top: top, behavior: 'smooth' });
      });
    });
  }

  /* ------------------------------------------------------------
     PASSWORD VISIBILITY TOGGLE
     data-toggle-pw="idInput" pada tombol; icon <svg> di dalamnya
     ------------------------------------------------------------ */
  function togglePassword(btn) {
    var input = document.getElementById(btn.getAttribute('data-toggle-pw'));
    if (!input) return;
    var shown = input.type === 'text';
    input.type = shown ? 'password' : 'text';
    btn.setAttribute('aria-pressed', String(shown));
    // Tukar icon: ic-off (mata terbuka) saat tersembunyi, ic-on (mata tercoret) saat terlihat
    var icOff = btn.querySelector('.ic-off');
    var icOn = btn.querySelector('.ic-on');
    if (icOff && icOn) {
      icOff.style.display = shown ? 'none' : '';
      icOn.style.display = shown ? '' : 'none';
    }
  }

  function initPasswordToggles() {
    $$('.pw-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () { togglePassword(btn); });
    });
  }

  /* ------------------------------------------------------------
     PASSWORD STRENGTH METER (register)
     0-2 = Lemah · 3 = Cukup · 4-5 = Baik · 6+ = Kuat
     ------------------------------------------------------------ */
  function passwordScore(value) {
    var score = 0;
    if (!value) return 0;
    if (value.length >= 8) score++;
    if (value.length >= 12) score++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
    if (/\d/.test(value)) score++;
    if (/[^a-zA-Z0-9]/.test(value)) score++;
    return score;
  }

  function strengthMeta(score) {
    if (score <= 2) return { label: 'Kekuatan password: Lemah', cls: 's1', seg: 1 };
    if (score === 3) return { label: 'Kekuatan password: Cukup', cls: 's2', seg: 2 };
    if (score <= 5) return { label: 'Kekuatan password: Baik', cls: 's3', seg: 3 };
    return { label: 'Kekuatan password: Kuat', cls: 's4', seg: 4 };
  }

  function initStrengthMeter() {
    var input = $('#registerPassword');
    if (!input) return;
    var bar = $('#strengthBar');
    var label = $('#strengthLabel');
    var colors = { 1: 'var(--color-error)', 2: 'var(--color-warning)', 3: 'var(--color-success)', 4: 'var(--color-success)' };

    input.addEventListener('input', function () {
      var score = passwordScore(input.value);
      var meta = strengthMeta(score);
      label.textContent = meta.label;
      label.className = 'strength-label ' + meta.cls;
      for (var i = 0; i < 4; i++) {
        var seg = bar.children[i];
        if (i < meta.seg) seg.style.backgroundColor = colors[meta.seg];
        else seg.style.backgroundColor = '';
      }
    });
  }

  /* ------------------------------------------------------------
     VALIDASI FORM — prototype (tanpa backend)
     ------------------------------------------------------------ */
  function setFieldError(input, message) {
    var field = input.closest('.field');
    var err = field ? field.querySelector('.field-error') : null;
    if (err && message) {
      err.textContent = message;
      err.classList.add('show');
    } else if (err) {
      err.classList.remove('show');
    }
    input.classList.toggle('invalid', Boolean(message));
    if (!message) input.classList.add('is-valid');
    else input.classList.remove('is-valid');
  }

  var validators = {
    email: function (v) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);
    },
    phone: function (v) {
      return /^[0-9+()\-\s]{9,16}$/.test(v.replace(/\s/g, ''));
    }
  };

  function showDemoNote(form, ok, msg) {
    var note = form.querySelector('.form-note');
    if (!note) return;
    note.classList.toggle('success', ok);
    note.querySelector('span').textContent = msg;
    note.style.display = 'flex';
  }

  function initLoginForm() {
    var form = $('#loginForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = $('#loginEmail');
      var password = $('#loginPassword');
      var ok = true;

      if (!email.value.trim()) { setFieldError(email, 'Email wajib diisi.'); ok = false; }
      else if (!validators.email(email.value.trim())) { setFieldError(email, 'Format email tidak valid.'); ok = false; }
      else setFieldError(email, '');

      if (!password.value) { setFieldError(password, 'Kata sandi wajib diisi.'); ok = false; }
      else setFieldError(password, '');

      if (ok) {
        var btn = form.querySelector('button[type="submit"]');
        setButtonLoading(btn, true, 'Memproses...');
        setTimeout(function () {
          setButtonLoading(btn, false);
          showDemoNote(form, true, 'Login berhasil — prototype UI. Integrasi autentikasi menyusul setelah backend terhubung.');
          showPublicToast('Login berhasil — prototype UI.', 'success');
        }, 900);
      }
    });

    // Bersihkan error saat mengetik
    $$('input', form).forEach(function (input) {
      input.addEventListener('input', function () {
        if (input.classList.contains('invalid')) setFieldError(input, '');
      });
    });
  }

  function initRegisterForm() {
    var form = $('#registerForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = $('#regName');
      var email = $('#regEmail');
      var phone = $('#regPhone');
      var password = $('#regPassword');
      var confirm = $('#regConfirm');
      var terms = $('#regTerms');
      var ok = true;

      if (!name.value.trim()) { setFieldError(name, 'Nama lengkap wajib diisi.'); ok = false; }
      else setFieldError(name, '');

      if (!email.value.trim()) { setFieldError(email, 'Email wajib diisi.'); ok = false; }
      else if (!validators.email(email.value.trim())) { setFieldError(email, 'Format email tidak valid.'); ok = false; }
      else setFieldError(email, '');

      if (!phone.value.trim()) { setFieldError(phone, 'Nomor telepon wajib diisi.'); ok = false; }
      else if (!validators.phone(phone.value.trim())) { setFieldError(phone, 'Nomor telepon tidak valid.'); ok = false; }
      else setFieldError(phone, '');

      if (!password.value) { setFieldError(password, 'Kata sandi wajib diisi.'); ok = false; }
      else if (password.value.length < 8) { setFieldError(password, 'Kata sandi minimal 8 karakter.'); ok = false; }
      else setFieldError(password, '');

      if (!confirm.value) { setFieldError(confirm, 'Konfirmasi kata sandi wajib diisi.'); ok = false; }
      else if (confirm.value !== password.value) { setFieldError(confirm, 'Konfirmasi tidak cocok dengan kata sandi.'); ok = false; }
      else setFieldError(confirm, '');

      if (!terms.checked) { ok = false; showDemoNote(form, false, 'Anda harus menyetujui Syarat & Ketentuan terlebih dahulu.'); }
      else { var tnote = form.querySelector('.form-note'); if (tnote) tnote.style.display = 'none'; }

      if (ok) {
        var btn = form.querySelector('button[type="submit"]');
        setButtonLoading(btn, true, 'Memproses...');
        setTimeout(function () {
          setButtonLoading(btn, false);
          showDemoNote(form, true, 'Registrasi berhasil — prototype UI. Integrasi backend menyusul setelah terhubung.');
          showPublicToast('Registrasi berhasil — prototype UI.', 'success');
        }, 900);
      }
    });

    $$('input', form).forEach(function (input) {
      input.addEventListener('input', function () {
        if (input.classList.contains('invalid')) setFieldError(input, '');
        var note = form.querySelector('.form-note');
        if (note && note.style.display === 'flex' && !note.classList.contains('success')) note.style.display = 'none';
      });
    });
  }

  /* ------------------------------------------------------------
     FINAL POLISH — reveal, count-up, toast, loading state
     ------------------------------------------------------------ */
  function prefersReducedMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  /* Scroll reveal: tambahkan class .revealed saat elemen masuk viewport */
  function initReveal() {
    var els = $$('[data-reveal]');
    if (!els.length) return;
    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('revealed'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -32px 0px' });
    els.forEach(function (el) { io.observe(el); });
  }

  /* Count-up angka mock stat saat terlihat */
  function runCountUp(el) {
    if (el.dataset.done) return;
    el.dataset.done = '1';
    var target = parseFloat(el.getAttribute('data-count')) || 0;
    var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
    var dur = 650;
    var start = null;
    function fmt(n) {
      return n.toLocaleString('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
      });
    }
    function step(ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
      el.textContent = fmt(target * eased);
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = fmt(target);
    }
    requestAnimationFrame(step);
  }

  function initCountUp() {
    var els = $$('.count-num[data-count]');
    if (!els.length || prefersReducedMotion()) return;
    if (!('IntersectionObserver' in window)) {
      els.forEach(runCountUp);
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          runCountUp(entry.target);
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    els.forEach(function (el) { io.observe(el); });
  }

  /* Toast publik (top-right desktop, atas penuh mobile) */
  var PUBLIC_TOAST_ICONS = {
    ok: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>',
    err: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
    info: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>'
  };
  var PUBLIC_TOAST_CLOSE =
    '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>';

  function showPublicToast(msg, type) {
    var root = $('#publicToastRoot');
    if (!root) {
      root = document.createElement('div');
      root.id = 'publicToastRoot';
      root.setAttribute('aria-live', 'polite');
      document.body.appendChild(root);
    }
    var t = document.createElement('div');
    t.className = 'p-toast ' + (type === 'error' ? 'err' : type === 'success' ? 'ok' : 'info');
    var ic = PUBLIC_TOAST_ICONS[type === 'error' ? 'err' : type === 'success' ? 'ok' : 'info'];
    t.innerHTML = '<span class="p-toast-ic">' + ic + '</span>' +
      '<span class="p-toast-msg"></span>' +
      '<button class="p-toast-close" aria-label="Tutup notifikasi">' + PUBLIC_TOAST_CLOSE + '</button>';
    t.querySelector('.p-toast-msg').textContent = msg;
    root.appendChild(t);
    var gone = false;
    function dismiss() {
      if (gone) return;
      gone = true;
      t.classList.add('leaving');
      setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 220);
    }
    t.querySelector('.p-toast-close').addEventListener('click', dismiss);
    setTimeout(dismiss, 3800);
  }

  /* Loading state tombol submit (spinner + label) */
  function setButtonLoading(btn, loading, label) {
    if (!btn) return;
    if (loading) {
      if (!btn.dataset.origHtml) btn.dataset.origHtml = btn.innerHTML;
      btn.classList.add('loading');
      btn.disabled = true;
      btn.setAttribute('aria-busy', 'true');
      btn.innerHTML = '<span class="spinner" aria-hidden="true"></span> ' + (label || 'Memproses...');
    } else {
      btn.classList.remove('loading');
      btn.disabled = false;
      btn.setAttribute('aria-busy', 'false');
      if (btn.dataset.origHtml) {
        btn.innerHTML = btn.dataset.origHtml;
        delete btn.dataset.origHtml;
      }
    }
  }

  /* ------------------------------------------------------------
     INIT PUBLIC UI — hanya berjalan di halaman public
     ------------------------------------------------------------ */
  document.addEventListener('DOMContentLoaded', function () {
    if (!isPublicPage()) return;
    document.body.classList.add('js-ready');
    initMobileNav();
    initSmoothScroll();
    initPasswordToggles();
    initStrengthMeter();
    initLoginForm();
    initRegisterForm();
    initReveal();
    initCountUp();
  });
})();
