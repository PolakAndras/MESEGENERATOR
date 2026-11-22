{{--
    Hibaösszesítő az űrlap tetején (CG 6.3): role="alert", minden hibás mezőre link. A belépési hiba is ide kerül
    (CG 9.3), mert ott nincs mezőszintű jelölés.
    Error summary at the top of a form (CG 6.3): role="alert", a link to every invalid field. Login errors go here
    too (CG 9.3), since they have no field-level marking.
--}}
@if ($errors->any())
    <div class="flash flash--error error-summary" role="alert">
        <x-icon name="alert-circle" />
        <div>
            <p class="flash__message">{{ __('errors.summary') }}</p>
            <ul class="error-summary__list">
                @foreach ($errors->keys() as $key)
                    @php($anchor = str_replace(['.', '[', ']'], ['-', '-', ''], $key))
                    <li><a class="error-summary__link" href="#{{ $anchor }}">{{ $errors->first($key) }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
