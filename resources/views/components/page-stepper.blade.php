{{--
    Oldalléptető (App Flow K-05, CG 6.7): 1…N gomb, mind űrlapküldés (előbb az aktuális oldal mentése).
    A háttér nélküli oldal jelölve (üres kör + aria-label). Telefonon a sáv vízszintesen görgethető.
    Page stepper (App Flow K-05, CG 6.7): buttons 1…N, each a form submit (the current page is saved first).
    Pages without a background are marked (empty circle + aria-label). On phones the bar scrolls horizontally.
--}}
@props(['current', 'count', 'missing' => [], 'form'])

<nav class="page-stepper" aria-label="{{ __('wizard.pages.stepper') }}">
    <ol class="page-stepper__list">
        @for ($n = 1; $n <= $count; $n++)
            @php($isMissing = in_array($n, $missing, true))
            <li>
                <button type="submit" form="{{ $form }}" name="target" value="page:{{ $n }}"
                        class="page-stepper__button {{ $n === $current ? 'page-stepper__button--current' : '' }}"
                        aria-label="{{ $isMissing ? __('wizard.pages.stepper_missing', ['page' => $n]) : __('wizard.pages.stepper_page', ['page' => $n]) }}"
                        @if ($n === $current) aria-current="page" @endif>
                    {{ $n }}
                    @if ($isMissing)
                        <span class="page-stepper__missing" aria-hidden="true"></span>
                    @endif
                </button>
            </li>
        @endfor
    </ol>
</nav>
