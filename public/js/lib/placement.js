/*
 * DOM-független elhelyezési szabályok (CG 8.4, 11.4) – a PHP App\Support\PlacementRules párja.
 * Külön modulban, hogy a szerkesztő és a húzás ugyanazt a képletet használja, és később React-ben is újrahasznosítható legyen.
 *
 * DOM-independent placement rules (CG 8.4, 11.4) – the twin of PHP App\Support\PlacementRules.
 * In a separate module so the editor and dragging share one formula, reusable later in React too.
 */

// A figura nem lóghat ki: pos_x ∈ [h; 100 − h], pos_y ∈ [s; 100], ahol h = s × halfWidthFactor.
// The figure must not overflow: pos_x ∈ [h; 100 − h], pos_y ∈ [s; 100], where h = s × halfWidthFactor.
export function bounds(scale, halfWidthFactor) {
  const h = scale * halfWidthFactor;
  return { minX: h, maxX: 100 - h, minY: scale, maxY: 100 };
}

export function clamp(x, y, scale, halfWidthFactor) {
  const b = bounds(scale, halfWidthFactor);
  return {
    x: round(Math.min(b.maxX, Math.max(b.minX, x))),
    y: round(Math.min(b.maxY, Math.max(b.minY, y))),
  };
}

// Rétegsorrend: nagyobb pos_y elöl, egyenlőségnél a később hozzáadott (order) elöl; z 1…N folytonos (US-06 AC5).
// Layer order: larger pos_y in front, ties go to the later added (order); z is a continuous 1…N (US-06 AC5).
export function zIndexes(items) {
  const sorted = [...items].sort((a, b) => (a.y - b.y) || (a.order - b.order));
  const result = new Map();
  sorted.forEach((item, i) => result.set(item.key, i + 1));
  return result;
}

// Az első szabad hely 1…max között; az eltávolított állat helyét az új kapja meg (CG 8.4).
// The first free slot within 1…max; a new animal takes the place of a removed one (CG 8.4).
export function firstFreeSlot(usedSlots, max) {
  for (let slot = 1; slot <= max; slot += 1) {
    if (!usedSlots.includes(slot)) {
      return slot;
    }
  }
  return null;
}

// Három tizedesjegy, mint az adatbázis DECIMAL(6,3) oszlopa. / Three decimals, like the DECIMAL(6,3) column.
function round(value) {
  return Math.round(value * 1000) / 1000;
}
