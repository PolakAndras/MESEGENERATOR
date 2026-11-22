{{-- S-25 – Hátterek listája (App Flow 4.4); törlés nincs (Backend Schema E-01).
     S-25 – Background list (App Flow 4.4); no deletion (Backend Schema E-01). --}}
<x-layouts.admin :title="__('admin.backgrounds.title')">
    <div class="page-head">
        <h1>{{ __('admin.backgrounds.title') }}</h1>
        <x-button variant="primary" :href="route('admin.backgrounds.create')"><x-icon name="plus" /> {{ __('admin.backgrounds.new') }}</x-button>
    </div>

    @if ($backgrounds->isEmpty())
        <x-empty-state icon="photo" :title="__('admin.backgrounds.empty_title')" :text="__('admin.backgrounds.empty_text')">
            <x-button variant="primary" :href="route('admin.backgrounds.create')">{{ __('admin.backgrounds.new') }}</x-button>
        </x-empty-state>
    @else
        <table class="data-table">
            <thead class="data-table__head">
                <tr>
                    <th class="data-table__heading" scope="col"><span class="sr-only">{{ __('admin.image') }}</span></th>
                    <th class="data-table__heading" scope="col">{{ __('admin.backgrounds.name') }}</th>
                    <th class="data-table__heading" scope="col"><span class="sr-only">{{ __('admin.edit') }}</span></th>
                </tr>
            </thead>
            <tbody class="data-table__body">
                @foreach ($backgrounds as $background)
                    <tr class="data-table__row">
                        <td class="data-table__cell">
                            <img class="thumb thumb--wide" src="{{ \App\Support\CanvasState::imageUrl($background->image_path) }}"
                                 alt="{{ $background->name }}" width="1600" height="1200" loading="lazy">
                        </td>
                        <td class="data-table__cell data-table__primary">{{ $background->name }}</td>
                        <td class="data-table__cell">
                            <div class="data-table__actions">
                                <x-button variant="secondary" :href="route('admin.backgrounds.edit', $background)"><x-icon name="edit" /> {{ __('admin.edit') }}</x-button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-layouts.admin>
