<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $payment->status === 'paid' ? 'Kuitansi' : 'Invoice' }} #{{ $payment->invoice_number }} — {{ $kosSettings['name'] ?? 'KosFly' }}</title>
  @include('partials.favicons')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background: #f1f5f9;
      color: #0f172a;
      line-height: 1.5;
      padding: 30px 16px;
      font-size: 13px;
    }
    .print-actions {
      max-width: 760px;
      margin: 0 auto 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
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
      transition: all .15s;
    }
    .btn-primary { background: #0f172a; color: #fff; border-color: #0f172a; }
    .btn-primary:hover { background: #334155; }
    .btn-secondary { background: #fff; color: #334155; border-color: #cbd5e1; }
    .btn-secondary:hover { background: #f8fafc; }

    .receipt-card {
      max-width: 760px;
      margin: 0 auto;
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
      padding: 40px;
      position: relative;
      overflow: hidden;
    }

    .doc-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 2px solid #0f172a;
      padding-bottom: 24px;
      margin-bottom: 28px;
    }
    .brand-title {
      font-size: 22px;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: -0.02em;
    }
    .brand-sub { font-size: 12px; color: #64748b; margin-top: 4px; max-width: 340px; }

    .doc-title-block { text-align: right; }
    .doc-badge {
      display: inline-block;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      padding: 4px 10px;
      border-radius: 4px;
      margin-bottom: 6px;
    }
    .badge-paid { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-unpaid { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .doc-number { font-size: 18px; font-weight: 800; color: #0f172a; }
    .doc-date { font-size: 12px; color: #64748b; margin-top: 2px; }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      margin-bottom: 28px;
    }
    .info-box {
      background: #f8fafc;
      border: 1px solid #f1f5f9;
      border-radius: 8px;
      padding: 16px;
    }
    .box-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #64748b;
      margin-bottom: 8px;
    }
    .box-name { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
    .box-desc { font-size: 12px; color: #475569; line-height: 1.6; }

    .table-details {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 28px;
    }
    .table-details th {
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
      text-align: left;
      padding: 12px 14px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      color: #475569;
    }
    .table-details td {
      padding: 14px;
      border-bottom: 1px solid #f1f5f9;
      font-size: 13px;
    }
    .table-details .text-right { text-align: right; }

    .amount-banner {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }
    .amount-label { font-size: 12px; font-weight: 600; color: #64748b; }
    .amount-val { font-size: 22px; font-weight: 800; color: #0f172a; }
    .amount-words {
      font-size: 12px;
      color: #475569;
      font-style: italic;
      margin-top: 4px;
    }

    .watermark-lunas {
      position: absolute;
      top: 42%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-18deg);
      font-size: 72px;
      font-weight: 900;
      color: rgba(22, 163, 74, 0.12);
      border: 6px dashed rgba(22, 163, 74, 0.2);
      border-radius: 16px;
      padding: 10px 40px;
      pointer-events: none;
      letter-spacing: 0.15em;
      text-transform: uppercase;
    }

    .doc-footer {
      display: grid;
      grid-template-columns: 1fr 220px;
      gap: 30px;
      margin-top: 36px;
      padding-top: 20px;
      align-items: flex-end;
    }
    .notes-box {
      font-size: 12px;
      color: #64748b;
      line-height: 1.6;
    }
    .notes-box strong { color: #334155; }
    .signature-box {
      text-align: center;
    }
    .sig-city { font-size: 12px; color: #64748b; margin-bottom: 50px; }
    .sig-name { font-size: 13px; font-weight: 700; color: #0f172a; border-top: 1px solid #cbd5e1; padding-top: 6px; }
    .sig-role { font-size: 11px; color: #64748b; }

    @media print {
      body { background: #fff; padding: 0; }
      .print-actions { display: none !important; }
      .receipt-card { border: none; box-shadow: none; padding: 0; max-width: 100%; }
      @page { margin: 15mm; size: auto; }
    }
  </style>
</head>
<body>

  <div class="print-actions">
    <button type="button" class="btn btn-secondary" onclick="window.history.length > 1 ? window.history.back() : window.close()">
      ← Kembali
    </button>
    <button type="button" class="btn btn-primary" onclick="window.print()">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
      Cetak / Simpan PDF
    </button>
  </div>

  <div class="receipt-card">

    @if($payment->status === 'paid')
      <div class="watermark-lunas">L U N A S</div>
    @endif

    <!-- KOP DOKUMEN -->
    <header class="doc-header">
      <div>
        <h1 class="brand-title">{{ $kosSettings['name'] ?? 'KosFly Residence' }}</h1>
        <p class="brand-sub">
          {{ $kosSettings['address'] ?? 'Jl. Sukabirus No. 12, Dayeuhkolot, Bandung' }}<br>
          Telp: {{ $kosSettings['phone'] ?? '0812-3456-7890' }} | Email: {{ $kosSettings['email'] ?? 'kontak@kosfly.com' }}
        </p>
      </div>

      <div class="doc-title-block">
        <span class="doc-badge {{ $payment->status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}">
          {{ $payment->status === 'paid' ? 'Kuitansi Pembayaran Lunas' : 'Invoice Tagihan Sewa' }}
        </span>
        <div class="doc-number">{{ $payment->invoice_number }}</div>
        <div class="doc-date">
          Tanggal: {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') : ($payment->created_at ? $payment->created_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y')) }}
        </div>
      </div>
    </header>

    <!-- IDENTITAS PEMBAYAR & TUJUAN -->
    <div class="info-grid">
      <div class="info-box">
        <div class="box-title">Diterima Dari (Penghuni)</div>
        <div class="box-name">{{ $payment->lease?->tenant?->user?->name ?? 'Penghuni Kos' }}</div>
        <div class="box-desc">
          No. KTP / NIK: {{ $payment->lease?->tenant?->ktp_number ?? '—' }}<br>
          No. Telepon: {{ $payment->lease?->tenant?->user?->phone ?? $payment->lease?->tenant?->emergency_contact ?? '—' }}<br>
          Kamar: <strong>Kamar {{ $payment->lease?->room?->room_number ?? '—' }}</strong> (Lantai {{ $payment->lease?->room?->floor ?? '—' }})
        </div>
      </div>

      <div class="info-box">
        <div class="box-title">Rekening Pembayaran Resmi</div>
        <div class="box-name">{{ $kosSettings['bank_name'] ?? 'BCA' }} — {{ $kosSettings['bank_account'] ?? '123-456-7890' }}</div>
        <div class="box-desc">
          Atas Nama: <strong>{{ $kosSettings['bank_holder'] ?? 'Pengelola KosFly' }}</strong><br>
          Metode: {{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Transfer Bank')) }}<br>
          Status: <strong>{{ $payment->status === 'paid' ? 'Lunas / Terverifikasi' : ucfirst($payment->status) }}</strong>
        </div>
      </div>
    </div>

    <!-- RINCIAN TAGIHAN -->
    <table class="table-details">
      <thead>
        <tr>
          <th>No</th>
          <th>Deskripsi Pembayaran</th>
          <th>Periode Sewa</th>
          <th>Kamar</th>
          <th class="text-right">Nominal</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>
            <strong>{{ $payment->notes ?: 'Biaya Sewa Kamar Bulanan' }}</strong>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
              Jatuh Tempo: {{ $payment->due_date ? \Carbon\Carbon::parse($payment->due_date)->translatedFormat('d F Y') : '—' }}
            </div>
          </td>
          <td>{{ $payment->billing_period ? \Carbon\Carbon::parse($payment->billing_period)->translatedFormat('F Y') : '—' }}</td>
          <td>Kamar {{ $payment->lease?->room?->room_number ?? '—' }}</td>
          <td class="text-right font-semibold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
        </tr>
      </tbody>
    </table>

    <!-- TOTAL & TERBILANG -->
    <div class="amount-banner">
      <div>
        <div class="amount-label">Jumlah Uang:</div>
        <div class="amount-words">"{{ $terbilang }}"</div>
      </div>
      <div style="text-align: right;">
        <div class="amount-label">Total Pembayaran</div>
        <div class="amount-val">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
      </div>
    </div>

    <!-- FOOTER & TANDA TANGAN -->
    <footer class="doc-footer">
      <div class="notes-box">
        <strong>Catatan:</strong>
        <p>Kuitansi ini merupakan bukti pembayaran resmi yang sah dari pengelola {{ $kosSettings['name'] ?? 'KosFly' }}. Harap disimpan dengan baik sebagai bukti transaksi.</p>
        @if($payment->verifier)
          <p style="margin-top: 4px; font-size: 11px; color: #15803d;">✓ Pembayaran diverifikasi oleh: {{ $payment->verifier->name }}</p>
        @endif
      </div>

      <div class="signature-box">
        <div class="sig-city">Bandung, {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</div>
        <div class="sig-name">{{ $payment->verifier?->name ?? ($kosSettings['bank_holder'] ?? 'Pengelola Kos') }}</div>
        <div class="sig-role">Pengelola {{ $kosSettings['name'] ?? 'KosFly' }}</div>
      </div>
    </footer>

  </div>

</body>
</html>
