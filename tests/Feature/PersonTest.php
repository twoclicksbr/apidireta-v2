<?php

use App\Models\Person;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pode listar persons vazio', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/persons');

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

test('pode listar persons com dados', function () {
    $tenant = Tenant::factory()->create();
    Person::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/persons');

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'total' => 3,
        ])
        ->assertJsonCount(3, 'data');
});

test('pode criar um novo person', function () {
    $tenant = Tenant::factory()->create();

    $personData = [
        'tenant_id' => $tenant->id,
        'name' => 'João Silva',
        'birth_date' => '1990-05-15',
        'whatsapp' => '+55 11 98765-4321',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/persons', $personData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'tenant_id',
                'name',
                'birth_date',
                'whatsapp',
                'status',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'name' => 'João Silva',
                'whatsapp' => '+55 11 98765-4321',
            ],
        ]);

    $this->assertDatabaseHas('persons', [
        'name' => 'João Silva',
        'tenant_id' => $tenant->id,
    ]);
});

test('pode exibir um person específico', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Maria Santos',
    ]);

    $response = $this->getJson(env('API_DOMAIN') . "/v2/admin/persons/{$person->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'tenant_id',
                'name',
                'birth_date',
                'whatsapp',
                'status',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $person->id,
                'name' => 'Maria Santos',
            ],
        ]);
});

test('retorna 404 ao buscar person inexistente', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/persons/9999');

    $response->assertStatus(404);
});

test('pode atualizar um person com PUT', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Nome Original',
    ]);

    $updateData = [
        'tenant_id' => $tenant->id,
        'name' => 'Nome Atualizado',
        'birth_date' => '1985-10-20',
        'whatsapp' => '+55 21 91111-2222',
        'status' => 'inactive',
    ];

    $response = $this->putJson(env('API_DOMAIN') . "/v2/admin/persons/{$person->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $person->id,
                'name' => 'Nome Atualizado',
            ],
        ]);

    $this->assertDatabaseHas('persons', [
        'id' => $person->id,
        'name' => 'Nome Atualizado',
    ]);
});

test('pode atualizar um person com PATCH', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Nome Original',
    ]);

    $updateData = [
        'name' => 'Nome Atualizado via PATCH',
    ];

    $response = $this->patchJson(env('API_DOMAIN') . "/v2/admin/persons/{$person->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $person->id,
                'name' => 'Nome Atualizado via PATCH',
            ],
        ]);

    $this->assertDatabaseHas('persons', [
        'id' => $person->id,
        'name' => 'Nome Atualizado via PATCH',
    ]);
});

test('pode deletar um person (soft delete)', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->deleteJson(env('API_DOMAIN') . "/v2/admin/persons/{$person->id}");

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'message' => 'Recurso excluído com sucesso.',
        ]);

    $this->assertSoftDeleted('persons', [
        'id' => $person->id,
    ]);
});

test('pode restaurar um person deletado', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    $person->delete();

    $this->assertSoftDeleted('persons', [
        'id' => $person->id,
    ]);

    $response = $this->patchJson(env('API_DOMAIN') . "/v2/admin/persons/{$person->id}/restore");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data',
        ]);

    $this->assertDatabaseHas('persons', [
        'id' => $person->id,
        'deleted_at' => null,
    ]);
});

test('paginação funciona corretamente', function () {
    $tenant = Tenant::factory()->create();
    Person::factory()->count(20)->create(['tenant_id' => $tenant->id]);

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/persons?per_page=5');

    $response->assertStatus(200)
        ->assertJson([
            'per_page' => 5,
            'total' => 20,
            'last_page' => 4,
        ])
        ->assertJsonCount(5, 'data');
});

test('validação impede criação de person sem tenant_id', function () {
    $personData = [
        'name' => 'Sem Tenant',
        'birth_date' => '1990-01-01',
        'whatsapp' => '+55 11 99999-9999',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/persons', $personData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tenant_id']);
});

test('validação impede criação de person com tenant_id inexistente', function () {
    $personData = [
        'tenant_id' => 9999,
        'name' => 'Pessoa Teste',
        'birth_date' => '1990-01-01',
        'whatsapp' => '+55 11 99999-9999',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/persons', $personData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tenant_id']);
});

test('validação impede criação de person sem name', function () {
    $tenant = Tenant::factory()->create();

    $personData = [
        'tenant_id' => $tenant->id,
        'birth_date' => '1990-01-01',
        'whatsapp' => '+55 11 99999-9999',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/persons', $personData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('validação impede criação de person sem birth_date', function () {
    $tenant = Tenant::factory()->create();

    $personData = [
        'tenant_id' => $tenant->id,
        'name' => 'Pessoa Teste',
        'whatsapp' => '+55 11 99999-9999',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/persons', $personData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['birth_date']);
});

test('validação impede criação de person com birth_date futura', function () {
    $tenant = Tenant::factory()->create();

    $personData = [
        'tenant_id' => $tenant->id,
        'name' => 'Pessoa Teste',
        'birth_date' => now()->addDay()->format('Y-m-d'),
        'whatsapp' => '+55 11 99999-9999',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/persons', $personData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['birth_date']);
});

test('validação impede criação de person sem whatsapp', function () {
    $tenant = Tenant::factory()->create();

    $personData = [
        'tenant_id' => $tenant->id,
        'name' => 'Pessoa Teste',
        'birth_date' => '1990-01-01',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/persons', $personData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['whatsapp']);
});

test('validação impede criação de person sem status', function () {
    $tenant = Tenant::factory()->create();

    $personData = [
        'tenant_id' => $tenant->id,
        'name' => 'Pessoa Teste',
        'birth_date' => '1990-01-01',
        'whatsapp' => '+55 11 99999-9999',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/persons', $personData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('relacionamento com tenant funciona', function () {
    $tenant = Tenant::factory()->create(['name' => 'Tenant Teste']);
    $person = Person::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Pessoa do Tenant',
    ]);

    expect($person->tenant)->not->toBeNull()
        ->and($person->tenant->name)->toBe('Tenant Teste')
        ->and($person->tenant->id)->toBe($tenant->id);
});

test('tenant pode ter múltiplas persons', function () {
    $tenant = Tenant::factory()->create();
    Person::factory()->count(5)->create(['tenant_id' => $tenant->id]);

    expect($tenant->persons)->toHaveCount(5);
});
