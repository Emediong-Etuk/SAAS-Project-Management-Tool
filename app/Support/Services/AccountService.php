<?php

namespace App\Support\Services;

use Illuminate\Http\Request;
use App\Support\Services\BaseService;
use App\Http\Requests\UpdateAccountRequest;
use App\Support\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccountService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly UserRepository $userRepository)
    {

    }

    public function view(Request $request): JsonResponse
    {
        $data = [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'occupation' => $request->user()->occupation,
            'skills' => $request->user()->skills,
            'profile_picture' => $request->user()->profile_picture,
            'cover_picture' => $request->user()->cover_picture,
            'projects_worked_on' => $request->user()->projects_worked_on
        ];

        return $this->successResponse(
            data: [
                'user' => $data
            ]
        );
    }

    public function update(UpdateAccountRequest $request): JsonResponse
    {

        $data = [
            'name' => $request->name ?? $request->user()->name,
            'occupation' => $request->occupation ?? $request->user()->occupation,
            'skills' => $request->skills ?? $request->user()->skills,
            'projects_worked_on' => $request->projects_worked_on ?? $request->user()->projects_worked_on,
            'profile_picture' => $request->file('profile_picture')->storeAs('profile_picture', $request->user()->id) ?? $request->user()->profile_picture,
            'cover_picture' => $request->file('cover_picture')->storeAs('profile_picture', $request->user()->id) ?? $request->user()->cover_picture,
            'linkedin_profile' => $request->linkedin_profile ?? $request->user()->linkedin_profile,

        ];


        $this->userRepository->update($request->user()->id, $data);
        $user = $this->userRepository->find($request->user()->id);

        return $this->successResponse(
            data: [
                'user' => $user->refresh()
            ]
        );
    }

    public function delete(Request $request): JsonResponse
    {
        $this->userRepository->delete($request->user()->id);

        return $this->successResponse('Account deleted successfully');

    }
}
