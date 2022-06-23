<?php

namespace Dcblogdev\DbSync\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbSyncCommand extends Command
{
    protected $signature   = 'db:production-sync';
    protected $description = 'Sync production database with local';

    public function handle()
    {
        if (app()->environment(['local', 'staging'])) {
            $host         = config('dbsync.host');
            $username     = config('dbsync.username');
            $database     = config('dbsync.database');
            $password     = config('dbsync.password');
            $ignore       = config('dbsync.ignore');
            $ignoreTables = explode(',', $ignore);

            if (empty($host) || empty($username) || empty($database)) {
                $this->error("DB credentials not set, have you published the config and set ENV variables?");
                return true;
            }

            $ignoreString = null;
            foreach ($ignoreTables as $name) {
                $ignoreString .= " --ignore-table=$database.$name";
            }

            // execute command
            exec("mysqldump -h $host -u $username -p$password $database --column-statistics=0 $ignoreString > file.sql",
                $output);
            $this->comment(implode(PHP_EOL, $output));

            DB::unprepared(file_get_contents(base_path('file.sql')));

            //delete files
            unlink('file.sql');

            $this->comment("DB Synced");
        }
    }
}