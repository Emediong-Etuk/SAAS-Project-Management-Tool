<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Notification;
use Illuminate\Testing\Fluent\AssertableJson;

test('get all notifications', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('notifications.get', ['tenant' => $tenant->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));
});

test('get specific notification', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $this->actingAs($user, 'sanctum');

    $notification = Notification::factory()->create([
        'user_id' => $user->id
    ]);

    $response = $this->get(route('notifications.get-specific', ['tenant' => $tenant->id, 'message' => $notification->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));
});

test('successfully marked notification as read', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $this->actingAs($user, 'sanctum');

    $notification = Notification::factory()->create([
        'user_id' => $user->id
    ]);

    $response = $this->post(route('notifications.markRead', ['tenant' => $tenant->id]), [
        'messages' => [$notification->message]
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ])->etc());

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'mark_read' => true
    ]);
});

test('successfully gotten unread messages', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('notifications.unread', ['tenant' => $tenant->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));
});

test('successfully deleted all notifications', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('notifications.deleteAll', ['tenant' => $tenant->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));

    $this->assertDatabaseMissing('notifications', [
        'user_id' => $user->id
    ]);
});

test('successfully deleted specific notification', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $notification = Notification::factory()->create([
        'user_id' => $user->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->delete(route('notifications.deleteAll', ['tenant' => $tenant->id, 'notification' => $notification->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));

    $this->assertDatabaseMissing('notifications', [
        'user_id' => $user->id
    ]);
});
