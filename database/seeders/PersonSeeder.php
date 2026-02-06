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
            'name' => 'Alex Alves de Almeida',
            'birth_date' => '1985-05-09',
            'whatsapp' => '+55 12 99769-8040',
            'status' => 'active',
        ]);
    }
}
