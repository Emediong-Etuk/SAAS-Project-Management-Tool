<?php

namespace App\Support\Services\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SignupRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Support\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use App\Notifications\VerifyEmailNotice;
use App\Http\Requests\VerifyEmailRequest;
use App\Notifications\WelcomeEmailNotice;
use App\Support\Repositories\UserRepository;
use Illuminate\Support\Facades\Notification;

class SignupService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly UserRepository $userRepository)
    {
        //
    }

    public function signup(SignupRequest $request): JsonResponse
    {
        $expiryTime = 900;

        $token = $this->generateToken();

        Cache::put("EMAIL_VERIFICATION_TOKEN_$request->email", $token, $expiryTime);

        Notification::route('mail', $request->email)->notify(new VerifyEmailNotice($token, $expiryTime));

        return $this->successResponse('An OTP has been sent to your email');
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $expiryTime = now()->addMonth();
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'username' => explode('@', $request->name . Str::random(6))
        ];

        $user = $this->userRepository->create($data);
        $token = $user->createToken('Auth Token', ['can-access-user'], $expiryTime);

        Auth::login($user);

        $user->notify(new WelcomeEmailNotice($user));

        return $this->successResponse('Signup successful', [
            'token' => $token->plainTextToken,
            'user' => new UserResource($user)
        ]);
    }
}
