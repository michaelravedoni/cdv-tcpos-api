<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attribute>
 */
class AttributeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            '_tcposId' => $this->faker->unique()->randomNumber(5),
            '_tcposCode' => $this->faker->unique()->word(),
            'notes1' => $this->faker->sentence(),
            'notes2' => $this->faker->sentence(),
            'notes3' => $this->faker->sentence(),
            'notes' => [$this->faker->word(), $this->faker->word()],
        ];
    }
}
