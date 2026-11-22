{{--
    Üzenetsáv (App Flow K-03, CG 6.5). Átirányítás után egyszer jelenik meg; a kontroller így állítja be:
    session()->flash('flash', ['type' => 'success', 'message' => __('…')]).
    A típus dönti el az ikont és az ARIA-szerepet: hibánál role="alert" (a képernyőolvasó azonnal felolvassa),
    a többinél role="status" (udvariasan, sorra kerülve). Ismeretlen típusnál semmi nem jelenik meg.

    Message bar (App Flow K-03, CG 6.5). Shown once after a redirect; the controller sets it like this:
    session()->flash('flash', ['type' => 'success', 'message' => __('…')]).
    The type decides the icon and the ARIA role: role="alert" for errors (read out immediately),
    role="status" for the rest (read politely, in turn). Unknown types render nothing.
--}}
@php
    $flash = session('flash');
    $icons = [
        'success' => 'circle-check',
        'info' => 'info-circle',
        'warning' => 'alert-triangle',
        'error' => 'alert-circle',
    ];
    $type = $flash['type'] ?? null;
@endphp

@if ($type && isset($icons[$type]))
    <div class="app-layout__flash">
        <div class="flash flash--{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}">
            <x-icon name="{{ $icons[$type] }}" class="flash__icon" />
            <p class="flash__message">{{ $flash['message'] ?? '' }}</p>
        </div>
    </div>
@endif
