<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use App\Notifications\VerifyEmailNotice;
use App\Notifications\WelcomeEmailNotice;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Notifications\AnonymousNotifiable;

test('otp for signup sent successfully', function () {

    Notification::fake();
    $name = fake()->unique()->name();
    $email = fake()->unique()->safeEmail();
    $password = 'password';


    $response = $this->post('api/auth/signup', [
        'name' => $name,
        'email' => $email,
        'password' => $password
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['status', 'message', 'data'])
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ])->etc());

    Notification::assertSentTo(new AnonymousNotifiable, VerifyEmailNotice::class, fn($notifiable, $channels) => in_array('mail', $channels));
});


test('resend otp for signup sent successfully', function () {

    Notification::fake();
    $name = fake()->unique()->name();
    $email = fake()->unique()->safeEmail();
    $password = 'password';

    $response = $this->post('/api/auth/signup/resend-token', [
        'email' => $email,
        'password' => $password,
        'name' => $name
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['status', 'message', 'data'])
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ])->etc());

    Notification::assertSentTo(new AnonymousNotifiable, VerifyEmailNotice::class, fn($notifiable, $channels) => in_array('mail', $channels));
});


test('otp for signup failed to send', function () {

    Notification::fake();


    $name = fake()->unique()->name();
    $email = fake()->unique()->safeEmail();

    $response = $this->post('/api/auth/signup', [
        'name' => $name,
        'email' => $email
    ]);

    $response->assertStatus(422)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['message'])
            ->whereAllType([
                'message' => 'string',
            ])->etc());

    Notification::assertNothingSent();
});

test('signup successful', function () {

    Notification::fake();

    $name = fake()->unique()->name();
    $email = fake()->unique()->safeEmail();
    $password = 'password';
    $token = '1234';

    Cache::shouldReceive('get')
        ->once()
        ->with("EMAIL_VERIFICATION_TOKEN_$email")
        ->andReturn($token);

    $response = $this->post('/api/auth/verify-email', [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'token' => $token
    ]);

    $user = User::where('email', $email)->first();

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['status', 'message', 'data'])
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
                'data' => 'array'
            ])->etc());

    $this->assertDatabaseHas('users', [
        'email' => $email,
        'name' => $name
    ]);

    Notification::assertSentTo($user, WelcomeEmailNotice::class, fn($notifiable, $channels) => in_array('mail', $channels));
});

test('signup failed due to invalid token', function () {

    Notification::fake();

    $name = fake()->unique()->name();
    $email = fake()->unique()->safeEmail();
    $password = 'password';
    $token = '1234';

    Cache::shouldReceive('get')
        ->once()
        ->with("EMAIL_VERIFICATION_TOKEN_$email")
        ->andReturn('5678');

    $response = $this->post('/api/auth/verify-email', [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'token' => $token
    ]);

    $response->assertStatus(422)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['message'])
            ->whereAllType([
                'message' => 'string',
            ])->etc());

    $this->assertDatabaseMissing('users', [
        'email' => $email,
        'name' => $name
    ]);

    Notification::assertNothingSent();
});


test('signup failed due to missing fields', function () {

    Notification::fake();

    $response = $this->post('/api/auth/verify-email', [
        // No data provided
    ]);

    $response->assertStatus(422)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['message'])
            ->whereAllType([
                'message' => 'string',
            ])->etc());

    Notification::assertNothingSent();
});

test('otp for signup failed due to missing fields', function () {

    Notification::fake();
    $response = $this->post('/api/auth/signup', [
        // No data provided
    ]);

    $response->assertStatus(422)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['message'])
            ->whereAllType([
                'message' => 'string',
            ])->etc());

    Notification::assertNothingSent();
});
