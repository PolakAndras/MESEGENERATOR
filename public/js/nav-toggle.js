/*
 * Mobilmenü (CG 6.4, 11.2): a hamburger gomb nyitja és zárja a fejléc menüjét telefonon.
 * Az elemeket data- attribútummal keresi, nem CSS-osztállyal, így a stílus átnevezése nem töri el.
 * Az állapotot az aria-expanded hordozza: ezt olvassa a képernyőolvasó, és erre épül a CSS is.
 * Esc és a menün kívüli kattintás bezárja (CG 6.4).
 *
 * Mobile menu (CG 6.4, 11.2): the hamburger button opens and closes the header menu on phones.
 * Elements are found by data- attributes, not CSS classes, so renaming styles does not break it.
 * The state lives in aria-expanded: screen readers read it and the CSS builds on it.
 * Esc and a click outside the menu close it (CG 6.4).
 */
export function init(root = document) {
  root.querySelectorAll('[data-nav]').forEach((nav) => {
    const toggle = nav.querySelector('[data-nav-toggle]');
    if (!toggle) {
      return;
    }

    // Az aria-expanded a képernyőolvasónak szól, a site-header--menu-open módosító a CSS-nek (CG 11.1:
    // állapot ARIA-val vagy módosító osztállyal); a gomb és a menü nem testvérek, ezért kell a fejlécre is.
    // aria-expanded is for screen readers, the site-header--menu-open modifier for CSS (CG 11.1: state via ARIA
    // or a modifier class); the button and the menu are not siblings, so the header needs it too.
    const setOpen = (open) => {
      toggle.setAttribute('aria-expanded', String(open));
      nav.classList.toggle('site-header--menu-open', open);
    };
    const isOpen = () => toggle.getAttribute('aria-expanded') === 'true';

    toggle.addEventListener('click', () => setOpen(!isOpen()));

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && isOpen()) {
        setOpen(false);
        toggle.focus();
      }
    });

    document.addEventListener('click', (event) => {
      if (isOpen() && !nav.contains(event.target)) {
        setOpen(false);
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => init());
