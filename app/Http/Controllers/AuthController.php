<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ResetPasswordTokenRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Support\Services\Auth\LoginService;
use App\Support\Services\Auth\SignupService;
use App\Support\Services\ResetPasswordService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    //
    public function __construct(
        private readonly SignupService $signupService,
        private readonly LoginService $loginService,
        private readonly ResetPasswordService $resetPasswordService
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

    public function resetPassword(ResetPasswordRequest $request):JsonResponse
    {
        return $this->resetPasswordService->resetPassword($request);
    }
}
