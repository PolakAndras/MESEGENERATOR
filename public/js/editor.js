/*
 * Oldalszerkesztő (App Flow S-09, CG 6.10, 11.2–11.3): háttér azonnali megjelenítése, gyerek ki/be, állat
 * hozzáadása/eltávolítása, cselekvésválasztás – mind a böngészőben, a szerver csak űrlapküldéskor kap adatot.
 * Az eredményt az űrlap rejtett mezői hordozzák; a JS nem küld háttérkérést (nincs fetch, CG D-08).
 * A vászon figuráit minden változáskor újrarajzolja a modellből, így a rétegsorrend mindig a CG 8.4 szerinti.
 * A drag.js „figure-moved” eseményét figyeli, így a két modul globális változó nélkül működik együtt.
 *
 * Page editor (App Flow S-09, CG 6.10, 11.2–11.3): shows the background at once, toggles the child, adds/removes
 * animals, picks actions – all in the browser; the server only gets data on form submit.
 * Hidden form fields carry the result; the JS sends no background request (no fetch, CG D-08).
 * It redraws the canvas figures from the model on every change, so the layer order always follows CG 8.4.
 * It listens to drag.js's "figure-moved" event, so the two modules cooperate without globals.
 */
import { firstFreeSlot, zIndexes } from './lib/placement.js';

export function init(root = document) {
  root.querySelectorAll('[data-editor]').forEach(setup);
}

