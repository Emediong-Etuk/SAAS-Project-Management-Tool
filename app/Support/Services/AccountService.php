<?php

namespace App\Support\Services;

use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\VerifyUpdatedEmail;
use App\Http\Resources\UserResource;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\VerifyEmailNotice;
use App\Support\Repositories\NotificationRepository;
use App\Support\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccountService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly NotificationRepository $notificationRepository) {}

    public function view(Tenant $tenant, User $user, Request $request): JsonResponse
    {

        $data = [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'occupation' => $request->user()->occupation,
            'skills' => $request->user()->skills,
            'profile_picture' => config('filesystems.disks.public.url') . '/' . $request->user()->profile_picture,
            'cover_picture' => config('filesystems.disks.public.url') . '/' . $request->user()->cover_picture,
            'projects_worked_on' => $request->user()->projects_worked_on,
        ];

        return $this->successResponse(
            data: [
                'user' => $data,
            ]
        );
    }

    public function update(Tenant $tenant, User $user, UpdateAccountRequest $request): JsonResponse
    {
        $authEmail = $request->user()->email;
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

        if ($request->email === null) {
            $data['email'] = $request->user()->email;
        }

        if ($request->email !== null && $request->email !== $request->user()->email) {
            $this->userRepository->update($request->user()->id, $data);
            $token = $this->generateToken();
            $expiryTime = 900;
            Cache::put("EMAIL_VERIFICATION_TOKEN_$authEmail", [$token, $request->email], $expiryTime);

            $request->user()->notify(new VerifyEmailNotice($token, $expiryTime));
            return $this->successResponse('An OTP has been sent to your new email address. Please verify to update your email.');
        }

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

    public function verifyEmail(Tenant $tenant, User $user, VerifyUpdatedEmail $request): JsonResponse
    {
        $cache = Cache::get('EMAIL_VERIFICATION_TOKEN_' . $request->user()->email);
        $this->userRepository->update($request->user()->id, ['email' => $cache[1]]);
        Cache::forget("EMAIL_VERIFICATION_TOKEN_$request->email");

        return $this->successResponse('Email verified successfully', [
            'user' => new UserResource($request->user()->refresh()),
        ]);
    }

    public function delete(Tenant $tenant, User $user, Request $request): JsonResponse
    {
        $this->userRepository->delete($request->user()->id);

        return $this->successResponse('Account deleted successfully');
    }
}
