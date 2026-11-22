{{-- S-23 – Új cselekvés (App Flow 4.4): név + kép (SVG/PNG, 2 MB, PRD US-17 AC3), súgóban az ajánlott méret.
     S-23 – New action (App Flow 4.4): name + image (SVG/PNG, 2 MB, PRD US-17 AC3), the hint gives the size. --}}
<x-layouts.admin :title="__('admin.actions.new_title', ['name' => $character->name])">
    <a class="back-link" href="{{ route('admin.characters.edit', $character) }}"><x-icon name="arrow-left" /> {{ $character->name }}</a>
    <h1>{{ __('admin.actions.new_title', ['name' => $character->name]) }}</h1>
    <x-error-summary />

    <form method="POST" action="{{ route('admin.characters.actions.store', $character) }}" enctype="multipart/form-data" class="card form form--narrow" novalidate>
        @csrf
        <x-form-field name="name" :label="__('admin.actions.name')" :hint="__('admin.actions.name_hint')" maxlength="50" required />
        <x-form-field name="image" type="file" :label="__('admin.image')" :hint="__('admin.action_image_hint')"
                      accept=".svg,.png,image/svg+xml,image/png" required />
        <div class="actions actions--split">
            <x-button variant="secondary" :href="route('admin.characters.edit', $character)">{{ __('admin.cancel') }}</x-button>
            <x-button variant="primary"><x-icon name="device-floppy" /> {{ __('admin.save') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