function setup(form) {
  const data = JSON.parse(form.querySelector('[data-editor-data]').textContent);
  const canvas = form.querySelector('[data-story-canvas]');
  const state = JSON.parse(canvas.querySelector('[data-story-canvas-state]').textContent);
  const list = form.querySelector('[data-animal-list]');
  const template = form.querySelector('[data-animal-template]');
  const addSelect = form.querySelector('[data-animal-add-select]');
  const addButton = form.querySelector('[data-animal-add-button]');
  const childToggle = form.querySelector('[data-child-toggle]');
  const childAction = form.querySelector('[data-child-action]');
  const scale = Number(data.placement.default_scale);
  const animalsById = new Map(data.animals.map((a) => [a.id, a]));
  let nextIndex = list ? list.querySelectorAll('[data-animal-row]').length : 0;

  // Hozzáadási sorrend a rétegsorrend döntetlenjeihez; az új elem mindig a végére kerül (CG 8.4).
  // Insertion order for layer-order ties; a new item always goes last (CG 8.4).
  const orderOf = new Map(state.placements.map((p) => [String(p.character_id), p.order ?? 0]));
  let orderCounter = state.placements.length;
  const touch = (characterId) => orderOf.set(String(characterId), orderCounter++);

  const actionOf = (character, actionId) =>
    character.actions.find((a) => a.id === Number(actionId)) || character.actions.find((a) => a.is_default);

  function figures() {
    const items = [];
    if (childToggle && childToggle.checked) {
      items.push({
        id: data.figure.id,
        name: data.figure.name,
        action: actionOf(data.figure, childAction.value),
        x: Number(form.querySelector('[data-child-x]').value),
        y: Number(form.querySelector('[data-child-y]').value),
        flipped: form.querySelector('[data-editor-child] [data-flip]').checked,
      });
    }
    form.querySelectorAll('[data-animal-row]').forEach((row) => {
      const animal = animalsById.get(Number(row.dataset.characterId));
      if (!animal) return;
      items.push({
        id: animal.id,
        name: animal.name,
        action: actionOf(animal, row.querySelector('[data-animal-action]').value),
        x: Number(row.querySelector('[data-animal-x]').value),
        y: Number(row.querySelector('[data-animal-y]').value),
        flipped: row.querySelector('[data-animal-flip]').checked,
      });
    });
    return items;
  }

  function render() {
    const items = figures();
    const z = zIndexes(items.map((f) => ({ key: f.id, y: f.y, order: orderOf.get(String(f.id)) ?? 0 })));
    canvas.querySelectorAll('[data-canvas-figure]').forEach((img) => img.remove());

    items.forEach((f) => {
      const img = document.createElement('img');
      img.className = 'story-canvas__figure story-canvas__figure--draggable';
      // Tükrözés (PRD US-22): módosító osztály, a CSS fordítja meg a képet. / Flip: a modifier class, CSS mirrors it.
      img.classList.toggle('story-canvas__figure--flipped', f.flipped);
      img.src = f.action.url;
      img.alt = `${f.name} – ${f.action.name}`;
      img.width = 800;
      img.height = 800;
      img.draggable = false;
      img.dataset.canvasFigure = String(f.id);
      // Kivétel a tokenszabály alól: a figura helye inline stílusban (CG 3.6, 11.2).
      // Exception to the token rule: the figure's position is an inline style (CG 3.6, 11.2).
      img.style.left = `${f.x}%`;
      img.style.top = `${f.y}%`;
      img.style.height = `${scale}%`;
      img.style.zIndex = String(z.get(f.id));
      canvas.appendChild(img);
    });
  }

  function renderBackground(input) {
    const bg = data.backgrounds.find((b) => b.id === Number(input.value));
    const img = canvas.querySelector('[data-canvas-bg]');
    if (!bg || !img) return;
    img.src = bg.url;
    img.alt = bg.name;
    img.hidden = false;
  }

  function usedSlots() {
    return [...form.querySelectorAll('[data-animal-slot]')].map((input) => Number(input.value));
  }

  // A választó és a „tele” üzenet a valós állapotot mutatja: az oldalon lévő állat szürke, 4 állatnál a gomb inaktív.
  // The picker and the "full" note reflect the real state: animals on the page are greyed out, at 4 the button is off.
  function syncAddControls() {
    if (!addSelect) return;
    const rows = form.querySelectorAll('[data-animal-row]');
    const taken = new Set([...rows].map((row) => row.dataset.characterId));
    const full = rows.length >= data.maxAnimals;

    [...addSelect.options].forEach((option) => {
      if (!option.value) return;
      const isTaken = taken.has(option.value);
      option.disabled = isTaken;
      option.textContent = isTaken ? option.dataset.takenLabel : option.dataset.name;
    });
    if (addSelect.selectedOptions[0] && addSelect.selectedOptions[0].disabled) {
      addSelect.value = '';
    }
    addSelect.disabled = full;
    addButton.disabled = full;
    form.querySelector('[data-animals-full]').hidden = !full;
    form.querySelector('[data-animals-empty]').hidden = rows.length > 0;
  }

  function addAnimal(animal) {
    const slot = firstFreeSlot(usedSlots(), data.maxAnimals);
    if (slot === null) return;
    const start = data.placement.animal_slots[slot];
    const index = `n${nextIndex++}`;
    const fragment = template.content.cloneNode(true);
    const row = fragment.querySelector('[data-animal-row]');

    row.querySelectorAll('[name], [id], [for]').forEach((el) => {
      ['name', 'id', 'for'].forEach((attr) => {
        if (el.hasAttribute(attr)) el.setAttribute(attr, el.getAttribute(attr).replace('__INDEX__', index));
      });
    });

    const defaultAction = animal.actions.find((a) => a.is_default) || animal.actions[0];
    row.dataset.characterId = String(animal.id);
    row.querySelector('[data-animal-thumb]').src = defaultAction.url;
    row.querySelector('[data-animal-name]').textContent = animal.name;
    row.querySelector('[data-animal-id]').value = animal.id;
    row.querySelector('[data-animal-x]').value = start.x;
    row.querySelector('[data-animal-y]').value = start.y;
    row.querySelector('[data-animal-slot]').value = slot;
    // A gyerektől jobbra álló állat alapból balra néz, a gyerek felé (mint a véletlen kitöltésnél).
    // An animal to the right of the child faces left by default, towards the child (as in random fill).
    row.querySelector('[data-animal-flip]').checked = start.x > data.placement.child.x;
    const remove = row.querySelector('[data-animal-remove]');
    remove.setAttribute('aria-label', remove.dataset.labelTemplate.replace(':name', animal.name));

    const select = row.querySelector('[data-animal-action]');
    animal.actions.forEach((action) => {
      const option = document.createElement('option');
      option.value = action.id;
      option.textContent = action.name;
      option.selected = action.is_default;
      select.appendChild(option);
    });

    list.appendChild(fragment);
    touch(animal.id);
    syncAddControls();
    render();
    select.focus();
  }

  form.addEventListener('change', (event) => {
    const target = event.target;
    if (target.matches('[data-background-input]')) {
      renderBackground(target);
    } else if (target === childToggle) {
      form.querySelector('[data-child-fields]').hidden = !childToggle.checked;
      // A gyerek a bekapcsolás pillanatában számít hozzáadottnak (CG 8.4). / The child counts as added when switched on.
      if (childToggle.checked) touch(data.figure.id);
      render();
    } else if (target.matches('[data-child-action], [data-animal-action], [data-flip]')) {
      render();
    }
  });

  form.addEventListener('click', (event) => {
    const remove = event.target.closest('[data-animal-remove]');
    if (remove) {
      const row = remove.closest('[data-animal-row]');
      orderOf.delete(row.dataset.characterId);
      row.remove();
      syncAddControls();
      render();
      if (addSelect && !addSelect.disabled) addSelect.focus();
    }
  });

  if (addButton) {
    addButton.addEventListener('click', () => {
      const animal = animalsById.get(Number(addSelect.value));
      if (animal) addAnimal(animal);
    });
  }

  // A drag.js jelzi, ha egy figura új helyre került: a rejtett mezők frissülnek, a rétegsorrend újraszámolódik.
  // drag.js reports a moved figure: the hidden fields update and the layer order is recalculated.
  canvas.addEventListener('figure-moved', (event) => {
    const { characterId, x, y } = event.detail;
    if (characterId === String(data.figure.id)) {
      form.querySelector('[data-child-x]').value = x;
      form.querySelector('[data-child-y]').value = y;
    } else {
      const row = form.querySelector(`[data-animal-row][data-character-id="${characterId}"]`);
      if (!row) return;
      row.querySelector('[data-animal-x]').value = x;
      row.querySelector('[data-animal-y]').value = y;
    }
    render();
  });

  syncAddControls();
  render();
}

document.addEventListener('DOMContentLoaded', () => init());
