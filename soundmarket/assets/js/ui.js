/* Flash-prevention: apply saved theme before first paint */
(function () {
  if (localStorage.getItem('sm_theme') === 'light') {
    document.documentElement.classList.add('preload-light');
  }
})();

/* SoundMarket — ui.js */
(function () {
  'use strict';

  /* ── Wishlist toggle ─────────────────────────────────────────────────────── */
  document.querySelectorAll('.wish-btn').forEach(function (btn) {
    if (btn.dataset.active === '1') btn.classList.add('active');

    btn.addEventListener('click', async function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (!btn.dataset.id) return;

      var fd = new FormData();
      fd.append('product_id', btn.dataset.id);

      try {
        var res  = await fetch(APP_BASE + '/wishlist_toggle.php', { method: 'POST', body: fd });
        var data = await res.json();
      } catch (_) {
        return;
      }

      if (data.action === 'login_required') {
        window.location = APP_BASE + '/login.php';
        return;
      }

      var added = data.action === 'added';
      btn.classList.toggle('active', added);
      btn.dataset.active = added ? '1' : '0';
    });
  });

  /* ── Add to cart feedback ────────────────────────────────────────────────── */
  document.querySelectorAll('form[action*="cart.php"] button[type="submit"]')
    .forEach(function (btn) {
      btn.closest('form').addEventListener('submit', function () {
        btn.textContent = 'Добавено ✓';
        btn.style.background = '#059669';
        btn.disabled = true;
      });
    });

  /* ── Safe delete confirmation ────────────────────────────────────────────── */
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (el.dataset.confirmed === '1') return;
      e.preventDefault();
      var orig = el.textContent;
      el.textContent = 'Сигурни ли сте?';
      el.style.background  = 'rgba(239,68,68,0.2)';
      el.style.borderColor = '#ef4444';
      el.style.color       = '#ef4444';
      el.dataset.confirmed = '1';
      setTimeout(function () {
        el.textContent = orig;
        el.style.background  = '';
        el.style.borderColor = '';
        el.style.color       = '';
        el.dataset.confirmed = '0';
      }, 3000);
    });
  });

  /* ── Form validation feedback ────────────────────────────────────────────── */
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var valid = true;
      form.querySelectorAll('[required]').forEach(function (input) {
        input.classList.remove('input-error', 'input-error-static');
        var old = input.nextElementSibling;
        if (old && old.classList.contains('input-error-msg')) old.remove();
        if (!input.value.trim()) {
          valid = false;
          input.classList.add('input-error');
          var msg = document.createElement('span');
          msg.className   = 'input-error-msg';
          msg.textContent = 'Това поле е задължително';
          input.after(msg);
          input.addEventListener('animationend', function () {
            input.classList.remove('input-error');
            input.classList.add('input-error-static');
          }, { once: true });
        }
      });
      if (!valid) e.preventDefault();
    });
  });

  /* ── Dark mode toggle ────────────────────────────────────────────────────── */
  (function () {
    var btn  = document.getElementById('theme-toggle');
    var icon = document.getElementById('theme-icon');
    if (!btn) return;

    var saved = localStorage.getItem('sm_theme');
    if (saved === 'light') {
      document.body.classList.add('light-mode');
      if (icon) icon.textContent = '🌙';
    }

    btn.addEventListener('click', function () {
      var isLight = document.body.classList.toggle('light-mode');
      document.documentElement.classList.toggle('preload-light', isLight);
      if (icon) icon.textContent = isLight ? '🌙' : '☀️';
      localStorage.setItem('sm_theme', isLight ? 'light' : 'dark');
    });
  })();

})();
