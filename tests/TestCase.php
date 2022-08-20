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
}
