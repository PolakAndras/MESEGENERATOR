{{--
    Gomb (CG 6.2): <x-button variant="primary|secondary|danger|ghost">.
    Ha van href, <a> lesz belőle (navigáció), különben <button> (űrlapküldés) – így mindig a helyes elem
    kerül az oldalba, de a kinézet ugyanaz.

    Button (CG 6.2): <x-button variant="primary|secondary|danger|ghost">.
    With href it renders an <a> (navigation), otherwise a <button> (form submit) – the right element
    every time, with the same look.
--}}
@props(['variant' => 'primary', 'href' => null, 'type' => 'submit'])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'button button--' . $variant]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'button button--' . $variant]) }}>{{ $slot }}</button>
@endif
