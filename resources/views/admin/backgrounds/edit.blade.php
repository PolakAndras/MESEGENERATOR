{{-- S-27 – Háttér szerkesztése (App Flow 4.4): név, jelenlegi kép, opcionális új kép; törlés nincs (E-01).
     S-27 – Edit background (App Flow 4.4): name, current image, optional new image; no deletion (E-01). --}}
<x-layouts.admin :title="__('admin.backgrounds.edit_title', ['name' => $background->name])">
    <a class="back-link" href="{{ route('admin.backgrounds.index') }}"><x-icon name="arrow-left" /> {{ __('admin.back_to_list') }}</a>
    <h1>{{ __('admin.backgrounds.edit_title', ['name' => $background->name]) }}</h1>
    <x-error-summary />

    <form method="POST" action="{{ route('admin.backgrounds.update', $background) }}" enctype="multipart/form-data" class="card form form--narrow" novalidate>
        @csrf
        @method('PUT')
        <x-form-field name="name" :label="__('admin.backgrounds.name')" :hint="__('admin.backgrounds.name_hint')" :value="$background->name" maxlength="100" required />
        <figure class="current-image">
            <img class="current-image__img" src="{{ \App\Support\CanvasState::imageUrl($background->image_path) }}"
                 alt="{{ $background->name }}" width="1600" height="1200">
            <figcaption class="current-image__caption">{{ __('admin.current_image') }}</figcaption>
        </figure>
        <x-form-field name="image" type="file" :label="__('admin.new_image')" :hint="__('admin.background_image_hint')"
                      accept=".png,.jpg,.jpeg,.svg,image/png,image/jpeg,image/svg+xml" />
        <div class="actions actions--split">
            <x-button variant="secondary" :href="route('admin.backgrounds.index')">{{ __('admin.cancel') }}</x-button>
            <x-button variant="primary"><x-icon name="device-floppy" /> {{ __('admin.save') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
