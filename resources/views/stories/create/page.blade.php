{{--
    S-09 – 3. lépés: oldalszerkesztő (App Flow 4.2, CG 6.10). Egyetlen űrlap: minden gomb (léptető, lépésjelző,
    előző/következő, generálás) először elmenti az oldalt, a `target` mondja meg, hová menjen utána.
    A szerver kirendereli a vásznat és a meglévő állatsorokat, így JS nélkül is menthető (háttér, gyerek ki/be,
    cselekvés). Az állat hozzáadása, eltávolítása és az élő előnézet az editor.js-ben, a húzás a drag.js-ben van.
    A pozíciók rejtett mezőkben utaznak (App Flow S-09); a szerver ellenőrzi őket (PageStepRequest).

    S-09 – Step 3: page editor (App Flow 4.2, CG 6.10). A single form: every button (stepper, step indicator,
    prev/next, generate) saves the page first, and `target` says where to go next.
    The server renders the canvas and the existing animal rows, so it can be saved without JS (background, child
    on/off, action). Adding/removing animals and the live preview live in editor.js, dragging in drag.js.
    Positions travel in hidden fields (App Flow S-09); the server validates them (PageStepRequest).
--}}
@push('scripts')
    <script type="module" src="{{ asset('js/editor.js') }}"></script>
    <script type="module" src="{{ asset('js/drag.js') }}"></script>
    <script type="module" src="{{ asset('js/loading-overlay.js') }}"></script>
@endpush

@php
    $isLast = $page === $pageCount;
    $childDefault = $placementConfig['child'];
    $childOn = $childPlacement !== null;
    $maxAnimals = $limits['animals_per_page'];
    $takenIds = collect($animalPlacements)->pluck('character_id')->all();
    $animalsById = collect($animals)->keyBy('id');
    // A JS-nek szóló katalógus és korlátok (CG 11.3); a @json vesszőnél darabolna, ezért előbb változóba kerül.
    // Catalog and limits for the JS (CG 11.3); @json would split on commas, so it goes into a variable first.
    $editorData = [
        'backgrounds' => $backgrounds,
        'animals' => $animals,
        'figure' => $figure,
        'maxAnimals' => $maxAnimals,
        'placement' => $placementConfig,
    ];
@endphp

