<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\Services\AccountService;
use App\Http\Requests\UpdateAccountRequest;
use Symfony\Component\HttpFoundation\JsonResponse;


class AccountController extends Controller
{
    //
    public function __construct(
        private readonly AccountService $accountService
    ) {}

    public function view(Request $request): JsonResponse
    {
        return $this->accountService->view($request);
    }

    public function update(UpdateAccountRequest $request): JsonResponse
    {
        return $this->accountService->update($request);
    }

    public function delete(Request $request): JsonResponse
    {
        return $this->accountService->delete($request);
    }
}
