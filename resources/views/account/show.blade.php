{{--
    S-13 – Fiókom (App Flow 4.1): név, e-mail, regisztráció dátuma; fióktörlés jelszóval, figyelmeztetéssel és
    JS-megerősítéssel (US-13 AC1). Adminnál a törlés szakasz nem jelenik meg (PRD 6.).
    S-13 – My account (App Flow 4.1): name, email, registration date; account deletion with password, a warning and
    a JS confirmation (US-13 AC1). For the admin the deletion section is not shown (PRD 6.).
--}}
<x-layouts.app :title="__('account.title')">
    <div class="app-layout__content app-layout__content--form">
        <h1>{{ __('account.title') }}</h1>

        <dl class="card details">
            <div class="details__row"><dt class="details__term">{{ __('account.name') }}</dt><dd class="details__value">{{ $user->name }}</dd></div>
            <div class="details__row"><dt class="details__term">{{ __('account.email') }}</dt><dd class="details__value">{{ $user->email }}</dd></div>
            <div class="details__row">
                <dt class="details__term">{{ __('account.registered_at') }}</dt>
                <dd class="details__value">{{ optional($user->created_at)->format(__('stories.date_format')) }}</dd>
            </div>
        </dl>

        @if ($user->isAdmin())
            <p class="form-footnote">{{ __('account.admin_note') }}</p>
        @else
            <section class="card card--danger" aria-labelledby="delete-title">
                <h2 id="delete-title">{{ __('account.delete_title') }}</h2>
                <div class="flash flash--warning">
                    <x-icon name="alert-triangle" />
                    <p class="flash__message">{{ __('account.delete_warning') }}</p>
                </div>
                <form method="POST" action="{{ route('account.destroy') }}" class="form" data-confirm="{{ __('account.delete_confirm') }}" novalidate>
                    @csrf
                    @method('DELETE')
                    <x-form-field name="password" type="password" :label="__('account.delete_password')" autocomplete="current-password" required />
                    <div class="actions actions--end">
                        <x-button variant="danger"><x-icon name="trash" /> {{ __('account.delete_button') }}</x-button>
                    </div>
                </form>
            </section>
        @endif
    </div>
</x-layouts.app>
