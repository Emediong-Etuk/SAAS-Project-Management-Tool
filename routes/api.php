<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TenantController;
use App\Http\Middleware\TenantMiddleware;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::webhooks('/flutterwave-webhook');

Route::controller(AdminController::class)->prefix('admin')->group(function () {
    Route::post('/login', 'login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/view', 'view');
        Route::delete('/user', 'delete');
    });
});

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/signup', 'signup');
    Route::post('/signup/resend-token', 'signup')->middleware('throttle:otp');
    Route::post('/verify-email', 'verifyEmail');
    Route::post('/login', 'login');
    Route::post('/password/reset/get-token', 'getResetPasswordToken');
    Route::post('/password/reset', 'resetPassword');
    Route::prefix('oauth')->group(function () {
        Route::get('/github/redirect', 'githubRedirect');
        Route::get('/github/callback', 'githubCallback');
        Route::get('/google/redirect', 'googleRedirect');
        Route::get('/google/callback', 'googleCallback');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', 'logout');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(TenantController::class)->prefix('tenants')->group(function () {
        Route::get('/dashboard', 'dashboard');
        Route::post('/create', 'create');
        Route::prefix('/{tenant}')->group(function () {
            Route::post('/upload-company-logo', 'uploadCompanyLogo');
            Route::post('/update', 'update')->can('update', Tenant::class)->name('tenant.update');
            Route::delete('/delete', 'delete')->can('delete', Tenant::class);
            Route::post('/invite', 'sendInvitation')->can('invite', Tenant::class)->name('tenant.invite');
            Route::post('/{user}/remove', 'removeMember')->can('removeMember', Tenant::class)->name('tenant.removeMember');
        });
    });
});

