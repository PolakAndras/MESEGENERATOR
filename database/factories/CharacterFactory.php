<?php

namespace Database\Factories;

use App\Models\Action;
use App\Models\Character;
use Illuminate\Database\Eloquent\Factories\Factory;

class CharacterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->firstName(),
            'type' => Character::TYPE_ANIMAL,
            'gender' => null,
            'hair_color' => null,
        ];
    }

    /**
     * Állat alaphelyzet-cselekvéssel.
     * An animal with its default action.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function animal()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Character::TYPE_ANIMAL,
                'gender' => null,
                'hair_color' => null,
            ];
        })->withDefaultAction();
    }

    /**
     * Gyerekfigura a megadott nemmel és hajszínnel, alaphelyzet-cselekvéssel.
     * A child figure with the given gender and hair colour, with its default action.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function childFigure(string $gender, string $hairColor)
    {
        return $this->state(function (array $attributes) use ($gender, $hairColor) {
            return [
                'type' => Character::TYPE_CHILD_FIGURE,
                'gender' => $gender,
                'hair_color' => $hairColor,
            ];
        })->withDefaultAction();
    }

    // Az alaphelyzet a szereplővel együtt jön létre, mert a valódi alkalmazásban sincs szereplő
    // alaphelyzet nélkül (Backend Schema 7.1); így a tesztek is valósághű adatot kapnak.
    // The default action is created together with the character, because in the real app no
    // character exists without one (Backend Schema 7.1); this keeps test data realistic.
    protected function withDefaultAction()
    {
        return $this->afterCreating(function (Character $character) {
            Action::factory()->default()->create(['character_id' => $character->id]);
        });
    }
}
