{{-- S-21 – Új szereplő (App Flow 4.4): az alaphelyzet neve és képe kötelező (D-16).
     S-21 – New character (App Flow 4.4): the default action's name and image are required (D-16). --}}
@push('scripts')
    <script type="module" src="{{ asset('js/character-form.js') }}"></script>
@endpush

<x-layouts.admin :title="__('admin.characters.new')">
    <h1>{{ __('admin.characters.new') }}</h1>
    <x-error-summary />

    <form method="POST" action="{{ route('admin.characters.store') }}" enctype="multipart/form-data" class="card form form--narrow" data-character-form novalidate>
        @csrf
        @include('admin.characters.partials.fields', ['character' => null])

        <fieldset class="form-section">
            <legend class="form-section__title">{{ __('admin.characters.default_action') }}</legend>
            <x-form-field name="default_action_name" :label="__('admin.characters.default_action_name')"
                          :hint="__('admin.characters.default_action_name_hint')" value="áll" maxlength="50" required />
            <x-form-field name="default_action_image" type="file" :label="__('admin.characters.default_action_image')"
                          :hint="__('admin.action_image_hint')" accept=".svg,.png,image/svg+xml,image/png" required />
        </fieldset>

        <div class="actions actions--split">
            <x-button variant="secondary" :href="route('admin.characters.index')">{{ __('admin.cancel') }}</x-button>
            <x-button variant="primary"><x-icon name="device-floppy" /> {{ __('admin.save') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
