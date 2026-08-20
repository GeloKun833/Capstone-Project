<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!in_array(Auth::user()->role_name, ['Admin'])) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
        $this->backupService = $backupService;
    }

    public function index()
    {
        $backups = $this->backupService->listBackups();
        return view('admin.backup.index', compact('backups'));
    }

    public function create()
    {
        try {
            $result = $this->backupService->createBackup();
            return redirect()->route('admin.backup.index')
                ->with('success', "Backup created: {$result['filename']} ({$result['size']})");
        } catch (\Exception $e) {
            return redirect()->route('admin.backup.index')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(string $filename): BinaryFileResponse
    {
        $path = $this->backupService->getBackupPath($filename);
        if (!file_exists($path)) {
            abort(404, 'Backup file not found.');
        }
        return response()->download($path);
    }

    public function destroy(string $filename)
    {
        if ($this->backupService->deleteBackup($filename)) {
            return redirect()->route('admin.backup.index')->with('success', 'Backup deleted.');
        }
        return redirect()->route('admin.backup.index')->with('error', 'Backup not found.');
    }
}
