<?php

namespace App\Support\Services;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Support\Services\BaseService;
use App\Http\Requests\UpdateAccountRequest;
use App\Support\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Support\Repositories\NotificationRepository;

class AccountService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly NotificationRepository $notificationRepository) {}

    public function view(Request $request): JsonResponse
    {

        $data = [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'occupation' => $request->user()->occupation,
            'skills' => $request->user()->skills,
            'profile_picture' => config('filesystems.disks.public.url') . '/' . $request->user()->profile_picture,
            'cover_picture' => config('filesystems.disks.public.url') . '/' . $request->user()->cover_picture,
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
        $new_profile_picture = $request->file('profile_picture');
        $new_cover_picture = $request->file('cover_picture');

        if ($new_profile_picture !== null) {
            $new_profile_picture->store('profile_picture', 'public');
        }

        if ($new_cover_picture !== null) {
            $new_cover_picture->store('cover_picture', 'public');
        }

        $data = [
            'name' => $request->name ?? $request->user()->name,
            'occupation' => $request->occupation ?? $request->user()->occupation,
            'skills' => $request->skills ?? $request->user()->skills,
            'projects_worked_on' => $request->projects_worked_on ?? $request->user()->projects_worked_on,
            'profile_picture' => $new_profile_picture ?? $request->user()->profile_picture,
            'cover_picture' => $new_cover_picture ?? $request->user()->cover_picture,
            'linkedin_profile' => $request->linkedin_profile ?? $request->user()->linkedin_profile,

        ];

        $this->userRepository->update($request->user()->id, $data);
        $user = $this->userRepository->find($request->user()->id);
        $this->notificationRepository->create([
            'user_id' => $request->user()->id,
            'alerts' => 'Your account has been updated successfully.',
            'mark_read' => true,
        ]);

        return $this->successResponse(
            data: [
                'user' => new UserResource($user->refresh()),
            ]
        );
    }

    public function delete(Request $request): JsonResponse
    {
        $this->userRepository->delete($request->user()->id);

        return $this->successResponse('Account deleted successfully');
    }
}
