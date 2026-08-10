<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KosFly — Manajemen Kos Lebih Mudah</title>
  <meta name="description" content="KosFly adalah platform manajemen kos: kelola kamar, penghuni, kontrak, pembayaran, pengeluaran, dan laporan dalam satu dashboard.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">
</head>
<body class="public-page">

  <!-- ===================== NAVBAR ===================== -->
  <header class="site-nav">
    <div class="container">
      <a class="brand" href="index.html" aria-label="KosFly — Beranda">
        <span class="brand-mark">K</span>
        Kos<span class="brand-accent">Fly</span>
      </a>

      <nav aria-label="Navigasi utama">
        <ul class="nav-links">
          <li><a href="#beranda">Beranda</a></li>
          <li><a href="#fitur">Fitur</a></li>
          <li><a href="#tentang">Tentang</a></li>
        </ul>
      </nav>

      <div class="nav-cta">
        <a class="btn btn-secondary btn-sm" href="{{ route('login') }}">Masuk</a>
        <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Daftar</a>
        <button class="hamburger" id="navToggle" aria-label="Buka menu" aria-controls="navDrawer" aria-expanded="false">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
        </button>
      </div>
    </div>
  </header>

  <!-- Drawer mobile -->
  <aside class="nav-drawer" id="navDrawer" aria-label="Menu mobile">
    <a class="brand" href="index.html">
      <span class="brand-mark">K</span>
      Kos<span class="brand-accent">Fly</span>
    </a>
    <nav aria-label="Navigasi mobile">
      <a class="drawer-link" href="#beranda">Beranda
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="#fitur">Fitur
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
      <a class="drawer-link" href="#tentang">Tentang
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
      </a>
    </nav>
    <div class="drawer-cta">
      <a class="btn btn-secondary btn-block" href="{{ route('login') }}">Masuk</a>
      <a class="btn btn-primary btn-block" href="{{ route('register') }}">Daftar Gratis</a>
    </div>
  </aside>
  <div class="nav-backdrop" aria-hidden="true"></div>

  <main id="beranda">

    <!-- ===================== HERO ===================== -->
    <section class="hero">
      <div class="container hero-grid">
        <div>
          <h1>Kelola Kos Lebih Mudah<br>dengan <span class="accent">KosFly</span></h1>
          <p class="hero-lead">Kelola kamar, penghuni, kontrak, pembayaran, pengeluaran, dan laporan kos dalam satu platform.</p>
          <div class="hero-actions">
            <a class="btn btn-primary" href="{{ route('register') }}">Mulai Sekarang</a>
            <a class="btn btn-secondary" href="{{ route('login') }}">Masuk</a>
          </div>
          <div class="hero-points">
            <span class="hero-point">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              Gratis dicoba
            </span>
            <span class="hero-point">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              Tanpa instalasi
            </span>
            <span class="hero-point">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              Data tersimpan rapi
            </span>
          </div>
        </div>

        <!-- Visual hero: mockup dashboard ringkas -->
        <div class="hero-visual" aria-hidden="true">
          <div class="mock-card">
            <div class="mock-bar">
              <span class="mock-bar-dots"><i></i><i></i><i></i></span>
              <span class="mock-url">app.kosfly.id/dashboard</span>
            </div>
            <div class="mock-body">
              <div class="mock-grid-2">
                <div class="mock-stat">
                  <div class="mock-stat-label">Kamar Terisi</div>
                  <div class="mock-stat-value"><b class="count-num" data-count="7">7</b> <small>/ 12 kamar</small></div>
                </div>
                <div class="mock-stat">
                  <div class="mock-stat-label">Pendapatan Bulan Ini</div>
                  <div class="mock-stat-value">Rp <b class="count-num" data-count="24.5" data-decimals="1">24,5</b> <small>jt</small></div>
                </div>
              </div>
              <div class="mock-chart">
                <div class="mock-chart-head">
                  <span class="mock-chart-title">Pendapatan 6 Bulan</span>
                  <span class="mock-chart-legend"><span class="lg">Masuk</span><span class="lg lg-2">Keluar</span></span>
                </div>
                <div class="mock-cols">
                  <div class="mock-col"><b style="height:45%"></b><i style="height:30%"></i></div>
                  <div class="mock-col"><b style="height:55%"></b><i style="height:35%"></i></div>
                  <div class="mock-col"><b style="height:50%"></b><i style="height:38%"></i></div>
                  <div class="mock-col"><b style="height:65%"></b><i style="height:40%"></i></div>
                  <div class="mock-col"><b style="height:70%"></b><i style="height:48%"></i></div>
                  <div class="mock-col"><b class="hl" style="height:80%"></b><i style="height:52%"></i></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== PROBLEM / VALUE ===================== -->
    <section class="problem section">
      <div class="container">
        <div class="section-head center" data-reveal>
          <span class="eyebrow">Masalah Umum Pengelola Kos</span>
          <h2>Masih Mengelola Kos Secara Manual?</h2>
          <p>Catatan di buku, tagihan diingat-ingat, dan laporan dibuat dari nol setiap bulan. KosFly menyelesaikan semua itu.</p>
        </div>

        <div class="compare-grid" data-reveal>
          <!-- Tanpa KosFly -->
          <div class="compare-col against">
            <div class="compare-col head">
              <span class="head-ic">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
              </span>
              <div>
                <h3>Tanpa KosFly</h3>
                <span>Masalah yang sering terjadi</span>
              </div>
            </div>
            <ul class="compare-list">
              <li class="against-ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                <span><strong>Data kamar sulit dipantau</strong><span class="cmp-desc">Status kamar kosong atau terisi tidak pernah terlihat jelas.</span></span>
              </li>
              <li class="against-ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                <span><strong>Pembayaran penghuni mudah terlewat</strong><span class="cmp-desc">Tidak ada pengingat tagihan dan riwayat pembayaran.</span></span>
              </li>
              <li class="against-ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                <span><strong>Pengeluaran tidak tercatat rapi</strong><span class="cmp-desc">Biaya perbaikan dan operasional mudah terlupakan.</span></span>
              </li>
              <li class="against-ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                <span><strong>Laporan harus dibuat manual</strong><span class="cmp-desc">Menghitung ulang pendapatan dan pengeluaran memakan waktu.</span></span>
              </li>
            </ul>
          </div>

          <!-- Dengan KosFly -->
          <div class="compare-col for">
            <div class="compare-col head">
              <span class="head-ic">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              </span>
              <div>
                <h3>Dengan KosFly</h3>
                <span>Semua teratasi otomatis</span>
              </div>
            </div>
            <ul class="compare-list">
              <li class="for-ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                <span><strong>Status kamar selalu jelas</strong><span class="cmp-desc">Pantau kamar terisi, kosong, dan perbaikan secara langsung.</span></span>
              </li>
              <li class="for-ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                <span><strong>Pembayaran terpantau</strong><span class="cmp-desc">Tagihan, status pembayaran, dan riwayat tersimpan terpusat.</span></span>
              </li>
              <li class="for-ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                <span><strong>Pengeluaran tercatat rapi</strong><span class="cmp-desc">Setiap biaya operasional terdokumentasi dan mudah ditelusuri.</span></span>
              </li>
              <li class="for-ic">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                <span><strong>Laporan instan</strong><span class="cmp-desc">Ringkasan pendapatan dan pengeluaran tersaji otomatis.</span></span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== FEATURES ===================== -->
    <section class="features section" id="fitur">
      <div class="container">
        <div class="section-head center" data-reveal>
          <span class="eyebrow">Fitur KosFly</span>
          <h2>Semua Kebutuhan Pengelolaan Kos dalam Satu Tempat</h2>
          <p>Delapan modul lengkap untuk mengelola kos Anda dari data kamar hingga laporan operasional.</p>
        </div>

        <div class="feature-grid" data-reveal>
          <article class="feature-card">
            <span class="feature-ic">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
            </span>
            <h3>Kelola Kamar</h3>
            <p>Kelola nomor kamar, lantai, harga, status, dan penghuni setiap kamar.</p>
          </article>

          <article class="feature-card">
            <span class="feature-ic">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <h3>Penghuni</h3>
            <p>Simpan dan kelola data penghuni secara terpusat dalam satu sistem.</p>
          </article>

          <article class="feature-card">
            <span class="feature-ic">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
            </span>
            <h3>Kontrak Sewa</h3>
            <p>Pantau kontrak dan masa sewa penghuni dengan tanggal yang jelas.</p>
          </article>

          <article class="feature-card">
            <span class="feature-ic">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 7h20v12H2z"/><path d="M2 11h20"/><path d="M6 15h4"/></svg>
            </span>
            <h3>Pembayaran</h3>
            <p>Pantau tagihan, pembayaran, status pembayaran, dan bukti pembayaran.</p>
          </article>

          <article class="feature-card">
            <span class="feature-ic">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            </span>
            <h3>Maintenance</h3>
            <p>Kelola laporan kerusakan dan proses maintenance kamar.</p>
          </article>

          <article class="feature-card">
            <span class="feature-ic">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 18h6v-6"/></svg>
            </span>
            <h3>Pengeluaran</h3>
            <p>Catat dan pantau berbagai pengeluaran operasional kos.</p>
          </article>

          <article class="feature-card">
            <span class="feature-ic">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </span>
            <h3>Fasilitas</h3>
            <p>Kelola fasilitas yang tersedia pada setiap kamar.</p>
          </article>

          <article class="feature-card">
            <span class="feature-ic">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
            </span>
            <h3>Laporan</h3>
            <p>Pantau data dan laporan operasional kos secara berkala.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- ===================== HOW IT WORKS ===================== -->
    <section class="steps section">
      <div class="container">
        <div class="section-head center" data-reveal>
          <span class="eyebrow">Cara Kerja</span>
          <h2>Mulai Mengelola Kos dalam 3 Langkah</h2>
          <p>Tanpa pelatihan rumit. KosFly siap dipakai dalam hitungan menit.</p>
        </div>

        <div class="step-grid" data-reveal>
          <div class="step-card">
            <div class="step-num">01</div>
            <h3>Tambahkan Data Kos</h3>
            <p>Input nama kos, daftar kamar, harga sewa, dan fasilitas yang tersedia.</p>
          </div>
          <div class="step-card">
            <div class="step-num">02</div>
            <h3>Kelola Operasional</h3>
            <p>Catat penghuni, buat kontrak, pantau pembayaran, dan kelola maintenance harian.</p>
          </div>
          <div class="step-card">
            <div class="step-num">03</div>
            <h3>Pantau &amp; Analisis</h3>
            <p>Lihat pendapatan, pengeluaran, tingkat hunian, dan laporan dalam satu dashboard.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== DASHBOARD PREVIEW ===================== -->
    <section class="preview section">
      <div class="container">
        <div class="section-head center" data-reveal>
          <span class="eyebrow">Dashboard</span>
          <h2>Semua Data Kos dalam Satu Dashboard</h2>
          <p>Kamar, penghuni, pembayaran, pendapatan, pengeluaran, dan tingkat hunian terpantau sekaligus.</p>
        </div>

        <div class="preview-frame" aria-hidden="true" data-reveal>
          <div class="preview-window">
            <!-- Sidebar mock -->
            <aside class="mock-sidebar">
              <a class="brand" href="#">
                <span class="brand-mark">K</span>
                Kos<span class="brand-accent">Fly</span>
              </a>
              <nav class="mock-menu">
                <a class="active" href="#">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h7v9H3z"/><path d="M14 3h7v5h-7z"/><path d="M14 12h7v9h-7z"/><path d="M3 16h7v5H3z"/></svg>
                  Dashboard
                </a>
                <a href="#">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-8h6v8"/></svg>
                  Kamar
                </a>
                <a href="#">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  Penghuni
                </a>
                <a href="#">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h20v12H2z"/><path d="M2 11h20"/><path d="M6 15h4"/></svg>
                  Pembayaran
                </a>
                <a href="#">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
                  Laporan
                </a>
              </nav>
            </aside>

            <!-- Konten mock -->
            <div class="mock-main">
              <div class="mock-topbar">
                <span class="mock-search">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                  Cari kamar, penghuni, pembayaran…
                </span>
                <span class="mock-top-right">
                  <span class="mock-bell">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                  </span>
                  <span class="mock-avatar">AK</span>
                </span>
              </div>

              <div class="mock-content">
                <div class="mock-head-row">
                  <h3>Ringkasan Kos Anda</h3>
                  <span class="mock-date">Agustus 2026</span>
                </div>

                <div class="mock-stats">
                  <div class="mock-stat big">
                    <div class="mock-stat-label">Kamar Terisi</div>
                    <div class="mock-stat-value lg"><b class="count-num" data-count="7">7</b> <small>/ 12 kamar</small></div>
                  </div>
                  <div class="mock-stat big">
                    <div class="mock-stat-label">Penghuni Aktif</div>
                    <div class="mock-stat-value lg"><b class="count-num" data-count="9">9</b></div>
                  </div>
                  <div class="mock-stat big">
                    <div class="mock-stat-label">Pendapatan Bulan Ini</div>
                    <div class="mock-stat-value lg">Rp <b class="count-num" data-count="24.5" data-decimals="1">24,5</b> <small>jt</small> <span class="up">▲ 12%</span></div>
                  </div>
                  <div class="mock-stat big">
                    <div class="mock-stat-label">Tingkat Hunian</div>
                    <div class="mock-stat-value lg"><b class="count-num" data-count="58">58</b>%</div>
                  </div>
                </div>

                <div class="mock-panels">
                  <div class="mock-panel">
                    <h4>Pendapatan vs Pengeluaran <span class="chip gray">6 bulan</span></h4>
                    <div class="mock-cols tall">
                      <div class="mock-col"><b style="height:38%"></b><i style="height:24%"></i></div>
                      <div class="mock-col"><b style="height:48%"></b><i style="height:30%"></i></div>
                      <div class="mock-col"><b style="height:42%"></b><i style="height:34%"></i></div>
                      <div class="mock-col"><b style="height:58%"></b><i style="height:36%"></i></div>
                      <div class="mock-col"><b style="height:64%"></b><i style="height:44%"></i></div>
                      <div class="mock-col"><b class="hl" style="height:72%"></b><i style="height:48%"></i></div>
                    </div>
                    <div class="mock-chart-legend" style="margin-top:12px;">
                      <span class="lg">Pendapatan</span><span class="lg lg-2">Pengeluaran</span>
                    </div>
                  </div>

                  <div class="mock-panel">
                    <h4>Okupansi Kamar <span class="chip amber">12 kamar</span></h4>
                    <div class="mock-donut-wrap">
                      <svg class="mock-donut" width="92" height="92" viewBox="0 0 100 100" role="img" aria-label="Okupansi kamar: 7 terisi, 4 kosong, 1 perbaikan">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#EEEEEE" stroke-width="12"></circle>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#1A1C1C" stroke-width="12" stroke-dasharray="158.3 251.3" transform="rotate(-90 50 50)"></circle>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#DADADA" stroke-width="12" stroke-dasharray="62.8 251.3" stroke-dashoffset="-158.3" transform="rotate(-90 50 50)"></circle>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#B7131A" stroke-width="12" stroke-dasharray="30.2 251.3" stroke-dashoffset="-221.1" transform="rotate(-90 50 50)"></circle>
                      </svg>
                      <div class="mock-legend">
                        <div><i style="background:#1A1C1C"></i>Terisi <b>7</b></div>
                        <div><i style="background:#DADADA"></i>Kosong <b>4</b></div>
                        <div><i style="background:#B7131A"></i>Perbaikan <b>1</b></div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mock-panel">
                  <h4>Status Kamar Terbaru</h4>
                  <div class="mock-rooms">
                    <div class="mock-room"><span class="r-no">A-01</span><span class="r-name">Budi Santoso</span><span class="chip green">Terisi</span></div>
                    <div class="mock-room"><span class="r-no">A-02</span><span class="r-name">Siti Aminah</span><span class="chip green">Terisi</span></div>
                    <div class="mock-room"><span class="r-no">A-03</span><span class="r-name">Kosong</span><span class="chip gray">Kosong</span></div>
                    <div class="mock-room"><span class="r-no">B-01</span><span class="r-name">Perbaikan AC</span><span class="chip amber">Perbaikan</span></div>
                    <div class="mock-room"><span class="r-no">B-02</span><span class="r-name">Kosong</span><span class="chip gray">Kosong</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== BENEFITS ===================== -->
    <section class="benefits section" id="tentang">
      <div class="container">
        <div class="section-head center" data-reveal>
          <span class="eyebrow">Keunggulan</span>
          <h2>Kenapa Menggunakan KosFly?</h2>
          <p>Dibuat khusus untuk pengelola kos di Indonesia agar operasional harian lebih ringan.</p>
        </div>

        <div class="benefit-grid" data-reveal>
          <div class="benefit-item">
            <span class="benefit-ic">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <div>
              <h3>Data Lebih Terorganisir</h3>
              <p>Semua data kamar, penghuni, dan transaksi tersimpan rapi dan terpusat.</p>
            </div>
          </div>

          <div class="benefit-item">
            <span class="benefit-ic">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <div>
              <h3>Menghemat Waktu</h3>
              <p>Hentikan pencatatan manual. Semua input dan pencarian jadi lebih cepat.</p>
            </div>
          </div>

          <div class="benefit-item">
            <span class="benefit-ic">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <div>
              <h3>Pembayaran Lebih Mudah Dipantau</h3>
              <p>Status tagihan dan riwayat pembayaran terlihat jelas kapan saja.</p>
            </div>
          </div>

          <div class="benefit-item">
            <span class="benefit-ic">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <div>
              <h3>Operasional Lebih Terkontrol</h3>
              <p>Maintenance dan pengeluaran terdokumentasi sehingga tidak ada yang terlewat.</p>
            </div>
          </div>

          <div class="benefit-item">
            <span class="benefit-ic">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <div>
              <h3>Data Tersimpan dalam Satu Sistem</h3>
              <p>Tidak ada lagi data tersebar di buku, Excel, dan catatan kecil.</p>
            </div>
          </div>

          <div class="benefit-item">
            <span class="benefit-ic">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <div>
              <h3>Laporan Lebih Cepat Dibuat</h3>
              <p>Ringkasan pendapatan dan pengeluaran tersaji otomatis setiap bulan.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== CTA ===================== -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-panel" data-reveal>
          <h2>Siap Mengelola Kos dengan Lebih Mudah?</h2>
          <p>Kelola operasional kos Anda dengan lebih teratur dalam satu platform.</p>
          <div class="cta-actions">
            <a class="btn btn-primary" href="{{ route('register') }}">Mulai Sekarang</a>
            <a class="btn btn-ghost" href="{{ route('login') }}">Masuk</a>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- ===================== FOOTER ===================== -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a class="brand" href="index.html">
            <span class="brand-mark">K</span>
            Kos<span class="brand-accent">Fly</span>
          </a>
          <p>Solusi sederhana untuk pengelolaan kos yang lebih teratur.</p>
        </div>

        <nav class="footer-col" aria-label="Navigasi footer">
          <h4>Navigasi</h4>
          <ul>
            <li><a href="index.html#beranda">Beranda</a></li>
            <li><a href="index.html#fitur">Fitur</a></li>
            <li><a href="index.html#tentang">Tentang</a></li>
          </ul>
        </nav>

        <nav class="footer-col" aria-label="Akun">
          <h4>Akun</h4>
          <ul>
            <li><a href="{{ route('login') }}">Masuk</a></li>
            <li><a href="{{ route('register') }}">Daftar</a></li>
          </ul>
        </nav>
      </div>

      <div class="footer-bottom">
        <span>© 2026 KosFly Management System. All rights reserved.</span>
        <span>Dibuat untuk pengelola kos di Indonesia.</span>
      </div>
    </div>
  </footer>

  @vite(['resources/css/app.css','resources/js/app.js'])
</body>
</html>
