<?php

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pode listar plans vazio', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/plans');

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

test('pode listar plans com dados', function () {
    Plan::factory()->count(3)->create();

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/plans');

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'total' => 3,
        ])
        ->assertJsonCount(3, 'data');
});

test('pode criar um novo plan', function () {
    $planData = [
        'name' => 'Plano Teste',
        'slug' => 'plano-teste',
        'monthly_price' => 99.90,
        'annual_price' => 999.00,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plans', $planData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'name',
                'slug',
                'monthly_price',
                'annual_price',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'name' => 'Plano Teste',
                'slug' => 'plano-teste',
                'monthly_price' => '99.90',
                'annual_price' => '999.00',
            ],
        ]);

    $this->assertDatabaseHas('plans', [
        'name' => 'Plano Teste',
        'slug' => 'plano-teste',
    ]);
});

test('pode exibir um plan específico', function () {
    $plan = Plan::factory()->create([
        'name' => 'Plano Específico',
        'slug' => 'plano-especifico',
    ]);

    $response = $this->getJson(env('API_DOMAIN') . "/v2/admin/plans/{$plan->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'name',
                'slug',
                'monthly_price',
                'annual_price',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $plan->id,
                'name' => 'Plano Específico',
                'slug' => 'plano-especifico',
            ],
        ]);
});

test('retorna 404 ao buscar plan inexistente', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/plans/9999');

    $response->assertStatus(404);
});

test('pode atualizar um plan com PUT', function () {
    $plan = Plan::factory()->create([
        'name' => 'Nome Original',
        'slug' => 'nome-original',
    ]);

    $updateData = [
        'name' => 'Nome Atualizado',
        'slug' => 'nome-atualizado',
        'monthly_price' => 150.00,
        'annual_price' => 1500.00,
    ];

    $response = $this->putJson(env('API_DOMAIN') . "/v2/admin/plans/{$plan->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $plan->id,
                'name' => 'Nome Atualizado',
                'slug' => 'nome-atualizado',
            ],
        ]);

    $this->assertDatabaseHas('plans', [
        'id' => $plan->id,
        'name' => 'Nome Atualizado',
        'slug' => 'nome-atualizado',
    ]);
});

test('pode atualizar um plan com PATCH', function () {
    $plan = Plan::factory()->create([
        'name' => 'Nome Original',
        'slug' => 'nome-original',
    ]);

    $updateData = [
        'name' => 'Nome Atualizado via PATCH',
    ];

    $response = $this->patchJson(env('API_DOMAIN') . "/v2/admin/plans/{$plan->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $plan->id,
                'name' => 'Nome Atualizado via PATCH',
            ],
        ]);

    $this->assertDatabaseHas('plans', [
        'id' => $plan->id,
        'name' => 'Nome Atualizado via PATCH',
    ]);
});

test('pode deletar um plan (soft delete)', function () {
    $plan = Plan::factory()->create();

    $response = $this->deleteJson(env('API_DOMAIN') . "/v2/admin/plans/{$plan->id}");

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'message' => 'Recurso excluído com sucesso.',
        ]);

    $this->assertSoftDeleted('plans', [
        'id' => $plan->id,
    ]);
});

test('pode restaurar um plan deletado', function () {
    $plan = Plan::factory()->create();
    $plan->delete();

    $this->assertSoftDeleted('plans', [
        'id' => $plan->id,
    ]);

    $response = $this->patchJson(env('API_DOMAIN') . "/v2/admin/plans/{$plan->id}/restore");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data',
        ]);

    $this->assertDatabaseHas('plans', [
        'id' => $plan->id,
        'deleted_at' => null,
    ]);
});

test('paginação funciona corretamente', function () {
    Plan::factory()->count(20)->create();

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/plans?per_page=5');

    $response->assertStatus(200)
        ->assertJson([
            'per_page' => 5,
            'total' => 20,
            'last_page' => 4,
        ])
        ->assertJsonCount(5, 'data');
});

test('validação impede criação de plan sem name', function () {
    $planData = [
        'slug' => 'sem-nome',
        'monthly_price' => 100.00,
        'annual_price' => 1000.00,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plans', $planData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('validação impede criação de plan sem slug', function () {
    $planData = [
        'name' => 'Sem Slug',
        'monthly_price' => 100.00,
        'annual_price' => 1000.00,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plans', $planData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});

test('validação impede criação de plan com name duplicado', function () {
    Plan::factory()->create([
        'name' => 'Nome Duplicado',
        'slug' => 'nome-duplicado',
    ]);

    $planData = [
        'name' => 'Nome Duplicado',
        'slug' => 'outro-slug',
        'monthly_price' => 100.00,
        'annual_price' => 1000.00,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plans', $planData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('validação impede criação de plan com slug duplicado', function () {
    Plan::factory()->create([
        'name' => 'Primeiro Nome',
        'slug' => 'slug-duplicado',
    ]);

    $planData = [
        'name' => 'Segundo Nome',
        'slug' => 'slug-duplicado',
        'monthly_price' => 100.00,
        'annual_price' => 1000.00,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plans', $planData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});

test('validação impede criação de plan sem monthly_price', function () {
    $planData = [
        'name' => 'Sem Preço Mensal',
        'slug' => 'sem-preco-mensal',
        'annual_price' => 1000.00,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plans', $planData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['monthly_price']);
});

test('validação impede criação de plan sem annual_price', function () {
    $planData = [
        'name' => 'Sem Preço Anual',
        'slug' => 'sem-preco-anual',
        'monthly_price' => 100.00,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plans', $planData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['annual_price']);
});

test('validação impede criação de plan com monthly_price negativo', function () {
    $planData = [
        'name' => 'Preço Negativo',
        'slug' => 'preco-negativo',
        'monthly_price' => -100.00,
        'annual_price' => 1000.00,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plans', $planData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['monthly_price']);
});

test('validação impede criação de plan com annual_price negativo', function () {
    $planData = [
        'name' => 'Preço Anual Negativo',
        'slug' => 'preco-anual-negativo',
        'monthly_price' => 100.00,
        'annual_price' => -1000.00,
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/plans', $planData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['annual_price']);
});
