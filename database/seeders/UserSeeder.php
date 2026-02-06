<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuário exemplo do tenant ApiDireta
        User::factory()->create([
            'person_id' => 1, // Alex Alves de Almeida
            'email' => 'alex@apidireta.com',
            'password' => 'Millena2012@',
            'status' => 'active',
        ]);
    }
}
