<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Support\Services\AdminService;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\SearchUserRequest;

class AdminController extends Controller
{
    //
    public function __construct(
        private readonly AdminService $adminService
    ){}

    public function login(AdminLoginRequest $request):JsonResponse
    {
        return $this->adminService->login($request);
    }

    public function view():JsonResponse
    {
        return $this->adminService->view();
    }

    public function delete(SearchUserRequest $request):JsonResponse
    {
        return $this->adminService->deleteUser($request);
    }
}
