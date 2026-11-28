{{--
    AI-jelölés (CG 7.3, PRD US-08 AC5): full = egy teljes mondat a címoldalon; compact = ikon + rövid felirat
    minden oldal szövege alatt, a teljes mondattal title-ben és képernyőolvasónak.
    Csak akkor jelenik meg, ha AI-szolgáltató írja a szöveget (STORY_GENERATOR=groq/mistral); a mesegyűjteményből
    összeállított mesénél (PRD 1.5 US-24) nincs mit jelölni.

    AI label (CG 7.3, PRD US-08 AC5): full = one full sentence on the title page; compact = icon + short label
    under each page's text, with the full sentence in title and for screen readers.
    Only shown when an AI provider writes the text (STORY_GENERATOR=groq/mistral); a story assembled from the story
    collection (PRD 1.5 US-24) has nothing to label.
--}}
@props(['variant' => 'compact'])

@if (config('story.providers.'.config('story.generator')))
    @if ($variant === 'full')
        <p class="ai-badge ai-badge--full"><x-icon name="sparkles" /> {{ __('story.ai_notice_full') }}</p>
    @else
        <p class="ai-badge ai-badge--compact" title="{{ __('story.ai_notice_full') }}">
            <x-icon name="sparkles" />
            <span aria-hidden="true">{{ __('story.ai_badge') }}</span>
            <span class="sr-only">{{ __('story.ai_notice_full') }}</span>
        </p>
    @endif
@endif
