<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanFeature>
 */
class PlanFeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_plan' => \App\Models\Plan::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'value' => fake()->randomElement(['true', 'false', fake()->numberBetween(1, 100), 'Ilimitado']),
            'order' => fake()->numberBetween(1, 10),
            'active' => fake()->boolean(90),
        ];
    }
}
