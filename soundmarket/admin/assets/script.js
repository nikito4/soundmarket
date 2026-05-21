/* SoundMarket Admin — script.js */
'use strict';

/* ── Dark mode ── */
(function () {
  const html = document.documentElement;
  const saved = localStorage.getItem('sm_admin_theme');
  if (saved === 'dark') html.setAttribute('data-theme', 'dark');

  const btn = document.getElementById('darkToggle');
  if (btn) {
    btn.textContent = html.getAttribute('data-theme') === 'dark' ? '☀️' : '🌙';
    btn.addEventListener('click', () => {
      const isDark = html.getAttribute('data-theme') === 'dark';
      html.setAttribute('data-theme', isDark ? 'light' : 'dark');
      localStorage.setItem('sm_admin_theme', isDark ? 'light' : 'dark');
      btn.textContent = isDark ? '🌙' : '☀️';
    });
  }
})();

/* ── Sidebar toggle ── */
(function () {
  const sidebar  = document.getElementById('sidebar');
  const overlay  = document.getElementById('sidebarOverlay');
  const hamburger = document.getElementById('hamburger');
  const closeBtn = document.getElementById('sidebarClose');

  function open()  { sidebar.classList.add('open'); overlay.classList.add('open'); }
  function close() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

  if (hamburger) hamburger.addEventListener('click', open);
  if (closeBtn)  closeBtn.addEventListener('click', close);
  if (overlay)   overlay.addEventListener('click', close);
})();

/* ── Profile dropdown ── */
(function () {
  const btn      = document.getElementById('profileBtn');
  const dropdown = document.getElementById('profileDropdown');
  if (!btn) return;

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('open');
  });
  document.addEventListener('click', () => dropdown.classList.remove('open'));
})();

/* ── Confirm delete ── */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-confirm]');
  if (!btn) return;
  const msg = btn.dataset.confirm || 'Сигурен ли си?';
  if (!confirm(msg)) e.preventDefault();
});

/* ── Order row expand ── */
document.addEventListener('click', (e) => {
  const trigger = e.target.closest('[data-expand]');
  if (!trigger) return;
  const targetId = trigger.dataset.expand;
  const row = document.getElementById('expand-' + targetId);
  if (!row) return;
  const isOpen = row.classList.contains('open');
  // Close all expand rows
  document.querySelectorAll('.expand-row.open').forEach(r => r.classList.remove('open'));
  // Toggle current
  if (!isOpen) {
    row.classList.add('open');
    trigger.querySelector('.expand-icon').textContent = '▲';
  } else {
    trigger.querySelector('.expand-icon').textContent = '▼';
  }
  // Reset other icons
  document.querySelectorAll('[data-expand]').forEach(t => {
    if (t !== trigger) {
      const icon = t.querySelector('.expand-icon');
      if (icon) icon.textContent = '▼';
    }
  });
});

/* ── Inline price edit ── */
document.addEventListener('click', (e) => {
  const display = e.target.closest('.price-display');
  if (!display) return;
  const wrap = display.closest('.price-wrap');
  if (!wrap) return;
  display.style.display = 'none';
  const form = wrap.querySelector('.price-edit-form');
  if (form) form.classList.add('active');
});

document.addEventListener('click', (e) => {
  if (e.target.matches('.price-cancel')) {
    const wrap = e.target.closest('.price-wrap');
    if (!wrap) return;
    const display = wrap.querySelector('.price-display');
    const form    = wrap.querySelector('.price-edit-form');
    if (display) display.style.display = '';
    if (form)    form.classList.remove('active');
  }
});

/* ── User modal ── */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-modal-open]');
  if (btn) {
    const id = btn.dataset.modalOpen;
    const modal = document.getElementById('modal-' + id);
    if (modal) modal.classList.add('open');
  }

  const close = e.target.closest('[data-modal-close], .modal-backdrop');
  if (close) {
    const modal = close.closest('.modal-backdrop');
    if (modal) {
      // don't close when clicking inside .modal
      if (close.matches('.modal-backdrop') && e.target !== close) return;
      modal.classList.remove('open');
    }
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-backdrop.open')
      .forEach(m => m.classList.remove('open'));
  }
});

/* ── Status select auto-submit (orders page) ── */
document.addEventListener('change', (e) => {
  if (e.target.matches('.status-select')) {
    e.target.closest('form').submit();
  }
});
