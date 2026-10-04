import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/* ============================================================================
   KosFly — app.js
   ----------------------------------------------------------------------------
   Berisi HANYA interaksi yang benar-benar dipakai halaman Blade saat ini:

   1. Bootstrap Alpine (dipakai komponen Breeze: dropdown, modal, profil).
   2. Sidebar: collapse di desktop + drawer off-canvas di mobile (#hamburger).
   3. Notifikasi & menu user di topbar (toggle dropdown).
   4. Dialog/modal statis: tutup via data-action="close-dialog" atau klik backdrop.
   5. Konfirmasi hapus yang mengirim form sungguhan (rooms/index).
   6. Toast server-side (session('success'|'error'|'info')).

   Prinsip: HTML di Blade adalah struktur utama. File ini tidak merender data,
   tidak menyimpan demoData, dan tidak tahu apa-apa soal domain kos.

   CATATAN UNTUK KONTRIBUTOR:
   Prototype lama (demoData, modal form room/pay/maint, render tabel, dsb) sudah
   dihapus karena semua halaman sudah memakai data backend. Kalau butuh perilaku
   UI baru, tambahkan di sini dengan pola event-delegation yang sama.
   ========================================================================== */

'use strict';

/* ------------------------------- HELPERS ------------------------------- */
const $ = (sel, ctx) => (ctx || document).querySelector(sel);
const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

/* ------------------------------- TOAST & CONFIRM DIALOG ------------------------------- */
// showToast(message, type) — type: 'success' | 'error' | 'warning' | 'info'
function showToast(message, type = 'info') {
  let root = $('#toastRoot');
  if (!root) {
    root = document.createElement('div');
    root.id = 'toastRoot';
    document.body.appendChild(root);
  }

  const cls = { success: 'ok', ok: 'ok', error: 'err', err: 'err', warning: 'warn', warn: 'warn' }[type] || 'info';
  const el = document.createElement('div');
  el.className = 'toast ' + cls;
  el.style.cursor = 'pointer';
  el.title = 'Klik untuk menutup';
  el.addEventListener('click', () => el.remove());

  let iconSvg = '';
  if (cls === 'ok') {
    iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';
  } else if (cls === 'err') {
    iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
  } else {
    iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="12" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="8"></line></svg>';
  }

  el.innerHTML = `${iconSvg}<span>${message}</span>`;
  root.appendChild(el);

  setTimeout(() => {
    el.style.opacity = '0';
    el.style.transition = 'opacity .3s ease, transform .3s ease';
    el.style.transform = 'translateY(-6px)';
    setTimeout(() => el.remove(), 350);
  }, 4500);
}

// Reusable Confirmation Dialog Modal (Promise-based)
function showConfirmDialog(options = {}) {
  return new Promise((resolve) => {
    const title = options.title || 'Konfirmasi Tindakan';
    const message = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
    const confirmText = options.confirmText || 'Ya, Lanjutkan';
    const cancelText = options.cancelText || 'Batal';
    const confirmColor = options.confirmColor || 'var(--color-primary, #e11d48)';

    let root = $('#dialogRoot');
    if (!root) {
      root = document.createElement('div');
      root.id = 'dialogRoot';
      document.body.appendChild(root);
    }

    const backdrop = document.createElement('div');
    backdrop.className = 'dialog-backdrop';
    backdrop.style.display = 'flex';
    backdrop.style.alignItems = 'center';
    backdrop.style.justifyContent = 'center';
    backdrop.style.zIndex = '9999';

    backdrop.innerHTML = `
      <div class="dialog" style="margin: auto; max-width: 440px;">
        <div class="dialog-title">${title}</div>
        <p class="small muted" style="margin: 8px 0 16px; line-height: 1.6;">${message}</p>
        <div class="dialog-actions">
          <button type="button" class="btn btn-secondary js-dialog-cancel">${cancelText}</button>
          <button type="button" class="btn btn-primary js-dialog-confirm" style="background-color: ${confirmColor}; border-color: ${confirmColor};">${confirmText}</button>
        </div>
      </div>
    `;

    function cleanup(val) {
      backdrop.remove();
      resolve(val);
    }

    backdrop.querySelector('.js-dialog-cancel').addEventListener('click', () => cleanup(false));
    backdrop.querySelector('.js-dialog-confirm').addEventListener('click', () => cleanup(true));
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) cleanup(false);
    });

    root.appendChild(backdrop);
  });
}

// Expose globally
window.showToast = showToast;
window.showConfirmDialog = showConfirmDialog;

// Fallback: Ganti window.alert bawaan browser dengan Toast custom
window.alert = function(msg) {
  showToast(String(msg), 'info');
};

// Auto-dismiss toast yang dirender server (session flash / error validasi).
function initServerToasts() {
  $$('#toastRoot .toast').forEach((toast, index) => {
    if (toast.dataset.autoDismiss === '1') return;
    toast.dataset.autoDismiss = '1';
    toast.style.cursor = 'pointer';
    toast.title = 'Klik untuk menutup';
    toast.addEventListener('click', () => toast.remove());
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity .3s ease, transform .3s ease';
      toast.style.transform = 'translateY(-6px)';
      setTimeout(() => toast.remove(), 350);
    }, 4500 + index * 600);
  });
}

