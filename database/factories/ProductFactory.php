<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
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
            'category' => 'wine',
            'minQuantity' => 1,
            'maxQuantity' => 100,
            'weight' => 0.75,
            'vatInPercent' => 7.7,
            'sync_action' => 'none',
            'hash' => md5($this->faker->word()),
            'description' => $this->faker->paragraph(),
            '_tcposId' => $this->faker->unique()->randomNumber(5),
            '_tcposCode' => $this->faker->unique()->word(),
        ];
    }
}
