<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pode listar tenants vazio', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/tenants');

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

test('pode listar tenants com dados', function () {
    Tenant::factory()->count(3)->create();

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/tenants');

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'total' => 3,
        ])
        ->assertJsonCount(3, 'data');
});

test('pode criar um novo tenant', function () {
    $tenantData = [
        'name' => 'Empresa Teste',
        'slug' => 'empresa-teste',
        'expires_at' => now()->addYear()->format('Y-m-d H:i:s'),
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/tenants', $tenantData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'name',
                'slug',
                'expires_at',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'name' => 'Empresa Teste',
                'slug' => 'empresa-teste',
            ],
        ]);

    $this->assertDatabaseHas('tenants', [
        'name' => 'Empresa Teste',
        'slug' => 'empresa-teste',
    ]);
});

test('pode exibir um tenant específico', function () {
    $tenant = Tenant::factory()->create([
        'name' => 'Tenant Específico',
        'slug' => 'tenant-especifico',
    ]);

    $response = $this->getJson(env('API_DOMAIN') . "/v2/admin/tenants/{$tenant->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'name',
                'slug',
                'expires_at',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $tenant->id,
                'name' => 'Tenant Específico',
                'slug' => 'tenant-especifico',
            ],
        ]);
});

test('retorna 404 ao buscar tenant inexistente', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/tenants/9999');

    $response->assertStatus(404);
});

test('pode atualizar um tenant com PUT', function () {
    $tenant = Tenant::factory()->create([
        'name' => 'Nome Original',
        'slug' => 'nome-original',
    ]);

    $updateData = [
        'name' => 'Nome Atualizado',
        'slug' => 'nome-atualizado',
    ];

    $response = $this->putJson(env('API_DOMAIN') . "/v2/admin/tenants/{$tenant->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $tenant->id,
                'name' => 'Nome Atualizado',
                'slug' => 'nome-atualizado',
            ],
        ]);

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'name' => 'Nome Atualizado',
        'slug' => 'nome-atualizado',
    ]);
});

test('pode atualizar um tenant com PATCH', function () {
    $tenant = Tenant::factory()->create([
        'name' => 'Nome Original',
        'slug' => 'nome-original',
    ]);

    $updateData = [
        'name' => 'Nome Atualizado via PATCH',
    ];

    $response = $this->patchJson(env('API_DOMAIN') . "/v2/admin/tenants/{$tenant->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $tenant->id,
                'name' => 'Nome Atualizado via PATCH',
            ],
        ]);

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'name' => 'Nome Atualizado via PATCH',
    ]);
});

test('pode deletar um tenant (soft delete)', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->deleteJson(env('API_DOMAIN') . "/v2/admin/tenants/{$tenant->id}");

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'message' => 'Recurso excluído com sucesso.',
        ]);

    $this->assertSoftDeleted('tenants', [
        'id' => $tenant->id,
    ]);
});

test('pode restaurar um tenant deletado', function () {
    $tenant = Tenant::factory()->create();
    $tenant->delete();

    $this->assertSoftDeleted('tenants', [
        'id' => $tenant->id,
    ]);

    $response = $this->patchJson(env('API_DOMAIN') . "/v2/admin/tenants/{$tenant->id}/restore");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data',
        ]);

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'deleted_at' => null,
    ]);
});

test('paginação funciona corretamente', function () {
    Tenant::factory()->count(20)->create();

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/tenants?per_page=5');

    $response->assertStatus(200)
        ->assertJson([
            'per_page' => 5,
            'total' => 20,
            'last_page' => 4,
        ])
        ->assertJsonCount(5, 'data');
});

test('validação impede criação de tenant sem name', function () {
    $tenantData = [
        'slug' => 'sem-nome',
        'expires_at' => now()->addYear()->format('Y-m-d H:i:s'),
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/tenants', $tenantData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('validação impede criação de tenant sem slug', function () {
    $tenantData = [
        'name' => 'Sem Slug',
        'expires_at' => now()->addYear()->format('Y-m-d H:i:s'),
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/tenants', $tenantData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});

test('validação impede criação de tenant com name duplicado', function () {
    Tenant::factory()->create([
        'name' => 'Nome Duplicado',
        'slug' => 'nome-duplicado',
    ]);

    $tenantData = [
        'name' => 'Nome Duplicado',
        'slug' => 'outro-slug',
        'expires_at' => now()->addYear()->format('Y-m-d H:i:s'),
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/tenants', $tenantData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('validação impede criação de tenant com slug duplicado', function () {
    Tenant::factory()->create([
        'name' => 'Primeiro Nome',
        'slug' => 'slug-duplicado',
    ]);

    $tenantData = [
        'name' => 'Segundo Nome',
        'slug' => 'slug-duplicado',
        'expires_at' => now()->addYear()->format('Y-m-d H:i:s'),
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/tenants', $tenantData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});

test('validação impede criação de tenant sem expires_at', function () {
    $tenantData = [
        'name' => 'Sem Expiração',
        'slug' => 'sem-expiracao',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/tenants', $tenantData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['expires_at']);
});

test('validação impede criação de tenant com expires_at no passado', function () {
    $tenantData = [
        'name' => 'Data Passada',
        'slug' => 'data-passada',
        'expires_at' => now()->subDay()->format('Y-m-d H:i:s'),
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/tenants', $tenantData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['expires_at']);
});
