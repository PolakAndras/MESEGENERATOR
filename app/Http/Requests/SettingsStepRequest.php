<?php

namespace App\Http\Requests;

use App\Support\StoryLimits;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Varázsló 2. lépés (Backend Schema 8.3, PRD US-02). A kontextus hosszát a szerver is ellenőrzi (US-02 AC3).
 * Wizard step 2 (Backend Schema 8.3, PRD US-02). The server checks the context length too (US-02 AC3).
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
            'context' => ['required', 'string', 'max:'.config('story.context_max')],
            'page_count' => ['required', 'integer', 'between:'.$limits['pages_min'].','.$limits['pages_max']],
            'confirm_reduce' => ['nullable', 'boolean'],
        ];
    }
}
