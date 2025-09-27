<?php

namespace App\Support\Services;

use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ResetPasswordTokenRequest;
use App\Http\Resources\UserResource;
use App\Notifications\ResetPasswordInfoNotice;
use App\Notifications\ResetPasswordTokenNotice;
use App\Support\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ResetPasswordService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly UserRepository $userRepository)
    {
        //
    }

    public function getResetPasswordToken(ResetPasswordTokenRequest $request):JsonResponse
    {
        $user=$this->userRepository->findByEmail($request->email);
        $token=$this->generateToken();
        $expiryTime=900;

        Cache::put("PASSWORD_RESET_TOKEN_$request->email",$token,$expiryTime);

        $user->notify(new ResetPasswordTokenNotice($token,$expiryTime));

        return $this->successResponse('Reset token has been sent to your email');
    }
    
    public function resetPassword(ResetPasswordRequest $request):JsonResponse
    {
        $user=$this->userRepository->findByEmail($request->email);

        $this->userRepository->update($user->id, [
            'password'=>$request->password,
        ]);

        $user->notify(new ResetPasswordInfoNotice());

        return $this->successResponse('Password has been reset successfully',[
            'user'=>new UserResource($user->refresh()),
        ]);
    }
}
