<?php

namespace App\Support\Services;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\Project;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Support\Services\BaseService;
use App\Http\Resources\NotificationResource;
use App\Http\Requests\SelectNotificationRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Support\Repositories\NotificationRepository;

class NotificationService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly NotificationRepository $notificationRepository)
    {
        //
    }

    public function view(Tenant $tenant, Project $project, Task $task,Request $request):JsonResponse
    {
        $allNotifications=$this->notificationRepository->getAll($request->user()->id);

        return $this->successResponse(
            data: [
                'notifications' => NotificationResource::collection($allNotifications)
            ]
        );
    }

    public function markasRead(Tenant $tenant,Project $project, Task $task, SelectNotificationRequest $request):JsonResponse
    {
        foreach ($request->messages as $message){
            $unread=$this->notificationRepository->getSelectedUnread($request->user()->id,$message);
            Log::info('message',[$unread]);
            
            DB::transaction(function() use ($unread){
               $unread->mark_read=true;
               $unread->save();
            });
        }

        $notifications=$this->notificationRepository->findByMarkedRead($request->user()->id);
        return $this->successResponse(
            data: [
                'notifications' => NotificationResource::collection($notifications)
            ]
        );
    }

    public function getUnreadNotifications(Tenant $tenant, Project $project, Task $task, Request $request):JsonResponse
    {
        $unreadNotifications=$this->notificationRepository->getUnread($request->user()->id);

        return $this->successResponse(
            data: [
                'unread_notifications' => NotificationResource::collection($unreadNotifications)
            ]
        );
    }

    public function getSpecificNotification(Tenant $tenant, Project $project, Task $task, Notification $message):JsonResponse
    {
        $notification=$this->notificationRepository->find($message->id);

        return $this->successResponse(
            data: [
                'notification' => new NotificationResource($notification->refresh())
            ]
        );
    }

    public function deleteNotification(Tenant $tenant, Project $project, Task $task, Request $request,Notification $message):JsonResponse
    {
        $this->notificationRepository->delete($request->user()->id,$message->message);

        return $this->successResponse(
            data: [
                'message' => 'Notification deleted successfully'
            ]
        );
    }

    public function deleteAllNotifications(Tenant $tenant, Project $project, Task $task, Request $request):JsonResponse
    {
        $notification=$this->notificationRepository->getAll($request->user()->id);

        foreach ($notification as $notice){
            $this->notificationRepository->delete($request->user()->id, $notice->message);
        }

        return $this->successResponse(
            data: [
                'message' => 'All notifications deleted successfully'
            ]
        );
    }


    public function deleteSelectedNotifications(Tenant $tenant, Project $project, Task $task, SelectNotificationRequest $request):JsonResponse
    {
        foreach ($request->messages as $message){
            $this->notificationRepository->delete($request->user()->id, $message);
        }

        return $this->successResponse(
            data: [
                'message' => 'Selected notifications deleted successfully'
            ]
        );
    }
}
