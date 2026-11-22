{{--
    Üres állapot (CG 9.2): ikon, cím, egy meghívó mondat, legfeljebb egy gomb (a slotban).
    Empty state (CG 9.2): icon, title, one inviting sentence, at most one button (in the slot).
--}}
@props(['icon' => 'book', 'title', 'text' => null])

<div class="empty-state">
    <x-icon :name="$icon" class="empty-state__icon" />
    <h2 class="empty-state__title">{{ $title }}</h2>
    @if ($text)
        <p class="empty-state__text">{{ $text }}</p>
    @endif
    {{ $slot }}
</div>
