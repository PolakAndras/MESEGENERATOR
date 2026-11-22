{{--
    S-22 – Szereplő szerkesztése (App Flow 4.4): adatok + cselekvések számlálóval, kártyánként Szerkesztés és
    Alaphelyzetté tesz. Törlés nincs (Backend Schema E-01). A korlát elérésekor az „Új cselekvés” inaktív, mellette
    magyarázattal (CG 6.2, 9.2).
    S-22 – Edit character (App Flow 4.4): details + actions with a counter, Edit and Make default per card.
    No deletion (Backend Schema E-01). When the limit is reached "New action" is inactive with an explanation.
--}}
@push('scripts')
    <script type="module" src="{{ asset('js/character-form.js') }}"></script>
@endpush

@php($full = $character->actions->count() >= $limit)

<x-layouts.admin :title="__('admin.characters.edit_title', ['name' => $character->name])">
    <a class="back-link" href="{{ route('admin.characters.index') }}"><x-icon name="arrow-left" /> {{ __('admin.back_to_list') }}</a>
    <h1>{{ __('admin.characters.edit_title', ['name' => $character->name]) }}</h1>
    <x-error-summary />

    <form method="POST" action="{{ route('admin.characters.update', $character) }}" class="card form form--narrow" data-character-form novalidate>
        @csrf
        @method('PUT')
        @include('admin.characters.partials.fields', ['character' => $character])
        <div class="actions actions--end">
            <x-button variant="primary"><x-icon name="device-floppy" /> {{ __('admin.save') }}</x-button>
        </div>
    </form>

    <section class="section" aria-labelledby="actions-title">
        <div class="page-head">
            <h2 id="actions-title">{{ __('admin.actions.title') }}
                <span class="counter">{{ __('admin.actions.counter', ['count' => $character->actions->count(), 'max' => $limit]) }}</span>
            </h2>
            @if ($full)
                <span class="button button--primary" aria-disabled="true" aria-describedby="limit-note">
                    <x-icon name="plus" /> {{ __('admin.actions.new') }}
                </span>
            @else
                <x-button variant="primary" :href="route('admin.characters.actions.create', $character)"><x-icon name="plus" /> {{ __('admin.actions.new') }}</x-button>
            @endif
        </div>
        @if ($full)
            <div class="flash flash--info" id="limit-note"><x-icon name="info-circle" /><p class="flash__message">{{ __('admin.actions.limit_reached', ['n' => $limit]) }}</p></div>
        @endif

        <ul class="action-grid">
            @foreach ($character->actions as $action)
                <li class="action-card {{ $action->is_default ? 'action-card--default' : '' }}">
                    <img class="action-card__image" src="{{ \App\Support\CanvasState::imageUrl($action->image_path) }}"
                         alt="{{ $character->name }} – {{ $action->name }}" width="800" height="800" loading="lazy">
                    <p class="action-card__name">{{ $action->name }}</p>
                    @if ($action->is_default)
                        <p class="badge"><x-icon name="check" /> {{ __('admin.actions.is_default') }}</p>
                    @endif
                    <div class="action-card__actions">
                        <x-button variant="secondary" :href="route('admin.actions.edit', $action)"><x-icon name="edit" /> {{ __('admin.edit') }}</x-button>
                        @unless ($action->is_default)
                            <form method="POST" action="{{ route('admin.actions.make-default', $action) }}">
                                @csrf
                                <x-button variant="ghost">{{ __('admin.actions.make_default') }}</x-button>
                            </form>
                        @endunless
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.admin>
