{{--
    S-08 – 2. lépés: kontextus és oldalszám (App Flow 4.2, PRD US-02). A számláló a char-counter.js-ből (US-02 AC2).
    Ha az oldalszám csökkentése tartalmat törölne, a kontroller visszaküld ide figyelmeztetéssel és
    „Megerősítem” gombbal, amely ugyanezt az űrlapot küldi confirm_reduce=1-gyel (App Flow S-08).
    S-08 – Step 2: context and page count (App Flow 4.2, PRD US-02). The counter comes from char-counter.js (US-02 AC2).
    If lowering the page count would delete content, the controller sends the user back here with a warning and a
    "Confirm" button that submits this same form with confirm_reduce=1 (App Flow S-08).
--}}
@push('scripts')
    <script type="module" src="{{ asset('js/char-counter.js') }}"></script>
    <script type="module" src="{{ asset('js/loading-overlay.js') }}"></script>
@endpush

@php($reduce = session('confirm_reduce'))

<x-layouts.app :title="__('wizard.settings.title')">
    <div class="app-layout__content app-layout__content--form">
        <x-wizard-steps :current="2" />

        <h1>{{ __('wizard.settings.title') }}</h1>

        <x-error-summary />

        @if ($reduce)
            <div class="flash flash--warning" role="status">
                <x-icon name="alert-triangle" />
                <p class="flash__message">
                    {{ $reduce['from'] === $reduce['to']
                        ? __('wizard.settings.reduce_warning_single', ['from' => $reduce['from']])
                        : __('wizard.settings.reduce_warning', $reduce) }}
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('stories.create.settings.store') }}" class="card form" data-loading-form novalidate>
            @csrf
            <x-form-field name="context" type="textarea" :label="__('wizard.settings.context')"
                          :hint="__('wizard.settings.context_hint')" :value="$settings['context'] ?? null"
                          :counter="$contextMax" rows="6" required />

            <x-choice-group name="page_count" :legend="__('wizard.settings.page_count')"
                            :options="collect($pageOptions)->mapWithKeys(fn ($n) => [$n => __('wizard.settings.pages_option', ['count' => $n])])->all()"
                            :value="$settings['page_count'] ?? $pageOptions[count($pageOptions) - 1]" />

            @if ($reduce)
                <input type="hidden" name="confirm_reduce" value="1">
            @endif

            <div class="actions actions--split">
                <x-button variant="secondary" :href="route('stories.create.child')"><x-icon name="arrow-left" /> {{ __('wizard.back') }}</x-button>
                @if ($reduce)
                    <x-button variant="primary"><x-icon name="check" /> {{ __('wizard.settings.confirm_reduce') }}</x-button>
                @else
                    <x-button variant="primary">{{ __('wizard.next') }} <x-icon name="arrow-right" /></x-button>
                @endif
            </div>

            {{-- Meglepetés mese (PRD US-21): ugyanaz az űrlap, formaction-nel a véletlen kitöltésre és generálásra megy;
                 másodlagos gomb, mert a képernyőn egy primary lehet (CG 6.2). A töltésjelzőt ez a gomb indítja.
                 Surprise story (PRD US-21): the same form, sent via formaction to random fill and generation; a secondary
                 button, because a screen has one primary (CG 6.2). This button triggers the loading overlay. --}}
            <div class="surprise">
                <p class="surprise__divider">{{ __('wizard.settings.or') }}</p>
                <p class="form-field__hint">{{ __('wizard.settings.surprise_hint') }}</p>
                <x-button variant="secondary" formaction="{{ route('stories.surprise') }}" data-loading-submit>
                    <x-icon name="sparkles" /> {{ __('wizard.settings.surprise') }}
                </x-button>
            </div>
        </form>
    </div>

    <x-loading-overlay />
</x-layouts.app>
