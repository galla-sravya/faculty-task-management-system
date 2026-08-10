<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:transfer-to-mysql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer all data from SQLite database to MySQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data transfer from SQLite to MySQL...');

        // Disable foreign key checks in MySQL
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=0;');

        // Get all tables from SQLite
        $tables = DB::connection('sqlite')->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        
        foreach ($tables as $table) {
            $tableName = $table->name;
            
            // Skip migrations table since we already ran migrations in MySQL
            if ($tableName === 'migrations') {
                continue;
            }

            $this->info("Transferring table: {$tableName}");
            
            // Clear existing data in MySQL table just in case
            DB::connection('mysql')->table($tableName)->truncate();

            // Fetch data from SQLite
            $rows = DB::connection('sqlite')->table($tableName)->get();
            
            // Insert data into MySQL in chunks to avoid memory issues
            $chunks = $rows->chunk(500);
            $totalImported = 0;
            
            foreach ($chunks as $chunk) {
                // Convert stdClass objects to arrays for insertion
                $insertData = json_decode(json_encode($chunk), true);
                
                if (!empty($insertData)) {
                    DB::connection('mysql')->table($tableName)->insert($insertData);
                    $totalImported += count($insertData);
                }
            }
            
            $this->line("  -> Imported {$totalImported} rows.");
        }

        // Re-enable foreign key checks
        DB::connection('mysql')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Data transfer completed successfully!');
    }
}
