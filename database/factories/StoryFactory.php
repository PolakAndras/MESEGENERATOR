<?php

namespace Database\Factories;

use App\Models\Background;
use App\Models\Character;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => 'Panni és a holdfényes erdő',
            'child_name' => 'Panni',
            'child_age' => 5,
            'child_gender' => 'girl',
            'child_hair_color' => 'red',
            'context' => 'barátság',
            'page_count' => 8,
        ];
    }

    /**
     * Mese oldalakkal és oldalanként egy állattal, hogy az olvasó-tesztek teljes mesét kapjanak (Backend Schema 11.4).
     * A story with pages and one animal per page, so reader tests get a complete story (Backend Schema 11.4).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withPages()
    {
        return $this->afterCreating(function (Story $story) {
            $background = Background::factory()->create();
            $animal = Character::factory()->animal()->create();

            for ($n = 1; $n <= $story->page_count; $n++) {
                $page = $story->pages()->create([
                    'page_number' => $n,
                    'background_id' => $background->id,
                    'text' => "A(z) {$n}. oldal szövege.",
                ]);
                $page->placements()->create([
                    'character_id' => $animal->id,
                    'action_id' => $animal->defaultAction->id,
                    'pos_x' => 20,
                    'pos_y' => 90,
                    'scale' => 35,
                    'z_index' => 1,
                ]);
            }
        });
    }
}
