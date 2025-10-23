<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Plan::class => \App\Policies\PlanPolicy::class,
        \App\Models\PlanSubscription::class => \App\Policies\PlanSubscriptionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('access-plan-feature', function ($user, string $feature) {
            if (!$user instanceof User) {
                return false;
            }

            $admin = $user->admin;

            if (!$admin) {
                return false;
            }

            return $admin->hasFeature($feature);
        });
    }
}
