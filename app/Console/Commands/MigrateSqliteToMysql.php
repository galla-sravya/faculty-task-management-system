<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateSqliteToMysql extends Command
{
    protected $signature = 'db:migrate-data-to-mysql';
    protected $description = 'Migrate data from SQLite to MySQL';

    public function handle()
    {
        $tables = [
            'users',
            'departments',
            'meetings',
            'tasks',
            'task_user',
            'meeting_user',
            'notification_logs',
            'task_documents',
            'jobs',
            'cache',
            'cache_locks'
        ];

        // Disable foreign key checks in MySQL
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            if (Schema::connection('sqlite')->hasTable($table) && Schema::connection('mysql')->hasTable($table)) {
                $this->info("Migrating {$table}...");
                
                // Clear the table in MySQL
                DB::connection('mysql')->table($table)->truncate();

                // Fetch data from SQLite
                $rows = DB::connection('sqlite')->table($table)->get()->map(function ($item) {
                    return (array) $item;
                })->toArray();

                // Insert into MySQL in chunks to avoid memory issues
                $chunks = array_chunk($rows, 500);
                foreach ($chunks as $chunk) {
                    DB::connection('mysql')->table($table)->insert($chunk);
                }

                $this->info("Migrated " . count($rows) . " rows for {$table}.");
            }
        }

        // Enable foreign key checks in MySQL
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Migration complete!');
    }
}