Route::middleware([TenantMiddleware::class, 'auth:sanctum'])->prefix('{tenant}')->group(function () {
    Route::prefix('projects')->group(function () {
        Route::controller(ProjectController::class)->group(function () {
            Route::get('/', 'getProjects')->name('projects.list');
            Route::get('/{project}', 'getSpecificProject')->name('projects.specific');
            Route::post('/create', 'create')->name('projects.create');
            Route::post('/{project}/update', 'update')->can('update', Project::class)->name('projects.update');
            Route::get('/{project}/status-list', 'projectStatusList')->name('projects.statusList');
            Route::delete('/{project}/delete', 'delete')->can('delete', Project::class)->name('projects.delete');
            Route::post('/{project}/{user}/add', 'addUser')->can('addUser', Project::class)->name('projects.addUser');
            Route::post('/{project}/{user}/remove', 'removeUser')->can('removeUser', Project::class)->name('projects.removeUser');
            Route::post('/{project}/{user}/assign-role', 'assignRole')->middleware('can:assignRole,project,user')->name('projects.assignRole');
            Route::post('/{project}/create-meeting', 'createMeeting')->can('createMeeting', Project::class)->name('projects.createMeeting');
            Route::post('/{project}/join-meeting', 'joinMeeting')->middleware('can:joinMeeting,project')->name('projects.joinMeeting');
            Route::get('/{project}/get-meeting', 'getMeeting')->middleware('can:joinMeeting,project')->name('projects.getMeeting');
            Route::get('/{project}/get-attendee', 'getAttendee')->middleware('can:joinMeeting,project')->name('projects.getAttendee');
            Route::get('/{project}/list-attendees', 'listAttendees')->middleware('can:joinMeeting,project')->name('projects.listAttendees');
            Route::delete('/{project}/delete-meeting', 'deleteMeeting')->can('deleteMeeting', Project::class)->name('projects.deleteMeeting');
            Route::delete('/{project}/delete-attendee', 'deleteAttendee')->name('projects.deleteAttendee');
            Route::post('/{project}/update-status', 'updateProjectStatus')->can('updateStatus', Project::class)->name('projects.updateStatus');
        });

        Route::controller(TaskController::class)->group(function () {
            Route::get('{project}/tasks', 'getTasks')->name('tasks.list');
            Route::get('{project}/tasks/{task}', 'getSpecificTask')->name('tasks.specific');
            Route::post('{project}/tasks/create', 'create')->can('create', Task::class)->name('tasks.create');
            Route::post('{project}/tasks/{task}/update', 'update')->can('update', Task::class)->name('tasks.update');
            Route::delete('{project}/tasks/{task}/delete', 'delete')->can('delete', Task::class)->name('tasks.delete');
            Route::post('{project}/tasks/{task}/mark-complete', 'markComplete')->can('markComplete', Task::class)->name('tasks.markComplete');
            Route::post('{project}/tasks/{task}/submit', 'submitTask')->name('tasks.submitTask');
            Route::post('{project}/tasks/{task}/view-submissions', 'viewSubmissions')->name('tasks.viewSubmissions');
            Route::post('{project}/tasks/{task}/view-user-submissions', 'viewUserSubmissions')->name('tasks.viewUserSubmissions');
            Route::get('{project}/tasks/{task}/{submittedTask}/download-files', 'downloadSubmissionFile')->can('downloadSubmissions', Task::class)->name('tasks.downloadSubmissionFile');
            Route::post('{project}/search', 'searchTask')->name('projects.searchTask');
            Route::post('{project}/tasks/{task}/{user}/assign', 'assignTask')->can('assignTask', Task::class)->name('tasks.assignTask');
            Route::post('{project}/tasks/{task}/search-user', 'searchUser')->name('tasks.searchUser');
            Route::post('{project}/tasks/{task}/{user}/remove-from-task', 'removeUserFromTask')->name('tasks.removeUserFromTask');
            Route::get('{project}/tasks/{task}/get-users-for-task', 'getUsersAssignedToTask')->name('tasks.getUsersAssignedToTask');
        });

        Route::controller(CommentController::class)->group(function () {
            Route::get('{project}/tasks/{task}/comments/all', 'getComments')->name('comments.get');
            Route::get('{project}/tasks/{task}/comments/{comment}', 'getSpecificComment')->name('comments.getSpecific');
            Route::post('{project}/tasks/{task}/comments/create', 'create')->name('comments.create');
            Route::post('{project}/tasks/{task}/comments/{comment}/update', 'update')->name('comments.update');
            Route::delete('{project}/tasks/{task}/comments/{comment}/delete', 'delete')->can('delete', 'comment')->name('comments.delete');
        });
    });

    Route::controller(NotificationController::class)->prefix('/notifications')->group(function () {
        Route::get('/view', 'view')->name('notifications.get');
        Route::post('/mark-as-read', 'markasRead')->name('notifications.markRead');
        Route::get('/unread', 'getUnreadNotifications')->name('notifications.unread');
        Route::delete('/delete-all', 'deleteAllNotifications')->name('notifications.deleteAll');
        Route::delete('/delete/{message}', 'deleteNotification')->name('notifications.delete');
        Route::delete('/delete-selected', 'deleteSelectedNotification')->name('notifications.deleteSelected');
        Route::get('/{message}/get', 'getSpecificNotification')->name('notifications.get-specific');
    });

    Route::controller(MembersController::class)->prefix('/team/members')->group(function () {
        Route::get('/all', 'getTenantMembers')->name('members.getAll');
    });

    Route::controller(SubscriptionController::class)->prefix('/subscription')->group(function () {
        Route::get('/plans', 'displayPlans')->name('subscription.getPlans');
        Route::post('/card/payment', 'cardPayment')->name('subscription.cardPayment');
        Route::post('/card/payment/validate', 'validateCardPayment')->name('subscription.validatePayment');
        Route::post('/cancel', 'cancelSubscription')->name('subscription.cancelSubscription');
        Route::get('/status', 'getSubscriptionStatus')->name('subscription.getSubscriptionStatus');
    });

    Route::controller(AccountController::class)->prefix('/account')->group(function () {
        Route::get('/{user:username}', 'view')->name('account.get');
        Route::post('/{user:username}/update', 'update')->can('update', 'user')->name('account.update');
        Route::post('/{user:username}/verify-email', 'verifyEmail')->can('update', 'user');
        Route::delete('/{user:username}/delete', 'delete')->can('delete', 'user')->name('account.delete');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/accept-invitation', [MembersController::class, 'acceptInvitation'])->name('members.acceptInvitation');
});


Route::get('/fix-sessions', function () {
    try {
        DB::statement('DROP TABLE IF EXISTS sessions');
        DB::statement('
            CREATE TABLE sessions (
                id VARCHAR(255) NOT NULL PRIMARY KEY,
                user_id UUID NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                payload TEXT NOT NULL,
                last_activity INTEGER NOT NULL
            )
        ');
        DB::statement('CREATE INDEX sessions_user_id_index ON sessions (user_id)');
        DB::statement('CREATE INDEX sessions_last_activity_index ON sessions (last_activity)');
        return response()->json(['message' => 'sessions table fixed!']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});
