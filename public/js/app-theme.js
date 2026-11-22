/*
 * Esti / nappali mód a szülői felületen (a fejlesztő kérésére, a CG 7.4 olvasó-kapcsolójának mintájára).
 * A gomb a <html data-theme> értékét váltja, és a választást a böngésző localStorage-ába menti;
 * a szerverre semmi nem megy (PRD US-08 AC6 szellemében). A kezdő témát a fej inline szkriptje állítja be.
 * A feliratot nem a JS cseréli: mindkét felirat az oldalban van, a CSS a data-theme alapján mutatja a megfelelőt.
 *
 * Night / day mode for the parent UI (at the developer's request, modelled on the CG 7.4 reader toggle).
 * The button switches <html data-theme> and stores the choice in the browser's localStorage;
 * nothing goes to the server. The initial theme is set by the inline script in <head>.
 * The JS does not swap the label: both labels are in the page and the CSS shows the right one by data-theme.
 */
const STORAGE_KEY = 'mesegenerator.appTheme';

export function init(root = document) {
  root.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const html = document.documentElement;
      const next = html.getAttribute('data-theme') === 'night' ? 'day' : 'night';
      html.setAttribute('data-theme', next);
      try {
        localStorage.setItem(STORAGE_KEY, next);
      } catch (error) {
        // Privát ablakban a tároló nem írható; a téma ilyenkor csak erre az oldalra érvényes.
        // In private windows storage is not writable; the theme then only applies to this page.
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => init());
