<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Berita Acara Check-Out & Pengembalian Deposit - #LS-{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }}</title>
  <style>
    :root {
      --primary: #0f172a;
      --accent: #2563eb;
      --success: #16a34a;
      --danger: #dc2626;
      --neutral-100: #f8fafc;
      --neutral-200: #e2e8f0;
      --neutral-400: #94a3b8;
      --neutral-600: #475569;
      --neutral-800: #1e293b;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      background: #f1f5f9;
      color: var(--neutral-800);
      line-height: 1.5;
      padding: 24px;
    }

    .doc-container {
      max-width: 820px;
      margin: 0 auto;
      background: #ffffff;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      position: relative;
    }

    /* Action bar */
    .action-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--neutral-200);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      border: 1px solid transparent;
      transition: all 0.15s ease;
    }

    .btn-primary {
      background: var(--primary);
      color: #ffffff;
    }

    .btn-primary:hover {
      background: #334155;
    }

    .btn-secondary {
      background: #ffffff;
      color: var(--neutral-600);
      border-color: var(--neutral-200);
    }

    .btn-secondary:hover {
      background: var(--neutral-100);
    }

    /* Kop Surat */
    .kop {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 2px solid var(--neutral-800);
      padding-bottom: 16px;
      margin-bottom: 24px;
    }

    .kop-brand h1 {
      font-size: 24px;
      font-weight: 800;
      color: var(--primary);
      letter-spacing: -0.5px;
    }

    .kop-brand p {
      font-size: 12px;
      color: var(--neutral-600);
      margin-top: 4px;
      max-width: 420px;
    }

    .kop-info {
      text-align: right;
    }

    .doc-type-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      background: #f1f5f9;
      color: var(--neutral-800);
      margin-bottom: 6px;
    }

    .doc-number {
      font-size: 15px;
      font-weight: 700;
      color: var(--primary);
    }

    /* Judul Dokumen */
    .doc-title-section {
      text-align: center;
      margin-bottom: 28px;
    }

    .doc-title {
      font-size: 18px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--neutral-800);
    }

    .doc-subtitle {
      font-size: 13px;
      color: var(--neutral-600);
      margin-top: 4px;
    }

    /* Data Grid */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
    }

    .info-card {
      background: var(--neutral-100);
      padding: 16px;
      border-radius: 6px;
      border: 1px solid var(--neutral-200);
    }

    .info-card h4 {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--neutral-600);
      margin-bottom: 10px;
      border-bottom: 1px dashed var(--neutral-400);
      padding-bottom: 4px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      margin-bottom: 6px;
    }

    .info-row:last-child {
      margin-bottom: 0;
    }

    .info-label {
      color: var(--neutral-600);
    }

    .info-val {
      font-weight: 600;
      text-align: right;
    }

    /* Tabel Rincian */
    .table-section {
      margin-bottom: 24px;
    }

    .doc-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .doc-table th {
      background: var(--neutral-100);
      color: var(--neutral-800);
      font-weight: 700;
      text-align: left;
      padding: 10px 12px;
      border-top: 1px solid var(--neutral-200);
      border-bottom: 1px solid var(--neutral-200);
    }

    .doc-table td {
      padding: 10px 12px;
      border-bottom: 1px solid var(--neutral-200);
      vertical-align: top;
    }

    .text-right {
      text-align: right;
    }

    /* Box Terbilang & Total */
    .settlement-box {
      background: #f8fafc;
      border: 2px solid #cbd5e1;
      border-radius: 6px;
      padding: 16px 20px;
      margin-bottom: 24px;
    }

    .settlement-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 0;
      font-size: 14px;
    }

    .settlement-row.highlight {
      border-top: 2px solid var(--neutral-800);
      margin-top: 8px;
      padding-top: 10px;
      font-size: 16px;
      font-weight: 800;
    }

    .terbilang-box {
      margin-top: 8px;
      padding: 8px 12px;
      background: #ffffff;
      border-radius: 4px;
      border-left: 3px solid var(--success);
      font-size: 12px;
      font-style: italic;
      color: var(--neutral-600);
    }

    /* Catatan serah terima */
    .notes-box {
      border: 1px solid var(--neutral-200);
      border-radius: 6px;
      padding: 12px 16px;
      margin-bottom: 32px;
      font-size: 12px;
      line-height: 1.6;
      background: #fff;
    }

    .notes-title {
      font-weight: 700;
      margin-bottom: 4px;
      color: var(--neutral-800);
    }

    /* Tanda Tangan */
    .signature-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      margin-top: 36px;
      page-break-inside: avoid;
    }

    .sig-col {
      text-align: center;
      font-size: 13px;
    }

    .sig-space {
      height: 70px;
    }

    .sig-name {
      font-weight: 700;
      border-top: 1px solid var(--neutral-400);
      padding-top: 6px;
      display: inline-block;
      min-width: 180px;
    }

    .sig-title {
      font-size: 11px;
      color: var(--neutral-600);
      margin-top: 2px;
    }

    /* Cetak Media */
    @media print {
      body {
        background: #ffffff;
        padding: 0;
      }

      .action-bar {
        display: none !important;
      }

      .doc-container {
        max-width: 100%;
        padding: 0;
        box-shadow: none;
        border-radius: 0;
      }
    }
  </style>
