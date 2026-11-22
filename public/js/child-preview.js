/*
 * Gyerekfigura-előnézet (PRD US-01 AC2, App Flow S-07): a nem + hajszín választásakor azonnal a megfelelő figura
 * alaphelyzet-képe látszik. A képek URL-jei a data-previews JSON-ban vannak (kulcs: "girl-red").
 * Child figure preview (PRD US-01 AC2, App Flow S-07): choosing gender + hair colour shows the matching figure's
 * default image at once. Image URLs are in the data-previews JSON (key: "girl-red").
 */
export function init(root = document) {
  root.querySelectorAll('[data-child-preview]').forEach((box) => {
    const previews = JSON.parse(box.dataset.previews || '{}');
    const image = box.querySelector('[data-child-preview-image]');
    const empty = box.querySelector('[data-child-preview-empty]');

    const update = () => {
      const gender = box.querySelector('input[name="child_gender"]:checked');
      const hair = box.querySelector('input[name="child_hair_color"]:checked');
      const url = gender && hair ? previews[`${gender.value}-${hair.value}`] : null;
      image.hidden = !url;
      empty.hidden = Boolean(url);
      if (url) image.src = url;
    };

    box.addEventListener('change', update);
  });
}

document.addEventListener('DOMContentLoaded', () => init());
