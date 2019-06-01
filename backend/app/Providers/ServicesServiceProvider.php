<?php

namespace App\Providers;

use App\Services\TodoList\TodoListService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

/**
 * Class ServicesServiceProvider
 *
 * @package App\Providers
 */
class ServicesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //Register system services as singletons
        $this->app->singleton(TodoListService::class);
        $this->app->singleton(UserService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
