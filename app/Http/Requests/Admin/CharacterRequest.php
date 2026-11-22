<?php

namespace App\Http\Requests\Admin;

use App\Models\Character;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Szereplő létrehozása és szerkesztése (Backend Schema 8.6). Egy osztály szolgálja ki mindkettőt: létrehozáskor
 * az alaphelyzet neve és képe is kötelező (App Flow D-16), szerkesztéskor ezek a mezők nincsenek.
 * Creating and editing a character (Backend Schema 8.6). One class serves both: on create the default action's
 * name and image are required too (App Flow D-16); on edit those fields do not exist.
 */
class CharacterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:50'],
            'type' => ['required', Rule::in([Character::TYPE_ANIMAL, Character::TYPE_CHILD_FIGURE])],
            'gender' => ['required_if:type,'.Character::TYPE_CHILD_FIGURE, 'nullable', Rule::in(Character::GENDERS)],
            'hair_color' => ['required_if:type,'.Character::TYPE_CHILD_FIGURE, 'nullable', Rule::in(Character::HAIR_COLORS)],
        ];

        if ($this->isCreate()) {
            $rules['default_action_name'] = ['required', 'string', 'max:50'];
            $rules['default_action_image'] = ['required', 'file', 'mimes:svg,png', 'max:2048'];
        }

        return $rules;
    }

    // A gyerekfigurák között a nem + hajszín pár egyedi (App Flow D-17); a hiba a nem mezőnél jelenik meg.
    // Among child figures the gender + hair pair is unique (App Flow D-17); the error shows at the gender field.
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('type') !== Character::TYPE_CHILD_FIGURE || $validator->errors()->has('gender')) {
                return;
            }
            $taken = Character::where('type', Character::TYPE_CHILD_FIGURE)
                ->where('gender', $this->input('gender'))
                ->where('hair_color', $this->input('hair_color'))
                ->when($this->route('character'), fn ($q, $character) => $q->whereKeyNot($character->id))
                ->exists();
            if ($taken) {
                $validator->errors()->add('gender', __('admin.characters.figure_taken'));
            }
        });
    }

    public function attributes()
    {
        return ['default_action_image' => __('validation.attributes.default_action_image')];
    }

    private function isCreate(): bool
    {
        return $this->route('character') === null;
    }
}
