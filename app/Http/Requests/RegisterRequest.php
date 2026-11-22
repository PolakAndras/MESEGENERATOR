<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Regisztráció (Backend Schema 8.1, PRD US-11). Az adatkezelési tájékoztató elfogadása kötelező (AC2).
 * Registration (Backend Schema 8.1, PRD US-11). Accepting the privacy notice is required (AC2).
 */
class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'privacy_accepted' => ['accepted'],
        ];
    }
}
