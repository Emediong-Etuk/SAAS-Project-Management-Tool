<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TenantController;
use App\Http\Middleware\TenantMiddleware;

Route::controller(AuthController::class)->prefix('auth')->group(function(){
    Route::post('/signup','signup');
    Route::post('/signup/resend-token','getSignupToken')->middleware('throttle:otp');
    Route::post('/verify-email','verifyEmail');
    Route::post('/login','login');
    Route::post('/password/reset/get-token','getResetPasswordToken');
    Route::post('/password/reset','resetPassword');

});

Route::middleware('auth:sanctum')->group(function(){
    Route::controller(TenantController::class)->prefix('tenants')->group(function(){
        Route::post('/create','create');
    });
});

Route::middleware([TenantMiddleware::class,'auth:sanctum'])->prefix('{tenant}')->group(function(){

    Route::controller(ProjectController::class)->prefix('projects')->group(function(){
            Route::get('/','getProjects');
            Route::post('/create','create');
            Route::get('/{project}','getSpecificProject');
            Route::post('/{project}/update','update');
            Route::post('/{project}/delete','delete');
        });

        Route::controller(TaskController::class)->prefix('projects')->group(function(){
            Route::get('{project}/tasks','getTasks');
            Route::get('{project}/tasks/{task}','getSpecificTask');
            Route::post('{project}/tasks/create','create');
            Route::post('{project}/tasks/{task}/update','update');
            Route::post('{project}/tasks/{task}/delete','delete');
        });

});