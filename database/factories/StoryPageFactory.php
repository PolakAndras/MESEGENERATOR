<?php

namespace Database\Factories;

use App\Models\Background;
use App\Models\Story;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoryPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'story_id' => Story::factory(),
            'page_number' => 1,
            'background_id' => Background::factory(),
            'text' => 'Egyszer volt, hol nem volt…',
        ];
    }
}
