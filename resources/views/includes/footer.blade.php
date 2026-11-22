{{--
    Lábléc (App Flow K-02): bal oldalt az alkalmazás neve egy mondattal, jobbra a link az adatkezelési
    tájékoztatóra (S-02). A link a tájékoztató kötelező elérési helye (PRD US-20 AC1), ezért minden oldalon ott van.

    Footer (App Flow K-02): the app name with one sentence on the left, the link to the privacy notice (S-02)
    on the right. The link is a required entry point to the notice (PRD US-20 AC1), so it is on every page.
--}}
<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__brand">
            <span class="site-footer__name">
                <x-icon name="book" />
                {{ __('common.app_name') }}
            </span>
            <p class="site-footer__tagline">{{ __('common.footer.tagline') }}</p>
        </div>

        <nav aria-label="{{ __('common.footer.label') }}">
            <a class="site-footer__link" href="{{ route('privacy') }}">{{ __('common.footer.privacy') }}</a>
        </nav>
    </div>
</footer>
