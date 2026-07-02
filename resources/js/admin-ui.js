/**
 * Admin panel UX helpers — flashes, modals, table responsiveness.
 */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.adm-flash').forEach((el) => {
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'adm-flash-close';
    close.setAttribute('aria-label', 'Funga');
    close.textContent = '×';
    close.addEventListener('click', () => el.remove());
    el.appendChild(close);
    setTimeout(() => {
      if (el.isConnected) {
        el.classList.add('adm-flash--hide');
        setTimeout(() => el.remove(), 400);
      }
    }, 8000);
  });

  document.querySelectorAll('[data-adm-modal-open]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-adm-modal-open');
      const modal = document.getElementById(id);
      if (modal) modal.classList.remove('hidden');
    });
  });

  document.querySelectorAll('[data-adm-modal-close]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.adm-modal');
      if (modal) modal.classList.add('hidden');
    });
  });

  document.querySelectorAll('.adm-modal-backdrop').forEach((backdrop) => {
    backdrop.addEventListener('click', () => {
      backdrop.closest('.adm-modal')?.classList.add('hidden');
    });
  });
});

window.admOpenAssignModal = function (jobId, title, price) {
  const modal = document.getElementById('assign-worker-modal');
  const form = document.getElementById('assign-worker-form');
  const amount = document.getElementById('assign_agreed_amount');
  if (!modal || !form) return;
  document.getElementById('assign-modal-job').textContent = title;
  form.action = `${window.__ADM_JOBS_BASE || '/admin/jobs'}/${jobId}/assign-worker`;
  if (amount) amount.value = price > 0 ? price : 1000;
  modal.classList.remove('hidden');
};

window.admCloseAssignModal = function () {
  document.getElementById('assign-worker-modal')?.classList.add('hidden');
};
