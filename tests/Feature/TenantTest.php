<?php

use App\Enum\PlansEnum;
use App\Enum\UserRolesEnum;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\CreateTenantInfoNotice;
use App\Notifications\DeleteTenantInfoNotice;
use App\Notifications\SendInvitationNotice;
use App\Notifications\UpdateTenantInfoNotice;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;

test('user cannot access other tenant data', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant1->id,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('tenant.update', ['tenant' => $tenant2->id]));

    $response->assertStatus(403)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('message')
            ->whereAllType([
                'message' => 'string',
            ])->etc());
});

test('queries are scoped to tenant', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    $project1 = Project::factory()->create(['tenant_id' => $tenant1->id]);
    $project2 = Project::factory()->count(5)->create(['tenant_id' => $tenant2->id]);

    $user = User::factory()->create(['tenant_id' => $tenant1->id, 'project_id' => $project1->id]);

    Project::factory()->count(5)->create(['tenant_id' => $tenant1->id]);

    $this->actingAs($user, 'sanctum');

    $projects = Project::all();

    expect($projects)->toHaveCount(6);
});

test('tenant created successfully', function () {

    Notification::fake();

    $user = User::factory()->create([
        'subscription_plan' => PlansEnum::Pro->value,
    ]);

    $this->actingAs($user, 'sanctum');
    $response = $this->post('/api/tenants/create', [
        'name' => 'Test Tenant',
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array',
            ])
            ->where('data.tenant.name', 'Test Tenant')
            ->etc());

    $this->assertDatabaseHas('tenants', [
        'name' => 'Test Tenant',
    ]);

    Notification::assertSentTo($user, CreateTenantInfoNotice::class, fn($notification, $channels) => in_array('mail', $channels));
});

test('tenant creation failed due to unauthenticated user', function () {
    Notification::fake();

    $response = $this->post('/api/tenants/create', [
        'name' => 'Test Tenant',
    ]);

    $response->assertStatus(401);

    Notification::assertNothingSent();
});

test('edit tenant', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => PlansEnum::Pro->value,
    ]);

    $this->actingAs($user, 'sanctum');
    $response = $this->post(route('tenant.update', $tenant), [
        'name' => 'new name',
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array',
            ])
            ->where('data.tenant.name', 'new name')
            ->etc());

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'name' => 'new name',
    ]);

    Notification::assertSentTo($user, UpdateTenantInfoNotice::class, fn($notification, $channels) => in_array('mail', $channels));
});

test('edit tenant unsuccessful due to unauthorized user', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,

        ]
    );

    $this->actingAs($user, 'sanctum');
    $response = $this->post(route('tenant.update', $tenant), [
        'name' => 'new name 2',
    ]);

    $response->assertStatus(403);

    Notification::assertNothingSent();
});

test('delete tenant successful', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => PlansEnum::Pro->value,
    ]);

    $this->actingAs($user, 'sanctum');
    $response = $this->delete('/api/tenants/' . $tenant->id . '/delete');

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json->hasAll('status', 'message')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
            ])
            ->etc());

    $this->assertDatabaseMissing('tenants', [
        'id' => $tenant->id,
    ]);

    Notification::assertSentTo($user, DeleteTenantInfoNotice::class, fn($notification, $channels) => in_array('mail', $channels));
});

test('delete tenant unsuccessful due to unauthorized user', function () {
    Notification::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,

        ]
    );

    $this->actingAs($user, 'sanctum');
    $response = $this->delete('/api/tenants/' . $tenant->id . '/delete');

    $response->assertStatus(403);

    Notification::assertNothingSent();
});

test('send invite successful', function () {

    Notification::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => PlansEnum::Pro->value,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('tenant.invite', $tenant), [
        'receiver_email' => 'test@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json->hasAll('status', 'message')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
            ])
            ->etc());

    Notification::assertSentTo(new AnonymousNotifiable, SendInvitationNotice::class, fn($notification, $channels) => in_array('mail', $channels));
});

test('send invite unsuccessful due to unauthorized user', function () {

    Notification::fake();

    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(
        [
            'tenant_id' => $tenant->id,

        ]
    );

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('tenant.invite', $tenant), [
        'receiver_email' => 'test@example.com',
    ]);
    $response->assertStatus(403)
        ->assertJson(fn(AssertableJson $json) => $json->hasAll('message')
            ->whereAllType([
                'message' => 'string',
            ])
            ->etc());

    Notification::assertNothingSent();
});

test('remove member successful', function () {

    $tenant = Tenant::factory()->create();

    $adminUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRolesEnum::TENANT_ADMIN->value,
        'subscription_plan' => PlansEnum::Pro->value,
    ]);

    $userToRemove = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'member',
    ]);

    $this->actingAs($adminUser, 'sanctum');

    $response = $this->post(route('tenant.removeMember', ['tenant' => $tenant->id, 'user' => $userToRemove->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json->hasAll('status', 'message')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
            ])
            ->etc());

    $this->assertDatabaseHas('users', [
        'id' => $userToRemove->id,
        'tenant_id' => null,
    ]);
});

test('remove member unsuccessful due to unauthorized user', function () {

    $tenant = Tenant::factory()->create();

    $normalUser = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'member',
    ]);
    $userToRemove = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => 'member',
    ]);
    $this->actingAs($normalUser, 'sanctum');
    $response = $this->post(route('tenant.removeMember', ['tenant' => $tenant->id, 'user' => $userToRemove->id]));

    $response->assertStatus(403);

    $this->assertDatabaseHas('users', [
        'id' => $userToRemove->id,
        'tenant_id' => $tenant->id,
    ]);
});