</head>
<body>

<div class="doc-container">

  <!-- Action Bar (Hidden when printing) -->
  <div class="action-bar">
    <a href="{{ route('leases.show', $lease) }}" class="btn btn-secondary">
      &larr; Kembali ke Detail Kontrak
    </a>
    <button type="button" class="btn btn-primary" onclick="window.print()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
      Cetak / Simpan PDF
    </button>
  </div>

  <!-- Kop Kos -->
  <div class="kop">
    <div class="kop-brand">
      <h1>{{ $kosSettings['name'] }}</h1>
      <p>{{ $kosSettings['address'] }} | Telp/WA: {{ $kosSettings['phone'] }}</p>
    </div>
    <div class="kop-info">
      <div class="doc-type-badge">SERAH TERIMA & DEPOSIT</div>
      <div class="doc-number">BA-CO/{{ now()->format('Y') }}/{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }}</div>
      <div style="font-size: 12px; color: var(--neutral-600); margin-top: 4px;">
        Tanggal: {{ $lease->checkout_date ? $lease->checkout_date->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
      </div>
    </div>
  </div>

  <!-- Judul Dokumen -->
  <div class="doc-title-section">
    <h2 class="doc-title">BERITA ACARA SERAH TERIMA KAMAR &amp; PENYELESAIAN DEPOSIT</h2>
    <p class="doc-subtitle">Nomor Kontrak Terkait: #LS-{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }}</p>
  </div>

  <!-- Informasi Pihak & Kamar -->
  <div class="info-grid">
    <div class="info-card">
      <h4>Identitas Penghuni</h4>
      <div class="info-row">
        <span class="info-label">Nama Lengkap</span>
        <span class="info-val">{{ $lease->tenant->user->name ?? '—' }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">NIK / No. KTP</span>
        <span class="info-val">{{ $lease->tenant->ktp_number ?? '—' }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Nomor Telepon</span>
        <span class="info-val">{{ $lease->tenant->user->phone ?? '—' }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Status Pekerjaan</span>
        <span class="info-val">{{ $lease->tenant->job ?? '—' }}</span>
      </div>
    </div>

    <div class="info-card">
      <h4>Objek Sewa &amp; Periode</h4>
      <div class="info-row">
        <span class="info-label">Nomor Kamar</span>
        <span class="info-val">Kamar {{ $lease->room->room_number ?? '—' }} (Lantai {{ $lease->room->floor ?? '—' }})</span>
      </div>
      <div class="info-row">
        <span class="info-label">Mulai Menghuni</span>
        <span class="info-val">{{ $lease->start_date ? $lease->start_date->translatedFormat('d M Y') : '—' }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Selesai Menghuni</span>
        <span class="info-val">{{ $lease->end_date ? $lease->end_date->translatedFormat('d M Y') : '—' }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Tanggal Check-Out</span>
        <span class="info-val" style="color: var(--primary);">
          {{ $lease->checkout_date ? $lease->checkout_date->translatedFormat('d M Y') : now()->translatedFormat('d M Y') }}
        </span>
      </div>
    </div>
  </div>

  <!-- Kondisi Kamar Saat Serah Terima -->
  <div class="table-section">
    <table class="doc-table">
      <thead>
        <tr>
          <th>Pemeriksaan Kondisi Kamar &amp; Fasilitas</th>
          <th style="width: 140px; text-align: center;">Hasil Inspeksi</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong>Status Fisik Kamar &amp; Kelengkapan Fasilitas</strong><br>
            <span style="font-size: 12px; color: var(--neutral-600);">
              Kunci kamar diserahkan kembali, kebersihan ruangan, cat dinding, kelistrikan, dan perabotan kamar.
            </span>
          </td>
          <td style="text-align: center; vertical-align: middle;">
            @if($lease->room_condition === 'good')
              <span style="display:inline-block; padding: 4px 8px; background: #dcfce7; color: #166534; font-weight: 700; border-radius: 4px; font-size: 11px;">
                ✓ Kondisi Baik
              </span>
            @elseif($lease->room_condition === 'needs_cleaning')
              <span style="display:inline-block; padding: 4px 8px; background: #fef9c3; color: #854d0e; font-weight: 700; border-radius: 4px; font-size: 11px;">
                ⚠ Perlu Pembersihan
              </span>
            @else
              <span style="display:inline-block; padding: 4px 8px; background: #fee2e2; color: #991b1b; font-weight: 700; border-radius: 4px; font-size: 11px;">
                ✕ Ada Kerusakan
              </span>
            @endif
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Rincian Penyelesaian Uang Jaminan (Deposit) -->
  <div class="settlement-box">
    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--neutral-600); margin-bottom: 8px;">
      Rincian Kalkulasi Pengembalian Uang Jaminan (Deposit)
    </div>
    <div class="settlement-row">
      <span>Total Uang Jaminan (Deposit Awal)</span>
      <span style="font-weight: 600;">Rp {{ number_format($lease->deposit_amount, 0, ',', '.') }}</span>
    </div>
    <div class="settlement-row" style="color: var(--danger);">
      <span>Potongan Biaya Kerusakan / Kebersihan / Denda</span>
      <span style="font-weight: 600;">- Rp {{ number_format($lease->deposit_deduction, 0, ',', '.') }}</span>
    </div>
    <div class="settlement-row highlight">
      <span style="color: var(--success);">Sisa Uang Jaminan Dikembalikan ke Penghuni</span>
      <span style="color: var(--success); font-size: 18px;">Rp {{ number_format($lease->deposit_refunded, 0, ',', '.') }}</span>
    </div>
    <div class="terbilang-box">
      <strong>Terbilang:</strong> {{ $refundTerbilang }}
    </div>
  </div>

  <!-- Catatan Serah Terima -->
  <div class="notes-box">
    <div class="notes-title">Catatan Pemeriksaan &amp; Keterangan Serah Terima:</div>
    <p>{{ $lease->checkout_notes ?: 'Kamar telah diperiksa bersama dan diserahkan dalam keadaan selesai sewa tanpa ada tuntutan di kemudian hari.' }}</p>
  </div>

  <!-- Tanda Tangan Kedua Belah Pihak -->
  <div class="signature-grid">
    <div class="sig-col">
      <div>Pihak Kedua (Penghuni / Penyewa)</div>
      <div class="sig-space"></div>
      <div class="sig-name">{{ $lease->tenant->user->name ?? 'Penyewa Kamar' }}</div>
      <div class="sig-title">Penyewa yang Menyerahkan Kamar</div>
    </div>
    <div class="sig-col">
      <div>Pihak Pertama (Pengelola Kos)</div>
      <div class="sig-space"></div>
      <div class="sig-name">{{ $kosSettings['name'] ?? 'Pengelola Kos' }}</div>
      <div class="sig-title">Pengelola / Petugas Kos</div>
    </div>
  </div>

</div>

</body>
</html>
