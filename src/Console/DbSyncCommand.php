<?php

namespace Dcblogdev\DbSync\Console;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbSyncCommand extends Command
{
    protected $signature   = 'db:production-sync {--T|test} {--F|filename=} {--tables=} {--A|anonymize}';
    protected $description = 'Sync production database with local';

    public function handle(): bool
    {
        $inTest = $this->option('test');

        if (! in_array(config('app.env'), config('dbsync.environments'))) {
            $this->error('DB sync will only run on local and staging environments');

            return true;
        }

        $useSsh      = config('dbsync.useSsh');
        $sshUsername = config('dbsync.sshUsername');
        $sshPort     = config('dbsync.sshPort');
        $host        = config('dbsync.host');

        $mysqlHostName         = config('dbsync.mysqlHostName');
        $username              = config('dbsync.username');
        $database              = config('dbsync.database');
        $port                  = config('dbsync.port');
        $password              = config('dbsync.password');
        $ignore                = config('dbsync.ignore');
        $ignoreTables          = explode(',', $ignore);
        $removeFileAfterImport = config('dbsync.removeFileAfterImport');
        $fileName              = $this->option('filename') ?? config('dbsync.defaultFileName');
        $mysqldumpSkipTzUtc    = config('dbsync.mysqldumpSkipTzUtc') ? '--skip-tz-utc' : '';

        $targetConnection      = config('dbsync.targetConnection');
        $defaultConnection     = config('database.default');

        $defaultConnection = empty($targetConnection) ? $defaultConnection : $targetConnection;

        $localUsername  = config("database.connections.{$defaultConnection}.username");
        $localPassword  = config("database.connections.{$defaultConnection}.password");
        $localHostname  = config("database.connections.{$defaultConnection}.host");
        $localPort      = config("database.connections.{$defaultConnection}.port");
        $localDatabase  = config("database.connections.{$defaultConnection}.database");
        $localMysqlPath = config('dbsync.localMysqlPath');

        if (empty($host) || empty($username) || empty($database)) {
            $this->error('DB credentials not set, have you published the config and set ENV variables?');

            return true;
        }

        if ($inTest === false) {
            $ignoreString = null;

            $tablesToDump = '';

            if ($this->option('tables')) {
                $tables       = explode(',', $this->option('tables'));
                $tablesToDump = implode(' ', $tables);
            } else {
                foreach ($ignoreTables as $name) {
                    $ignoreString .= " --ignore-table=$database.$name";
                }
            }

            $useSsh && $this->info("\n".sprintf('Connecting to %s@%s on port %s', $sshUsername, $host, $sshPort)."\n");

            if (isset($tables) && count($tables) > 0) {
                $this->info("\n".'Syncing tables: '.implode(', ', $tables)."\n");
            } else {
                $this->info("\n".'Syncing database: '.$database."\n");
            }

            $bar = $this->output->createProgressBar(2);
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
            $bar->setMessage('Exporting...');
            $bar->start();

            if ($useSsh === true) {
                exec("ssh $sshUsername@$host -p$sshPort \"mysqldump --single-transaction --set-gtid-purged=OFF --port=$port --host=$mysqlHostName --user=$username --password=$password $database $tablesToDump $ignoreString 2>/dev/null\" > $fileName", $output);
            } else {
                $remoteCnf = tempnam(sys_get_temp_dir(), 'dbsync_');
                file_put_contents($remoteCnf, "[client]\npassword={$password}\n");
                exec("mysqldump --defaults-extra-file=$remoteCnf --single-transaction --set-gtid-purged=OFF --port=$port --host=$mysqlHostName --user=$username $database $tablesToDump $ignoreString $mysqldumpSkipTzUtc --column-statistics=0 > $fileName", $output);
                unlink($remoteCnf);
            }

            $bar->setMessage('Importing...');
            $bar->advance();

            $localCnf = tempnam(sys_get_temp_dir(), 'dbsync_');
            file_put_contents($localCnf, "[client]\npassword={$localPassword}\n");

            $command = $localPassword
                ? "$localMysqlPath --defaults-extra-file=$localCnf -u$localUsername -h$localHostname -P$localPort $localDatabase < $fileName"
                : "$localMysqlPath -u$localUsername -h$localHostname -P$localPort $localDatabase < $fileName";

            exec($command, $output);

            if ($localPassword) {
                unlink($localCnf);
            }

            $bar->setMessage('Done!');
            $bar->finish();
            $this->newLine();

            if ($removeFileAfterImport === true) {
                unlink($fileName);
            }

            // Anonymize data if --anonymize flag is set
            if ($this->option('anonymize')) {
                $this->anonymizeData($defaultConnection);
            }
        }

        $this->info("\nDB Synced");

        return true;
    }

    protected function anonymizeData(string $connection): void
    {
        $anonymizationConfig = config('dbsync.anonymize');

        if (empty($anonymizationConfig) || ! is_array($anonymizationConfig)) {
            $this->warn('No anonymization configuration found. Add tables to config/dbsync.php');

            return;
        }

        $this->newLine();
        $this->info('Anonymizing data...');
        $this->newLine();

        $faker       = Faker::create();
        $totalTables = count($anonymizationConfig);
        $bar         = $this->output->createProgressBar($totalTables);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $bar->setMessage('Starting anonymization...');
        $bar->start();

        foreach ($anonymizationConfig as $table => $columns) {
            $bar->setMessage("Anonymizing table: {$table}");

            try {
                // Get all records from the table
                $records = DB::connection($connection)->table($table)->get();

                foreach ($records as $record) {
                    $updates = [];

                    foreach ($columns as $column => $fakerMethod) {
                        // Handle special cases like bcrypt
                        if (str_starts_with($fakerMethod, 'bcrypt:')) {
                            $actualMethod     = str_replace('bcrypt:', '', $fakerMethod);
                            $updates[$column] = bcrypt($faker->$actualMethod);
                        } else {
                            $updates[$column] = $faker->$fakerMethod;
                        }
                    }

                    // Use all columns as WHERE conditions to uniquely identify the record
                    $where = [];
                    foreach ((array) $record as $col => $val) {
                        $where[$col] = $val;
                    }

                    // Update the record
                    DB::connection($connection)
                        ->table($table)
                        ->where($where)
                        ->limit(1)
                        ->update($updates);
                }

                $bar->advance();
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to anonymize table '{$table}': ".$e->getMessage());
                $bar->advance();
            }
        }

        $bar->setMessage('Anonymization complete!');
        $bar->finish();
        $this->newLine();
    }
}
