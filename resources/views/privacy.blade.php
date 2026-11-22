{{--
    Adatkezelési tájékoztató (App Flow S-02, PRD US-20). A szakaszok a US-20 AC2 kötelező pontjait követik,
    kiegészítve a Backend Schema E-03 eltérésével (a vendég félkész meséje a sessions táblában van).
    A szöveg helyőrző; a végleges változatot a projekt tulajdonosa írja (PRD US-20 AC2).
    A szakaszok listája a lang-fájlból jön, így új szakaszhoz csak a privacy.php-t kell bővíteni.

    Privacy notice (App Flow S-02, PRD US-20). The sections follow the required points of US-20 AC2,
    plus Backend Schema deviation E-03 (a guest's draft story is stored in the sessions table).
    The text is a placeholder; the final version is written by the project owner (PRD US-20 AC2).
    The section list comes from the lang file, so a new section only needs an entry in privacy.php.
--}}
<x-layouts.app :title="__('privacy.title')">
    <div class="app-layout__content privacy">
        <h1>{{ __('privacy.title') }}</h1>
        <p class="privacy__lead">{{ __('privacy.lead') }}</p>

        @foreach (__('privacy.sections') as $section)
            <section class="privacy__section">
                <h2>{{ $section['heading'] }}</h2>
                <p>{{ $section['text'] }}</p>
            </section>
        @endforeach
    </div>
</x-layouts.app>
