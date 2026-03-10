<?php

namespace App\Policies;

use App\Models\User;
use App\Enum\PlansEnum;
use App\Enum\UserRolesEnum;
use Illuminate\Auth\Access\Response;

class TenantPolicy
{

    public function update(User $user): Response
    {

        return $this->validate($user, 'update');
    }

    public function delete(User $user): Response
    {
        return $this->validate($user, 'delete');
    }

    public function invite(User $user): Response
    {
        return $this->validate($user, 'invite someone to');
    }

    public function removeMember(User $user): Response
    {
        return $this->validate($user, 'remove member from');
    }

    public function validate(User $user, string $method): Response
    {

        if ($user->subscription_plan === PlansEnum::Pro->value) {
            return Response::allow();
        }

        if ($user->role === UserRolesEnum::TENANT_ADMIN->value) {
            return Response::allow();
        }

        return Response::denyAsNotFound("You do not have permission to {$method} a tenant");
    }
}
