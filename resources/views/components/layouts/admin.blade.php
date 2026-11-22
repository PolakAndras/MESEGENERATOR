{{--
    Admin elrendezés (App Flow K-09, CG 6.1). A közös elrendezésre épül (fejléc, lábléc, üzenetsáv), és egy saját
    admin menüt ad: Szereplők · Hátterek · Mesék. A „Vissza az oldalra” és a „Kilépés” a közös fejlécben van.
    Admin layout (App Flow K-09, CG 6.1). Built on the shared layout (header, footer, message bar) and adds its own
    admin menu: Characters · Backgrounds · Stories. "Back to the site" and "Log out" live in the shared header.
--}}
@props(['title' => null])

<x-layouts.app :title="$title" :admin="true">
    <div class="app-layout__content admin-layout">
        <nav class="admin-layout__nav" aria-label="{{ __('admin.nav.label') }}">
            @foreach (['characters' => 'admin.characters.*', 'backgrounds' => 'admin.backgrounds.*', 'stories' => 'admin.stories.*'] as $key => $pattern)
                @php($active = request()->routeIs($pattern) || ($key === 'characters' && request()->routeIs('admin.actions.*')))
                <a class="admin-layout__link" href="{{ route('admin.'.$key.'.index') }}" @if ($active) aria-current="page" @endif>
                    {{ __('admin.nav.'.$key) }}
                </a>
            @endforeach
        </nav>

        {{ $slot }}
    </div>
</x-layouts.app>
