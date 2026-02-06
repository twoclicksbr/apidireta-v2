<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tenant único do sistema
        Tenant::factory()->create([
            'name' => 'ApiDireta',
            'slug' => 'apidireta',
            'expires_at' => now()->addDays(90),
        ]);
    }
}
