<?php

namespace Database\Seeders;

use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlanFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Features do sistema para cada plano
        $features = [
            // Plano Go (id_plan = 1)
            ['id_plan' => 1, 'name' => 'Número de Projetos', 'description' => null, 'value' => '1', 'order' => 1, 'active' => true],
            ['id_plan' => 1, 'name' => 'Módulos por Projeto', 'description' => null, 'value' => '10', 'order' => 2, 'active' => true],
            ['id_plan' => 1, 'name' => 'Usuários por conta', 'description' => null, 'value' => '1', 'order' => 3, 'active' => true],
            ['id_plan' => 1, 'name' => 'Armazenamento', 'description' => 'banco + arquivos', 'value' => '100 Mb', 'order' => 4, 'active' => true],
            ['id_plan' => 1, 'name' => 'Domínios personalizados', 'description' => 'White label', 'value' => 'false', 'order' => 5, 'active' => true],
            ['id_plan' => 1, 'name' => 'Suporte técnico', 'description' => 'Seg–Sex 9h às 17h', 'value' => 'false', 'order' => 6, 'active' => true],
            ['id_plan' => 1, 'name' => 'Logs e monitoramento', 'description' => null, 'value' => 'false', 'order' => 7, 'active' => true],
            ['id_plan' => 1, 'name' => 'Backups automáticos', 'description' => null, 'value' => 'false', 'order' => 8, 'active' => true],

            // Plano Pro (id_plan = 2)
            ['id_plan' => 2, 'name' => 'Número de Projetos', 'description' => null, 'value' => '5', 'order' => 1, 'active' => true],
            ['id_plan' => 2, 'name' => 'Módulos por Projeto', 'description' => null, 'value' => '20', 'order' => 2, 'active' => true],
            ['id_plan' => 2, 'name' => 'Usuários por conta', 'description' => null, 'value' => '3', 'order' => 3, 'active' => true],
            ['id_plan' => 2, 'name' => 'Armazenamento', 'description' => 'banco + arquivos', 'value' => '500 Mb', 'order' => 4, 'active' => true],
            ['id_plan' => 2, 'name' => 'Domínios personalizados', 'description' => 'White label', 'value' => 'true', 'order' => 5, 'active' => true],
            ['id_plan' => 2, 'name' => 'Suporte técnico', 'description' => 'Seg–Sex 9h às 17h', 'value' => 'true', 'order' => 6, 'active' => true],
            ['id_plan' => 2, 'name' => 'Logs e monitoramento', 'description' => null, 'value' => 'false', 'order' => 7, 'active' => true],
            ['id_plan' => 2, 'name' => 'Backups automáticos', 'description' => null, 'value' => 'false', 'order' => 8, 'active' => true],

            // Plano Max (id_plan = 3)
            ['id_plan' => 3, 'name' => 'Número de Projetos', 'description' => null, 'value' => 'Ilimitado', 'order' => 1, 'active' => true],
            ['id_plan' => 3, 'name' => 'Módulos por Projeto', 'description' => null, 'value' => 'Ilimitado', 'order' => 2, 'active' => true],
            ['id_plan' => 3, 'name' => 'Usuários por conta', 'description' => null, 'value' => 'Ilimitado', 'order' => 3, 'active' => true],
            ['id_plan' => 3, 'name' => 'Armazenamento', 'description' => 'banco + arquivos', 'value' => '1 Gb', 'order' => 4, 'active' => true],
            ['id_plan' => 3, 'name' => 'Domínios personalizados', 'description' => 'White label', 'value' => 'true', 'order' => 5, 'active' => true],
            ['id_plan' => 3, 'name' => 'Suporte técnico', 'description' => 'Seg–Sex 9h às 17h', 'value' => 'true', 'order' => 6, 'active' => true],
            ['id_plan' => 3, 'name' => 'Logs e monitoramento', 'description' => null, 'value' => 'true', 'order' => 7, 'active' => true],
            ['id_plan' => 3, 'name' => 'Backups automáticos', 'description' => null, 'value' => 'true', 'order' => 8, 'active' => true],
        ];

        foreach ($features as $feature) {
            PlanFeature::factory()->create($feature);
        }
    }
}
