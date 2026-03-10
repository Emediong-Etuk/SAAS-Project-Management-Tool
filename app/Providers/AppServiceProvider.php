<?php

namespace App\Providers;



use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use App\ThirdParty\AWSChimeApi;
use App\Models\PersonalAccessToken;
use App\Contracts\Interface\AWSChimeInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Validation\Rules\Password;
use App\ThirdParty\SubscriptionPaymentApi;
use Illuminate\Support\Facades\RateLimiter;
use Aws\ChimeSDKMeetings\ChimeSDKMeetingsClient;
use Illuminate\Contracts\Foundation\Application;
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
        $this->app->singleton(SubscriptionPaymentInterface::class, function (Application $app) {
            return new SubscriptionPaymentApi;
        });

        $this->app->singleton(AWSChimeInterface::class, function (Application $app) {
            return new AWSChimeApi($app->make(ChimeSDKMeetingsClient::class));
        });

        $this->app->singleton(ChimeSDKMeetingsClient::class, function (Application $app) {


            return new ChimeSDKMeetingsClient([
                'version' => 'latest',
                'region'  => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'credentials' => [
                    'key'    => config('services.aws-chime.access_key_id'),
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
