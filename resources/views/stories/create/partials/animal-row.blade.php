{{--
    Egy állat sora a szerkesztőben (CG 6.10): bélyegkép, név, cselekvésválasztó, eltávolítás; a pozíció rejtett mezőkben.
    Ugyanez a sablon kerül a <template>-be (animal = null, index = __INDEX__), amelyet az editor.js tölt ki,
    így a szerver és a JS ugyanazt a HTML-t állítja elő.
    One animal row in the editor (CG 6.10): thumbnail, name, action select, remove; the position in hidden fields.
    The same partial fills the <template> (animal = null, index = __INDEX__) that editor.js completes, so the server
    and the JS produce identical HTML.
--}}
@php
    $prefix = "animals[{$index}]";
    $defaultAction = $animal ? collect($animal['actions'])->firstWhere('is_default', true) : null;
@endphp
<li class="animal-list__row" data-animal-row data-character-id="{{ $animal['id'] ?? '' }}">
    <img class="animal-list__thumb" src="{{ $defaultAction['url'] ?? '' }}" alt="" width="800" height="800" data-animal-thumb>
    <div class="animal-list__body">
        <label class="animal-list__name" for="animal-action-{{ $index }}" data-animal-name>{{ $animal['name'] ?? '' }}</label>
        <select class="form-field__control animal-list__select" id="animal-action-{{ $index }}" name="{{ $prefix }}[action_id]" data-animal-action>
            @foreach ($animal['actions'] ?? [] as $action)
                <option value="{{ $action['id'] }}" @if (($placement['action_id'] ?? null) === $action['id']) selected @endif>{{ $action['name'] }}</option>
            @endforeach
        </select>
        <label class="flip-toggle">
            <input class="flip-toggle__input" type="checkbox" name="{{ $prefix }}[flipped]" value="1" data-flip data-animal-flip
                   @if ($placement['flipped'] ?? false) checked @endif>
            <span class="flip-toggle__label"><x-icon name="arrows-left-right" /> {{ __('wizard.pages.flip') }}</span>
        </label>
    </div>
    <input type="hidden" name="{{ $prefix }}[character_id]" value="{{ $animal['id'] ?? '' }}" data-animal-id>
    <input type="hidden" name="{{ $prefix }}[pos_x]" value="{{ $placement['pos_x'] ?? '' }}" data-animal-x>
    <input type="hidden" name="{{ $prefix }}[pos_y]" value="{{ $placement['pos_y'] ?? '' }}" data-animal-y>
    <input type="hidden" name="{{ $prefix }}[slot]" value="{{ $placement['slot'] ?? '' }}" data-animal-slot>
    <button type="button" class="button button--ghost button--ghost-danger animal-list__remove js-only" data-animal-remove
            aria-label="{{ __('wizard.pages.remove_label', ['name' => $animal['name'] ?? '']) }}"
            data-label-template="{{ __('wizard.pages.remove_label', ['name' => ':name']) }}">
        <x-icon name="trash" /><span class="animal-list__remove-text">{{ __('wizard.pages.remove') }}</span>
    </button>
</li>
