<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Cselekvés létrehozása (kép kötelező) és szerkesztése (kép opcionális) (Backend Schema 8.6, PRD US-17 AC3).
 * A cselekvésszám-korlátot (BR-05, BR-06) a kontroller ellenőrzi, mert túllépéskor S-22-re kell visszavinni.
 * Creating an action (image required) and editing one (image optional) (Backend Schema 8.6, PRD US-17 AC3).
 * The action count limit (BR-05, BR-06) is checked by the controller, because exceeding it returns to S-22.
 */
class ActionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'image' => [$this->route('action') ? 'nullable' : 'required', 'file', 'mimes:svg,png', 'max:2048'],
        ];
    }
}
