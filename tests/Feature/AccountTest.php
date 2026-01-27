<?php

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Testing\Fluent\AssertableJson;

test('successfully gotten user account', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('account.get', ['tenant' => $tenant->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'data', 'message')
            ->whereAllType([
                'status' => 'string',
                'data' => 'array',
                'message' => 'string'
            ]));
});

test('successfully updated user account', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('account.update', ['tenant' => $tenant->id]), [
        'name' => 'new name'
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ])->etc());

    $this->assertDatabaseHas(
        'users',
        [
            'name' => 'new name'
        ]
    );
});


test('successfully deleted account', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('account.delete', ['tenant' => $tenant->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string'
            ])->etc());

    $this->assertDatabaseMissing('users', [
        'id' => $user->id
    ]);
});
