<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\StoryPage;
use App\Models\StoryPagePlacement;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoryPagePlacementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'story_page_id' => StoryPage::factory(),
            'character_id' => Character::factory()->animal(),
            'action_id' => null,
            'pos_x' => 20,
            'pos_y' => 90,
            'scale' => 35,
            'z_index' => 1,
        ];
    }

    /**
     * Az action_id a szereplő alaphelyzete, ha nincs megadva (BR-09).
     * action_id defaults to the character's default action when not given (BR-09).
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (StoryPagePlacement $placement) {
            if ($placement->action_id === null) {
                $placement->action_id = Character::find($placement->character_id)->defaultAction->id;
            }
        });
    }
}
