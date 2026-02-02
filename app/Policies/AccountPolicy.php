<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccountPolicy
{

    public function update(User $user, Request $request)
    {
        return $this->validate($user, $request->user());
    }

    public function delete(User $user, Request $request)
    {
        return $this->validate($user, $request->user());
    }

    public function validate(User $user, Request $authUser): Response
    {
        if ($authUser->id !== $user->id) {
            return Response::deny("This is not your account");
        }

        return Response::allow();
    }
}
