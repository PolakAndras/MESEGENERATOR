{{--
    S-03 – Belépés (App Flow 4.1). Hibánál általános üzenet az űrlap tetején, mezőszintű jelölés nélkül
    (US-12 AC1, CG 9.3); az e-mail megmarad. Elfelejtett jelszó link nincs (US-12 AC3).
    S-03 – Login (App Flow 4.1). On failure a generic message at the top, no field-level marking
    (US-12 AC1, CG 9.3); the email is kept. No forgotten-password link (US-12 AC3).
--}}
<x-layouts.app :title="__('account.login_title')">
    <div class="app-layout__content app-layout__content--form">
        <h1>{{ __('account.login_title') }}</h1>
        <p class="lead">{{ __('account.login_lead') }}</p>

        @if ($errors->has('login'))
            <div class="flash flash--error" role="alert">
                <x-icon name="alert-circle" />
                <p class="flash__message">{{ $errors->first('login') }}</p>
            </div>
        @else
            <x-error-summary />
        @endif

        <form method="POST" action="{{ route('login') }}" class="card form" novalidate>
            @csrf
            <x-form-field name="email" type="email" :label="__('account.email')" autocomplete="email" required autofocus />
            <x-form-field name="password" type="password" :label="__('account.password')" autocomplete="current-password" required />
            <div class="actions actions--end">
                <x-button variant="primary"><x-icon name="user" /> {{ __('account.login_button') }}</x-button>
            </div>
        </form>

        <p class="form-footnote">{{ __('account.no_account') }} <a href="{{ route('register') }}">{{ __('account.register_link') }}</a></p>
    </div>
</x-layouts.app>
