<?php

namespace Dcblogdev\DbSync\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Dcblogdev\DbSync\DbSyncServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            DbSyncServiceProvider::class,
        ];
    }
}