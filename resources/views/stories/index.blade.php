{{--
    S-11 – Meséim (App Flow 4.1, CG 6.8): a saját mesék, legújabb elöl. Asztalin táblázat, telefonon kártyák –
    ugyanaz a HTML-táblázat, a CSS rendezi kártyákká (a cellacímke telefonon látszik). Üres állapot: CG 9.2.
    S-11 – My stories (App Flow 4.1, CG 6.8): own stories, newest first. A table on desktop, cards on phones –
    the same HTML table turned into cards by CSS (the cell label shows on phones). Empty state: CG 9.2.
--}}
<x-layouts.app :title="__('stories.title')">
    <div class="app-layout__content">
        <h1>{{ __('stories.title') }}</h1>

        @if ($stories->isEmpty())
            <x-empty-state icon="book" :title="__('stories.empty_title')" :text="__('stories.empty_text')">
                <x-button variant="primary" :href="route('stories.create')"><x-icon name="sparkles" /> {{ __('stories.create') }}</x-button>
            </x-empty-state>
        @else
            <table class="data-table">
                <thead class="data-table__head">
                    <tr>
                        <th class="data-table__heading" scope="col">{{ __('stories.col_title') }}</th>
                        <th class="data-table__heading" scope="col">{{ __('stories.col_child') }}</th>
                        <th class="data-table__heading" scope="col">{{ __('stories.col_date') }}</th>
                        <th class="data-table__heading" scope="col"><span class="sr-only">{{ __('stories.col_actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="data-table__body">
                    @foreach ($stories as $story)
                        <tr class="data-table__row">
                            <td class="data-table__cell data-table__primary">
                                <a href="{{ route('stories.show', $story) }}">{{ $story->title }}</a>
                            </td>
                            <td class="data-table__cell"><span class="data-table__label">{{ __('stories.col_child') }}: </span>{{ $story->child_name }}</td>
                            <td class="data-table__cell">
                                <span class="data-table__label">{{ __('stories.col_date') }}: </span>
                                <time datetime="{{ $story->created_at->toDateString() }}">{{ $story->created_at->format(__('stories.date_format')) }}</time>
                            </td>
                            <td class="data-table__cell">
                                <div class="data-table__actions">
                                    <x-button variant="secondary" :href="route('stories.show', $story)"><x-icon name="book" /> {{ __('stories.open') }}</x-button>
                                    <form method="POST" action="{{ route('stories.destroy', $story) }}" data-confirm="{{ __('stories.delete_confirm') }}">
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
    </div>
</x-layouts.app>
