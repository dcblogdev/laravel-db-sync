<?php

namespace Dcblogdev\DbSync;

use Illuminate\Support\ServiceProvider;

class DbSyncServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishCommands();
        }
    }

    public function provides()
    {
        return [
            Console\DbSyncCommand::class,
            Console\RemoteSyncCommand::class,
        ];
    }

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../config/dbsync.php' => config_path('dbsync.php'),
        ], 'config');
    }

    protected function publishCommands()
    {
        $this->commands([
            Console\DbSyncCommand::class,
            Console\RemoteSyncCommand::class,
        ]);
    }
}
