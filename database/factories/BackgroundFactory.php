<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BackgroundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Erdő', 'Tengerpart', 'Rét', 'Hálószoba', 'Hegyek']),
            // Fix minta-útvonal, mint az ActionFactoryban (Backend Schema 11.4).
            // Fixed sample path, as in ActionFactory (Backend Schema 11.4).
            'image_path' => 'backgrounds/test.svg',
        ];
    }
}