/* ------------------------------- SIDEBAR ------------------------------- */
// Satu tombol toggle (#hamburger) di topbar:
//   - layar >= 641px -> ciutkan / lebarkan sidebar (body.sidebar-collapsed)
//   - layar <= 640px -> buka / tutup drawer off-canvas (body.nav-open)
// State collapse disimpan di localStorage agar persisten antar halaman.
const SIDEBAR_STORAGE = 'kosfly_sidebar_collapsed';

function isMobileNav() {
  return window.matchMedia('(max-width: 640px)').matches;
}

function setSidebarState(collapsed) {
  const apply = !!collapsed;
  document.body.classList.toggle('sidebar-collapsed', apply);

  let stored = null;
  try {
    stored = localStorage.getItem(SIDEBAR_STORAGE) === '1';
  } catch (e) {
    /* mode privat: abaikan */
  }
  if (stored !== apply) {
    try {
      localStorage.setItem(SIDEBAR_STORAGE, apply ? '1' : '0');
    } catch (e) {
      /* mode privat: abaikan */
    }
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

function toggleNav() {
  if (isMobileNav()) toggleMobileNav();
  else toggleSidebar();
}

function initSidebar() {
  // Label bantu untuk ikon menu (aksesibilitas).
  $$('.side-nav .nav-btn').forEach((b) => {
    const labelEl = b.querySelector('span');
    if (!labelEl) return;
    const label = labelEl.textContent.trim();
    if (!b.getAttribute('title')) b.title = label;
    if (!b.getAttribute('aria-label')) b.setAttribute('aria-label', label);
  });

  let saved = null;
  try {
    saved = localStorage.getItem(SIDEBAR_STORAGE);
  } catch (e) {
    /* mode privat: abaikan */
  }
  setSidebarState(saved === '1');

  window.addEventListener('resize', () => {
    if (!isMobileNav()) document.body.classList.remove('nav-open');
  });
}

/* --------------------------- MENU / DROPDOWN --------------------------- */
function closeAllMenus() {
  const notif = $('#notifMenu');
  const user = $('#userMenu');
  if (notif) notif.classList.remove('show');
  if (user) user.classList.remove('show');
}

function closeDialogs() {
  $$('#dialogRoot .dialog-backdrop').forEach((d) => {
    d.hidden = true;
  });
}

/* --------------------------- KONFIRMASI HAPUS --------------------------- */
// Tombol dengan data-action="room-delete-open" membuka #modalConfirm dan
// mengarahkan form #formDelete ke data-url tombol tersebut.
function openDeleteConfirm(button) {
  const form = $('#formDelete');
  const modal = $('#modalConfirm');
  if (!form || !modal) return;

  form.setAttribute('action', button.dataset.url || '');
  const nameEl = $('#delRoomName');
  if (nameEl) nameEl.textContent = button.dataset.name || '—';
  modal.hidden = false;
}

/* ------------------------------- EVENTS ------------------------------- */
document.addEventListener('click', (e) => {
  const el = e.target.closest('[data-action]');

  if (el) {
    switch (el.dataset.action) {
      case 'toggle-nav':
        toggleNav();
        return;

      case 'toggle-notif': {
        e.stopPropagation();
        const menu = $('#notifMenu');
        const dot = $('#notifDot');
        if (menu) menu.classList.toggle('show');
        if (dot) dot.hidden = true;
        const userMenu = $('#userMenu');
        if (userMenu) userMenu.classList.remove('show');
        return;
      }

      case 'toggle-user': {
        e.stopPropagation();
        const menu = $('#userMenu');
        if (menu) menu.classList.toggle('show');
        const notifMenu = $('#notifMenu');
        if (notifMenu) notifMenu.classList.remove('show');
        return;
      }

      case 'close-dialog':
        closeDialogs();
        return;

      case 'room-delete-open':
        openDeleteConfirm(el);
        return;

      default:
        break;
    }
  }

  // Tutup dropdown saat klik di luar area terkait.
  if (!e.target.closest('.notif')) {
    const notifMenu = $('#notifMenu');
    if (notifMenu) notifMenu.classList.remove('show');
  }
  if (!e.target.closest('.userchip')) {
    const userMenu = $('#userMenu');
    if (userMenu) userMenu.classList.remove('show');
  }

  // Klik backdrop dialog (elemen ber-data-close) menutup dialog.
  if (e.target.getAttribute && e.target.getAttribute('data-close') === '1') {
    closeDialogs();
  }

  // Tutup drawer navigasi mobile.
  if (e.target.id === 'navBackdrop') {
    document.body.classList.remove('nav-open');
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return;

  const openDialog = $('#dialogRoot .dialog-backdrop:not([hidden])');
  if (openDialog) {
    closeDialogs();
    return;
  }

  if (document.body.classList.contains('nav-open')) {
    document.body.classList.remove('nav-open');
    return;
  }

  closeAllMenus();
});

/* ------------------------------- INIT ------------------------------- */
function init() {
  initSidebar();
  initServerToasts();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
