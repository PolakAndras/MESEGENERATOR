<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Háttér létrehozása (kép kötelező) és szerkesztése (kép opcionális) (Backend Schema 8.6, PRD US-18 AC2).
 * Creating a background (image required) and editing one (image optional) (Backend Schema 8.6, PRD US-18 AC2).
 */
class BackgroundRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'image' => [$this->route('background') ? 'nullable' : 'required', 'file', 'mimes:png,jpg,jpeg,svg', 'max:5120'],
        ];
    }
}
