{{--
    S-04 – Regisztráció (App Flow 4.1, PRD US-11). A tájékoztató elfogadása kötelező jelölőnégyzet; a link új lapon
    nyílik, hogy a kitöltött űrlap ne vesszen el (App Flow S-02). Hibánál a jelszómezők kivételével minden megmarad.
    S-04 – Registration (App Flow 4.1, PRD US-11). Accepting the privacy notice is a required checkbox; the link opens
    in a new tab so the filled form is not lost (App Flow S-02). On error everything but the passwords is kept.
--}}
@php($privacyError = $errors->first('privacy_accepted'))

<x-layouts.app :title="__('account.register_title')">
    <div class="app-layout__content app-layout__content--form">
        <h1>{{ __('account.register_title') }}</h1>
        <p class="lead">{{ __('account.register_lead') }}</p>

        <x-error-summary />

        <form method="POST" action="{{ route('register') }}" class="card form" novalidate>
            @csrf
            <x-form-field name="name" :label="__('account.name')" autocomplete="name" maxlength="255" required />
            <x-form-field name="email" type="email" :label="__('account.email')" autocomplete="email" maxlength="255" required />
            <x-form-field name="password" type="password" :label="__('account.password')" :hint="__('account.password_hint')"
                          autocomplete="new-password" required />
            <x-form-field name="password_confirmation" type="password" :label="__('account.password_confirmation')"
                          autocomplete="new-password" required />

            <div class="form-field {{ $privacyError ? 'form-field--invalid' : '' }}" id="privacy_accepted-wrap">
                <label class="checkbox">
                    <input class="checkbox__input" type="checkbox" name="privacy_accepted" value="1" id="privacy_accepted"
                           @if (old('privacy_accepted')) checked @endif
                           @if ($privacyError) aria-invalid="true" aria-describedby="privacy_accepted-error" @endif required>
                    <span class="checkbox__label">
                        {!! __('account.privacy_accept', ['link' => '<a href="'.e(route('privacy')).'" target="_blank" rel="noopener">'.e(__('account.privacy_link')).'<span class="sr-only"> '.e(__('account.privacy_new_tab')).'</span></a>']) !!}
                    </span>
                </label>
                @if ($privacyError)
                    <p class="form-field__error" id="privacy_accepted-error"><x-icon name="alert-circle" /> {{ $privacyError }}</p>
                @endif
            </div>

            <div class="actions actions--end">
                <x-button variant="primary">{{ __('account.register_button') }}</x-button>
            </div>
        </form>

        <p class="form-footnote">{{ __('account.has_account') }} <a href="{{ route('login') }}">{{ __('account.login_link') }}</a></p>
    </div>
</x-layouts.app>
