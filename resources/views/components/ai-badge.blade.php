{{--
    AI-jelölés (CG 7.3, PRD US-08 AC5): full = egy teljes mondat a címoldalon; compact = ikon + rövid felirat
    minden oldal szövege alatt, a teljes mondattal title-ben és képernyőolvasónak.
    AI label (CG 7.3, PRD US-08 AC5): full = one full sentence on the title page; compact = icon + short label
    under each page's text, with the full sentence in title and for screen readers.
--}}
@props(['variant' => 'compact'])

@if ($variant === 'full')
    <p class="ai-badge ai-badge--full"><x-icon name="sparkles" /> {{ __('story.ai_notice_full') }}</p>
@else
    <p class="ai-badge ai-badge--compact" title="{{ __('story.ai_notice_full') }}">
        <x-icon name="sparkles" />
        <span aria-hidden="true">{{ __('story.ai_badge') }}</span>
        <span class="sr-only">{{ __('story.ai_notice_full') }}</span>
    </p>
@endif
