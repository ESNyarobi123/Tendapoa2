/**
 * Tanzania mobile numbers inside free text (07/06/255/+255, spaces/hyphens).
 */
export const TP_NO_PHONE_MESSAGE =
  'Usiweke nambari ya simu kwenye kichwa au maelezo. Tumia mazungumzo ya ndani ya mfumo.';

export const TP_PHONE_ALLOWED_CHAT_MESSAGE =
  'Malipo ya escrow yamethibitishwa na kazi imeanzishwa. Unaweza kushiriki nambari yako ya simu hapa ili muweze kuwasiliana na kukutana.';

export function tpTextContainsPhoneNumber(text) {
  if (!text || !String(text).trim()) return false;
  const raw = String(text);
  const digitsOnly = raw.replace(/\D/g, '');
  if (/0[67]\d{8}/.test(digitsOnly) || /255[67]\d{8}/.test(digitsOnly)) {
    return true;
  }
  if (/(?<!\d)[67]\d{8}(?!\d)/.test(digitsOnly)) {
    return true;
  }
  if (/(?<!\d)0[67]\d{5,9}(?!\d)/.test(digitsOnly)) {
    return true;
  }
  if (/(?<!\d)255[67]\d{5,9}(?!\d)/.test(digitsOnly)) {
    return true;
  }
  if (/(?<!\d)[67]\d{5,7}(?!\d)/.test(digitsOnly)) {
    return true;
  }
  if (/(?<!\d)0[\s\-.]?[67](?:[\s\-.]?\d){5,11}/u.test(raw)) {
    return true;
  }
  if (/(?:\+|00)?[\s]*255[\s\-.]?[67](?:[\s\-.]?\d){5,11}/u.test(raw)) {
    return true;
  }
  return /(?<!\d)[67](?:[\s\-.]?\d){5,11}(?!\d)/u.test(raw);
}

function ensureInlineErrorEl(field) {
  const wrapper = field.closest('.input-wrapper') || field.parentElement;
  if (!wrapper) return null;
  let el = wrapper.querySelector('.tp-phone-inline-error');
  if (!el) {
    el = document.createElement('p');
    el.className =
      'tp-phone-inline-error mt-1.5 text-[12px] font-semibold leading-snug text-red-600';
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'polite');
    wrapper.appendChild(el);
  }
  return el;
}

export function tpSetPhoneFieldError(field, hasError) {
  if (!field) return;
  const err = ensureInlineErrorEl(field);
  if (hasError) {
    field.classList.add(
      '!border-red-500',
      'ring-2',
      'ring-red-200',
      'focus:!border-red-500',
      'focus:ring-red-200',
    );
    field.setAttribute('aria-invalid', 'true');
    if (err) err.textContent = TP_NO_PHONE_MESSAGE;
  } else {
    field.classList.remove(
      '!border-red-500',
      'ring-2',
      'ring-red-200',
      'focus:!border-red-500',
      'focus:ring-red-200',
    );
    field.removeAttribute('aria-invalid');
    if (err) err.textContent = '';
  }
}

export function tpCheckPhoneField(field) {
  if (!field) return false;
  const bad = tpTextContainsPhoneNumber(field.value);
  tpSetPhoneFieldError(field, bad);
  return bad;
}

/**
 * Live red validation while typing (web).
 */
export function tpAttachLivePhoneValidation(form, fieldNames) {
  if (!form || !fieldNames?.length) return;
  if (form.getAttribute('data-tp-allow-phone') === '1') return;

  fieldNames.forEach((name) => {
    const field = form.querySelector(`[name="${name}"]`);
    if (!field) return;
    const run = () => {
      if (!field.value.trim()) {
        tpSetPhoneFieldError(field, false);
        return;
      }
      tpCheckPhoneField(field);
    };
    field.addEventListener('input', run);
    field.addEventListener('blur', run);
  });
}

/**
 * Attach submit-time validation to a form for named fields.
 */
export function tpAttachNoPhoneFormValidation(form, fieldNames) {
  if (!form || !fieldNames?.length) return;
  if (form.getAttribute('data-tp-allow-phone') === '1') return;

  tpAttachLivePhoneValidation(form, fieldNames);

  form.addEventListener('submit', (event) => {
    for (const name of fieldNames) {
      const field = form.querySelector(`[name="${name}"]`);
      if (!field) continue;
      if (tpCheckPhoneField(field)) {
        event.preventDefault();
        if (typeof window.tpToast === 'function') {
          window.tpToast(TP_NO_PHONE_MESSAGE, 'error');
        }
        field.focus();
        return;
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-tp-no-phone-fields]').forEach((form) => {
    if (form.getAttribute('data-tp-allow-phone') === '1') return;
    const raw = form.getAttribute('data-tp-no-phone-fields') || '';
    const fields = raw
      .split(',')
      .map((s) => s.trim())
      .filter(Boolean);
    tpAttachNoPhoneFormValidation(form, fields);
  });
});

if (typeof window !== 'undefined') {
  window.tpTextContainsPhoneNumber = tpTextContainsPhoneNumber;
  window.TP_NO_PHONE_MESSAGE = TP_NO_PHONE_MESSAGE;
  window.tpSetPhoneFieldError = tpSetPhoneFieldError;
  window.tpCheckPhoneField = tpCheckPhoneField;
}
