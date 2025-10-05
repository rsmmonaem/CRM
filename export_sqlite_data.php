<?php
/**
 * SQLite Data Export Script for MySQL Migration
 * This script exports data from SQLite in a format compatible with MySQL
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SQLite Data Export Script ===\n\n";

try {
    // Get all tables
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

    $exportData = [];

    foreach ($tables as $table) {
        $tableName = $table->name;
        echo "Exporting table: {$tableName}\n";

        // Get table structure
        $columns = DB::select("PRAGMA table_info({$tableName})");

        // Get all data
        $data = DB::table($tableName)->get();

        $exportData[$tableName] = [
            'columns' => $columns,
            'data' => $data
        ];
    }

    // Generate MySQL-compatible SQL
    $sql = "-- MySQL Migration SQL\n";
    $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

    foreach ($exportData as $tableName => $tableData) {
        if (empty($tableData['data'])) {
            continue;
        }

        $sql .= "-- Data for table: {$tableName}\n";

        foreach ($tableData['data'] as $row) {
            $values = [];
            foreach ($row as $key => $value) {
                if ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . addslashes($value) . "'";
                }
            }

            $sql .= "INSERT INTO {$tableName} VALUES (" . implode(', ', $values) . ");\n";
        }

        $sql .= "\n";
    }

    // Save to file
    file_put_contents('database_mysql_import.sql', $sql);

    echo "\n✅ Export completed successfully!\n";
    echo "📁 Data saved to: database_mysql_import.sql\n";
    echo "📊 Tables exported: " . count($exportData) . "\n";

    // Show summary
    foreach ($exportData as $tableName => $tableData) {
        $count = count($tableData['data']);
        echo "   - {$tableName}: {$count} records\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Create MySQL database\n";
echo "2. Update .env file with MySQL credentials\n";
echo "3. Run: php artisan migrate:fresh\n";
echo "4. Import data: mysql -u username -p database_name < database_mysql_import.sql\n";
echo "5. Test your application\n";
