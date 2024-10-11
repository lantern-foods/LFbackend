<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model-to-policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Register model-to-policy mappings here
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Set the expiration time for personal access tokens
        Passport::personalAccessTokensExpireIn(now()->addHours(8));

        // You can uncomment the following lines if you want to use client or password grants
        // Passport::tokensExpireIn(now()->addDays(15)); // Adjust as needed
        // Passport::refreshTokensExpireIn(now()->addDays(30)); // Adjust as needed
    }
}
