<?php

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('login successful', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123')
    ]);

    $response = $this->post('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['status', 'message', 'data'])
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ])->etc());
});


test('login failed with incorrect credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123')
    ]);

    $response = $this->post('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(422)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['message'])
            ->whereAllType([
                'message' => 'string',
            ])->etc());
});

test('login failed with missing fields', function () {
    $response = $this->post('/api/auth/login', [
        'email' => ''
    ]);

    $response->assertStatus(422)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['message'])
            ->whereAllType([
                'message' => 'string',
            ])->etc());
});
