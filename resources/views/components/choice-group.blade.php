{{--
    Rádiógomb-csoport kártyaszerű címkékkel (CG 6.3): fieldset + legend, kiválasztva primary-soft háttér.
    options: [érték => felirat]. A hiba ugyanúgy a csoport alatt jelenik meg, mint a form-field-nél.

    Radio group with card-like labels (CG 6.3): fieldset + legend, selected item gets the primary-soft background.
    options: [value => label]. The error shows under the group, like in form-field.
--}}
@props(['name', 'legend', 'options' => [], 'value' => null, 'hint' => null])

@php
    $error = $errors->first($name);
    $current = (string) old($name, $value);
@endphp

<fieldset class="form-field form-field--choices {{ $error ? 'form-field--invalid' : '' }}" id="{{ $name }}"
          @if ($error) aria-describedby="{{ $name }}-error" @endif>
    <legend class="form-field__label">{{ $legend }}</legend>
    @if ($hint)
        <p class="form-field__hint">{{ $hint }}</p>
    @endif
    <div class="form-field__choices">
        @foreach ($options as $optionValue => $optionLabel)
            <label class="form-field__choice">
                <input class="form-field__choice-input" type="radio" name="{{ $name }}" value="{{ $optionValue }}"
                       @if ($current === (string) $optionValue) checked @endif {{ $attributes }}>
                <span class="form-field__choice-label">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>
    @if ($error)
        <p class="form-field__error" id="{{ $name }}-error"><x-icon name="alert-circle" /> {{ $error }}</p>
    @endif
</fieldset>
