<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Modules\Setting\Services\DatabaseMergeService;

class DatabaseBackupController extends Controller
{
    /**
     * Show database backup page
     */
    public function index()
    {
        abort_if(Gate::denies('access_settings'), 403);

        return view('setting::database-backup.index');
    }

    /**
     * Download database backup
     */
    public function download()
    {
        try {
            abort_if(Gate::denies('access_settings'), 403);

            $dbPath = database_path('database.sqlite');
            
            if (!file_exists($dbPath)) {
                return redirect()->back()->with('error', 'Database file not found.');
            }

            $fileName = 'database_' . now()->format('Y-m-d_H-i-s') . '.sqlite';
            
            Log::info('Database backup downloaded', [
                'file_name' => $fileName,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);

            return response()->download($dbPath, $fileName);

        } catch (\Exception $e) {
            Log::error('Database backup download failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()->with('error', 'Failed to download backup: ' . $e->getMessage());
        }
    }

    /**
     * Full restore database from backup file (DESTRUCTIVE - replaces all data)
     */
    public function restore(Request $request)
    {
        try {
            abort_if(Gate::denies('access_settings'), 403);

            $request->validate([
                'backup_file' => 'required|file|mimetypes:application/octet-stream,application/x-sqlite3|max:104857600'
            ], [
                'backup_file.required' => 'Please select a backup file.',
                'backup_file.mimetypes' => 'File must be a SQLite database file (.sqlite).',
                'backup_file.max' => 'File size must not exceed 100MB.'
            ]);

            $dbPath = database_path('database.sqlite');
            $backupPath = $dbPath . '.restore_' . now()->format('Y-m-d_H-i-s') . '.bak';

            // Create backup of current database before restore
            if (file_exists($dbPath)) {
                copy($dbPath, $backupPath);
            }

            // Move uploaded file to database location
            $uploadedFile = $request->file('backup_file');
            $uploadedFile->move(dirname($dbPath), 'database.sqlite');

            Log::info('Database restored from backup (FULL RESTORE)', [
                'backup_created' => $backupPath,
                'restored_by' => auth()->id(),
                'user_name' => auth()->user()->name,
                'timestamp' => now()
            ]);

            return redirect()->back()->with('success', 'Database restored successfully (FULL RESTORE). Previous database backed up to: ' . basename($backupPath));

        } catch (\Exception $e) {
            Log::error('Database restore failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Failed to restore database: ' . $e->getMessage());
        }
    }

    /**
     * Merge data from backup file to current database (non-destructive)
     * Existing data in current database is preserved
     */
    public function merge(Request $request)
    {
        try {
            abort_if(Gate::denies('access_settings'), 403);

            $request->validate([
                'backup_file' => 'required|file|mimetypes:application/octet-stream,application/x-sqlite3|max:104857600'
            ], [
                'backup_file.required' => 'Please select a backup file.',
                'backup_file.mimetypes' => 'File must be a SQLite database file (.sqlite).',
                'backup_file.max' => 'File size must not exceed 100MB.'
            ]);

            // Store backup file temporarily
            $uploadedFile = $request->file('backup_file');
            $tempPath = storage_path('temp/backup_' . now()->timestamp . '.sqlite');
            @mkdir(dirname($tempPath), 0755, true);
            $uploadedFile->move(dirname($tempPath), basename($tempPath));

            try {
                $mergeService = new DatabaseMergeService($tempPath);
                $results = $mergeService->mergeAllTables();
                $mergeService->closeConnection();

                // Delete temp file
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }

                Log::info('Database merge completed', [
                    'merged_by' => auth()->id(),
                    'user_name' => auth()->user()->name,
                    'results' => $results,
                    'timestamp' => now()
                ]);

                $summary = "Merged " . count($results) . " tables successfully. ";
                $summary .= "Existing data was preserved. New data from backup was added.";

                return redirect()->back()->with('success', $summary);

            } catch (\Exception $e) {
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Database merge failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Failed to merge database: ' . $e->getMessage());
        }
    }

    /**
     * Show analysis of differences between current and backup database
     */
    public function analyzeBackup(Request $request)
    {
        try {
            abort_if(Gate::denies('access_settings'), 403);

            $request->validate([
                'backup_file' => 'required|file|mimetypes:application/octet-stream,application/x-sqlite3|max:104857600'
            ]);

            $uploadedFile = $request->file('backup_file');
            $tempPath = storage_path('temp/backup_' . now()->timestamp . '.sqlite');
            @mkdir(dirname($tempPath), 0755, true);
            $uploadedFile->move(dirname($tempPath), basename($tempPath));

            try {
                $mergeService = new DatabaseMergeService($tempPath);
                $differences = $mergeService->getDifferences();
                $mergeService->closeConnection();

                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }

                return response()->json([
                    'success' => true,
                    'data' => $differences
                ]);

            } catch (\Exception $e) {
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Database analysis failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Selective restore - restore specific tables only
     */
    public function selectiveRestore(Request $request)
    {
        try {
            abort_if(Gate::denies('access_settings'), 403);

            $request->validate([
                'backup_file' => 'required|file|mimetypes:application/octet-stream,application/x-sqlite3|max:104857600',
                'tables' => 'required|array|min:1',
                'tables.*' => 'required|string'
            ], [
                'backup_file.required' => 'Please select a backup file.',
                'tables.required' => 'Please select at least one table to restore.',
                'tables.*.required' => 'Invalid table name.'
            ]);

            $uploadedFile = $request->file('backup_file');
            $tempPath = storage_path('temp/backup_' . now()->timestamp . '.sqlite');
            @mkdir(dirname($tempPath), 0755, true);
            $uploadedFile->move(dirname($tempPath), basename($tempPath));

            try {
                $mergeService = new DatabaseMergeService($tempPath);
                $results = [];

                foreach ($request->tables as $tableName) {
                    try {
                        $result = $mergeService->restoreTable($tableName);
                        $results[$tableName] = $result;
                    } catch (\Exception $e) {
                        $results[$tableName] = [
                            'success' => false,
                            'error' => $e->getMessage()
                        ];
                    }
                }

                $mergeService->closeConnection();

                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }

                Log::info('Selective table restore completed', [
                    'restored_by' => auth()->id(),
                    'user_name' => auth()->user()->name,
                    'tables' => $request->tables,
                    'results' => $results,
                    'timestamp' => now()
                ]);

                return redirect()->back()->with('success', 'Selective restore completed. ' . count($request->tables) . ' table(s) restored.');

            } catch (\Exception $e) {
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Selective restore failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Failed to perform selective restore: ' . $e->getMessage());
        }
    }
}
