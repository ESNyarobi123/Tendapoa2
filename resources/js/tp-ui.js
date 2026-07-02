/**
 * TendaPoa — toasts & modal prompt/confirm (works on admin + app).
 */
import '../css/tp-ui.css';

(function () {
  function getToastRoot() {
    let el = document.getElementById('tp-toast-root');
    if (!el) {
      el = document.createElement('div');
      el.id = 'tp-toast-root';
      el.setAttribute('aria-live', 'polite');
      document.body.appendChild(el);
    }
    return el;
  }

  function tpToast(message, type = 'info') {
    const msg = String(message || '').trim();
    if (!msg) return;
    const root = getToastRoot();
    const card = document.createElement('div');
    card.setAttribute('role', 'status');
    card.className =
      'tp-toast-card ' +
      (type === 'success'
        ? 'tp-toast-card--success'
        : type === 'error'
          ? 'tp-toast-card--error'
          : 'tp-toast-card--info');
    card.textContent = msg;
    root.appendChild(card);
    setTimeout(() => {
      card.style.opacity = '0';
      card.style.transform = 'translateX(8px)';
      setTimeout(() => card.remove(), 260);
    }, 4200);
  }

  function ensureDialogShell() {
    let host = document.getElementById('tp-dialog-host');
    if (host) return host;
    host = document.createElement('div');
    host.id = 'tp-dialog-host';
    host.innerHTML =
      '<div id="tp-dialog-backdrop" class="tp-hidden" aria-hidden="true"></div>' +
      '<div id="tp-dialog-panel" class="tp-hidden" role="dialog" aria-modal="true">' +
      '<p id="tp-dialog-title"></p>' +
      '<textarea id="tp-dialog-input" rows="3"></textarea>' +
      '<div class="tp-ui-actions">' +
      '<button type="button" id="tp-dialog-cancel" class="tp-ui-btn tp-ui-btn--ghost">Funga</button>' +
      '<button type="button" id="tp-dialog-ok" class="tp-ui-btn tp-ui-btn--primary">Sawa</button>' +
      '</div></div>';
    document.body.appendChild(host);
    return host;
  }

  function ensureConfirmShell() {
    let host = document.getElementById('tp-confirm-host');
    if (host) return host;
    host = document.createElement('div');
    host.id = 'tp-confirm-host';
    host.innerHTML =
      '<div id="tp-confirm-backdrop" class="tp-hidden" aria-hidden="true"></div>' +
      '<div id="tp-confirm-panel" class="tp-hidden" role="alertdialog" aria-modal="true">' +
      '<p id="tp-confirm-message"></p>' +
      '<div class="tp-ui-actions">' +
      '<button type="button" id="tp-confirm-cancel" class="tp-ui-btn tp-ui-btn--ghost">Ghairi</button>' +
      '<button type="button" id="tp-confirm-ok" class="tp-ui-btn tp-ui-btn--primary">Ndio</button>' +
      '</div></div>';
    document.body.appendChild(host);
    return host;
  }

  /**
   * @returns {Promise<boolean>}
   */
  function tpConfirm(message) {
    return new Promise((resolve) => {
      ensureConfirmShell();
      const backdrop = document.getElementById('tp-confirm-backdrop');
      const panel = document.getElementById('tp-confirm-panel');
      const msgEl = document.getElementById('tp-confirm-message');
      const btnOk = document.getElementById('tp-confirm-ok');
      const btnCancel = document.getElementById('tp-confirm-cancel');
      msgEl.textContent = String(message || 'Thibitisha?');

      function cleanup() {
        backdrop.classList.add('tp-hidden');
        panel.classList.add('tp-hidden');
        btnOk.onclick = null;
        btnCancel.onclick = null;
        backdrop.onclick = null;
        document.removeEventListener('keydown', onKey);
      }

      function onKey(e) {
        if (e.key === 'Escape') {
          cleanup();
          resolve(false);
        }
      }

      backdrop.classList.remove('tp-hidden');
      panel.classList.remove('tp-hidden');
      document.addEventListener('keydown', onKey);

      btnOk.onclick = () => {
        cleanup();
        resolve(true);
      };
      btnCancel.onclick = () => {
        cleanup();
        resolve(false);
      };
      backdrop.onclick = btnCancel.onclick;
    });
  }

  /**
   * @returns {Promise<string|null>} text or null if cancelled
   */
  function tpPrompt(title, initial = '') {
    return new Promise((resolve) => {
      ensureDialogShell();
      const backdrop = document.getElementById('tp-dialog-backdrop');
      const panel = document.getElementById('tp-dialog-panel');
      const titleEl = document.getElementById('tp-dialog-title');
      const input = document.getElementById('tp-dialog-input');
      const btnOk = document.getElementById('tp-dialog-ok');
      const btnCancel = document.getElementById('tp-dialog-cancel');
      titleEl.textContent = title || 'Ingiza maelezo';
      input.value = initial || '';

      function cleanup() {
        backdrop.classList.add('tp-hidden');
        panel.classList.add('tp-hidden');
        btnOk.onclick = null;
        btnCancel.onclick = null;
        backdrop.onclick = null;
        document.removeEventListener('keydown', onKey);
      }

      function onKey(e) {
        if (e.key === 'Escape') {
          cleanup();
          resolve(null);
        }
      }

      backdrop.classList.remove('tp-hidden');
      panel.classList.remove('tp-hidden');
      document.addEventListener('keydown', onKey);
      setTimeout(() => input.focus(), 50);

      btnOk.onclick = () => {
        const v = input.value.trim();
        cleanup();
        resolve(v === '' ? '' : v);
      };
      btnCancel.onclick = () => {
        cleanup();
        resolve(null);
      };
      backdrop.onclick = btnCancel.onclick;
    });
  }

  window.tpToast = tpToast;
  window.tpPrompt = tpPrompt;
  window.tpConfirm = tpConfirm;
})();
