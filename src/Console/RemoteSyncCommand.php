<?php

namespace Dcblogdev\DbSync\Console;

use Illuminate\Console\Command;

class RemoteSyncCommand extends Command
{
    protected $signature   = 'db:remote-sync {--T|test} {--F|filename=}';
    protected $description = 'Alias of the db:production-sync command.';

    public function handle(): bool
    {
        return $this->call('db:production-sync', [
            '--test'     => $this->option('test'),
            '--filename' => $this->option('filename'),
        ]);
    }
}
