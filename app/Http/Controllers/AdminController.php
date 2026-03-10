<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\SearchUserRequest;
use App\Support\Services\AdminService;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    //
    public function __construct(
        private readonly AdminService $adminService
    ) {}

    public function login(AdminLoginRequest $request): JsonResponse
    {
        return $this->adminService->login($request);
    }

    public function view(): JsonResponse
    {
        return $this->adminService->view();
    }

    public function delete(SearchUserRequest $request): JsonResponse
    {
        return $this->adminService->deleteUser($request);
    }
}
