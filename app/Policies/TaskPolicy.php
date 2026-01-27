<?php

namespace App\Policies;

use App\Models\User;
use App\Enum\PlansEnum;
use App\Enum\UserRolesEnum;
use Illuminate\Auth\Access\Response;


class TaskPolicy
{

    public function create(User $user): Response
    {
        return $this->validate($user);
    }


    public function update(User $user): Response
    {
        return $this->validate($user);
    }


    public function delete(User $user): Response
    {
        return $this->validate($user);
    }

    public function markComplete(User $user): Response
    {
        return $this->validate($user);
    }

    public function downloadSubmissions(User $user): Response
    {
        return $this->validate($user);
    }

    public function assignTask(User $user): Response
    {
        return $this->validate($user);
    }

    public function validate(User $user): Response
    {

        if ($user->subscription_plan === PlansEnum::Pro->value) {

            if ($user->role === UserRolesEnum::TENANT_ADMIN->value) {
                return Response::allow();
            }

            if ($user->role === UserRolesEnum::PROJECT_MANAGER->value) {
                return Response::allow();
            }
        }




        return Response::deny("Not Authorized", 403);
    }
}
