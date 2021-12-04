<?php

namespace Illuminate\Vendor\HepplerDotNet\Providers;

use HepplerDotNet\LaravelConfigOverride\Models\Config;
use HepplerDotNet\LaravelConfigOverride\Models\Group;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class ConfigurationProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load Migrations
        $this->loadMigrationsFrom(__DIR__.'/../../../../database/migrations');

        // Retrieve Settings from DB and publish them to global config() helper
        if (!Schema::hasTable('config_groups')) {
            Artisan::call('migrate');
        }
        if (!Schema::hasTable('config_entries')) {
            Artisan::call('migrate');
        }

        $config = Cache::rememberForever('laravel-configuration', function () {
            return (new Config(Group::getRootGroups()->get()))->get();
        });

        config(array_replace_recursive(config()->all(), $config));
    }
}
