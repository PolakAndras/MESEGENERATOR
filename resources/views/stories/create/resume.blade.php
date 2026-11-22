{{-- S-05 – Folytatás vagy új mese (App Flow 4.2, D-06). Az „Újat kezdek” POST, JS-megerősítéssel (confirm.js).
     S-05 – Continue or start a new story (App Flow 4.2, D-06). "Start new" is a POST, with a JS confirmation (confirm.js). --}}
<x-layouts.app :title="__('wizard.create.resume_title')">
    <div class="app-layout__content app-layout__content--form">
        <div class="card">
            <h1>{{ __('wizard.create.resume_title') }}</h1>
            <p>{{ $childName ? __('wizard.create.resume_text_named', ['name' => $childName]) : __('wizard.create.resume_text') }}</p>

            @if ($generated)
                <div class="flash flash--warning" role="status">
                    <x-icon name="alert-triangle" />
                    <p class="flash__message">{{ __('wizard.create.resume_generated_warning') }}</p>
                </div>
            @endif

            <div class="actions">
                <x-button variant="primary" :href="$resumeUrl"><x-icon name="arrow-right" /> {{ __('wizard.create.continue') }}</x-button>
                <form method="POST" action="{{ route('stories.create.reset') }}" data-confirm="{{ __('wizard.create.restart_confirm') }}">
                    @csrf
                    <x-button variant="secondary"><x-icon name="refresh" /> {{ __('wizard.create.restart') }}</x-button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
