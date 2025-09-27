<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\Sanctum;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        JsonResource::withoutWrapping();

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        
        $this->configurePassword();
        $this->configureRateLimiting();

    }

    public function configurePassword(): void
    {
        Password::defaults(function (){
            return app()->environment('production')
            ? Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : Password::min(8);
        });
    }

    public function configureRateLimiting():void
    {
        RateLimiter::for('otp',function(Request $request){
            return Limit::perMinute(1)->by($request->ip());
        });
    }

   
}
