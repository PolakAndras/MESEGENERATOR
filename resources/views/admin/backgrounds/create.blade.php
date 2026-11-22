{{-- S-26 – Új háttér (App Flow 4.4): név (ezt kapja az AI is) + kép (PNG/JPG/SVG, 5 MB, PRD US-18 AC2).
     S-26 – New background (App Flow 4.4): name (also sent to the AI) + image (PNG/JPG/SVG, 5 MB, PRD US-18 AC2). --}}
<x-layouts.admin :title="__('admin.backgrounds.new')">
    <a class="back-link" href="{{ route('admin.backgrounds.index') }}"><x-icon name="arrow-left" /> {{ __('admin.back_to_list') }}</a>
    <h1>{{ __('admin.backgrounds.new') }}</h1>
    <x-error-summary />

    <form method="POST" action="{{ route('admin.backgrounds.store') }}" enctype="multipart/form-data" class="card form form--narrow" novalidate>
        @csrf
        <x-form-field name="name" :label="__('admin.backgrounds.name')" :hint="__('admin.backgrounds.name_hint')" maxlength="100" required />
        <x-form-field name="image" type="file" :label="__('admin.image')" :hint="__('admin.background_image_hint')"
                      accept=".png,.jpg,.jpeg,.svg,image/png,image/jpeg,image/svg+xml" required />
        <div class="actions actions--split">
            <x-button variant="secondary" :href="route('admin.backgrounds.index')">{{ __('admin.cancel') }}</x-button>
            <x-button variant="primary"><x-icon name="device-floppy" /> {{ __('admin.save') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
