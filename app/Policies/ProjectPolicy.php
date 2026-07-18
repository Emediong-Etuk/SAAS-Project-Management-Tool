<?php

namespace App\Policies;

use App\Enum\PlansEnum;
use App\Enum\UserRolesEnum;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    public function create(User $user): Response
    {
        if ($user->role !== UserRolesEnum::TENANT_ADMIN->value) {
            return Response::deny('You do not have permission to create a project', 403);
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
            return Response::deny('You are not assigned to any project to join meeting', 403);
        }
        if ($user->project_id !== $project->id) {
            return Response::deny('You are not assigned to this project and cannot join the meeting', 403);
        }

        return Response::allow();
    }

    public function deleteMeeting(User $user): Response
    {
        return $this->validate($user, 'delete meeting for');
    }

    public function assignRole(User $user): Response
    {
        return $this->validate($user, 'assign user to');
    }

    public function removeUser(User $user): Response
    {
        return $this->validate($user, 'remove user from');
    }

    public function updateStatus(User $user): Response
    {
        return $this->validate($user, 'update status of');
    }

    public function validate(User $user, string $method): Response
    {

        if ($user->subscription_plan === PlansEnum::Pro->value) {

            if ($user->role === UserRolesEnum::TENANT_ADMIN->value) {
                return Response::allow();
            }

            if ($user->role === UserRolesEnum::PROJECT_MANAGER->value) {
                return Response::allow();
            }
        }

        return Response::deny("You do not have permission to {$method} a project", 403);
    }
}
