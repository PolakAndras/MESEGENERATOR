{{--
    Admin meselista (Backend Schema 10.5, E-02; NY-02 döntés: cím, gyerek neve, szülő neve, dátum; Megnyitás és
    Törlés soronként; menüpont az admin menüben; üres állapot a CG 9.2 mintájára).
    Admin story list (Backend Schema 10.5, E-02; NY-02 decision: title, child's name, parent's name, date; Open and
    Delete per row; a menu item in the admin menu; empty state following CG 9.2).
--}}
<x-layouts.admin :title="__('admin.stories.title')">
    <h1>{{ __('admin.stories.title') }}</h1>
    <p class="lead">{{ __('admin.stories.lead') }}</p>

    @if ($stories->isEmpty())
        <x-empty-state icon="book" :title="__('admin.stories.empty_title')" :text="__('admin.stories.empty_text')" />
    @else
        <table class="data-table">
            <thead class="data-table__head">
                <tr>
                    <th class="data-table__heading" scope="col">{{ __('stories.col_title') }}</th>
                    <th class="data-table__heading" scope="col">{{ __('stories.col_child') }}</th>
                    <th class="data-table__heading" scope="col">{{ __('admin.stories.parent') }}</th>
                    <th class="data-table__heading" scope="col">{{ __('stories.col_date') }}</th>
                    <th class="data-table__heading" scope="col"><span class="sr-only">{{ __('stories.col_actions') }}</span></th>
                </tr>
            </thead>
            <tbody class="data-table__body">
                @foreach ($stories as $story)
                    <tr class="data-table__row">
                        <td class="data-table__cell data-table__primary"><a href="{{ route('stories.show', $story) }}">{{ $story->title }}</a></td>
                        <td class="data-table__cell"><span class="data-table__label">{{ __('stories.col_child') }}: </span>{{ $story->child_name }}</td>
                        <td class="data-table__cell"><span class="data-table__label">{{ __('admin.stories.parent') }}: </span>{{ optional($story->user)->name }}</td>
                        <td class="data-table__cell"><span class="data-table__label">{{ __('stories.col_date') }}: </span>{{ $story->created_at->format(__('stories.date_format')) }}</td>
                        <td class="data-table__cell">
                            <div class="data-table__actions">
                                <x-button variant="secondary" :href="route('stories.show', $story)"><x-icon name="book" /> {{ __('stories.open') }}</x-button>
                                <form method="POST" action="{{ route('admin.stories.destroy', $story) }}" data-confirm="{{ __('stories.delete_confirm') }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" aria-label="{{ __('stories.delete_label', ['title' => $story->title]) }}">
                                        <x-icon name="trash" /> {{ __('stories.delete') }}
                                    </x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-layouts.admin>
