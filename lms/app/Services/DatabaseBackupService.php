<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    protected string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        if (!File::isDirectory($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function listBackups(): array
    {
        $files = File::files($this->backupPath);

        return collect($files)
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'date' => date('M d, Y H:i:s', $file->getMTime()),
                'timestamp' => $file->getMTime(),
            ])
            ->sortByDesc('timestamp')
            ->values()
            ->all();
    }

    public function createBackup(): array
    {
        $connection = config('database.default');
        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

        if ($connection === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if (!File::exists($dbPath)) {
                throw new \RuntimeException('SQLite database file not found.');
            }
            File::copy($dbPath, $filepath);
        } else {
            $this->createMysqlBackup($filepath);
        }

        return [
            'filename' => $filename,
            'path' => $filepath,
            'size' => $this->formatBytes(File::size($filepath)),
        ];
    }

    public function deleteBackup(string $filename): bool
    {
        $path = $this->backupPath . DIRECTORY_SEPARATOR . basename($filename);
        if (!File::exists($path)) {
            return false;
        }

        return File::delete($path);
    }

    public function getBackupPath(string $filename): string
    {
        return $this->backupPath . DIRECTORY_SEPARATOR . basename($filename);
    }

    protected function createMysqlBackup(string $filepath): void
    {
        $config = config('database.connections.' . config('database.default'));
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'] ?? '';

        $mysqldump = $this->findMysqldump();
        if ($mysqldump) {
            $process = new Process([
                $mysqldump,
                '--host=' . $host,
                '--port=' . $port,
                '--user=' . $username,
                '--password=' . $password,
                '--single-transaction',
                '--routines',
                '--triggers',
                $database,
            ]);
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException('mysqldump failed: ' . $process->getErrorOutput());
            }

            File::put($filepath, $process->getOutput());
            return;
        }

        $this->createPhpBackup($filepath);
    }

    protected function createPhpBackup(string $filepath): void
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.' . config('database.default') . '.database');
        $key = 'Tables_in_' . $dbName;
        $sql = "-- LMS Database Backup\n-- Generated: " . now()->toDateTimeString() . "\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$key;
            $create = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $createKey = 'Create Table';
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $create->$createKey . ";\n\n";

            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = collect((array) $row)->map(function ($value) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    return "'" . addslashes((string) $value) . "'";
                })->implode(', ');

                $columns = implode('`, `', array_keys((array) $row));
                $sql .= "INSERT INTO `{$tableName}` (`{$columns}`) VALUES ({$values});\n";
            }
            $sql .= "\n";
        }

        File::put($filepath, $sql);
    }

    protected function findMysqldump(): ?string
    {
        $candidates = ['mysqldump', 'C:\\xampp\\mysql\\bin\\mysqldump.exe', 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe'];
        foreach ($candidates as $cmd) {
            $process = new Process([$cmd, '--version']);
            $process->run();
            if ($process->isSuccessful()) {
                return $cmd;
            }
        }
        return null;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
