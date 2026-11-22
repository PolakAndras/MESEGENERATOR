{{--
    Ikon (CG 8.7): <x-icon name="sun" /> → a components/icons/<név>.blade.php inline SVG-je egy span-ben.
    Inline SVG kell (nem <img>), mert csak így veszi fel a szöveg színét (stroke="currentColor").
    Ez csak a repóban lévő, ellenőrzött Tabler-ikonokra érvényes; feltöltött SVG soha nem kerül így az oldalba (NFR-07).

    Icon (CG 8.7): <x-icon name="sun" /> → the inline SVG of components/icons/<name>.blade.php inside a span.
    Inline SVG (not <img>) is needed because only then does it take the text colour (stroke="currentColor").
    This applies only to the vetted Tabler icons in the repo; uploaded SVGs are never embedded like this (NFR-07).
--}}
@props(['name'])

<span {{ $attributes->merge(['class' => 'icon']) }}>@include('components.icons.' . $name)</span>
