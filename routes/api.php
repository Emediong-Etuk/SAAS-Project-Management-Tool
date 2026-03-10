<?php

use App\Models\Task;
use App\Models\Tenant;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\TenantMiddleware;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubscriptionController;

Route::webhooks('/flutterwave-webhook');


Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/signup', 'signup');
    Route::post('/signup/resend-token', 'signup')->middleware('throttle:otp');
    Route::post('/verify-email', 'verifyEmail');
    Route::post('/login', 'login');
    Route::post('/password/reset/get-token', 'getResetPasswordToken');
    Route::post('/password/reset', 'resetPassword');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::controller(TenantController::class)->prefix('tenants')->group(function () {
        Route::post('/create', 'create');
        Route::prefix('/{tenant}')->group(function () {
            Route::post('/update', 'update')->can('update', Tenant::class);
            Route::delete('/delete', 'delete')->can('delete', Tenant::class);
            Route::post('/invite', 'sendInvitation')->can('invite', Tenant::class);
            Route::post('/{user}/remove', 'removeMember')->can('removeMember', Tenant::class);
        });
    });
});



Route::middleware([TenantMiddleware::class, 'auth:sanctum'])->prefix('{tenant}')->group(function () {
    Route::prefix('projects')->group(function () {
        Route::controller(ProjectController::class)->group(function () {
            Route::get('/', 'getProjects');
            Route::get('/{project}', 'getSpecificProject');
            Route::post('/create', 'create');
            Route::post('/{project}/update', 'update')->can('update', Project::class);
            Route::post('/{project}/status-list', 'projectStatusList');
            Route::delete('/{project}/delete', 'delete')->can('delete', Project::class);
            Route::post('/{project}/{user}/add', 'addUser')->can('addUser', Project::class);
            Route::post('/{project}/{user}/assign-role', 'assignRole')->can('assignRole', Project::class);
            Route::post('/{project}/create-meeting', 'createMeeting')->can('createMeeting', Project::class);
            Route::post('/{project}/join-meeting', 'joinMeeting')->middleware('can:joinMeeting,project');
            Route::get('/{project}/get-meeting', 'getMeeting')->middleware('can:joinMeeting,project');
            Route::get('/{project}/get-attendee', 'getAttendee')->middleware('can:joinMeeting,project');
            Route::get('/{project}/list-attendees', 'listAttendees')->middleware('can:joinMeeting,project');
            Route::delete('/{project}/delete-meeting','deleteMeeting')->can('deleteMeeting', Project::class);
            Route::delete('/{project}/delete-attendee','deleteAttendee');
        });

        Route::controller(TaskController::class)->group(function () {
            Route::get('{project}/tasks', 'getTasks');
            Route::get('{project}/tasks/{task}', 'getSpecificTask');
            Route::post('{project}/tasks/create', 'create')->can('create', Task::class);
            Route::post('{project}/tasks/{task}/update', 'update')->can('update', Task::class);
            Route::delete('{project}/tasks/{task}/delete', 'delete')->can('delete', Task::class);
            Route::post('{project}/tasks/{task}/mark-complete', 'markComplete')->can('markComplete', Task::class);
        });

        Route::controller(CommentController::class)->group(function () {
            Route::get('{project}/tasks/{task}/comments/all', 'getComments');
            Route::get('{project}/tasks/{task}/comments/{comment}', 'getSpecificComment')->can('getSpecific', Comment::class);
            Route::post('{project}/tasks/{task}/comments/create', 'create')->can('create', Task::class);
            Route::post('{project}/tasks/{task}/comments/{comment}/update', 'update')->middleware('can:update,comment');
            Route::delete('{project}/tasks/{task}/comments/{comment}/delete', 'delete')->can('delete', Comment::class);
        });
    });

    Route::controller(MembersController::class)->prefix('/team/members')->group(function () {
        Route::get('/all', 'getTenantMembers');
    });

    Route::controller(SubscriptionController::class)->prefix('/subscription')->group(function () {
        Route::get('/plans', 'displayPlans');
        Route::post('/card/payment', 'cardPayment');
        Route::post('/card/payment/validate', 'validateCardPayment');
        Route::post('/{user}/cancel', 'cancelSubscription');
    });

    Route::controller(AccountController::class)->prefix('/account')->group(function () {
        Route::get('/', 'view');
        Route::post('/update', 'update');
        Route::delete('/delete', 'delete');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/accept-invitation', [MembersController::class, 'acceptInvitation']);
});
