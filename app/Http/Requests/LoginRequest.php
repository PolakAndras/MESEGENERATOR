<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Belépés (Backend Schema 8.1). / Login (Backend Schema 8.1).
class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
