<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Validation\Rules\Password;
use App\ThirdParty\SubscriptionPaymentApi;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Contracts\Interface\SubscriptionPaymentInterface;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(SubscriptionPaymentInterface::class, function(Application $app){
            return new SubscriptionPaymentApi;
        });
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
