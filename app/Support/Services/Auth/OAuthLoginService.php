<?php

namespace App\Support\Services\Auth;

use App\Support\Repositories\UserRepository;
use App\Support\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

    public function githubCallback(): RedirectResponse
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

        // return redirect(config('services.frontend.url') . '?token=' . $token->plainTextToken . '&user=' . $user);

        return redirect(
            config('services.frontend.url') . '/oauth-callback?' .
                http_build_query([
                    'token' => $token->plainTextToken,
                    'tenant_id' => $user->tenant_id,
                    'user' => json_encode([
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ])
                ])
        );
    }

    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleCallback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $expiryTime = now()->addMonth();

        $data = [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
        ];

        $user = $this->userRepository->firstOrCreate($data['email'], $data);

        $token = $user->createToken('Auth Token', ['can-access-user'], $expiryTime);

        // return redirect(config('services.frontend.url') . '?token=' . $token->plainTextToken . '&user=' . $user);

        return redirect(
            config('services.frontend.url') . '/oauth-callback?' .
                http_build_query([
                    'token' => $token->plainTextToken,
                    'tenant_id' => $user->tenant_id,
                    'user' => json_encode([
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ])
                ])
        );
    }
}
