{{--
    Töltésjelző (App Flow K-08, CG 9.1): teljes képernyős réteg forgó jelzővel. A loading-overlay.js kapcsolja be
    a data-loading-submit gombos űrlap elküldésekor, és 20 mp után türelemre kérő alcímre vált.
    Loading overlay (App Flow K-08, CG 9.1): a full-screen layer with a spinner. loading-overlay.js shows it when a
    form is submitted with a data-loading-submit button, and switches to a patience subtitle after 20 s.
--}}
<div class="loading-overlay" data-loading-overlay hidden>
    <div class="loading-overlay__card" role="status" aria-live="polite" tabindex="-1" data-loading-card>
        <span class="loading-overlay__spinner" aria-hidden="true"></span>
        <p class="loading-overlay__title">{{ __('loading.title') }}</p>
        <p class="loading-overlay__subtitle" data-loading-subtitle data-slow-text="{{ __('loading.subtitle_slow') }}">{{ __('loading.subtitle') }}</p>
    </div>
</div>
