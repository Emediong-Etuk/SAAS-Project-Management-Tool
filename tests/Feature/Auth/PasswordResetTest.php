<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Notifications\ResetPasswordInfoNotice;
use App\Notifications\ResetPasswordTokenNotice;

test('password reset token sent successfully', function () {

    Notification::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/api/auth/password/reset/get-token', [
        'email' => $user->email
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['status', 'message'])
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
            ])->etc());

    Notification::assertSentTo(
        $user,
        ResetPasswordTokenNotice::class,
        fn($notification, $channels) =>
        in_array('mail', $channels)
    );
});

test('password reset token request with invalid email', function () {

    Notification::fake();
    $this->actingAs(User::factory()->create());

    $response = $this->post('/api/auth/password/reset/get-token', [
        'email' => '<invalid-email>'
    ]);

    $response->assertStatus(422)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['message', 'errors'])
            ->whereAllType([
                'errors' => 'array',
                'message' => 'string',
            ])->etc());

    Notification::assertNothingSent();
});

test('password reset successful', function () {

    Notification::fake();

    $user = User::factory()->create();
    $this->actingAs($user);
    $newPassword = 'newpassword';
    $token = '1234';

    Cache::shouldReceive('get')
        ->with("PASSWORD_RESET_TOKEN_{$user->email}")
        ->andReturn($token);


    $response = $this->post('/api/auth/password/reset', [
        'email' => $user->email,
        'token' => $token,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $response->assertStatus(200)
        ->assertJson(fn(AssertableJson $json) =>
        $json->hasAll(['status', 'message'])
            ->whereAllType([
                'status' => 'string',
                'message' => 'string',
            ])->etc());
            
    Notification::assertSentTo($user, ResetPasswordInfoNotice::class, fn($notification, $channels) =>
    in_array('mail', $channels));

    $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
});
