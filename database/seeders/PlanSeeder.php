<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Planos do sistema
        $plans = [
            [
                'name' => 'Go',
                'slug' => 'go',
                'monthly_price' => 97.00,
                'annual_price' => 970.00,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'monthly_price' => 197.00,
                'annual_price' => 1970.00,
            ],
            [
                'name' => 'Max',
                'slug' => 'max',
                'monthly_price' => 497.00,
                'annual_price' => 4970.00,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::factory()->create($plan);
        }
    }
}
