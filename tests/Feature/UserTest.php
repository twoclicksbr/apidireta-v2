<?php

use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('pode listar users vazio', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/users');

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

test('pode listar users com dados', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    User::factory()->count(3)->create(['person_id' => $person->id]);

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/users');

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'total' => 3,
        ])
        ->assertJsonCount(3, 'data');
});

test('pode criar um novo user', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);

    $userData = [
        'person_id' => $person->id,
        'email' => 'test@example.com',
        'password' => 'SecurePassword123!',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/users', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'person_id',
                'email',
                'status',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'email' => 'test@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'person_id' => $person->id,
    ]);

    // Verifica se a senha foi hasheada
    $user = User::where('email', 'test@example.com')->first();
    expect(Hash::check('SecurePassword123!', $user->password))->toBeTrue();
});

test('password não é retornado no JSON', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create(['person_id' => $person->id]);

    $response = $this->getJson(env('API_DOMAIN') . "/v2/admin/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonMissing(['password']);
});

test('pode exibir um user específico', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'person_id' => $person->id,
        'email' => 'specific@example.com',
    ]);

    $response = $this->getJson(env('API_DOMAIN') . "/v2/admin/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'site',
            'docs',
            'endpoint',
            'data' => [
                'id',
                'person_id',
                'email',
                'status',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $user->id,
                'email' => 'specific@example.com',
            ],
        ]);
});

test('retorna 404 ao buscar user inexistente', function () {
    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/users/9999');

    $response->assertStatus(404);
});

test('pode atualizar um user com PUT', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'person_id' => $person->id,
        'email' => 'original@example.com',
    ]);

    $updateData = [
        'person_id' => $person->id,
        'email' => 'updated@example.com',
        'password' => 'NewPassword123!',
        'status' => 'inactive',
    ];

    $response = $this->putJson(env('API_DOMAIN') . "/v2/admin/users/{$user->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $user->id,
                'email' => 'updated@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'updated@example.com',
    ]);
});

test('pode atualizar um user com PATCH', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create([
        'person_id' => $person->id,
        'email' => 'original@example.com',
    ]);

    $updateData = [
        'status' => 'pending',
    ];

    $response = $this->patchJson(env('API_DOMAIN') . "/v2/admin/users/{$user->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'data' => [
                'id' => $user->id,
                'status' => 'pending',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'status' => 'pending',
    ]);
});

test('pode deletar um user permanentemente', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create(['person_id' => $person->id]);

    $response = $this->deleteJson(env('API_DOMAIN') . "/v2/admin/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJson([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'message' => 'Recurso excluído com sucesso.',
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('paginação funciona corretamente', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    User::factory()->count(20)->create(['person_id' => $person->id]);

    $response = $this->getJson(env('API_DOMAIN') . '/v2/admin/users?per_page=5');

    $response->assertStatus(200)
        ->assertJson([
            'per_page' => 5,
            'total' => 20,
            'last_page' => 4,
        ])
        ->assertJsonCount(5, 'data');
});

test('validação impede criação de user sem person_id', function () {
    $userData = [
        'email' => 'test@example.com',
        'password' => 'SecurePassword123!',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/users', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['person_id']);
});

test('validação impede criação de user com person_id inexistente', function () {
    $userData = [
        'person_id' => 9999,
        'email' => 'test@example.com',
        'password' => 'SecurePassword123!',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/users', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['person_id']);
});

test('validação impede criação de user sem email', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);

    $userData = [
        'person_id' => $person->id,
        'password' => 'SecurePassword123!',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/users', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('validação impede criação de user com email duplicado', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    User::factory()->create([
        'person_id' => $person->id,
        'email' => 'duplicate@example.com',
    ]);

    $userData = [
        'person_id' => $person->id,
        'email' => 'duplicate@example.com',
        'password' => 'SecurePassword123!',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/users', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('validação impede criação de user sem password', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);

    $userData = [
        'person_id' => $person->id,
        'email' => 'test@example.com',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/users', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('validação impede criação de user com password curto', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);

    $userData = [
        'person_id' => $person->id,
        'email' => 'test@example.com',
        'password' => '123',
        'status' => 'active',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/users', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('validação impede criação de user sem status', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);

    $userData = [
        'person_id' => $person->id,
        'email' => 'test@example.com',
        'password' => 'SecurePassword123!',
    ];

    $response = $this->postJson(env('API_DOMAIN') . '/v2/admin/users', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('relacionamento com person funciona', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Test Person',
    ]);
    $user = User::factory()->create([
        'person_id' => $person->id,
        'email' => 'test@example.com',
    ]);

    expect($user->person)->not->toBeNull()
        ->and($user->person->name)->toBe('Test Person')
        ->and($user->person->id)->toBe($person->id);
});

test('person pode ter um user', function () {
    $tenant = Tenant::factory()->create();
    $person = Person::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create(['person_id' => $person->id]);

    expect($person->user)->not->toBeNull()
        ->and($person->user->id)->toBe($user->id);
});
