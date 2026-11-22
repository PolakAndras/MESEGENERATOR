<?php

namespace Database\Factories;

use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // Alap szereplő (alaphelyzet nélkül), hogy egy önálló cselekvés ne hozzon létre második alaphelyzetet.
            // A plain character (without a default action), so a standalone action does not create a second default.
            'character_id' => Character::factory(),
            'name' => $this->faker->randomElement(['ül', 'fut', 'integet', 'alszik', 'ugrik']),
            // Fix minta-útvonal: a tesztek Storage::fake('images')-t használnak, valódi fájl nem kell (Backend Schema 11.4).
            // Fixed sample path: tests use Storage::fake('images'), no real file is needed (Backend Schema 11.4).
            'image_path' => 'actions/test.svg',
            'is_default' => false,
        ];
    }

    /**
     * Alaphelyzet-cselekvés.
     * A default action.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function default()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'áll',
                'is_default' => true,
            ];
        });
    }
}
