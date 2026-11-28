{{--
    Űrlapmező (CG 6.3): látható címke a mező fölött, súgó, mező, hiba a mező alatt ikonnal.
    A hibát és a régi értéket maga olvassa ($errors, old()), így a nézetekben nem kell ismételni.
    type: text | email | password | number | file | textarea.

    Form field (CG 6.3): visible label above the field, hint, control, error below it with an icon.
    It reads the error and old value itself ($errors, old()), so views do not repeat that logic.
    type: text | email | password | number | file | textarea.
--}}
@props(['name', 'label', 'hint' => null, 'type' => 'text', 'value' => null])

@php
    $id = str_replace(['.', '[', ']'], ['-', '-', ''], $name);
    $error = $errors->first($name);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $current = $type === 'password' ? null : old($name, $value);
@endphp

<div class="form-field {{ $error ? 'form-field--invalid' : '' }}">
    <label class="form-field__label" for="{{ $id }}">{{ $label }}</label>
    @if ($hint)
        <p class="form-field__hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif

    @if ($type === 'textarea')
        <textarea class="form-field__control form-field__control--textarea" id="{{ $id }}" name="{{ $name }}"
                  @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                  @if ($error) aria-invalid="true" @endif
                  {{ $attributes }}>{{ $current }}</textarea>
    @else
        <input class="form-field__control" type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $current }}"
               @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
               @if ($error) aria-invalid="true" @endif
               {{ $attributes }}>
    @endif

    @if ($error)
        <p class="form-field__error" id="{{ $id }}-error"><x-icon name="alert-circle" /> {{ $error }}</p>
    @endif
</div>
