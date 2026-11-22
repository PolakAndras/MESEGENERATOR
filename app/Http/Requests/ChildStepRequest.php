<?php

namespace App\Http\Requests;

use App\Models\Character;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Varázsló 1. lépés (Backend Schema 8.2, PRD US-01). A korlátok a config/story.php-ból jönnek.
 * Wizard step 1 (Backend Schema 8.2, PRD US-01). Limits come from config/story.php.
 */
class ChildStepRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'child_name' => ['required', 'string', 'max:'.config('story.child_name_max')],
            'child_age' => ['required', 'integer', 'between:'.config('story.child_age_min').','.config('story.child_age_max')],
            'child_gender' => ['required', Rule::in(Character::GENDERS)],
            'child_hair_color' => ['required', Rule::in(Character::HAIR_COLORS)],
        ];
    }

    // Utólag: létezik-e gyerekfigura a nem + hajszín párhoz (Backend Schema 7.2); a hiba a nem mezőnél jelenik meg.
    // Afterwards: does a child figure exist for the gender + hair pair (Backend Schema 7.2); the error shows at gender.
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if ($this->figure() === null) {
                $validator->errors()->add('child_gender', __('wizard.child.no_figure'));
            }
        });
    }

    public function figure(): ?Character
    {
        return Character::where('type', Character::TYPE_CHILD_FIGURE)
            ->where('gender', $this->input('child_gender'))
            ->where('hair_color', $this->input('child_hair_color'))
            ->whereHas('defaultAction')
            ->first();
    }
}
