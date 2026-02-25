<?php

namespace App\Support\Services\Auth;

use App\Http\Resources\UserResource;
use App\Support\Services\BaseService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class LogoutService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(data: [
            'user' => new UserResource($request->user()),
        ]);
    }
}
