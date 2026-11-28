<?php

namespace App\Http\Requests;

use App\Models\StoryTheme;
use App\Support\StoryLimits;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Varázsló 2. lépés (PRD 1.6 US-02, US-24): a mese témája a 15 közül és az oldalszám.
 * A „Meglepetés mese” (stories.surprise) téma nélkül is indulhat: ilyenkor a rendszer választ témát.
 * Wizard step 2 (PRD 1.6 US-02, US-24): the story theme out of 15 and the page count.
 * "Surprise story" (stories.surprise) may start without a theme: the system picks one then.
 */
class SettingsStepRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $limits = StoryLimits::current();

        return [
            'theme_id' => [$this->routeIs('stories.surprise') ? 'nullable' : 'required', 'integer', 'exists:story_themes,id'],
            'page_count' => ['required', 'integer', 'between:'.$limits['pages_min'].','.$limits['pages_max']],
            'confirm_reduce' => ['nullable', 'boolean'],
        ];
    }

    public function theme(): StoryTheme
    {
        return StoryTheme::find($this->input('theme_id')) ?? StoryTheme::inRandomOrder()->firstOrFail();
    }
}
