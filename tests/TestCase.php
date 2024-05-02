<?php

namespace Dcblogdev\DbSync\Tests;

use Dcblogdev\DbSync\DbSyncServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            DbSyncServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('dbsync.useSsh', false);
        $app['config']->set('dbsync.host', '');
        $app['config']->set('dbsync.sshUsername', '');
        $app['config']->set('dbsync.sshPort', 22);
        $app['config']->set('dbsync.username', '');
        $app['config']->set('dbsync.mysqlHostName', 'localhost');
        $app['config']->set('dbsync.port', 3306);
        $app['config']->set('dbsync.database', '');
        $app['config']->set('dbsync.password', '');
        $app['config']->set('dbsync.ignore', '');
        $app['config']->set('dbsync.importSqlFile', true);
        $app['config']->set('dbsync.removeFileAfterImport', true);
        $app['config']->set('dbsync.defaultFileName', 'file.sql');
        $app['config']->set('dbsync.targetConnection', 'mysql');
        $app['config']->set('dbsync.mysqldumpSkipTzUtc', false);
        $app['config']->set('dbsync.localMysqlPath', '/usr/local/bin/mysql');
        $app['config']->set('dbsync.environments', [
            'local',
            'staging'
        ]);
    }
}
