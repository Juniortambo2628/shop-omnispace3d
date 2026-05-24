<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;

class SetupDatabase extends Command
{
    protected $signature = 'db:setup';

    protected $description = 'Create OmniShop database tables from database/schema.sql';

    public function handle(): int
    {
        $schemaPath = base_path('database/schema.sql');

        if (! file_exists($schemaPath)) {
            $this->error('Schema file not found: database/schema.sql');

            return self::FAILURE;
        }

        $host = env('DB_HOST', '127.0.0.1');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');
        $database = env('DB_NAME', 'omnishop_db');

        try {
            $pdo = new PDO(
                "mysql:host={$host};charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            $this->error('Could not connect to MySQL: ' . $e->getMessage());

            return self::FAILURE;
        }

        $sql = file_get_contents($schemaPath);
        $statements = array_filter(
            array_map('trim', preg_split('/;\s*\n/', $sql)),
            fn (string $statement) => $statement !== '' && ! str_starts_with($statement, '--')
        );

        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }

        $this->info("Database schema applied to {$database}.");

        return self::SUCCESS;
    }
}
