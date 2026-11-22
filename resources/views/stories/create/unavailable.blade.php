{{-- S-06 – Mesekészítés nem elérhető: nincs egyetlen háttér sem (App Flow 4.2, CG 9.2).
     S-06 – Story creation unavailable: there is no background at all (App Flow 4.2, CG 9.2). --}}
<x-layouts.app :title="__('wizard.create.unavailable_title')">
    <div class="app-layout__content app-layout__content--form">
        <x-empty-state icon="photo" :title="__('wizard.create.unavailable_title')" :text="__('wizard.create.unavailable_text')">
            <x-button variant="secondary" :href="route('home')">{{ __('wizard.create.back_home') }}</x-button>
        </x-empty-state>
    </div>
</x-layouts.app>
