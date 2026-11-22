{{--
    Fejléc (App Flow K-01, CG 6.4, mintaoldal): logó balra, menüpontok jobbra, szerepkör szerint.
    Vendég: Mese készítése · Belépés · Regisztráció. Szülő: Mese készítése · Meséim · Fiókom · Kilépés. Admin: + Admin.

    ELTÉRÉS a D-12-től (a fejlesztő kérésére): a menüpontok már most megjelennek, pedig az útvonalaik még nem léteznek.
    Ezért a link a route() helyett az App Flow 6.3 szerinti URL-re mutat, amíg az útvonal el nem készül
    (addig 404-et ad); utána a Route::has() miatt magától a route()-ot használja.

    Telefonon (< 600 px) a menü a hamburger gomb mögé kerül; a nav-toggle.js nyitja és zárja.
    JS nélkül (html.no-js) a menü nyitva, listaként látszik (CG 6.4).

    Header (App Flow K-01, CG 6.4, sample page): logo on the left, menu items on the right, by role.
    DEVIATION from D-12 (at the developer's request): menu items show up already although their routes do not exist yet.
    So each link points to the App Flow 6.3 URL until the route exists (it 404s until then); afterwards Route::has()
    makes it use route() automatically. On phones (< 600 px) the menu sits behind the hamburger button, opened and
    closed by nav-toggle.js. Without JS (html.no-js) the menu stays open as a list (CG 6.4).
--}}
@php
    // Útvonalnév => App Flow 6.3 URI (tartalék, amíg az útvonal nem létezik)
    // Route name => App Flow 6.3 URI (fallback until the route exists)
    $link = fn (string $name, string $uri) => Route::has($name) ? route($name) : url($uri);
@endphp

<header class="site-header" data-nav>
    <div class="site-header__inner">
        <a class="site-header__logo" href="{{ route('home') }}">
            <x-icon name="book" />
            <span>{{ __('common.app_name') }}</span>
        </a>

        {{--
            Témaváltó és hamburger egy csoportban, hogy telefonon is mindig látszódjanak a logó mellett.
            A témaváltó mindkét feliratot tartalmazza; a CSS a data-theme alapján a KÖVETKEZŐ állapotot mutatja
            (CG 7.4 mintája: „Esti mód” nappal, „Nappali mód” este). Telefonon csak az ikon látszik,
            a felirat képernyőolvasónak megmarad. JS nélkül a gomb rejtett, mert úgysem működne.

            Theme switch and hamburger in one group so they always sit next to the logo, even on phones.
            The theme switch holds both labels; CSS shows the NEXT state based on data-theme (CG 7.4 pattern).
            On phones only the icon shows, the label stays for screen readers. Without JS the button is hidden.
        --}}
        <div class="site-header__actions">
            <button type="button" class="site-header__theme" data-theme-toggle>
                <span class="site-header__theme-to-night">
                    <x-icon name="moon" />
                    <span class="site-header__theme-label">{{ __('common.theme.to_night') }}</span>
                </span>
                <span class="site-header__theme-to-day">
                    <x-icon name="sun" />
                    <span class="site-header__theme-label">{{ __('common.theme.to_day') }}</span>
                </span>
            </button>

            <button type="button" class="site-header__toggle" data-nav-toggle
                    aria-expanded="false" aria-controls="site-menu" aria-label="{{ __('common.nav.toggle') }}">
                <x-icon name="menu" class="site-header__toggle-open" />
                <x-icon name="x" class="site-header__toggle-close" />
            </button>
        </div>

        <nav class="site-header__nav" aria-label="{{ __('common.nav.label') }}">
            <ul id="site-menu" class="site-header__menu" data-nav-menu>
                <li>
                    <a class="site-header__link" href="{{ $link('stories.create', '/stories/create') }}"
                       @if (request()->routeIs('stories.create*')) aria-current="page" @endif>
                        {{ __('common.nav.create') }}
                    </a>
                </li>

                @guest
                    <li>
                        <a class="site-header__link" href="{{ $link('login', '/login') }}"
                           @if (request()->routeIs('login')) aria-current="page" @endif>
                            {{ __('common.nav.login') }}
                        </a>
                    </li>
                    <li>
                        <a class="site-header__link" href="{{ $link('register', '/register') }}"
                           @if (request()->routeIs('register')) aria-current="page" @endif>
                            {{ __('common.nav.register') }}
                        </a>
                    </li>
                @endguest

                @auth
                    <li>
                        <a class="site-header__link" href="{{ $link('stories.index', '/stories') }}"
                           @if (request()->routeIs('stories.index', 'stories.show')) aria-current="page" @endif>
                            {{ __('common.nav.stories') }}
                        </a>
                    </li>
                    <li>
                        <a class="site-header__link" href="{{ $link('account.show', '/account') }}"
                           @if (request()->routeIs('account.*')) aria-current="page" @endif>
                            {{ __('common.nav.account') }}
                        </a>
                    </li>
                    @if (auth()->user()->isAdmin())
                        <li>
                            <a class="site-header__link" href="{{ $link('admin.home', '/admin') }}"
                               @if (request()->routeIs('admin.*')) aria-current="page" @endif>
                                {{ __('common.nav.admin') }}
                            </a>
                        </li>
                    @endif
                    <li>
                        {{-- A kilépés adatot módosít, ezért POST űrlap, nem link (App Flow 1., CG 6.4: ghost stílus)
                             Logging out changes state, so it is a POST form, not a link (App Flow 1., CG 6.4: ghost style) --}}
                        <form method="POST" action="{{ $link('logout', '/logout') }}">
                            @csrf
                            <button type="submit" class="site-header__link site-header__link--button">
                                {{ __('common.nav.logout') }}
                            </button>
                        </form>
                    </li>
                @endauth
            </ul>
        </nav>
    </div>
</header>
