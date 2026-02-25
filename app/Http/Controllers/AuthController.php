<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ResetPasswordTokenRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Support\Services\Auth\LoginService;
use App\Support\Services\Auth\LogoutService;
use App\Support\Services\Auth\OAuthLoginService;
use App\Support\Services\Auth\SignupService;
use App\Support\Services\ResetPasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    //
    public function __construct(
        private readonly SignupService $signupService,
        private readonly LoginService $loginService,
        private readonly ResetPasswordService $resetPasswordService,
        private readonly LogoutService $logoutService,
        private readonly OAuthLoginService $oauthService,
    ) {}

    public function signup(SignupRequest $request): JsonResponse
    {
        return $this->signupService->signup($request);
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        return $this->signupService->verifyEmail($request);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return $this->loginService->login($request);
    }

    public function getResetPasswordToken(ResetPasswordTokenRequest $request): JsonResponse
    {
        return $this->resetPasswordService->getResetPasswordToken($request);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return $this->resetPasswordService->resetPassword($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->logoutService->logout($request);
    }

    public function githubRedirect()
    {
        return $this->oauthService->githubRedirect();
    }

    public function githubCallback(): JsonResponse
    {
        return $this->oauthService->githubCallback();
    }

    public function googleRedirect()
    {
        return $this->oauthService->googleRedirect();
    }

    public function googleCallback(): JsonResponse
    {
        return $this->oauthService->googleCallback();
    }
}
