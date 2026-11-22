/*
 * Admin szereplőűrlap (App Flow S-21): a nem és a hajszín mezői csak gyerekfigurához látszanak.
 * JS nélkül mindig látszanak; állatnál a szerver úgyis NULL-ra állítja őket (Backend Schema 8.6).
 * Admin character form (App Flow S-21): gender and hair colour only show for child figures.
 * Without JS they always show; for animals the server sets them to NULL anyway (Backend Schema 8.6).
 */
export function init(root = document) {
  root.querySelectorAll('[data-character-form]').forEach((form) => {
    const fields = form.querySelector('[data-figure-fields]');
    const update = () => {
      const type = form.querySelector('input[name="type"]:checked');
      fields.hidden = !type || type.value !== 'child_figure';
    };
    form.addEventListener('change', (event) => {
      if (event.target.name === 'type') update();
    });
    update();
  });
}

document.addEventListener('DOMContentLoaded', () => init());
