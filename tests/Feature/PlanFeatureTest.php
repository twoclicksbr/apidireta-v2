<?php

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pode listar plan_features vazio', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/plan_features');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'current_page',
            'data',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total',
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'total' => 0,
        ]);
});

test('pode listar plan_features com dados', function () {
    $plan = Plan::factory()->create();
    PlanFeature::factory()->count(3)->create(['id_plan' => $plan->id]);

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/plan_features');

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'total' => 3,
        ])
        ->assertJsonCount(3, 'data');
});

test('pode criar um novo plan_feature', function () {
    $plan = Plan::factory()->create();

    $featureData = [
        'id_plan' => $plan->id,
        'name' => 'Feature Teste',
        'description' => 'Descrição teste',
        'value' => '100',
        'order' => 1,
        'active' => true,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plan_features', $featureData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'id_plan',
                'name',
                'description',
                'value',
                'order',
                'active',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id_plan' => $plan->id,
                'name' => 'Feature Teste',
                'description' => 'Descrição teste',
                'value' => '100',
            ],
        ]);

    $this->assertDatabaseHas('plan_features', [
        'name' => 'Feature Teste',
        'id_plan' => $plan->id,
    ]);
});

test('pode exibir um plan_feature específico', function () {
    $plan = Plan::factory()->create();
    $feature = PlanFeature::factory()->create([
        'id_plan' => $plan->id,
        'name' => 'Feature Específica',
    ]);

    $response = $this->getJson(env('API_DOMAIN') . "/v2/admin/plan_features/{$feature->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'id_plan',
                'name',
                'description',
                'value',
                'order',
                'active',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $feature->id,
                'name' => 'Feature Específica',
            ],
        ]);
});

test('retorna 404 ao buscar plan_feature inexistente', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/plan_features/9999');

    $response->assertStatus(404);
});

test('pode atualizar um plan_feature com PUT', function () {
    $plan = Plan::factory()->create();
    $feature = PlanFeature::factory()->create([
        'id_plan' => $plan->id,
        'name' => 'Nome Original',
    ]);

    $updateData = [
        'id_plan' => $plan->id,
        'name' => 'Nome Atualizado',
        'value' => '200',
        'order' => 5,
        'active' => false,
    ];

    $response = $this->putJson(env('API_DOMAIN') . "/v2/admin/plan_features/{$feature->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $feature->id,
                'name' => 'Nome Atualizado',
                'value' => '200',
            ],
        ]);

    $this->assertDatabaseHas('plan_features', [
        'id' => $feature->id,
        'name' => 'Nome Atualizado',
        'value' => '200',
    ]);
});

test('pode atualizar um plan_feature com PATCH', function () {
    $plan = Plan::factory()->create();
    $feature = PlanFeature::factory()->create([
        'id_plan' => $plan->id,
        'name' => 'Nome Original',
    ]);

    $updateData = [
        'name' => 'Nome Atualizado via PATCH',
    ];

    $response = $this->patchJson(env('API_DOMAIN') . "/v2/admin/plan_features/{$feature->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $feature->id,
                'name' => 'Nome Atualizado via PATCH',
            ],
        ]);

    $this->assertDatabaseHas('plan_features', [
        'id' => $feature->id,
        'name' => 'Nome Atualizado via PATCH',
    ]);
});

test('pode deletar um plan_feature (soft delete)', function () {
    $plan = Plan::factory()->create();
    $feature = PlanFeature::factory()->create(['id_plan' => $plan->id]);

    $response = $this->deleteJson(env('API_DOMAIN') . "/v2/admin/plan_features/{$feature->id}");

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'message' => 'Recurso excluído com sucesso.',
        ]);

    $this->assertSoftDeleted('plan_features', [
        'id' => $feature->id,
    ]);
});

test('pode restaurar um plan_feature deletado', function () {
    $plan = Plan::factory()->create();
    $feature = PlanFeature::factory()->create(['id_plan' => $plan->id]);
    $feature->delete();

    $this->assertSoftDeleted('plan_features', [
        'id' => $feature->id,
    ]);

    $response = $this->patchJson(env('API_DOMAIN') . "/v2/admin/plan_features/{$feature->id}/restore");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data',
        ]);

    $this->assertDatabaseHas('plan_features', [
        'id' => $feature->id,
        'deleted_at' => null,
    ]);
});

test('paginação funciona corretamente', function () {
    $plan = Plan::factory()->create();
    PlanFeature::factory()->count(20)->create(['id_plan' => $plan->id]);

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/plan_features?per_page=5');

    $response->assertStatus(200)
        ->assertJson([
            'per_page' => 5,
            'total' => 20,
            'last_page' => 4,
        ])
        ->assertJsonCount(5, 'data');
});

test('validação impede criação de plan_feature sem id_plan', function () {
    $featureData = [
        'name' => 'Sem Plan',
        'value' => '100',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plan_features', $featureData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['id_plan']);
});

test('validação impede criação de plan_feature com id_plan inexistente', function () {
    $featureData = [
        'id_plan' => 9999,
        'name' => 'Feature Teste',
        'value' => '100',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plan_features', $featureData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['id_plan']);
});

test('validação impede criação de plan_feature sem name', function () {
    $plan = Plan::factory()->create();

    $featureData = [
        'id_plan' => $plan->id,
        'value' => '100',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plan_features', $featureData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('validação impede criação de plan_feature sem value', function () {
    $plan = Plan::factory()->create();

    $featureData = [
        'id_plan' => $plan->id,
        'name' => 'Feature Sem Valor',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plan_features', $featureData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value']);
});

test('relacionamento com plan funciona', function () {
    $plan = Plan::factory()->create(['name' => 'Plano Teste']);
    $feature = PlanFeature::factory()->create([
        'id_plan' => $plan->id,
        'name' => 'Feature do Plano',
    ]);

    expect($feature->plan)->not->toBeNull()
        ->and($feature->plan->name)->toBe('Plano Teste')
        ->and($feature->plan->id)->toBe($plan->id);
});

test('plan pode ter múltiplas features', function () {
    $plan = Plan::factory()->create();
    PlanFeature::factory()->count(5)->create(['id_plan' => $plan->id]);

    expect($plan->features)->toHaveCount(5);
});
