{{-- S-24 – Cselekvés szerkesztése (App Flow 4.4): név, jelenlegi kép, opcionális új kép.
     S-24 – Edit action (App Flow 4.4): name, current image, optional new image. --}}
<x-layouts.admin :title="__('admin.actions.edit_title', ['name' => $action->name])">
    <a class="back-link" href="{{ route('admin.characters.edit', $action->character) }}"><x-icon name="arrow-left" /> {{ $action->character->name }}</a>
    <h1>{{ __('admin.actions.edit_title', ['name' => $action->name]) }}</h1>
    <x-error-summary />

    <form method="POST" action="{{ route('admin.actions.update', $action) }}" enctype="multipart/form-data" class="card form form--narrow" novalidate>
        @csrf
        @method('PUT')
        <x-form-field name="name" :label="__('admin.actions.name')" :hint="__('admin.actions.name_hint')" :value="$action->name" maxlength="50" required />
        <figure class="current-image">
            <img class="current-image__img current-image__img--square" src="{{ \App\Support\CanvasState::imageUrl($action->image_path) }}"
                 alt="{{ $action->character->name }} – {{ $action->name }}" width="800" height="800">
            <figcaption class="current-image__caption">{{ __('admin.current_image') }}</figcaption>
        </figure>
        <x-form-field name="image" type="file" :label="__('admin.new_image')" :hint="__('admin.action_image_hint')"
                      accept=".svg,.png,image/svg+xml,image/png" />
        <div class="actions actions--split">
            <x-button variant="secondary" :href="route('admin.characters.edit', $action->character)">{{ __('admin.cancel') }}</x-button>
            <x-button variant="primary"><x-icon name="device-floppy" /> {{ __('admin.save') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
