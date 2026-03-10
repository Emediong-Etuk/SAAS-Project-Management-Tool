<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectNotificationRequest;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tenant;
use App\Support\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //
    public function __construct(private readonly NotificationService $notificationService) {}

    public function view(Tenant $tenant, Project $project, Task $task, Request $request): JsonResponse
    {
        return $this->notificationService->view($tenant, $project, $task, $request);
    }

    public function markasRead(Tenant $tenant, Project $project, Task $task, SelectNotificationRequest $request): JsonResponse
    {
        return $this->notificationService->markasRead($tenant, $project, $task, $request);
    }

    public function getUnreadNotifications(Tenant $tenant, Project $project, Task $task, Request $request): JsonResponse
    {
        return $this->notificationService->getUnreadNotifications($tenant, $project, $task, $request);
    }

    public function deleteAllNotifications(Tenant $tenant, Project $project, Task $task, Request $request): JsonResponse
    {
        return $this->notificationService->deleteAllNotifications($tenant, $project, $task, $request);
    }

    public function deleteNotification(Tenant $tenant, Project $project, Task $task, Request $request, Notification $message): JsonResponse
    {
        return $this->notificationService->deleteNotification($tenant, $project, $task, $request, $message);
    }

    public function deleteSelectedNotification(Tenant $tenant, Project $project, Task $task, SelectNotificationRequest $request): JsonResponse
    {
        return $this->notificationService->deleteSelectedNotifications($tenant, $project, $task, $request);
    }

    public function getSpecificNotification(Tenant $tenant, Project $project, Task $task, Notification $message): JsonResponse
    {
        return $this->notificationService->getSpecificNotification($tenant, $project, $task, $message);
    }
}
