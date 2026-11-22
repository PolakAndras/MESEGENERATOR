{{--
    Közös hibaoldal (App Flow 6.2, CG 9.3): app-elrendezés, középen kártya a hibakóddal, címmel, egy-két mondattal
    és egy primary gombbal. Illusztráció nincs, hogy hiba esetén se terheljen.
    Shared error page (App Flow 6.2, CG 9.3): app layout, a centred card with the code, a title, one or two sentences
    and one primary button. No illustration, so it stays light even when something is wrong.
--}}
<x-layouts.app :title="__('errors.'.$code.'.title')">
    <div class="app-layout__content app-layout__content--form">
        <div class="card error-card">
            <p class="error-card__code">{{ $code }}</p>
            <h1>{{ __('errors.'.$code.'.title') }}</h1>
            <p>{{ __('errors.'.$code.'.text') }}</p>
            @if ($code === 419)
                <x-button variant="primary" :href="route('stories.create')"><x-icon name="sparkles" /> {{ __('errors.new_story') }}</x-button>
            @else
                <x-button variant="primary" :href="route('home')">{{ __('errors.back_home') }}</x-button>
            @endif
        </div>
    </div>
</x-layouts.app>
