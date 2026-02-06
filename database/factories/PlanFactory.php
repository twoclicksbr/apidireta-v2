<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true) . ' Plan';
        $monthlyPrice = fake()->randomFloat(2, 50, 500);
        $annualPrice = $monthlyPrice * 10; // Desconto de ~17% no anual

        return [
            'name' => ucfirst($name),
            'slug' => \Illuminate\Support\Str::slug($name),
            'monthly_price' => $monthlyPrice,
            'annual_price' => $annualPrice,
        ];
    }
}