<x-layouts.app :title="__('wizard.pages.title', ['page' => $page, 'count' => $pageCount])" :story="true">
    <div class="app-layout__content app-layout__content--editor">
        <x-wizard-steps :current="3" form="page-form" />

        <div class="editor__head">
            <h1 class="editor__title">{{ __('wizard.pages.title', ['page' => $page, 'count' => $pageCount]) }}</h1>
            <x-page-stepper :current="$page" :count="$pageCount" :missing="$missingBackgrounds" form="page-form" />
        </div>

        <x-error-summary />

        <form id="page-form" method="POST" action="{{ route('stories.create.page.store', $page) }}" class="editor"
              data-editor data-loading-form novalidate>
            @csrf
            <script type="application/json" data-editor-data>@json($editorData)</script>

            <div class="editor__stage">
                <x-story-canvas :canvas="$canvas" mode="edit" :label="__('wizard.pages.canvas_label', ['page' => $page])" />
                <p class="editor__hint js-only">{{ __('wizard.pages.drag_hint') }}</p>

                {{-- Véletlen oldal (PRD US-21): az űrlap target=random gombja, a szerver tölti ki az oldalt.
                     Random page (PRD US-21): the form's target=random button; the server fills the page. --}}
                <div class="editor__random">
                    <x-button variant="secondary" name="target" value="random"><x-icon name="sparkles" /> {{ __('wizard.pages.random_page') }}</x-button>
                    <p class="editor__hint">{{ __('wizard.pages.random_hint') }}</p>
                </div>
            </div>

            <div class="editor__controls">
                {{-- Háttér (US-03): bélyegkép-rács rádiógombokkal; JS azonnal mutatja a vásznon (AC4).
                     Background (US-03): a thumbnail grid of radio buttons; JS shows it on the canvas at once (AC4). --}}
                <div class="editor__section"><fieldset class="editor__fieldset" id="background_id">
                    <legend class="editor__legend">{{ __('wizard.pages.background') }}</legend>
                    <p class="form-field__hint">{{ __('wizard.pages.background_hint') }}</p>
                    <div class="background-picker">
                        @foreach ($backgrounds as $bg)
                            <label class="background-picker__option">
                                <input class="background-picker__input" type="radio" name="background_id" value="{{ $bg['id'] }}"
                                       data-background-input
                                       @if (($canvas['background']['id'] ?? null) === $bg['id']) checked @endif>
                                <span class="background-picker__card">
                                    <img class="background-picker__image" src="{{ $bg['url'] }}" alt="" width="1600" height="1200" loading="lazy">
                                    <span class="background-picker__name">{{ $bg['name'] }}</span>
                                    <span class="background-picker__check"><x-icon name="check" /></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset></div>

                {{-- Gyerek ezen az oldalon (US-05): be/ki + cselekvés. A rejtett 0 miatt kikapcsolva is érkezik érték.
                     Child on this page (US-05): on/off + action. The hidden 0 makes sure a value arrives when off. --}}
                <div class="editor__section"><fieldset class="editor__fieldset" data-editor-child>
                    <legend class="editor__legend">{{ __('wizard.pages.child') }}</legend>
                    <input type="hidden" name="child_enabled" value="0">
                    <label class="switch">
                        <input class="switch__input" type="checkbox" name="child_enabled" value="1" data-child-toggle
                               @if ($childOn) checked @endif>
                        <span class="switch__track" aria-hidden="true"></span>
                        <span class="switch__label">{{ __('wizard.pages.child_on', ['name' => $childName]) }}</span>
                    </label>
                    <div class="form-field" data-child-fields @if (! $childOn) hidden @endif>
                        <label class="form-field__label" for="child-action">{{ __('wizard.pages.action') }}</label>
                        <select class="form-field__control" id="child-action" name="child[action_id]" data-child-action>
                            @foreach ($figure['actions'] as $action)
                                <option value="{{ $action['id'] }}" @if (($childPlacement['action_id'] ?? null) === $action['id']) selected @endif>
                                    {{ $action['name'] }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Tükrözés (PRD US-22): sima checkbox, így JS nélkül is menthető. / Flip: a plain checkbox, works without JS. --}}
                        <label class="flip-toggle">
                            <input class="flip-toggle__input" type="checkbox" name="child[flipped]" value="1" data-flip
                                   @if ($childPlacement['flipped'] ?? false) checked @endif>
                            <span class="flip-toggle__label"><x-icon name="arrows-left-right" /> {{ __('wizard.pages.flip') }}</span>
                        </label>
                    </div>
                    <input type="hidden" name="child[pos_x]" value="{{ $childPlacement['pos_x'] ?? $childDefault['x'] }}" data-child-x>
                    <input type="hidden" name="child[pos_y]" value="{{ $childPlacement['pos_y'] ?? $childDefault['y'] }}" data-child-y>
                </fieldset></div>

                {{-- Állatok (US-04): legfeljebb N, mindegyik egyszer; a sorok sorrendje a hozzáadás sorrendje.
                     Animals (US-04): at most N, each once; the row order is the order they were added. --}}
                <div class="editor__section"><fieldset class="editor__fieldset" data-animals>
                    <legend class="editor__legend">{{ __('wizard.pages.animals') }}</legend>

                    @if (count($animals) === 0)
                        <div class="flash flash--info"><x-icon name="info-circle" /><p class="flash__message">{{ __('wizard.pages.no_animals') }}</p></div>
                    @else
                        <p class="editor__empty" data-animals-empty @if (count($animalPlacements) > 0) hidden @endif>{{ __('wizard.pages.animals_empty') }}</p>
                        <ul class="animal-list" data-animal-list>
                            @foreach ($animalPlacements as $i => $p)
                                @include('stories.create.partials.animal-row', [
                                    'index' => $i,
                                    'animal' => $animalsById[$p['character_id']],
                                    'placement' => $p,
                                ])
                            @endforeach
                        </ul>

                        <div class="animal-add js-only" data-animal-add>
                            <label class="form-field__label" for="animal-add-select">{{ __('wizard.pages.add_animal') }}</label>
                            <div class="animal-add__row">
                                <select class="form-field__control animal-add__select" id="animal-add-select" data-animal-add-select>
                                    <option value="">{{ __('wizard.pages.add_animal_label') }}</option>
                                    @foreach ($animals as $animal)
                                        <option value="{{ $animal['id'] }}" @if (in_array($animal['id'], $takenIds, true)) disabled @endif
                                                data-name="{{ $animal['name'] }}" data-taken-label="{{ __('wizard.pages.animal_taken', ['name' => $animal['name']]) }}">
                                            {{ in_array($animal['id'], $takenIds, true) ? __('wizard.pages.animal_taken', ['name' => $animal['name']]) : $animal['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-button variant="secondary" type="button" data-animal-add-button><x-icon name="plus" /> {{ __('wizard.pages.add') }}</x-button>
                            </div>
                            <p class="form-field__hint" data-animals-full @if (count($animalPlacements) < $maxAnimals) hidden @endif>
                                {{ __('wizard.pages.animals_full', ['max' => $maxAnimals]) }}
                            </p>
                        </div>
                        <noscript><p class="form-field__hint">{{ __('wizard.pages.needs_js') }}</p></noscript>

                        <template data-animal-template>
                            @include('stories.create.partials.animal-row', ['index' => '__INDEX__', 'animal' => null, 'placement' => null])
                        </template>
                    @endif
                </fieldset></div>
            </div>

            <div class="editor__footer">
                @if ($page > 1)
                    <x-button variant="secondary" name="target" value="prev"><x-icon name="arrow-left" /> {{ __('wizard.pages.prev') }}</x-button>
                @else
                    <span></span>
                @endif

                @if ($isLast)
                    {{-- Ugyanaz az űrlap, de formaction-nel a generálásra megy (App Flow S-09); a töltésjelzőt ez a gomb indítja.
                         Same form, but formaction sends it to generation (App Flow S-09); this button triggers the overlay. --}}
                    <x-button variant="primary" formaction="{{ route('stories.generate') }}" data-loading-submit>
                        <x-icon name="sparkles" /> {{ __('wizard.pages.generate') }}
                    </x-button>
                @else
                    <x-button variant="primary" name="target" value="next">{{ __('wizard.pages.next') }} <x-icon name="arrow-right" /></x-button>
                @endif
            </div>
        </form>
    </div>

    <x-loading-overlay />
</x-layouts.app>
