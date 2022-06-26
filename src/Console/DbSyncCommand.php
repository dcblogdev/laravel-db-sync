<?php

namespace Dcblogdev\DbSync\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbSyncCommand extends Command
{
    protected $signature   = 'db:production-sync {--test}';
    protected $description = 'Sync production database with local';

    public function handle(): bool
    {
        $inTest = $this->option('test');

        if (!in_array(config('app.env'), ['local', 'staging'])) {
            $this->error('DB sync will only run on local and staging environments');
            return true;
        }

        $host        = config('dbsync.host');
        $useSsh      = config('dbsync.useSsh');
        $sshUsername = config('dbsync.sshUsername');
        $sshPort     = config('dbsync.sshPort');

        $username              = config('dbsync.username');
        $database              = config('dbsync.database');
        $password              = config('dbsync.password');
        $ignore                = config('dbsync.ignore');
        $ignoreTables          = explode(',', $ignore);
        $ImportSqlFile         = config('dbdync.ImportSqlFile');
        $removeFileAfterImport = config('dbdync.removeFileAfterImport');

        if (empty($host) || empty($username) || empty($database)) {
            $this->error("DB credentials not set, have you published the config and set ENV variables?");
            return true;
        }

        if ($inTest === false) {

            $ignoreString = null;
            foreach ($ignoreTables as $name) {
                $ignoreString .= " --ignore-table=$database.$name";
            }

            if ($useSsh === true) {
                exec("ssh $sshUsername@$host -p$sshPort mysqldump -u $username -p$password $database $ignoreString > file.sql", $output);
            } else {
                exec("mysqldump -h$host -u $username -p$password $database $ignoreString --column-statistics=0 > file.sql", $output);
            }

            $this->comment(implode(PHP_EOL, $output));

            if ($ImportSqlFile === true) {
                DB::unprepared(file_get_contents(base_path('file.sql')));
            }

            if ($removeFileAfterImport === true) {
                unlink('file.sql');
            }
        }

        $this->comment("DB Synced");

        return true;
    }
}