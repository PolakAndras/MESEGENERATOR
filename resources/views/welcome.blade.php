{{--
    Kezdőoldal (App Flow S-01, CG 6.9) – „mesekönyv-borító”: a cím a mese betűjével (Fraunces) a bevezetővel és a
    „Mese készítése” gombbal, mellette (telefonon alatta) a díszítő vászon. Alatta a három lépés.
    A vászon háttere a témát követi: nappal napos rét, este holdas erdő – ez az oldal egyetlen „hangos” eleme,
    minden más visszafogott.

    Home page (App Flow S-01, CG 6.9) – "storybook cover": the heading in the story typeface (Fraunces) with the
    intro and the "Create story" button, the decorative canvas beside it (below on phones), then the three steps.
    The canvas background follows the theme: sunny meadow by day, moonlit forest by night – the one "loud" element
    on the page; everything else stays quiet.
--}}
<x-layouts.app :story="true">
    <div class="app-layout__content home">
        <section class="home__hero">
            <div class="home__intro">
                <h1 class="home__title">{{ __('home.title') }}</h1>
                <p class="home__lead">{{ __('home.lead') }}</p>
                <x-button variant="primary" :href="route('stories.create')">
                    <x-icon name="sparkles" />
                    {{ __('home.cta') }}
                </x-button>
            </div>

            {{--
                Díszítő vászon: két háttér (a CSS a data-theme alapján mutatja az egyiket) + róka (A1) + gyerekfigura (G)
                a CG 8.4 kezdőpozícióin, 35%-os mérettel. Minden kép alt="", mert díszítés (CG 6.9, 8.1);
                a width/height a természetes méret (CG 8.3). A képek az images tárolóról jönnek (Tech Stack 4.3).

                Decorative canvas: two backgrounds (CSS shows one based on data-theme) + fox (A1) + child figure (G)
                at the CG 8.4 start positions, 35% size. Every image has alt="" because it is decoration (CG 6.9, 8.1);
                width/height are the natural size (CG 8.3). Images come from the images disk (Tech Stack 4.3).
            --}}
            @php($images = Storage::disk('images'))
            <div class="story-canvas home__scene">
                <img class="story-canvas__bg home__scene-day" src="{{ $images->url('backgrounds/meadow-day.svg') }}"
                     alt="" width="1600" height="1200">
                <img class="story-canvas__bg home__scene-night" src="{{ $images->url('backgrounds/forest-night.svg') }}"
                     alt="" width="1600" height="1200">
                <img class="story-canvas__figure" src="{{ $images->url('actions/fox-default.svg') }}"
                     alt="" width="800" height="800" style="left: 20%; top: 90%; height: 35%; z-index: 1;">
                <img class="story-canvas__figure" src="{{ $images->url('actions/child-girl-red-default.svg') }}"
                     alt="" width="800" height="800" style="left: 50%; top: 90%; height: 35%; z-index: 2;">
            </div>
        </section>

        {{--
            A lépések valódi sorrendet írnak le, ezért sorszámozott lista; a szám a képernyőolvasónak is szól.
            The steps are a real sequence, hence an ordered list; the number is announced to screen readers too.
        --}}
        <h2 class="sr-only">{{ __('home.steps.heading') }}</h2>
        <ol class="home__steps">
            @foreach (['child' => 'user', 'pages' => 'photo', 'story' => 'book'] as $step => $icon)
                <li class="home__step">
                    <span class="home__step-badge"><x-icon name="{{ $icon }}" /></span>
                    <span class="home__step-label">{{ __("home.steps.$step") }}</span>
                </li>
            @endforeach
        </ol>
    </div>
</x-layouts.app>
