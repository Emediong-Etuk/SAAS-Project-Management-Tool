<?php

use App\Contracts\DataObjects\CreateCardChargeData;
use App\Contracts\DataObjects\ValidateCardChargeData;
use App\Contracts\Interface\SubscriptionPaymentInterface;
use App\Models\PricingPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery;

test('successfully gotten subscription plans', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->get(route('subscription.getPlans', ['tenant' => $tenant->id]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array',
            ])->etc());
});

test('successfully made card payments', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    PricingPlan::factory()->create([
        'name' => 'Pro',
        'description' => 'Professional plan with advanced features',
        'price' => 30,
        'role' => ['admin', 'user', 'tenant_admin', 'project_admin'],
        'can_create_tenant' => true,
        'can_edit_tenant' => true,
        'can_delete_tenant' => true,
        'can_create_tasks' => true,
        'can_create_projects' => true,
        'can_edit_tasks' => true,
        'can_edit_projects' => true,
        'can_delete_tasks' => true,
        'can_delete_projects' => true,
        'can_invite_members' => true,
    ]);

    $mockApi = Mockery::mock(SubscriptionPaymentInterface::class);
    $mockApi->shouldReceive('cardPayment')
        ->once()
        ->andReturn(new CreateCardChargeData(
            status: 'success',
            payment_type: 'card',
            flw_ref: 'FLW_1234'
        ));

    $this->app->instance(SubscriptionPaymentInterface::class, $mockApi);

    $data = [
        'card_number' => fake()->creditCardNumber('Visa'),
        'cvv' => fake()->numerify('###'),
        'expiry_month' => '11',
        'expiry_year' => '2030',
        'card_holder' => fake()->name(),
        'pin' => '1104',
    ];

    $response = $this->post(route('subscription.cardPayment', ['tenant' => $tenant->id]).'?subscription_plan=Pro', $data);

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array',
            ])->etc());
});

test('successfully validated card payments', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $mockApi = Mockery::mock(SubscriptionPaymentInterface::class);
    $mockApi->shouldReceive('validateCardPayment')
        ->once()
        ->andReturn(new ValidateCardChargeData(
            status: 'success',
            message: 'subscription paid',
            email: $user->email
        ));

    $this->app->instance(SubscriptionPaymentInterface::class, $mockApi);

    $this->actingAs($user, 'sanctum');

    $data = [
        'otp' => '1234',
    ];

    $response = $this->post(route('subscription.validatePayment', ['tenant' => $tenant->id]), $data);

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array',
            ]));

    $this->assertDatabaseHas('users', [
        'subscription_plan' => 'pro',
    ]);
});

test('payment verified successfully', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($user, 'sanctum');

    $payload = [
        'event' => 'charge.completed',
        'data' => [
            'id' => 285959875,
            'tx_ref' => 'subscription_72fad353-c782-4968-b6ae-4e7714b91ac8',
            'flw_ref' => '-4463-8437-0342c61651b4',
            'device_fingerprint' => 'a42937f4a73ce8bb8b8df14e63a2df31',
            'amount' => 100,
            'currency' => 'NGN',
            'charged_amount' => 100,
            'app_fee' => 1.4,
            'merchant_fee' => 0,
            'processor_response' => 'Approved by Financial Institution',
            'auth_model' => 'PIN',
            'ip' => '197.210.64.96',
            'narration' => 'CARD Transaction ',
            'status' => 'successful',
            'payment_type' => 'card',
            'created_at' => '2020-07-06T19:17:04.000Z',
            'account_id' => 17321,
            'customer' => [
                'id' => 215604089,
                'name' => 'Yemi Desola',
                'phone_number' => null,
                'email' => 'user@gmail.com',
                'created_at' => '2020-07-06T19:17:04.000Z',
            ],
            'card' => [
                'first_6digits' => '123456',
                'last_4digits' => '7889',
                'issuer' => 'VERVE FIRST CITY MONUMENT BANK PLC',
                'country' => 'NG',
                'type' => 'VERVE',
                'expiry' => '02/23',
            ],
        ],
    ];

    $response = $this->post('api/flutterwave-webhook', $payload);

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json
            ->hasAll('message')
            ->whereAllType([
                'message' => 'string',
            ])->etc());
});

test('subscription cancelled successfully', function () {
    $tenant = Tenant::factory()->create();

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'subscription_plan' => 'pro',
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->post(route('subscription.cancelSubscription', ['tenant' => $tenant->id, 'user' => $user->id]));

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) => $json
            ->hasAll('status', 'message', 'data')
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array',
            ])->etc());

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'subscription_plan' => 'free',
    ]);
});
