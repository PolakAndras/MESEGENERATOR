/*
 * Figurák húzása egérrel és ujjal (PRD US-06, Tech Stack 6.1): Pointer Events, a vásznon touch-action: none,
 * így telefonon húzás közben nem görget az oldal (AC2). A pozíció a vászonhoz viszonyított százalék (AC4), és a
 * placement.js képlete nem engedi kilógni (AC3). Húzás végén „figure-moved” eseményt küld; a rejtett mezőket és a
 * rétegsorrendet az editor.js frissíti. Külön modul, mert a húzás külön ticket (US-06 AC6).
 *
 * Dragging figures with mouse and touch (PRD US-06, Tech Stack 6.1): Pointer Events with touch-action: none on the
 * canvas, so the page does not scroll while dragging on phones (AC2). Positions are percentages of the canvas (AC4),
 * and the placement.js formula keeps figures inside (AC3). On drop it fires "figure-moved"; editor.js updates the
 * hidden fields and the layer order. A separate module because dragging is a separate ticket (US-06 AC6).
 */
import { clamp } from './lib/placement.js';

export function init(root = document) {
  root.querySelectorAll('[data-editor]').forEach((form) => {
    const canvas = form.querySelector('[data-story-canvas]');
    const data = JSON.parse(form.querySelector('[data-editor-data]').textContent);
    const scale = Number(data.placement.default_scale);
    const factor = Number(data.placement.half_width_factor);
    let drag = null;

    canvas.classList.add('story-canvas--draggable');

    canvas.addEventListener('pointerdown', (event) => {
      const figure = event.target.closest('[data-canvas-figure]');
      if (!figure || event.button > 0) return;
      event.preventDefault();

      const rect = canvas.getBoundingClientRect();
      // A megfogási pont és a talppont különbsége, hogy a figura ne ugorjon a mutató alá.
      // Offset between the grab point and the foot point, so the figure does not jump under the pointer.
      drag = {
        figure,
        rect,
        dx: (event.clientX - rect.left) / rect.width * 100 - parseFloat(figure.style.left),
        dy: (event.clientY - rect.top) / rect.height * 100 - parseFloat(figure.style.top),
        pointerId: event.pointerId,
      };
      figure.classList.add('story-canvas__figure--dragging');
      canvas.setPointerCapture(event.pointerId);
    });

    canvas.addEventListener('pointermove', (event) => {
      if (!drag || event.pointerId !== drag.pointerId) return;
      const pos = position(event);
      // Kivétel a tokenszabály alól: a figura helye inline stílus (CG 3.6). / Exception: position is inline (CG 3.6).
      drag.figure.style.left = `${pos.x}%`;
      drag.figure.style.top = `${pos.y}%`;
    });

    const finish = (event) => {
      if (!drag || event.pointerId !== drag.pointerId) return;
      const pos = position(event);
      const characterId = drag.figure.dataset.canvasFigure;
      drag.figure.classList.remove('story-canvas__figure--dragging');
      drag = null;
      canvas.dispatchEvent(new CustomEvent('figure-moved', { detail: { characterId, x: pos.x, y: pos.y } }));
    };
    canvas.addEventListener('pointerup', finish);
    canvas.addEventListener('pointercancel', finish);

    function position(event) {
      const x = (event.clientX - drag.rect.left) / drag.rect.width * 100 - drag.dx;
      const y = (event.clientY - drag.rect.top) / drag.rect.height * 100 - drag.dy;
      return clamp(x, y, scale, factor);
    }
  });
}

document.addEventListener('DOMContentLoaded', () => init());
