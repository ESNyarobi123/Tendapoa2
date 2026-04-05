/**
 * TendaPoa — toasts & modal prompt (replaces alert/prompt where loaded via Vite).
 */
(function () {
  function getToastRoot() {
    let el = document.getElementById('tp-toast-root');
    if (!el) {
      el = document.createElement('div');
      el.id = 'tp-toast-root';
      el.className =
        'fixed top-4 right-4 z-[10050] flex w-[min(100%,22rem)] flex-col gap-2 pointer-events-none px-3 sm:px-0';
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
      'pointer-events-auto rounded-xl border px-4 py-3 text-[13px] font-semibold shadow-lg transition ' +
      (type === 'success'
        ? 'border-emerald-500/30 bg-emerald-600 text-white'
        : type === 'error'
          ? 'border-red-500/30 bg-red-600 text-white'
          : 'border-slate-600/40 bg-slate-800 text-white');
    card.textContent = msg;
    root.appendChild(card);
    setTimeout(() => {
      card.style.opacity = '0';
      card.style.transform = 'translateX(8px)';
      card.style.transition = 'opacity 0.25s, transform 0.25s';
      setTimeout(() => card.remove(), 260);
    }, 4200);
  }

  function ensureDialogShell() {
    let host = document.getElementById('tp-dialog-host');
    if (host) return host;
    host = document.createElement('div');
    host.id = 'tp-dialog-host';
    host.innerHTML =
      '<div id="tp-dialog-backdrop" class="fixed inset-0 z-[10060] hidden bg-slate-900/50 backdrop-blur-[1px]" aria-hidden="true"></div>' +
      '<div id="tp-dialog-panel" class="fixed left-1/2 top-1/2 z-[10061] hidden w-[min(100%,24rem)] -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl" role="dialog" aria-modal="true">' +
      '<p id="tp-dialog-title" class="text-[14px] font-bold text-slate-900"></p>' +
      '<textarea id="tp-dialog-input" rows="3" class="mt-3 w-full rounded-xl border border-slate-200 px-3 py-2 text-[13px] text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"></textarea>' +
      '<div class="mt-4 flex justify-end gap-2">' +
      '<button type="button" id="tp-dialog-cancel" class="rounded-xl border border-slate-200 px-4 py-2 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Funga</button>' +
      '<button type="button" id="tp-dialog-ok" class="rounded-xl bg-brand-600 px-4 py-2 text-[12px] font-bold text-white hover:bg-brand-700">Sawa</button>' +
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
      '<div id="tp-confirm-backdrop" class="fixed inset-0 z-[10060] hidden bg-slate-900/50 backdrop-blur-[1px]" aria-hidden="true"></div>' +
      '<div id="tp-confirm-panel" class="fixed left-1/2 top-1/2 z-[10061] hidden w-[min(100%,22rem)] -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl" role="alertdialog" aria-modal="true">' +
      '<p id="tp-confirm-message" class="text-[13px] font-semibold leading-relaxed text-slate-800"></p>' +
      '<div class="mt-5 flex justify-end gap-2">' +
      '<button type="button" id="tp-confirm-cancel" class="rounded-xl border border-slate-200 px-4 py-2 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Ghairi</button>' +
      '<button type="button" id="tp-confirm-ok" class="rounded-xl bg-brand-600 px-4 py-2 text-[12px] font-bold text-white hover:bg-brand-700">Ndio</button>' +
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
        backdrop.classList.add('hidden');
        panel.classList.add('hidden');
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

      backdrop.classList.remove('hidden');
      panel.classList.remove('hidden');
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
        backdrop.classList.add('hidden');
        panel.classList.add('hidden');
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

      backdrop.classList.remove('hidden');
      panel.classList.remove('hidden');
      document.addEventListener('keydown', onKey);
      setTimeout(() => input.focus(), 50);

      btnOk.onclick = () => {
        const v = input.value.trim();
        cleanup();
        resolve(v || null);
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
