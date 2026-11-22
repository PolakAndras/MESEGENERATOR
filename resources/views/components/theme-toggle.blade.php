{{--
    Az olvasó esti/nappali kapcsolója (CG 7.4). Mindkét felirat benne van; a CSS a data-story-theme alapján a
    KÖVETKEZŐ állapotot mutatja. Telefonon csak az ikon látszik, a felirat képernyőolvasónak megmarad.
    JS nélkül rejtett, mert nem működne. A story-theme.js kezeli.
    The reader's night/day switch (CG 7.4). It holds both labels; CSS shows the NEXT state based on data-story-theme.
    On phones only the icon shows; the label stays for screen readers. Hidden without JS. Handled by story-theme.js.
--}}
<button type="button" class="theme-toggle" data-story-theme-toggle>
    <span class="theme-toggle__to-day">
        <x-icon name="sun" />
        <span class="theme-toggle__label">{{ __('story.theme_to_day') }}</span>
    </span>
    <span class="theme-toggle__to-night">
        <x-icon name="moon" />
        <span class="theme-toggle__label">{{ __('story.theme_to_night') }}</span>
    </span>
</button>
