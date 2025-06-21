<?php

namespace ClarionApp\RssTorrents;

use Illuminate\Console\Scheduling\Schedule;
use ClarionApp\Backend\ClarionPackageServiceProvider;
use ClarionApp\RssTorrents\Commands\CheckFeeds;

class RssTorrentsServiceProvider extends ClarionPackageServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if(!$this->app->routesAreCached())
        {
            require __DIR__.'/../routes/api.php';
        }

        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command('feeds:check')->everyMinute();
        });

    }

    public function register(): void
    {
        parent::register();
        $this->commands(
            [
                CheckFeeds::class,
            ]
        );
    }
}
