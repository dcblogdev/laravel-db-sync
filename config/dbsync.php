<?php

return [
    'host'     => env('REMOTE_DATABASE_HOST', ''),
    'username' => env('REMOTE_DATABASE_USERNAME', ''),
    'database' => env('REMOTE_DATABASE_NAME', ''),
    'password' => env('REMOTE_DATABASE_PASSWORD', ''),
    'ignore'   => env('REMOTE_DATABASE_IGNORE_TABLES', '')
];
