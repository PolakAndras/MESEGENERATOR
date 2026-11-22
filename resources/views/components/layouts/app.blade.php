{{--
    Közös elrendezés (CG 6.1 <x-layouts.app>): minden szülői oldal ebbe kerül, így a fejléc, a lábléc,
    az üzenetsáv és a stíluslapok egy helyen vannak. Használat: <x-layouts.app :title="__('…')">…</x-layouts.app>

    Shared layout (CG 6.1 <x-layouts.app>): every parent-facing page uses it, so header, footer,
    message bar and stylesheets live in one place. Usage: <x-layouts.app :title="__('…')">…</x-layouts.app>
--}}
{{--
    A story.css csak a vásznat vagy olvasót tartalmazó oldalakon töltődik be (CG 11.1): :story="true".
    story.css only loads on pages with a canvas or reader (CG 11.1): :story="true".
--}}
@props(['title' => null, 'story' => false])

<!DOCTYPE html>
<html lang="hu" class="no-js" data-theme="day">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' – ' . __('common.app_name') : __('common.app_name') }}</title>

    {{--
        Az egyetlen inline szkript, mert a kirajzolás ELŐTT kell lefutnia (különben villanna az oldal):
        1. no-js → js: jelzi a CSS-nek, hogy fut a JavaScript; JS nélkül a menü nyitva marad (CG 6.4);
        2. téma: a böngészőben tárolt választás, ennek hiányában a rendszer beállítása (esti/nappali).
        A localStorage privát ablakban kivételt dobhat, ezért try/catch (CG 7.4 mintájára).
        A stíluslapok sorrendje kötött (CG 11.1): betűk → tokenek → alapstílus → komponensek.

        The only inline script, because it must run BEFORE painting (otherwise the page would flash):
        1. no-js → js: tells the CSS that JavaScript runs; without JS the menu stays open (CG 6.4);
        2. theme: the choice stored in the browser, or else the system setting (night/day).
        localStorage can throw in private windows, hence try/catch (following CG 7.4).
        Stylesheet order is fixed (CG 11.1): fonts → tokens → base → components.
    --}}
    <script>(function (d) { var t = null; d.classList.replace('no-js', 'js'); try { t = localStorage.getItem('mesegenerator.appTheme'); } catch (e) {} d.setAttribute('data-theme', t === 'night' || (!t && matchMedia('(prefers-color-scheme: dark)').matches) ? 'night' : 'day'); })(document.documentElement);</script>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    @if ($story)
        <link rel="stylesheet" href="{{ asset('css/story.css') }}">
    @endif
    <script type="module" src="{{ asset('js/nav-toggle.js') }}"></script>
    <script type="module" src="{{ asset('js/app-theme.js') }}"></script>
</head>
<body class="app-layout">
    @include('includes.navbar')

    <x-flash />

    <main class="app-layout__main">
        {{ $slot }}
    </main>

    @include('includes.footer')
</body>
</html>
