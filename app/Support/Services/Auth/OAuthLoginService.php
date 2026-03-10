<?php

namespace App\Support\Services\Auth;

use App\Support\Repositories\UserRepository;
use App\Support\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Socialite;
use Symfony\Component\HttpFoundation\JsonResponse;

class OAuthLoginService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly UserRepository $userRepository)
    {
        //
    }

    public function githubRedirect()
    {
        return Socialite::driver('github')->stateless()->redirect();
    }

    public function githubCallback(): JsonResponse
    {
        $githubUser = Socialite::driver('github')->stateless()->user();
        $expiryTime = now()->addMonth();

        $data = [
            'name' => $githubUser->name,
            'email' => $githubUser->email,
        ];

        $user = $this->userRepository->firstOrCreate($githubUser->email, $data);

        Auth::login($user);

        $token = $user->createToken('Auth Token', ['can-access-user'], $expiryTime);

        Log::info('user', [$user]);

        return $this->successResponse(data: [
            'data' => $data,
            'token' => $token->plainTextToken,
        ]);
    }

    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleCallback(): JsonResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $expiryTime = now()->addMonth();

        $data = [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
        ];

        $user = $this->userRepository->firstOrCreate($data['email'], $data);

        $token = $user->createToken('Auth Token', ['can-access-user'], $expiryTime);

        return $this->successResponse(data: [
            'user' => $data,
            'token' => $token->plainTextToken,
        ]);
    }
}
