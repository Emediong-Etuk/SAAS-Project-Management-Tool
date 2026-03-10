<?php

namespace App\Policies;

use App\Models\User;
use App\Enum\PlansEnum;
use App\Enum\UserRolesEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\Response;


class TaskPolicy
{
    
    public function create(User $user): Response
    {
        return $this->validate($user, 'create');
    }

    
    public function update(User $user): Response
    {
        return $this->validate($user, 'update');
    }


    public function delete(User $user): Response
    {
        return $this->validate($user, 'delete');
    }

    public function markComplete(User $user): Response
    {
        return $this->validate($user, 'mark complete');
    }

    public function validate(User $user, string $method):Response
    {

        if($user->subscription_plan === PlansEnum::Pro->value){
           
            return Response::allow();
        }

        
        if($user->role === UserRolesEnum::TENANT_ADMIN->value){
            return Response::allow();
        }

        if($user->role === UserRolesEnum::PROJECT_MANAGER->value){
            return Response::allow();
        }

        Log::info('User details',['plan'=>$user->subscription_plan,'role'=>$user->role, 'email'=>$user->email]);

        return Response::denyAsNotFound("You do not have permission to {$method} a task");
    }


}
