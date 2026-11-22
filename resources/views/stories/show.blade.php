{{--
    S-12 – Mentett mese (App Flow 4.1): olvasó + „Vissza a Meséimhez” és „Törlés” (JS-megerősítéssel).
    Szerkesztés és új szöveg nincs (PRD 4.4, D-18).
    S-12 – Saved story (App Flow 4.1): reader + "Back to my stories" and "Delete" (with JS confirmation).
    No editing and no new text (PRD 4.4, D-18).
--}}
@push('scripts')
    <script type="module" src="{{ asset('js/story-theme.js') }}"></script>
@endpush

<x-layouts.app :title="$story->title" :story="true">
    <x-story-reader :title="$story->title" :child-name="$story->child_name" :pages="$pages" :current="$current"
                    :page-url="fn ($n) => route('stories.show', ['story' => $story, 'page' => $n])">
        <a class="story-button story-button--secondary" href="{{ route('stories.index') }}">
            <x-icon name="arrow-left" /><span>{{ __('story.back_to_list') }}</span>
        </a>
        <form method="POST" action="{{ route('stories.destroy', $story) }}" data-confirm="{{ __('stories.delete_confirm') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="story-button story-button--secondary">
                <x-icon name="trash" /><span>{{ __('stories.delete') }}</span>
            </button>
        </form>
    </x-story-reader>
</x-layouts.app>
