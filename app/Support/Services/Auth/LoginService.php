<?php

namespace App\Support\Services\Auth;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Support\Services\BaseService;
use Illuminate\Http\JsonResponse;

class LoginService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function login(LoginRequest $request):JsonResponse
    {
        $user=$request->user();
        $expiryTime=now()->addMonth();

        $token=$user->createToken('Auth Token',['can-access-user'],$expiryTime);
        
        return $this->successResponse('Login successful',[
            'token'=>$token->plainTextToken,
            'user'=>new UserResource($user)
        ]);
    }
}
