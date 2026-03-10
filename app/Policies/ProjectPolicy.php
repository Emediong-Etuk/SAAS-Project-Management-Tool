<?php

namespace App\Policies;

use App\Models\User;
use App\Enum\PlansEnum;
use App\Models\Project;
use App\Enum\UserRolesEnum;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
   

    public function create(User $user):Response
    {
        if($user->role!==UserRolesEnum::TENANT_ADMIN->value){
            return Response::denyAsNotFound('You do not have permission to create a project');
        }
        
        return Response::allow();
    }


    public function update(User $user): Response
    {
        return $this->validate($user, 'update');
    }

    public function addUser(User $user):Response
    {
        return $this->validate($user, 'add user to');
    }

    public function delete(User $user): Response
    {
        return $this->validate($user, 'delete');
    }

    public function view(User $user): Response
    {
        return $this->validate($user, 'view');
    }

    public function assignRole(User $user): Response
    {
        if($user->subscription_plan === PlansEnum::Pro->value){
            return Response::allow();
        }

        if($user->role === UserRolesEnum::TENANT_ADMIN->value){
            return Response::allow();
        }

        return Response::denyAsNotFound("You do not have permission to assign roles in a project");
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

        return Response::denyAsNotFound("You do not have permission to {$method} a project");
    }


}
