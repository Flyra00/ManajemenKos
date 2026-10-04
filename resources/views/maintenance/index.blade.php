@extends('layouts.app')
@section('content')
    <main class="page">

      <section class="page-head" aria-label="Judul halaman">
        <div>
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span class="sep">/</span>
            <span class="current">Maintenance</span>
          </nav>
          <h2 class="page-title">Maintenance & Keluhan</h2>
          <p class="page-sub">Pantau dan kelola tiket laporan kerusakan kamar serta perbaikan fasilitas kos.</p>
        </div>
        @if(!auth()->user() || !auth()->user()->hasRole('owner'))
          <div class="flex head-actions">
            <a href="{{ route('maintenance.create') }}" class="btn btn-primary">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
              Tambah Laporan
            </a>
          </div>
        @endif
      </section>


      @if(session('success'))
        <div class="alert alert-success" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      <!-- Kartu statistik -->
      <section class="grid-5" aria-label="Statistik maintenance">
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Total Laporan</div>
          <div class="stat-value">{{ $stats['total'] }}</div>
          @if(auth()->user() && auth()->user()->hasRole('tenant'))
            <div class="stat-sub muted">tiket keluhan Anda</div>
          @else
            <div class="stat-sub muted">Rp {{ number_format($stats['total_cost'], 0, ',', '.') }} total biaya</div>
          @endif
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-accent)">
          <div class="stat-label">Baru (Reported)</div>
          <div class="stat-value">{{ $stats['reported'] }}</div>
          <div class="stat-sub muted">menunggu tindakan</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Sedang Diproses</div>
          <div class="stat-value">{{ $stats['in_progress'] }}</div>
          <div class="stat-sub muted">dalam perbaikan</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-900)">
          <div class="stat-label">Selesai</div>
          <div class="stat-value">{{ $stats['completed'] }}</div>
          <div class="stat-sub muted">telah terselesaikan</div>
        </div>
        <div class="card elev-sm stat-card" style="border-top:2px solid var(--color-neutral-300)">
          <div class="stat-label">Dibatalkan</div>
          <div class="stat-value">{{ $stats['cancelled'] }}</div>
          <div class="stat-sub muted">tidak diproses</div>
        </div>
      </section>

      <!-- Filter + tabel maintenance -->
      <section class="card elev-sm section-card" aria-label="Daftar laporan maintenance">
        <div class="card-head">
          <h3 class="card-title">Daftar Laporan Perbaikan</h3>
          <span class="small muted">{{ $maintenances->total() }} laporan</span>
        </div>

        <form method="GET" action="{{ route('maintenance.index') }}" class="filter-bar">
          <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="Cari judul, kamar, penghuni…" aria-label="Cari laporan">
          <select class="input" name="priority" aria-label="Filter prioritas">
            <option value="">Semua Prioritas</option>
            <option value="high" @selected(request('priority') === 'high')>Tinggi (High)</option>
            <option value="medium" @selected(request('priority') === 'medium')>Sedang (Medium)</option>
            <option value="low" @selected(request('priority') === 'low')>Rendah (Low)</option>
          </select>
          <select class="input" name="status" aria-label="Filter status">
            <option value="">Semua Status</option>
            <option value="reported" @selected(request('status') === 'reported')>Baru (Reported)</option>
            <option value="in_progress" @selected(request('status') === 'in_progress')>Diproses (In Progress)</option>
            <option value="completed" @selected(request('status') === 'completed')>Selesai (Completed)</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
          </select>
          <button type="submit" class="btn btn-secondary">Terapkan</button>
          @if(request()->hasAny(['search', 'priority', 'status']))
            <a href="{{ route('maintenance.index') }}" class="btn btn-ghost">Reset</a>
          @endif
        </form>

        <div class="table-wrap">
          <table class="table table-wide">
            <thead>
              <tr>
                <th>Judul</th>
                <th>Kamar</th>
                <th>Penghuni</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Dilaporkan</th>
                <th>Biaya</th>
                <th>Ditangani</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($maintenances as $item)
                <tr>
                  <td class="font-semibold">
                    <a href="{{ route('maintenance.show', $item) }}" class="text-accent hover:underline">
                      {{ $item->title }}
                    </a>
                  </td>
                  <td>
                    @if($item->room)
                      @if(auth()->user() && auth()->user()->hasRole('tenant'))
                        <span class="font-semibold text-neutral-800">Kamar {{ $item->room->room_number }}</span>
                      @else
                        <a href="{{ route('rooms.show', $item->room) }}" class="font-semibold text-neutral-800">
                          Kamar {{ $item->room->room_number }}
                        </a>
                      @endif
                    @else
                      <span class="muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($item->tenant && $item->tenant->user)
                      @if(auth()->user() && auth()->user()->hasRole('tenant'))
                        <span class="text-neutral-700">{{ $item->tenant->user->name }}</span>
                      @else
                        <a href="{{ route('tenants.show', $item->tenant) }}" class="text-neutral-700 hover:underline">
                          {{ $item->tenant->user->name }}
                        </a>
                      @endif
                    @else
                      <span class="muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if($item->priority === 'high')
                      <span class="tag tag-accent">Tinggi</span>
                    @elseif($item->priority === 'medium')
                      <span class="tag tag-neutral">Sedang</span>
                    @else
                      <span class="tag tag-outline">Rendah</span>
                    @endif
                  </td>
                  <td>
                    @if($item->status === 'reported')
                      <span class="tag tag-accent">Reported</span>
                    @elseif($item->status === 'in_progress')
                      <span class="tag tag-neutral">In Progress</span>
                    @elseif($item->status === 'completed')
                      <span class="tag tag-outline" style="color:var(--color-neutral-800); font-weight:600">Selesai</span>
                    @else
                      <span class="tag tag-outline">Dibatalkan</span>
                    @endif
                  </td>
                  <td>{{ $item->reported_at ? $item->reported_at->translatedFormat('d M Y') : '—' }}</td>
                  <td>Rp {{ number_format($item->cost, 0, ',', '.') }}</td>
                  <td>{{ $item->handler->name ?? 'Belum ditentukan' }}</td>
                  <td>
                    <div class="flex gap-1">
                      <a href="{{ route('maintenance.show', $item) }}" class="btn btn-ghost btn-sm" title="Lihat detail laporan">
                        Detail
                      </a>
                      @if(!auth()->user() || (!auth()->user()->hasRole('owner') && !auth()->user()->hasRole('tenant')))
                        <button type="button" class="btn btn-primary btn-sm"
                                style="background: var(--color-neutral-900); border-color: var(--color-neutral-900); padding: 3px 10px; font-size: 12px;"
                                onclick="openUpdateStatusModal('{{ route('maintenance.update-status', $item) }}', '{{ addslashes($item->title) }}', '{{ $item->room->room_number ?? 'Kamar' }}', '{{ $item->status }}', '{{ $item->handled_by ?? '' }}', '{{ (int)$item->cost }}', '{{ $item->completion_image ? asset('storage/' . $item->completion_image) : '' }}', '{{ addslashes($item->handler->name ?? '') }}')"
                                title="Perbarui status pengerjaan tiket">
                          Status
                        </button>
                        <a href="{{ route('maintenance.edit', $item) }}" class="btn btn-secondary btn-sm" title="Edit laporan">
                          Edit
                        </a>
                        <button type="button" class="btn btn-ghost btn-sm text-accent"
                                onclick="openDeleteMaintenanceModal('{{ route('maintenance.destroy', $item) }}', '{{ $item->title }}', '{{ $item->room->room_number ?? 'Kamar' }}')">
                          Hapus
                        </button>
                      @elseif(auth()->user() && auth()->user()->hasRole('tenant') && $item->status === 'reported')
                        <a href="{{ route('maintenance.edit', $item) }}" class="btn btn-secondary btn-sm" title="Edit laporan">
                          Edit
                        </a>
                        <button type="button" class="btn btn-ghost btn-sm text-accent"
                                onclick="openDeleteMaintenanceModal('{{ route('maintenance.destroy', $item) }}', '{{ $item->title }}', '{{ $item->room->room_number ?? 'Kamar' }}')">
                          Batalkan
                        </button>
                      @endif
                    </div>

                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-6 text-neutral-500">
                    Tidak ada laporan perbaikan yang ditemukan.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if($maintenances->hasPages())
          <div class="card-footer flex justify-between items-center py-3 px-4 border-t border-neutral-200">
            <span class="text-sm text-neutral-600">
              Menampilkan {{ $maintenances->firstItem() }} - {{ $maintenances->lastItem() }} dari {{ $maintenances->total() }} laporan
            </span>
            <div>
              {{ $maintenances->links() }}
            </div>
          </div>
        @endif
      </section>

    </main>

    <!-- Modal Konfirmasi Hapus Maintenance -->
    <div class="dialog-backdrop" id="deleteMaintenanceModal" style="display:none; align-items:center; justify-content:center;" data-close="1">
      <div class="dialog" style="margin: auto;">
        <div class="dialog-title">Konfirmasi Hapus Laporan</div>
        <p class="small muted" style="margin:8px 0 16px;line-height:1.5">
          Apakah Anda yakin ingin menghapus laporan <strong id="deleteMaintenanceTitle"></strong> untuk Kamar <strong id="deleteMaintenanceRoom"></strong>? Tindakan ini tidak dapat dibatalkan.
        </p>
        <form id="deleteMaintenanceForm" method="POST" action="">
          @csrf
          @method('DELETE')
          <div class="dialog-actions">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteMaintenanceModal()">Batal</button>
            <button type="submit" class="btn btn-primary" style="background-color: var(--color-accent, #e53e3e); border-color: var(--color-accent, #e53e3e);">
              Ya, Hapus
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Update Status Perbaikan (Alur Bertahap) -->
    <div class="dialog-backdrop" id="updateStatusModal" style="display:none; align-items:center; justify-content:center;" data-close="1">
      <div class="dialog" style="margin: auto; max-width: 480px; width: 100%;">
        <div class="dialog-title" id="statusModalMainTitle">Update Status Perbaikan</div>
        <p class="small muted" style="margin:4px 0 16px;line-height:1.5">
          Tiket: <strong id="statusModalTitle"></strong> (<span id="statusModalRoom"></span>).
        </p>

        <form id="updateStatusForm" method="POST" action="" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <!-- TAHAP 1: Tiket Berstatus 'reported' (Pilihan: Diproses / Batalkan) -->
          <div id="sectionReported" style="display:none;">
            <input type="hidden" name="status" id="reportedStatusInput" value="in_progress">

            <p class="text-sm font-semibold" style="margin-bottom: 8px;">Pilih Tindakan Awal:</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px;">
              <button type="button" id="btnReportedProcess" class="btn" onclick="setReportedMode('in_progress')"
                      style="padding: 10px 8px; font-weight: 600; text-align: center; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                🔧 Diproses
              </button>
              <button type="button" id="btnReportedCancel" class="btn" onclick="setReportedMode('cancelled')"
                      style="padding: 10px 8px; font-weight: 600; text-align: center; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                ❌ Batalkan
              </button>
            </div>

            <div id="reportedStaffField" class="field" style="margin-bottom: 14px;">
              <label for="reportedStaffSelect">Ditangani Oleh (Staff / Teknisi) <span class="text-accent">*</span></label>
              <select name="handled_by" id="reportedStaffSelect" class="input">
                <option value="">— Pilih Staff / Teknisi —</option>
                @foreach($users ?? [] as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roles->pluck('name')->implode(', ') ?: 'Staff' }})</option>
                @endforeach
              </select>
              <span class="small muted" style="margin-top: 4px; display: block; font-size: 11px;">Hanya staff, admin, atau owner yang dapat dipilih (penyewa tidak dapat dipilih).</span>
            </div>

            <div id="reportedCancelNotice" style="display:none; padding: 10px 12px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; margin-bottom: 14px; font-size: 12px; color: #c53030;">
              Laporan ini akan dibatalkan dan tidak ditindaklanjuti.
            </div>
          </div>

          <!-- TAHAP 2: Tiket Berstatus 'in_progress' (Penyelesaian: Biaya & Foto Bukti) -->
          <div id="sectionInProgress" style="display:none;">
            <input type="hidden" name="status" id="inProgressStatusInput" value="completed">

            <div style="background: var(--color-neutral-100, #f1f5f9); border: 1px solid var(--color-neutral-300, #cbd5e1); border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;">
              <div style="font-size: 11px; text-transform: uppercase; color: var(--color-neutral-600); font-weight: 600;">Sedang Ditangani Oleh</div>
              <div id="inProgressHandlerText" style="font-weight: 600; color: var(--color-neutral-900); font-size: 13px; margin-top: 2px;">—</div>
            </div>

            <p class="text-sm font-semibold" style="margin-bottom: 8px;">Tindakan Tiket:</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
              <button type="button" id="btnInProgressComplete" class="btn" onclick="setInProgressMode('completed')"
                      style="padding: 10px 8px; font-weight: 600; text-align: center; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                ✅ Tandai Selesai
              </button>
              <button type="button" id="btnInProgressCancel" class="btn" onclick="setInProgressMode('cancelled')"
                      style="padding: 10px 8px; font-weight: 600; text-align: center; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                ❌ Batalkan
              </button>
            </div>

            <div id="inProgressCompletionFields">
              <div class="field" style="margin-bottom: 14px;">
                <label for="inProgressCost">Harga Pengeluaran / Biaya Perbaikan (Rp)</label>
                <input type="number" name="cost" id="inProgressCost" class="input" min="0" step="1000" placeholder="0">
                <span class="small muted" style="margin-top: 4px; display: block; font-size: 11px;">Biaya otomatis tersinkronisasi ke modul Pengeluaran (Expenses).</span>
              </div>

              <div class="field" style="margin-bottom: 16px;">
                <label for="inProgressImage">Bukti Foto Penyelesaian</label>
                <input type="file" name="completion_image" id="inProgressImage" class="input" accept="image/*">
                <span class="small muted" style="margin-top: 4px; display: block; font-size: 11px;">Unggah foto hasil perbaikan (opsional, maks 2MB).</span>
              </div>
            </div>

            <div id="inProgressCancelNotice" style="display:none; padding: 10px 12px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; margin-bottom: 14px; font-size: 12px; color: #c53030;">
              Laporan yang sedang berjalan ini akan dihentikan dan dibatalkan.
            </div>
          </div>

          <!-- TAHAP 3: Tiket Berstatus 'completed' atau 'cancelled' -->
          <div id="sectionOtherStatus" style="display:none;">
            <div class="field" style="margin-bottom: 14px;">
              <label for="otherStatusSelect">Status Pengerjaan <span class="text-accent">*</span></label>
              <select name="status" id="otherStatusSelect" class="input">
                <option value="completed">✅ Selesai (Completed)</option>
                <option value="in_progress">🔧 Sedang Diproses (In Progress)</option>
                <option value="reported">⏳ Baru (Reported)</option>
                <option value="cancelled">❌ Dibatalkan (Cancelled)</option>
              </select>
            </div>

            <div class="field" style="margin-bottom: 14px;">
              <label for="otherStaffSelect">Ditangani Oleh (Staff / Teknisi)</label>
              <select name="handled_by" id="otherStaffSelect" class="input">
                <option value="">— Belum Ditentukan —</option>
                @foreach($users ?? [] as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roles->pluck('name')->implode(', ') ?: 'Staff' }})</option>
                @endforeach
              </select>
            </div>

            <div class="field" style="margin-bottom: 14px;">
              <label for="otherCost">Biaya Perbaikan (Rp)</label>
              <input type="number" name="cost" id="otherCost" class="input" min="0" step="1000" placeholder="0">
              <span class="small muted" style="margin-top: 4px; display: block; font-size: 11px;">Biaya otomatis tersinkronisasi ke modul Pengeluaran (Expenses).</span>
            </div>

            <div class="field" style="margin-bottom: 16px;">
              <label for="otherImage">Bukti Foto Penyelesaian</label>
              <div id="otherImagePreview" style="margin-bottom: 8px; display:none;">
                <img id="otherImgThumb" src="" alt="Bukti Selesai" style="max-height: 80px; border-radius: 4px; border: 1px solid #ddd;">
              </div>
              <input type="file" name="completion_image" id="otherImage" class="input" accept="image/*">
            </div>
          </div>

          <div class="dialog-actions">
            <button type="button" class="btn btn-secondary" onclick="closeUpdateStatusModal()">Batal</button>
            <button type="submit" id="statusSubmitBtn" class="btn btn-primary">
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function setReportedMode(mode) {
        const statusInput = document.getElementById('reportedStatusInput');
        const btnProcess = document.getElementById('btnReportedProcess');
        const btnCancel = document.getElementById('btnReportedCancel');
        const staffField = document.getElementById('reportedStaffField');
        const staffSelect = document.getElementById('reportedStaffSelect');
        const cancelNotice = document.getElementById('reportedCancelNotice');
        const submitBtn = document.getElementById('statusSubmitBtn');

        statusInput.value = mode;

        if (mode === 'in_progress') {
          btnProcess.style.background = 'var(--color-neutral-900)';
          btnProcess.style.color = '#ffffff';
          btnProcess.style.border = '2px solid var(--color-neutral-900)';

          btnCancel.style.background = 'transparent';
          btnCancel.style.color = 'var(--color-neutral-700)';
          btnCancel.style.border = '1px solid var(--color-neutral-300)';

          staffField.style.display = 'block';
          staffSelect.disabled = false;
          staffSelect.required = true;
          cancelNotice.style.display = 'none';

          submitBtn.textContent = 'Mulai Proses Perbaikan';
          submitBtn.style.background = 'var(--color-neutral-900)';
          submitBtn.style.borderColor = 'var(--color-neutral-900)';
        } else {
          btnCancel.style.background = 'var(--color-accent, #e53e3e)';
          btnCancel.style.color = '#ffffff';
          btnCancel.style.border = '2px solid var(--color-accent, #e53e3e)';

          btnProcess.style.background = 'transparent';
          btnProcess.style.color = 'var(--color-neutral-700)';
          btnProcess.style.border = '1px solid var(--color-neutral-300)';

          staffField.style.display = 'none';
          staffSelect.disabled = true;
          staffSelect.required = false;
          cancelNotice.style.display = 'block';

          submitBtn.textContent = 'Batalkan Laporan';
          submitBtn.style.background = 'var(--color-accent, #e53e3e)';
          submitBtn.style.borderColor = 'var(--color-accent, #e53e3e)';
        }
      }

      function setInProgressMode(mode) {
        const statusInput = document.getElementById('inProgressStatusInput');
        const btnComplete = document.getElementById('btnInProgressComplete');
        const btnCancel = document.getElementById('btnInProgressCancel');
        const completionFields = document.getElementById('inProgressCompletionFields');
        const costInput = document.getElementById('inProgressCost');
        const imageInput = document.getElementById('inProgressImage');
        const cancelNotice = document.getElementById('inProgressCancelNotice');
        const submitBtn = document.getElementById('statusSubmitBtn');

        statusInput.value = mode;

        if (mode === 'completed') {
          btnComplete.style.background = 'var(--color-neutral-900)';
          btnComplete.style.color = '#ffffff';
          btnComplete.style.border = '2px solid var(--color-neutral-900)';

          btnCancel.style.background = 'transparent';
          btnCancel.style.color = 'var(--color-neutral-700)';
          btnCancel.style.border = '1px solid var(--color-neutral-300)';

          completionFields.style.display = 'block';
          costInput.disabled = false;
          imageInput.disabled = false;
          cancelNotice.style.display = 'none';

          submitBtn.textContent = 'Tandai Selesai & Simpan';
          submitBtn.style.background = 'var(--color-neutral-900)';
          submitBtn.style.borderColor = 'var(--color-neutral-900)';
        } else {
          btnCancel.style.background = 'var(--color-accent, #e53e3e)';
          btnCancel.style.color = '#ffffff';
          btnCancel.style.border = '2px solid var(--color-accent, #e53e3e)';

          btnComplete.style.background = 'transparent';
          btnComplete.style.color = 'var(--color-neutral-700)';
          btnComplete.style.border = '1px solid var(--color-neutral-300)';

          completionFields.style.display = 'none';
          costInput.disabled = true;
          imageInput.disabled = true;
          cancelNotice.style.display = 'block';

          submitBtn.textContent = 'Batalkan Laporan';
          submitBtn.style.background = 'var(--color-accent, #e53e3e)';
          submitBtn.style.borderColor = 'var(--color-accent, #e53e3e)';
        }
      }

      function openUpdateStatusModal(actionUrl, title, room, status, handledBy, cost, completionImg, handlerName) {
        const modal = document.getElementById('updateStatusModal');
        const form = document.getElementById('updateStatusForm');
        const mainTitle = document.getElementById('statusModalMainTitle');
        const titleSpan = document.getElementById('statusModalTitle');
        const roomSpan = document.getElementById('statusModalRoom');

        const secReported = document.getElementById('sectionReported');
        const secInProgress = document.getElementById('sectionInProgress');
        const secOther = document.getElementById('sectionOtherStatus');
        const submitBtn = document.getElementById('statusSubmitBtn');

        form.action = actionUrl;
        titleSpan.textContent = title;
        roomSpan.textContent = room;

        // Reset display
        secReported.style.display = 'none';
        secInProgress.style.display = 'none';
        secOther.style.display = 'none';

        // Nonaktifkan semua input section terlebih dahulu agar form yang terkirim bersih
        secReported.querySelectorAll('input, select').forEach(el => el.disabled = true);
        secInProgress.querySelectorAll('input, select').forEach(el => el.disabled = true);
        secOther.querySelectorAll('input, select').forEach(el => el.disabled = true);

        if (status === 'reported') {
          mainTitle.textContent = 'Tindak Lanjut Laporan Kerusakan';
          secReported.style.display = 'block';
          secReported.querySelectorAll('input, select').forEach(el => el.disabled = false);

          const staffSelect = document.getElementById('reportedStaffSelect');
          staffSelect.value = handledBy || '';

          setReportedMode('in_progress');
        } else if (status === 'in_progress') {
          mainTitle.textContent = 'Selesaikan Perbaikan Kerusakan';
          secInProgress.style.display = 'block';
          secInProgress.querySelectorAll('input, select').forEach(el => el.disabled = false);

          const handlerText = document.getElementById('inProgressHandlerText');
          handlerText.textContent = handlerName || 'Belum ditentukan';

          const costInput = document.getElementById('inProgressCost');
          costInput.value = cost || '';

          const imgInput = document.getElementById('inProgressImage');
          imgInput.value = '';

          setInProgressMode('completed');
        } else {
          mainTitle.textContent = 'Update Status Perbaikan';
          secOther.style.display = 'block';
          secOther.querySelectorAll('input, select').forEach(el => el.disabled = false);

          document.getElementById('otherStatusSelect').value = status || 'completed';
          document.getElementById('otherStaffSelect').value = handledBy || '';
          document.getElementById('otherCost').value = cost || 0;

          const previewWrap = document.getElementById('otherImagePreview');
          const thumb = document.getElementById('otherImgThumb');
          if (completionImg) {
            thumb.src = completionImg;
            previewWrap.style.display = 'block';
          } else {
            previewWrap.style.display = 'none';
          }

          submitBtn.textContent = 'Simpan Perubahan';
          submitBtn.style.background = 'var(--color-neutral-900)';
          submitBtn.style.borderColor = 'var(--color-neutral-900)';
        }

        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
      }

      function closeUpdateStatusModal() {
        const modal = document.getElementById('updateStatusModal');
        if (modal) modal.style.display = 'none';
      }

      function openDeleteMaintenanceModal(actionUrl, title, room) {
        const modal = document.getElementById('deleteMaintenanceModal');
        const form = document.getElementById('deleteMaintenanceForm');
        const titleSpan = document.getElementById('deleteMaintenanceTitle');
        const roomSpan = document.getElementById('deleteMaintenanceRoom');

        form.action = actionUrl;
        titleSpan.textContent = title;
        roomSpan.textContent = room;
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
      }

      function closeDeleteMaintenanceModal() {
        const modal = document.getElementById('deleteMaintenanceModal');
        modal.style.display = 'none';
      }

      window.addEventListener('click', function(e) {
        const delModal = document.getElementById('deleteMaintenanceModal');
        if (e.target === delModal) {
          closeDeleteMaintenanceModal();
        }
        const statusModal = document.getElementById('updateStatusModal');
        if (e.target === statusModal) {
          closeUpdateStatusModal();
        }
      });
    </script>
@endsection

