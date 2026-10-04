<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Surat Perjanjian Sewa #LS-{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }} — {{ $kosSettings['name'] ?? 'KosFly' }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      background: #f1f5f9;
      color: #0f172a;
      line-height: 1.6;
      padding: 30px 16px;
      font-size: 13px;
    }
    .print-actions {
      max-width: 820px;
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

    .contract-card {
      max-width: 820px;
      margin: 0 auto;
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
      padding: 48px 52px;
    }

    .contract-header {
      text-align: center;
      border-bottom: 2px solid #0f172a;
      padding-bottom: 20px;
      margin-bottom: 24px;
    }
    .brand-name { font-size: 20px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; }
    .brand-contact { font-size: 11px; color: #64748b; margin-top: 4px; }
    .contract-title {
      font-size: 16px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-top: 18px;
      text-decoration: underline;
    }
    .contract-no { font-size: 12px; color: #64748b; margin-top: 2px; }

    .contract-intro {
      margin-bottom: 20px;
      text-align: justify;
      line-height: 1.7;
    }

    .party-block {
      margin-bottom: 16px;
      padding-left: 20px;
    }
    .party-title { font-weight: 700; margin-bottom: 4px; }
    .party-table {
      width: 100%;
      border-collapse: collapse;
    }
    .party-table td {
      padding: 3px 0;
      font-size: 12.5px;
      vertical-align: top;
    }
    .party-table td:first-child { width: 140px; color: #475569; }
    .party-table td:nth-child(2) { width: 14px; }

    .article {
      margin-top: 22px;
    }
    .article-title {
      font-size: 13px;
      font-weight: 800;
      text-align: center;
      text-transform: uppercase;
      margin-bottom: 8px;
    }
    .article-body {
      text-align: justify;
      line-height: 1.7;
      font-size: 12.5px;
    }
    .article-body ol {
      padding-left: 20px;
      margin-top: 6px;
    }
    .article-body li {
      margin-bottom: 4px;
    }

    .facilities-list {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 6px;
    }
    .facility-tag {
      font-size: 11px;
      padding: 2px 8px;
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
      border-radius: 4px;
      color: #334155;
    }

    .signatures {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 40px;
      margin-top: 48px;
      padding-top: 20px;
      text-align: center;
    }
    .sig-label { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 64px; }
    .sig-person { font-size: 13px; font-weight: 700; color: #0f172a; text-decoration: underline; }
    .sig-sub { font-size: 11px; color: #64748b; margin-top: 2px; }

    @media print {
      body { background: #fff; padding: 0; }
      .print-actions { display: none !important; }
      .contract-card { border: none; box-shadow: none; padding: 0; max-width: 100%; }
      @page { margin: 15mm; size: A4 portrait; }
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

  <div class="contract-card">

    <header class="contract-header">
      <div class="brand-name">{{ $kosSettings['name'] ?? 'KosFly Residence' }}</div>
      <div class="brand-contact">
        {{ $kosSettings['address'] ?? 'Jl. Sukabirus No. 12, Dayeuhkolot, Bandung' }} | Telp: {{ $kosSettings['phone'] ?? '0812-3456-7890' }}
      </div>
      <div class="contract-title">SURAT PERJANJIAN SEWA MENYEWA KAMAR KOS</div>
      <div class="contract-no">Nomor: SPK/{{ date('Y') }}/{{ str_pad($lease->id, 4, '0', STR_PAD_LEFT) }}</div>
    </header>

    <div class="contract-intro">
      Pada hari ini, tanggal <strong>{{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</strong>, telah dibuat dan disepakati perjanjian sewa menyewa kamar kos antara pihak-pihak sebagai berikut:
    </div>

    <div class="party-block">
      <div class="party-title">I. PIHAK PERTAMA (PENGELOLA KOS):</div>
      <table class="party-table">
        <tr>
          <td>Nama Instansi / Usaha</td>
          <td>:</td>
          <td><strong>{{ $kosSettings['name'] ?? 'KosFly Residence' }}</strong></td>
        </tr>
        <tr>
          <td>Pengelola / Penanggung Jawab</td>
          <td>:</td>
          <td>{{ $kosSettings['bank_holder'] ?? 'Pengelola Kos' }}</td>
        </tr>
        <tr>
          <td>Alamat Kos</td>
          <td>:</td>
          <td>{{ $kosSettings['address'] ?? 'Jl. Sukabirus No. 12, Dayeuhkolot, Bandung' }}</td>
        </tr>
        <tr>
          <td>Kontak Resmi</td>
          <td>:</td>
          <td>{{ $kosSettings['phone'] ?? '0812-3456-7890' }}</td>
        </tr>
      </table>
    </div>

    <div class="party-block">
      <div class="party-title">II. PIHAK KEDUA (PENYEWA / PENGHUNI):</div>
      <table class="party-table">
        <tr>
          <td>Nama Lengkap</td>
          <td>:</td>
          <td><strong>{{ $lease->tenant?->user?->name ?? '—' }}</strong></td>
        </tr>
        <tr>
          <td>No. KTP / NIK</td>
          <td>:</td>
          <td>{{ $lease->tenant?->ktp_number ?? '—' }}</td>
        </tr>
        <tr>
          <td>Pekerjaan / Status</td>
          <td>:</td>
          <td>{{ $lease->tenant?->job ?? 'Mahasiswa / Karyawan' }}</td>
        </tr>
        <tr>
          <td>No. Telepon / WhatsApp</td>
          <td>:</td>
          <td>{{ $lease->tenant?->user?->phone ?? '—' }}</td>
        </tr>
        <tr>
          <td>Kontak Darurat</td>
          <td>:</td>
          <td>{{ $lease->tenant?->emergency_name ?? 'Keluarga' }} ({{ $lease->tenant?->emergency_contact ?? '—' }})</td>
        </tr>
      </table>
    </div>

    <p style="text-align: justify; margin-top: 12px;">
      Kedua belah pihak telah bersepakat untuk mengadakan perjanjian sewa menyewa kamar kos dengan ketentuan dan syarat-syarat yang tertuang dalam pasal-pasal berikut:
    </p>

    <!-- PASAL 1 -->
    <div class="article">
      <div class="article-title">Pasal 1 — Objek Sewa dan Fasilitas</div>
      <div class="article-body">
        PIHAK PERTAMA menyewakan kepada PIHAK KEDUA berupa 1 (satu) unit kamar kos dengan rincian:
        <table class="party-table" style="margin-top:6px;">
          <tr>
            <td>Nomor Kamar</td>
            <td>:</td>
            <td><strong>Kamar {{ $lease->room?->room_number ?? '—' }} (Lantai {{ $lease->room?->floor ?? '—' }})</strong></td>
          </tr>
          <tr>
            <td>Fasilitas Kamar</td>
            <td>:</td>
            <td>
              @if($lease->room && $lease->room->facilities->isNotEmpty())
                <div class="facilities-list">
                  @foreach($lease->room->facilities as $fac)
                    <span class="facility-tag">{{ $fac->name }}</span>
                  @endforeach
                </div>
              @else
                <span>Sesuai standar kamar yang tersedia.</span>
              @endif
            </td>
          </tr>
        </table>
      </div>
    </div>

    <!-- PASAL 2 -->
    <div class="article">
      <div class="article-title">Pasal 2 — Jangka Waktu Sewa</div>
      <div class="article-body">
        Perjanjian sewa ini berlaku terhitung mulai tanggal <strong>{{ $lease->start_date ? \Carbon\Carbon::parse($lease->start_date)->translatedFormat('d F Y') : '—' }}</strong> sampai dengan tanggal <strong>{{ $lease->end_date ? \Carbon\Carbon::parse($lease->end_date)->translatedFormat('d F Y') : 'Jangka waktu tidak terbatas (fleksibel bulanan)' }}</strong>.
      </div>
    </div>

    <!-- PASAL 3 -->
    <div class="article">
      <div class="article-title">Pasal 3 — Tarif Sewa dan Uang Jaminan (Deposit)</div>
      <div class="article-body">
        <ol>
          <li>Biaya sewa kamar disepakati sebesar <strong>Rp {{ number_format($lease->m_price, 0, ',', '.') }}</strong> per bulan (<em>{{ $monthlyTerbilang }}</em>).</li>
          <li>PIHAK KEDUA menyerahkan uang jaminan (deposit) sebesar <strong>Rp {{ number_format($lease->deposit_amount, 0, ',', '.') }}</strong> (<em>{{ $depositTerbilang }}</em>) pada saat awal sewa.</li>
          <li>Uang deposit akan dikembalikan seutuhnya kepada PIHAK KEDUA pada saat masa sewa berakhir setelah dipastikan tidak ada tunggakan dan tidak ada kerusakan pada kamar maupun fasilitas.</li>
          <li>Pembayaran sewa bulanan wajib dilakukan paling lambat tanggal <strong>{{ $kosSettings['billing_due'] ?? 10 }}</strong> setiap bulannya melalui kanal pembayaran online resmi PIHAK PERTAMA.</li>
        </ol>
      </div>
    </div>

    <!-- PASAL 4 -->
    <div class="article">
      <div class="article-title">Pasal 4 — Tata Tertib dan Ketentuan Hunian</div>
      <div class="article-body">
        <ol>
          <li>PIHAK KEDUA wajib menjaga kebersihan, ketertiban, dan ketenangan lingkungan kos.</li>
          <li>Dilarang keras membawa, menyimpan, atau mengonsumsi minuman keras, narkotika, atau barang terlarang lainnya di lingkungan kos.</li>
          <li>Tamu lawan jenis dilarang menginap di dalam kamar demi kenyamanan dan norma lingkungan kos.</li>
          <li>Setiap kerusakan fasilitas akibat kelalaian PIHAK KEDUA menjadi tanggung jawab PIHAK KEDUA untuk biaya perbaikannya.</li>
        </ol>
      </div>
    </div>

    <!-- TANDA TANGAN -->
    <div class="signatures">
      <div>
        <div class="sig-label">PIHAK PERTAMA (Pengelola Kos)</div>
        <div class="sig-person">{{ $kosSettings['bank_holder'] ?? 'Pengelola KosFly' }}</div>
        <div class="sig-sub">{{ $kosSettings['name'] ?? 'KosFly Residence' }}</div>
      </div>

      <div>
        <div class="sig-label">PIHAK KEDUA (Penyewa / Penghuni)</div>
        <div class="sig-person">{{ $lease->tenant?->user?->name ?? 'Penghuni' }}</div>
        <div class="sig-sub">Penyewa Kamar {{ $lease->room?->room_number ?? '—' }}</div>
      </div>
    </div>

  </div>

</body>
</html>
