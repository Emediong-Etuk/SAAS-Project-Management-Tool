<?php

namespace App\Providers;

use App\Contracts\Interface\AWSChimeInterface;
use App\Contracts\Interface\SubscriptionPaymentInterface;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Policies\UserPolicy;
use App\ThirdParty\AWSChimeApi;
use App\ThirdParty\SubscriptionPaymentApi;
use Aws\ChimeSDKMeetings\ChimeSDKMeetingsClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
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
        $this->app->singleton(SubscriptionPaymentInterface::class, function (Application $app) {
            return new SubscriptionPaymentApi;
        });

        $this->app->singleton(AWSChimeInterface::class, function (Application $app) {
            return new AWSChimeApi($app->make(ChimeSDKMeetingsClient::class));
        });

        $this->app->singleton(ChimeSDKMeetingsClient::class, function (Application $app) {

            return new ChimeSDKMeetingsClient([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'credentials' => [
                    'key' => config('services.aws-chime.access_key_id'),
                    'secret' => config('services.aws-chime.secret_access_key'),
                ],
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Gate::policy(User::class, UserPolicy::class);

        JsonResource::withoutWrapping();

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        $this->configurePassword();
        $this->configureRateLimiting();
    }

    public function configurePassword(): void
    {
        Password::defaults(function () {
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

    public function configureRateLimiting(): void
    {
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(1)->by($request->ip());
        });
    }
}
