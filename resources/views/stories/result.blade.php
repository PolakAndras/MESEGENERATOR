{{--
    S-10 – Eredmény (App Flow 4.2): olvasó + műveletsáv. „Legenerált” állapotban Új szöveg és – bejelentkezve – Mentés;
    vendégnek tájékoztató doboz belépéssel és regisztrációval (US-10 AC3). Mentett állapotban „Mentve” + link (D-18).
    S-10 – Result (App Flow 4.2): reader + action bar. In the "generated" state New text and – when logged in – Save;
    guests get an info box with login and registration (US-10 AC3). Once saved: "Saved" + link (D-18).
--}}
@push('scripts')
    <script type="module" src="{{ asset('js/story-theme.js') }}"></script>
    <script type="module" src="{{ asset('js/loading-overlay.js') }}"></script>
@endpush

<x-layouts.app :title="$title" :story="true">
    <x-story-reader :title="$title" :child-name="$childName" :pages="$pages" :current="$current"
                    :page-url="fn ($n) => route('stories.result', ['page' => $n])">
        @if ($saved)
            <p class="story-reader__saved"><x-icon name="circle-check" /> {{ __('story.saved') }}</p>
            <a class="story-button story-button--primary" href="{{ route('stories.show', $savedStoryId) }}">
                <x-icon name="book" /><span>{{ __('story.open_saved') }}</span>
            </a>
            <a class="story-button story-button--secondary" href="{{ route('stories.create') }}">
                <x-icon name="plus" /><span>{{ __('story.new_story') }}</span>
            </a>
        @else
            <form method="POST" action="{{ route('stories.regenerate') }}" data-loading-form>
                @csrf
                <button type="submit" class="story-button story-button--secondary" data-loading-submit>
                    <x-icon name="refresh" /><span>{{ __('story.regenerate') }}</span>
                </button>
            </form>

            @auth
                <form method="POST" action="{{ route('stories.store') }}">
                    @csrf
                    <button type="submit" class="story-button story-button--primary">
                        <x-icon name="device-floppy" /><span>{{ __('story.save') }}</span>
                    </button>
                </form>
            @else
                <div class="flash flash--info story-reader__guest">
                    <x-icon name="info-circle" />
                    <div>
                        <p class="flash__title">{{ __('story.guest_title') }}</p>
                        <p class="flash__message">{{ __('story.guest_text') }}</p>
                        <div class="actions">
                            <x-button variant="primary" :href="route('login')">{{ __('story.login') }}</x-button>
                            <x-button variant="secondary" :href="route('register')">{{ __('story.register') }}</x-button>
                        </div>
                    </div>
                </div>
            @endauth
        @endif
    </x-story-reader>

    <x-loading-overlay />
</x-layouts.app>
