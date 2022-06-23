<?php

namespace Dcblogdev\DbSync\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use TopPackages\DbSync\DbSyncServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            DbSyncServiceProvider::class,
        ];
    }
}