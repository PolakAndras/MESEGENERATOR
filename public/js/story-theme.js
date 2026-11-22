/*
 * Az olvasó esti/nappali kapcsolója (CG 7.4, PRD US-08 AC6). A választás a localStorage-ba kerül
 * (mesegenerator.storyTheme), a szerverre nem; privát ablakban a tároló kivételt dobhat, ezért try/catch,
 * hiba esetén az esti mód marad. A betöltéskori beállítást az olvasó inline szkriptje végzi (villanás ellen).
 *
 * The reader's night/day switch (CG 7.4, PRD US-08 AC6). The choice goes to localStorage
 * (mesegenerator.storyTheme), never to the server; storage can throw in private windows, hence try/catch,
 * and night mode stays on error. The on-load setting is applied by the reader's inline script (no flash).
 */
const KEY = 'mesegenerator.storyTheme';

export function init(root = document) {
  root.querySelectorAll('[data-story-reader]').forEach((reader) => {
    const toggle = reader.querySelector('[data-story-theme-toggle]');
    if (!toggle) return;

    toggle.addEventListener('click', () => {
      const next = reader.getAttribute('data-story-theme') === 'night' ? 'day' : 'night';
      reader.setAttribute('data-story-theme', next);
      try {
        localStorage.setItem(KEY, next);
      } catch (e) {
        // A tároló nem érhető el: a téma csak erre az oldalra érvényes. / Storage unavailable: theme applies to this page only.
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => init());
