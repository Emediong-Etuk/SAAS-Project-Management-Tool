<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\TenantMiddleware;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubscriptionController;

Route::webhooks('/flutterwave-webhook');


Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/signup', 'signup');
    Route::post('/signup/resend-token', 'getSignupToken')->middleware('throttle:otp');
    Route::post('/verify-email', 'verifyEmail');
    Route::post('/login', 'login');
    Route::post('/password/reset/get-token', 'getResetPasswordToken');
    Route::post('/password/reset', 'resetPassword');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::controller(TenantController::class)->prefix('tenants')->group(function () {
        Route::post('/create', 'create');
    });

    Route::controller(SubscriptionController::class)->prefix('/subscription')->group(function () {
        Route::get('/plans', 'displayPlans');
        Route::post('/customer/create','createCustomer');
        Route::post('/card/create','createCardMethod');
        Route::post('/card/payment','cardPayment');
        Route::post('/card/payment/pin/confirm','confirmCardPin');
        Route::post('/card/payment/validate','validateCardPayment');
    });
});




Route::middleware([TenantMiddleware::class, 'auth:sanctum'])->prefix('{tenant}')->group(function () {
    Route::prefix('projects')->group(function () {
        Route::controller(ProjectController::class)->group(function () {
            Route::get('/', 'getProjects');
            Route::post('/create', 'create');
            Route::get('/{project}', 'getSpecificProject');
            Route::post('/{project}/update', 'update');
            Route::post('/{project}/delete', 'delete');
        });

        Route::controller(TaskController::class)->group(function () {
            Route::get('{project}/tasks', 'getTasks');
            Route::get('{project}/tasks/{task}', 'getSpecificTask');
            Route::post('{project}/tasks/create', 'create');
            Route::post('{project}/tasks/{task}/update', 'update');
            Route::post('{project}/tasks/{task}/delete', 'delete');
        });

        Route::controller(CommentController::class)->group(function () {
            Route::get('{project}/tasks/{task}/comments/all', 'getComments');
            Route::get('{project}/tasks/{task}/comments/{comment}', 'getSpecificComment');
            Route::post('{project}/tasks/{task}/comments/create', 'create');
            Route::put('{project}/tasks/{task}/comments/{comment}/update', 'update');
            Route::delete('{project}/tasks/{task}/comments/{comment}/delete', 'delete');
        });
    });

    Route::controller(MembersController::class)->prefix('/team/members')->group(function () {
        Route::get('/all', 'getTenantMembers');
        Route::post('/invite', 'sendInvitation');
        Route::post('/{user}/remove', 'removeMember');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/accept-invitation', [MembersController::class, 'acceptInvitation']);
});
