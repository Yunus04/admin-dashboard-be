<?php

namespace App\Providers;

use App\Repositories\Interfaces\AuthRepositoryInterface;
use App\Repositories\Interfaces\MerchantRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\AuthRepository;
use App\Repositories\MerchantRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Auth Repository Interface
        $this->app->bind(
            AuthRepositoryInterface::class,
            AuthRepository::class
        );

        // Bind User Repository Interface
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        // Bind Merchant Repository Interface
        $this->app->bind(
            MerchantRepositoryInterface::class,
            MerchantRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

