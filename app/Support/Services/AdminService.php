<?php

namespace App\Support\Services;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Support\Services\BaseService;
use App\Notifications\DeleteUserNotice;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\SearchUserRequest;
use App\Support\Repositories\UserRepository;
use App\Support\Repositories\TenantRepository;
use App\Support\Repositories\TransactionRepository;

class AdminService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly TenantRepository $tenantRepository,
        private readonly UserRepository $userRepository)
    {
        //
    }

    public function login(AdminLoginRequest $request):JsonResponse
    {
        $data=[
            'name'=>config('admin.name'),
            'email'=>config('admin.email'),
            'password'=>config('admin.password'),
            'is_admin'=>true
        ];

        $expiryTime=now()->addMonth();

        if($request->name !== $data['name'] || $request->email !== $data['email'] || $request->password !==$data['password']){
            return $this->badRequestResponse('Invalid credentials');
        }

        $admin=User::query()->firstOrCreate(['email'=>$data['email']],$data);
        $token=$admin->createToken('Admin Auth Token',['can-access-user'], $expiryTime);
        
        Auth::login($admin);
        

        return $this->successResponse("Welcome Admin {{$data['name']}}",[
            'token'=>$token->plainTextToken
        ]);
    }

    public function view():JsonResponse
    {
        $transactions=$this->transactionRepository->getAll();

        $totalRevenue=0;

        foreach($transactions as $transaction)
        {
            $totalRevenue=$totalRevenue + $transaction->amount;
        }

        $tenants=$this->tenantRepository->countAllTenants();
        $users=$this->userRepository->countAllUsers();

        return $this->successResponse(data:[
            'totalRevenue'=>$totalRevenue,
            'tenants'=>$tenants,
            'users'=>$users
        ]);
    }

    public function deleteUser(SearchUserRequest $request):JsonResponse
    {
        $user=$this->userRepository->searchUser($request->name);

        if(!$user){
            return $this->badRequestResponse('User not found');
        }

        Notification::route('mail',$user->email)->notify(new DeleteUserNotice($user->name));

        $this->userRepository->delete($user->id);


        return $this->successResponse(message:"User {$user->name} has been removed",data:[
            'user'=>new UserResource($user)
        ]);
    }
}
