<?php

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\Fluent\AssertableJson;

test('successfully gotten all members', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('members.getAll', ['tenant' => $tenant->id]));

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));
});


test('successfully accepted an invitation', function () {
    $tenant = Tenant::factory()->create();
    $inviter = User::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create();
    $inviteCode = 'inviteCode';

    $this->actingAs($user, 'sanctum');

    Cache::shouldReceive('get')
        ->with("TENANCY_INVITATION_CODE_$inviteCode")
        ->andReturn([$user->email, $tenant->id, $inviter->id]);

    $response = $this->post(route('members.acceptInvitation') . '?token=' . $inviteCode);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ]));
});

test('failed to accept invitation due to invalid invite code', function () {
    $tenant = Tenant::factory()->create();
    $inviter = User::factory()->create(['tenant_id' => $tenant->id]);
    $user = User::factory()->create();
    $inviteCode = 'inviteCode';

    $this->actingAs($user, 'sanctum');

    Cache::shouldReceive('get')
        ->with("TENANCY_INVITATION_CODE_$inviteCode")
        ->andReturn([$user->email, $tenant->id, $inviter->id]);

    $response = $this->post(route('members.acceptInvitation') . '?token=' . 'wrongcode');

    $response->assertStatus(500)
        ->assertJson(fn(AssertableJson $json) => $json
            ->hasAll('message')
            ->whereAllType([
                'message' => 'string',
            ])->etc());
});
