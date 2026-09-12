<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PDO;
use Throwable;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--email : Send the compressed backup file to the configured admin email} {--retention=30 : Days of backups to retain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a compressed MySQL database backup (.sql.gz) with rolling retention and optional email dispatch.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting database backup...');

        $backupDir = storage_path('app/backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $dbName = config('database.connections.mysql.database', 'amiga');
        $filename = "backup_{$dbName}_{$timestamp}.sql.gz";
        $filePath = "{$backupDir}/{$filename}";

        try {
            $this->generateDump($filePath);

            $fileSize = File::size($filePath);
            $formattedSize = $this->formatBytes($fileSize);

            $this->info("Backup successfully generated: {$filename} ({$formattedSize})");
            Log::info("Database backup created: {$filename} ({$formattedSize})", [
                'path' => $filePath,
                'size' => $fileSize,
            ]);

            // 1. Enforce rolling retention
            $retentionDays = (int) $this->option('retention');
            $this->cleanupOldBackups($backupDir, $retentionDays);

            // 2. Optional: Email backup file
            $backupEmail = env('BACKUP_EMAIL');
            if ($this->option('email') || ! empty($backupEmail)) {
                $recipient = $backupEmail ?: env('MAIL_FROM_ADDRESS');
                if ($recipient && $fileSize < 20 * 1024 * 1024) { // Only email if under 20MB
                    $this->emailBackup($filePath, $filename, $formattedSize, $recipient);
                } elseif ($fileSize >= 20 * 1024 * 1024) {
                    $this->warn("Backup file ({$formattedSize}) is too large to email directly (>20MB). Saved to disk only.");
                }
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Database backup failed: ' . $e->getMessage());
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Stream database tables directly into a gzip compressed SQL file.
     * Uses streaming to keep PHP memory usage near zero regardless of DB size.
     */
    protected function generateDump(string $destinationPath): void
    {
        $gz = gzopen($destinationPath, 'wb9');
        if (! $gz) {
            throw new \RuntimeException("Unable to open {$destinationPath} for writing gzip archive.");
        }

        gzwrite($gz, "-- Amiga Travel Automated Database Backup\n");
        gzwrite($gz, "-- Generated: " . Carbon::now()->toIso8601String() . "\n");
        gzwrite($gz, "-- Host: " . config('database.connections.mysql.host') . "\n");
        gzwrite($gz, "-- Database: " . config('database.connections.mysql.database') . "\n\n");
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=0;\n");
        gzwrite($gz, "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n");
        gzwrite($gz, "SET time_zone = \"+00:00\";\n\n");

        /** @var PDO $pdo */
        $pdo = DB::connection()->getPdo();

        // Retrieve list of all tables
        $tables = [];
        $stmt = $pdo->query('SHOW TABLES');
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            // Write DROP TABLE & CREATE TABLE statement
            gzwrite($gz, "-- --------------------------------------------------------\n");
            gzwrite($gz, "-- Table structure for table `{$table}`\n");
            gzwrite($gz, "-- --------------------------------------------------------\n\n");
            gzwrite($gz, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(PDO::FETCH_NUM);
            gzwrite($gz, $createRow[1] . ";\n\n");

            // Dump data in chunks of 500 rows
            gzwrite($gz, "-- Dumping data for table `{$table}`\n\n");

            $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $totalRows = (int) $countStmt->fetchColumn();

            if ($totalRows > 0) {
                $offset = 0;
                $chunkSize = 500;

                while ($offset < $totalRows) {
                    $dataStmt = $pdo->query("SELECT * FROM `{$table}` LIMIT {$chunkSize} OFFSET {$offset}");
                    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

                    if (! empty($rows)) {
                        $columnNames = array_keys($rows[0]);
                        $escapedColumns = array_map(fn ($c) => "`" . str_replace("`", "``", $c) . "`", $columnNames);
                        $colList = implode(', ', $escapedColumns);

                        gzwrite($gz, "INSERT INTO `{$table}` ({$colList}) VALUES\n");

                        $valuesList = [];
                        foreach ($rows as $row) {
                            $escapedVals = [];
                            foreach ($row as $val) {
                                if ($val === null) {
                                    $escapedVals[] = 'NULL';
                                } elseif (is_numeric($val) && ! is_string($val)) {
                                    $escapedVals[] = $val;
                                } else {
                                    $escapedVals[] = $pdo->quote($val);
                                }
                            }
                            $valuesList[] = '(' . implode(', ', $escapedVals) . ')';
                        }

                        gzwrite($gz, implode(",\n", $valuesList) . ";\n\n");
                    }

                    $offset += $chunkSize;
                }
            }
        }

        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);
    }

    /**
     * Remove backups older than the retention period.
     */
    protected function cleanupOldBackups(string $dir, int $retentionDays): void
    {
        if ($retentionDays <= 0) {
            return;
        }

        $cutoff = Carbon::now()->subDays($retentionDays)->getTimestamp();
        $files = File::files($dir);
        $deleted = 0;

        foreach ($files as $file) {
            if ($file->getExtension() === 'gz' && $file->getMTime() < $cutoff) {
                File::delete($file->getRealPath());
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} backup(s) older than {$retentionDays} days.");
        }
    }

    /**
     * Dispatch backup file as an email attachment.
     */
    protected function emailBackup(string $filePath, string $filename, string $formattedSize, string $toEmail): void
    {
        try {
            Mail::raw(
                "Attached is the automated database backup for Amiga Travel.\n\nDate: " . Carbon::now()->toFormattedDateString() . "\nSize: {$formattedSize}\nFilename: {$filename}",
                function ($message) use ($filePath, $filename, $toEmail) {
                    $message->to($toEmail)
                        ->subject("Amiga Travel Database Backup - " . Carbon::now()->format('Y-m-d'))
                        ->attach($filePath, [
                            'as' => $filename,
                            'mime' => 'application/gzip',
                        ]);
                }
            );
            $this->info("Backup successfully emailed to {$toEmail}");
        } catch (Throwable $e) {
            $this->warn("Failed to email backup: " . $e->getMessage());
        }
    }

    /**
     * Format bytes into a human readable string.
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
