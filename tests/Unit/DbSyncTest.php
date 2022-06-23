<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

//TODO pass test
test('demo', function () {
    $this->artisan('db:production-sync')
        ->expectsOutput('DB credentials not set, have you published the config and set ENV variables?');
});