{{-- S-20 – Szereplők listája (App Flow 4.4, CG 6.8): szűrő típus szerint; törlés nincs (Backend Schema E-01).
     S-20 – Character list (App Flow 4.4, CG 6.8): filter by type; no deletion (Backend Schema E-01). --}}
<x-layouts.admin :title="__('admin.characters.title')">
    <div class="page-head">
        <h1>{{ __('admin.characters.title') }}</h1>
        <x-button variant="primary" :href="route('admin.characters.create')"><x-icon name="plus" /> {{ __('admin.characters.new') }}</x-button>
    </div>

    <nav class="filter-tabs" aria-label="{{ __('admin.characters.filter') }}">
        @foreach ([null => __('admin.characters.filter_all')] + __('admin.characters.types') as $value => $label)
            <a class="filter-tabs__tab" href="{{ route('admin.characters.index', $value ? ['type' => $value] : []) }}"
               @if ($type === ($value ?: null)) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($characters->isEmpty())
        <x-empty-state icon="photo" :title="__('admin.characters.empty_title')" :text="__('admin.characters.empty_text')">
            <x-button variant="primary" :href="route('admin.characters.create')">{{ __('admin.characters.new') }}</x-button>
        </x-empty-state>
    @else
        <table class="data-table">
            <thead class="data-table__head">
                <tr>
                    <th class="data-table__heading" scope="col"><span class="sr-only">{{ __('admin.characters.image') }}</span></th>
                    <th class="data-table__heading" scope="col">{{ __('admin.characters.name') }}</th>
                    <th class="data-table__heading" scope="col">{{ __('admin.characters.type') }}</th>
                    <th class="data-table__heading" scope="col">{{ __('admin.characters.actions_count') }}</th>
                    <th class="data-table__heading" scope="col"><span class="sr-only">{{ __('admin.edit') }}</span></th>
                </tr>
            </thead>
            <tbody class="data-table__body">
                @foreach ($characters as $character)
                    <tr class="data-table__row">
                        <td class="data-table__cell">
                            @if ($character->defaultAction)
                                <img class="thumb" src="{{ \App\Support\CanvasState::imageUrl($character->defaultAction->image_path) }}"
                                     alt="{{ $character->name }} – {{ $character->defaultAction->name }}" width="800" height="800" loading="lazy">
                            @endif
                        </td>
                        <td class="data-table__cell data-table__primary">{{ $character->name }}</td>
                        <td class="data-table__cell">
                            <span class="data-table__label">{{ __('admin.characters.type') }}: </span>
                            {{ __('admin.characters.types.'.$character->type) }}
                            @if ($character->type === \App\Models\Character::TYPE_CHILD_FIGURE)
                                <span class="muted">({{ __('wizard.child.genders.'.$character->gender) }}, {{ mb_strtolower(__('wizard.child.hair_colors.'.$character->hair_color)) }})</span>
                            @endif
                        </td>
                        <td class="data-table__cell"><span class="data-table__label">{{ __('admin.characters.actions_count') }}: </span>{{ $character->actions_count }}</td>
                        <td class="data-table__cell">
                            <div class="data-table__actions">
                                <x-button variant="secondary" :href="route('admin.characters.edit', $character)"><x-icon name="edit" /> {{ __('admin.edit') }}</x-button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-layouts.admin>
