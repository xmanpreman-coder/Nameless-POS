<?php

namespace Modules\Setting\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

class DatabaseMergeService
{
    private $currentDb;
    private $backupDb;

    /**
     * Initialize merge service with backup file path
     */
    public function __construct($backupFilePath)
    {
        $this->currentDb = DB::connection()->getPdo();
        
        // Connect to backup database
        $backupPdo = new PDO('sqlite:' . $backupFilePath);
        $backupPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->backupDb = $backupPdo;
    }

    /**
     * Get all table names from backup database
     */
    public function getBackupTables()
    {
        try {
            $tables = $this->backupDb->query(
                "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
            )->fetchAll(PDO::FETCH_COLUMN);

            return $tables;
        } catch (\Exception $e) {
            Log::error('Failed to get backup tables', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get all table names from current database
     */
    public function getCurrentTables()
    {
        try {
            $tables = $this->currentDb->query(
                "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
            )->fetchAll(PDO::FETCH_COLUMN);

            return $tables;
        } catch (\Exception $e) {
            Log::error('Failed to get current tables', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Merge specific tables from backup to current database
     * Data dari backup ditambahkan ke current database (tidak menghapus existing data)
     */
    public function mergeTable($tableName)
    {
        try {
            // Get table structure dari backup
            $tableSchema = $this->backupDb->query(
                "PRAGMA table_info($tableName)"
            )->fetchAll(PDO::FETCH_ASSOC);

            if (empty($tableSchema)) {
                throw new \Exception("Table $tableName not found in backup");
            }

            // Get data dari backup table
            $backupData = $this->backupDb->query(
                "SELECT * FROM $tableName"
            )->fetchAll(PDO::FETCH_ASSOC);

            // Get primary key
            $pkInfo = $this->backupDb->query(
                "PRAGMA table_info($tableName) WHERE pk = 1"
            )->fetch(PDO::FETCH_ASSOC);

            $primaryKey = $pkInfo ? $pkInfo['name'] : null;

            // Insert data dengan conflict handling
            foreach ($backupData as $row) {
                try {
                    // Jika ada primary key, check jika data sudah exist di current database
                    if ($primaryKey && isset($row[$primaryKey])) {
                        $existing = DB::table($tableName)
                            ->where($primaryKey, $row[$primaryKey])
                            ->first();

                        // Skip jika data sudah ada (keep current data)
                        if ($existing) {
                            continue;
                        }
                    }

                    // Insert data baru
                    DB::table($tableName)->insert($row);
                } catch (\Exception $e) {
                    // Log warning tapi lanjut insert yang lain
                    Log::warning("Failed to insert row in $tableName", [
                        'row' => $row,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Successfully merged table: $tableName", [
                'rows_processed' => count($backupData)
            ]);

            return [
                'success' => true,
                'table' => $tableName,
                'rows_processed' => count($backupData)
            ];

        } catch (\Exception $e) {
            Log::error("Failed to merge table: $tableName", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Merge all tables from backup to current database
     */
    public function mergeAllTables()
    {
        try {
            $backupTables = $this->getBackupTables();
            $results = [];

            DB::beginTransaction();

            foreach ($backupTables as $table) {
                try {
                    $result = $this->mergeTable($table);
                    $results[$table] = $result;
                } catch (\Exception $e) {
                    Log::warning("Skipped table during merge: $table", ['error' => $e->getMessage()]);
                    // Continue dengan table lainnya
                }
            }

            DB::commit();

            return $results;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Merge all tables failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get statistics tentang perbedaan antara dua database
     */
    public function getDifferences()
    {
        try {
            $currentTables = $this->getCurrentTables();
            $backupTables = $this->getBackupTables();

            $differences = [
                'tables_only_in_current' => array_diff($currentTables, $backupTables),
                'tables_only_in_backup' => array_diff($backupTables, $currentTables),
                'tables_in_both' => array_intersect($currentTables, $backupTables),
                'row_counts' => []
            ];

            // Count rows untuk setiap table yang ada di kedua database
            foreach ($differences['tables_in_both'] as $table) {
                $currentCount = DB::table($table)->count();
                $backupCount = $this->backupDb->query("SELECT COUNT(*) FROM $table")->fetchColumn();

                $differences['row_counts'][$table] = [
                    'current' => $currentCount,
                    'backup' => $backupCount,
                    'difference' => $currentCount - $backupCount
                ];
            }

            return $differences;

        } catch (\Exception $e) {
            Log::error('Failed to get differences', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Restore specific tables from backup (destructive - replace current table)
     */
    public function restoreTable($tableName)
    {
        try {
            // Truncate current table
            DB::table($tableName)->truncate();

            // Get data dari backup
            $backupData = $this->backupDb->query(
                "SELECT * FROM $tableName"
            )->fetchAll(PDO::FETCH_ASSOC);

            // Insert semua data dari backup
            if (!empty($backupData)) {
                DB::table($tableName)->insert($backupData);
            }

            Log::info("Successfully restored table: $tableName", [
                'rows_restored' => count($backupData)
            ]);

            return [
                'success' => true,
                'table' => $tableName,
                'rows_restored' => count($backupData)
            ];

        } catch (\Exception $e) {
            Log::error("Failed to restore table: $tableName", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Close backup database connection
     */
    public function closeConnection()
    {
        $this->backupDb = null;
    }
}
