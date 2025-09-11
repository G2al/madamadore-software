<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Dress;
use App\Policies\DressPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Le policy dell'app.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Dress::class => DressPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
