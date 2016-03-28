<?php

namespace NetForceWS\Database;

class MigrationServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.migrate.make', function ($app) {

            $creator = $app['migration.creator'];
            $composer = $app['composer'];

            return new \NetForceWS\Database\Migration\MigrateMakeCommand($creator, $composer);
        });

        $this->commands(['command.migrate.make']);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.migrate.make'];
    }
}