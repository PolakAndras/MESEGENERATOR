{{--
    Olvasó (App Flow K-07, CG 7.1–7.4). Egyszerre egy oldal: 0 = címoldal, 1…N = meseoldal; a lapozás sima link
    (?page=), mert csak navigáció, adatot nem módosít. A téma alapból esti; a szekció nyitótagja utáni egysoros
    szkript a kirajzolás előtt beállítja a tárolt témát, így nincs villanás (CG 7.4, D-09).
    pages: [n => ['canvas' => CanvasState, 'text' => string]]; pageUrl: fn (int $n) => URL. A műveletsáv a slotban.

    Reader (App Flow K-07, CG 7.1–7.4). One page at a time: 0 = title page, 1…N = story pages; paging is a plain
    link (?page=) because it only navigates and changes no data. The default theme is night; a one-line script right
    after the section's opening tag applies the stored theme before painting, so there is no flash (CG 7.4, D-09).
    pages: [n => ['canvas' => CanvasState, 'text' => string]]; pageUrl: fn (int $n) => URL. Actions go in the slot.
--}}
@props(['title', 'childName', 'pages', 'current', 'pageUrl'])

@php
    $count = count($pages);
    $isCover = $current === 0;
    $page = $isCover ? null : $pages[$current];
@endphp

<section class="story-reader" data-story-theme="night" data-story-reader>
    <script>try { var t = localStorage.getItem('mesegenerator.storyTheme'); if (t === 'day' || t === 'night') document.currentScript.parentNode.setAttribute('data-story-theme', t); } catch (e) {}</script>

    <div class="story-reader__sheet">
        <header class="story-reader__header">
            @if ($isCover)
                <p class="story-reader__kicker">{{ __('story.cover') }}</p>
            @else
                <h1 class="story-reader__title">{{ $title }}</h1>
            @endif
            <x-theme-toggle />
        </header>

        @if ($isCover)
            {{-- Címoldal (CG 7.2): cím, főszereplő, teljes AI-mondat, az 1. oldal vászna díszítésként.
                 Title page (CG 7.2): title, main character, full AI sentence, page 1's canvas as decoration. --}}
            <div class="story-reader__cover">
                <h1 class="story-reader__cover-title">{{ $title }}</h1>
                <p class="story-reader__byline">{{ __('story.main_character', ['name' => $childName]) }}</p>
                <x-ai-badge variant="full" />
            </div>
            @if ($count > 0)
                <x-story-canvas :canvas="$pages[1]['canvas']" class="story-reader__canvas" />
            @endif
        @else
            <x-story-canvas :canvas="$page['canvas']" class="story-reader__canvas" />
            <p class="story-reader__text">{{ $page['text'] }}</p>
            <x-ai-badge variant="compact" />
        @endif

        <nav class="story-reader__pager" aria-label="{{ __('story.pager') }}">
            @if ($current > 0)
                <a class="story-button story-button--secondary" href="{{ $pageUrl($current - 1) }}" aria-label="{{ __('story.prev_label') }}">
                    <x-icon name="arrow-left" /><span class="story-button__label">{{ __('story.prev') }}</span>
                </a>
            @else
                <span class="story-button story-button--secondary" aria-disabled="true" aria-label="{{ __('story.prev_label') }}">
                    <x-icon name="arrow-left" /><span class="story-button__label">{{ __('story.prev') }}</span>
                </span>
            @endif

            <span class="story-reader__page-number">
                {{ $isCover ? __('story.cover') : __('story.page_of', ['page' => $current, 'count' => $count]) }}
            </span>

            @if ($current < $count)
                <a class="story-button story-button--primary" href="{{ $pageUrl($current + 1) }}" aria-label="{{ __('story.next_label') }}">
                    <span class="story-button__label">{{ __('story.next') }}</span><x-icon name="arrow-right" />
                </a>
            @else
                <span class="story-button story-button--primary" aria-disabled="true" aria-label="{{ __('story.next_label') }}">
                    <span class="story-button__label">{{ __('story.next') }}</span><x-icon name="arrow-right" />
                </span>
            @endif
        </nav>
    </div>

    @if (trim($slot) !== '')
        <div class="story-reader__actions">
            {{ $slot }}
        </div>
    @endif
</section>
