<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pessoa exemplo do tenant ApiDireta
        Person::factory()->create([
            'tenant_id' => 1, // ApiDireta tenant
            'name' => 'Alexandre Costa',
            'birth_date' => '1990-01-15',
            'whatsapp' => '+55 11 99999-9999',
            'status' => 'active',
        ]);
    }
}
