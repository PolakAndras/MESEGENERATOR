/*
 * Megerősítő kérdés törlés és „Újat kezdek” előtt (App Flow S-05, S-11, S-12, S-13). A kérdés szövege a
 * data-confirm attribútumban, a lang-fájlból jön. JS nélkül az űrlap megerősítés nélkül megy el – a művelet akkor
 * is szerveroldali és PRG-mintás marad (CG 11.2).
 * Confirmation before deleting and "Start new" (App Flow S-05, S-11, S-12, S-13). The question text lives in the
 * data-confirm attribute, from the lang file. Without JS the form submits unconfirmed – the action stays
 * server-side and PRG-based anyway (CG 11.2).
 */
export function init(root = document) {
  root.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm)) {
        event.preventDefault();
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => init());
