<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function update(User $authUser, User $user)
    {
        return $this->validate($authUser, $user);
    }

    public function delete(User $authUser, User $user)
    {
        return $this->validate($authUser, $user);
    }

    public function validate(User $authUser, User $user): Response
    {
        if ($authUser->id !== $user->id) {
            return Response::deny('This is not your account');
        }

        return Response::allow();
    }
}
