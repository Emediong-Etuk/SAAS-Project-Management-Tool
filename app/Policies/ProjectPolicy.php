<?php

namespace App\Policies;

use App\Models\User;
use App\Enum\PlansEnum;
use App\Models\Project;
use App\Enum\UserRolesEnum;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{


    public function create(User $user): Response
    {
        if ($user->role !== UserRolesEnum::TENANT_ADMIN->value) {
            return Response::denyAsNotFound('You do not have permission to create a project');
        }

        return Response::allow();
    }


    public function update(User $user): Response
    {
        return $this->validate($user, 'update');
    }

    public function addUser(User $user): Response
    {
        return $this->validate($user, 'add user to');
    }

    public function delete(User $user): Response
    {
        return $this->validate($user, 'delete');
    }

    public function createMeeting(User $user): Response
    {
        return $this->validate($user, 'create meeting for');
    }

    public function joinMeeting(User $user, Project $project): Response
    {
        if ($user->project_id === null) {
            return Response::denyAsNotFound("You are not assigned to any project to join meeting");
        }
        if ($user->project_id !== $project->id) {
            return Response::denyAsNotFound("You are not assigned to this project and cannot join the meeting");
        }

        return Response::allow();
    }

    public function deleteMeeting(User $user): Response
    {
        return $this->validate($user, 'delete meeting for');
    }

    public function assignRole(User $user): Response
    {
        if ($user->subscription_plan === PlansEnum::Pro->value) {
            return Response::allow();
        }

        if ($user->role === UserRolesEnum::TENANT_ADMIN->value) {
            return Response::allow();
        }

        return Response::denyAsNotFound("You do not have permission to assign roles in a project");
    }

    public function validate(User $user, string $method): Response
    {

        if ($user->subscription_plan === PlansEnum::Pro->value) {
            return Response::allow();

            if ($user->role === UserRolesEnum::TENANT_ADMIN->value) {
                return Response::allow();
            }

            if ($user->role === UserRolesEnum::PROJECT_MANAGER->value) {
                return Response::allow();
            }
        }

        return Response::denyAsNotFound("You do not have permission to {$method} a project");
    }
}
