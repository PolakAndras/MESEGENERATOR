<?php

namespace App\Http\Requests;

use App\Models\Action;
use App\Models\Character;
use App\Services\StoryDraftService;
use App\Support\PlacementRules;
use App\Support\StoryLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Varázsló 3. lépés: egy oldal mentése, és ugyanez a generálás előtti utolsó oldalé (Backend Schema 8.4).
 * A cselekvés–szereplő összetartozást és a vászonból kilógást is a szerver ellenőrzi (US-06 AC3).
 * Wizard step 3: saving one page, and the same for the last page before generation (Backend Schema 8.4).
 * The server also checks that actions belong to their character and figures stay on the canvas (US-06 AC3).
 */
class PageStepRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $max = StoryLimits::current()['animals_per_page'];

        return [
            // random: az aktuális oldal véletlen kitöltése (PRD US-21). / random: fill the current page randomly.
            'target' => [$this->routeIs('stories.generate') ? 'nullable' : 'required', 'regex:/^(prev|next|child|settings|random|page:\d{1,2})$/'],
            'background_id' => ['nullable', 'integer', 'exists:backgrounds,id'],
            'child_enabled' => ['required', 'boolean'],
            'child.action_id' => ['nullable', 'integer'],
            'child.pos_x' => ['required_if:child_enabled,1', 'nullable', 'numeric'],
            'child.pos_y' => ['required_if:child_enabled,1', 'nullable', 'numeric'],
            'child.flipped' => ['nullable', 'boolean'],
            'animals' => ['nullable', 'array', 'max:'.$max],
            'animals.*.character_id' => ['required', 'integer', 'distinct', Rule::exists('characters', 'id')->where('type', Character::TYPE_ANIMAL)],
            'animals.*.action_id' => ['nullable', 'integer'],
            'animals.*.pos_x' => ['required', 'numeric'],
            'animals.*.pos_y' => ['required', 'numeric'],
            'animals.*.slot' => ['required', 'integer', 'between:1,'.$max, 'distinct'],
            'animals.*.flipped' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $childId = app(StoryDraftService::class)->child()['character_id'] ?? null;
            $figures = [];
            if ($this->boolean('child_enabled')) {
                $figures[] = ['character_id' => $childId] + (array) $this->input('child');
            }
            foreach ((array) $this->input('animals', []) as $animal) {
                $figures[] = $animal;
            }

            foreach ($figures as $figure) {
                $actionId = $figure['action_id'] ?? null;
                if (! empty($actionId) && ! Action::where('id', $actionId)->where('character_id', $figure['character_id'])->exists()) {
                    $validator->errors()->add('placements', __('wizard.pages.invalid_action'));

                    return;
                }
                if (! PlacementRules::isWithin((float) $figure['pos_x'], (float) $figure['pos_y'])) {
                    $validator->errors()->add('placements', __('wizard.pages.out_of_canvas'));

                    return;
                }
            }
        });
    }

    public function attributes()
    {
        return [
            'animals.*.character_id' => __('validation.attributes.animals'),
        ];
    }
}
