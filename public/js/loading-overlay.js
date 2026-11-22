/*
 * Töltésjelző (App Flow K-08, CG 9.1, PRD US-07 AC7). Csak a data-loading-submit gombbal elküldött űrlapnál jelenik
 * meg (a szerkesztőben a lapozó gombok nem generálnak). A gomb letiltódik a dupla küldés ellen, 20 mp után az alcím
 * türelemre kér, és a böngésző „Vissza” gombjával visszatérve (pageshow, persisted) a réteg eltűnik.
 *
 * Loading overlay (App Flow K-08, CG 9.1, PRD US-07 AC7). It only appears for forms submitted with a
 * data-loading-submit button (the editor's paging buttons do not generate). The button is disabled against double
 * submits, the subtitle asks for patience after 20 s, and coming back with the browser's Back button (pageshow,
 * persisted) hides the layer again.
 */
const SLOW_AFTER_MS = 20000;

export function init(root = document) {
  const overlay = root.querySelector('[data-loading-overlay]');
  if (!overlay) return;
  const card = overlay.querySelector('[data-loading-card]');
  const subtitle = overlay.querySelector('[data-loading-subtitle]');
  const normalText = subtitle.textContent;
  let timer = null;
  let activeButton = null;

  root.querySelectorAll('[data-loading-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const button = event.submitter;
      if (!button || !button.matches('[data-loading-submit]')) return;

      // A letiltott gomb nem küldené el a formaction-t, ezért csak a küldés után, a következő ütemben tiltjuk le.
      // A disabled button would drop its formaction, so it is disabled only after the submit, on the next tick.
      setTimeout(() => {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
      }, 0);
      activeButton = button;
      overlay.hidden = false;
      card.focus();
      timer = setTimeout(() => { subtitle.textContent = subtitle.dataset.slowText; }, SLOW_AFTER_MS);
    });
  });

  window.addEventListener('pageshow', (event) => {
    if (!event.persisted) return;
    overlay.hidden = true;
    subtitle.textContent = normalText;
    clearTimeout(timer);
    if (activeButton) {
      activeButton.disabled = false;
      activeButton.removeAttribute('aria-busy');
    }
  });
}

document.addEventListener('DOMContentLoaded', () => init());
