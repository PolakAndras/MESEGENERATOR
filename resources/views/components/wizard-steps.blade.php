{{--
    Lépésjelző (App Flow K-04, CG 6.6). A teljesített lépés link; az aktuális aria-current="step".
    A 3. lépésben a link helyett űrlapgomb kell (előbb az oldal mentése, App Flow S-09), ezért a `form` prop
    megadásakor a teljesített lépések a megadott űrlap target-gombjai lesznek.

    Step indicator (App Flow K-04, CG 6.6). Completed steps are links; the current one has aria-current="step".
    Step 3 needs form buttons instead of links (save the page first, App Flow S-09), so when `form` is given the
    completed steps become target buttons of that form.
--}}
@props(['current', 'form' => null])

@php
    $steps = [
        1 => ['key' => 'child', 'route' => 'stories.create.child'],
        2 => ['key' => 'settings', 'route' => 'stories.create.settings'],
        3 => ['key' => 'pages', 'route' => null],
    ];
@endphp

<nav class="wizard-steps" aria-label="{{ __('wizard.steps.label') }}">
    <p class="wizard-steps__mobile">{{ __('wizard.steps.mobile', ['current' => $current, 'label' => __('wizard.steps.'.$steps[$current]['key'])]) }}</p>
    <ol class="wizard-steps__list">
        @foreach ($steps as $number => $step)
            @php($label = __('wizard.steps.'.$step['key']))
            <li class="wizard-steps__item {{ $number < $current ? 'wizard-steps__item--done' : '' }} {{ $number === $current ? 'wizard-steps__item--current' : '' }}"
                @if ($number === $current) aria-current="step" @endif>
                @if ($number < $current && $form)
                    <button type="submit" form="{{ $form }}" name="target" value="{{ $step['key'] }}" class="wizard-steps__link">
                        <span class="wizard-steps__badge"><x-icon name="check" /></span>
                        <span class="wizard-steps__label">{{ $label }}<span class="sr-only"> ({{ __('wizard.steps.done') }})</span></span>
                    </button>
                @elseif ($number < $current)
                    <a href="{{ route($step['route']) }}" class="wizard-steps__link">
                        <span class="wizard-steps__badge"><x-icon name="check" /></span>
                        <span class="wizard-steps__label">{{ $label }}<span class="sr-only"> ({{ __('wizard.steps.done') }})</span></span>
                    </a>
                @else
                    <span class="wizard-steps__link">
                        <span class="wizard-steps__badge">{{ $number }}</span>
                        <span class="wizard-steps__label">{{ $label }}</span>
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
