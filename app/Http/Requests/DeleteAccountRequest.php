<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Fióktörlés jelszóval megerősítve (Backend Schema 8.1, PRD US-13 AC1). / Account deletion confirmed by password.
class DeleteAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}
