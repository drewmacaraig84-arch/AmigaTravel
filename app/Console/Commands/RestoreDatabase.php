<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PDO;
use Throwable;

class RestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore 
                            {file? : The backup filename (e.g. backup_amiga_2026-09-12_212533.sql.gz) or full path}
                            {--latest : Automatically restore the most recent backup}
                            {--force : Force restore without interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore the MySQL database from a compressed backup file (.sql.gz or .sql).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        $targetPath = $this->argument('file');

        // 1. Resolve target backup file
        if ($this->option('latest')) {
            $latest = $this->getLatestBackup($backupDir);
            if (! $latest) {
                $this->error("No backup files found in {$backupDir}.");
                return self::FAILURE;
            }
            $targetPath = $latest;
        } elseif (empty($targetPath)) {
            $files = $this->getAllBackups($backupDir);
            if (empty($files)) {
                $this->error("No backup files found in {$backupDir}.");
                return self::FAILURE;
            }

            $options = [];
            foreach ($files as $file) {
                $size = round(File::size($file) / 1024, 1);
                $date = date('Y-m-d H:i:s', File::lastModified($file));
                $options[] = basename($file) . " ({$size} KB - {$date})";
            }

            $selected = $this->choice('Select a backup to restore:', $options, 0);
            $index = array_search($selected, $options, true);
            $targetPath = $files[$index];
        } else {
            // Check if user passed a filename inside the default backup folder
            if (! File::exists($targetPath)) {
                $inDir = $backupDir . DIRECTORY_SEPARATOR . $targetPath;
                if (File::exists($inDir)) {
                    $targetPath = $inDir;
                } else {
                    $this->error("Backup file not found: {$targetPath}");
                    return self::FAILURE;
                }
            }
        }

        $filename = basename($targetPath);
        $fileSizeFormatted = $this->formatBytes(File::size($targetPath));

        $this->newLine();
        $this->warn("===============================================================");
        $this->warn(" TARGET BACKUP: {$filename} ({$fileSizeFormatted})");
        $this->warn(" DATABASE:      " . config('database.connections.mysql.database'));
        $this->warn("===============================================================");
        $this->newLine();

        // 2. Safety confirmation
        if (! $this->option('force')) {
            $confirmed = $this->confirm('WARNING: This will DROP and REPLACE current database tables with data from the backup. Are you sure you want to proceed?', false);
            if (! $confirmed) {
                $this->info('Restore aborted by user.');
                return self::SUCCESS;
            }
        }

        $this->info("Restoring database from {$filename}...");
        $startTime = microtime(true);

        try {
            $statementsExecuted = $this->executeRestore($targetPath);
            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info("===============================================================");
            $this->info(" SUCCESS: Database restored successfully in {$duration}s!");
            $this->info(" Total SQL statements executed: {$statementsExecuted}");
            $this->info("===============================================================");
            $this->newLine();

            Log::info("Database successfully restored from backup", [
                'filename' => $filename,
                'path' => $targetPath,
                'duration_seconds' => $duration,
                'statements' => $statementsExecuted,
            ]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Restore failed: " . $e->getMessage());
            Log::error("Database restore failed", [
                'file' => $targetPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Read and execute SQL statements from a .sql or .sql.gz file.
     */
    protected function executeRestore(string $filePath): int
    {
        $isGzip = str_ends_with(strtolower($filePath), '.gz');

        /** @var PDO $pdo */
        $pdo = DB::connection()->getPdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');

        $count = 0;
        $currentQuery = '';

        if ($isGzip) {
            $gz = gzopen($filePath, 'rb');
            if (! $gz) {
                throw new \RuntimeException("Could not open gzip archive: {$filePath}");
            }

            while (! gzeof($gz)) {
                $line = gzgets($gz, 65536);
                if ($line === false) break;

                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                $currentQuery .= $line;
                if (str_ends_with($trimmed, ';')) {
                    $pdo->exec($currentQuery);
                    $count++;
                    $currentQuery = '';
                }
            }
            gzclose($gz);
        } else {
            $handle = fopen($filePath, 'r');
            if (! $handle) {
                throw new \RuntimeException("Could not open SQL file: {$filePath}");
            }

            while (($line = fgets($handle, 65536)) !== false) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                $currentQuery .= $line;
                if (str_ends_with($trimmed, ';')) {
                    $pdo->exec($currentQuery);
                    $count++;
                    $currentQuery = '';
                }
            }
            fclose($handle);
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');

        return $count;
    }

    /**
     * Get the latest backup file in the directory.
     */
    protected function getLatestBackup(string $dir): ?string
    {
        $backups = $this->getAllBackups($dir);
        return $backups[0] ?? null;
    }

    /**
     * Get all backups in the directory sorted newest first.
     */
    protected function getAllBackups(string $dir): array
    {
        if (! File::exists($dir)) {
            return [];
        }

        $files = File::files($dir);
        $backups = [];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if ($ext === 'gz' || $ext === 'sql') {
                $backups[$file->getRealPath()] = $file->getMTime();
            }
        }

        arsort($backups);

        return array_keys($backups);
    }

    /**
     * Format bytes to human readable string.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
