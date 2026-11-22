/*
 * Karakterszámláló (PRD US-02 AC2, CG 6.3): a mező alatt a hátralévő karakterek száma, 50 alatt figyelmeztető
 * színnel. A sablon a lang-fájlból jön (data-template), így a szöveg nincs a JS-be égetve.
 * Character counter (PRD US-02 AC2, CG 6.3): remaining characters under the field, in the warning colour below 50.
 * The template comes from the lang file (data-template), so no text is hard-coded in JS.
 */
const WARNING_BELOW = 50;

export function init(root = document) {
  root.querySelectorAll('[data-char-counter]').forEach((field) => {
    const output = document.getElementById(field.dataset.charCounter);
    const max = Number(field.getAttribute('maxlength'));
    if (!output || !max) return;

    const update = () => {
      const remaining = max - field.value.length;
      output.textContent = output.dataset.template.replace(':count', String(remaining));
      output.classList.toggle('form-field__counter--low', remaining < WARNING_BELOW);
    };

    output.hidden = false;
    field.addEventListener('input', update);
    update();
  });
}

document.addEventListener('DOMContentLoaded', () => init());
