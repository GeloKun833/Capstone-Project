<?php

namespace App\Console\Commands;

use App\Services\DummyStudentService;
use Illuminate\Console\Command;

class DummyStudentsDeleteCommand extends Command
{
    protected $signature = 'dummy:students:delete
        {--dry-run : Only show what would be deleted}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Delete ONLY the dummy students (DUMMY-STU-*, @'.DummyStudentService::EMAIL_DOMAIN.'), their dummy user accounts and their own academic rows.';

    public function handle(DummyStudentService $service): int
    {
        $preview = $service->deletionPreview();
        $this->warn('Scope: students with admission ID '.DummyStudentService::ADMISSION_PREFIX.'* AND email @'.DummyStudentService::EMAIL_DOMAIN.', plus Student users @'.DummyStudentService::EMAIL_DOMAIN.'.');
        $this->warn('Real students, users, teachers, parents, sections, subjects and academic periods are not touched.');
        $this->table(['Table', 'Rows to delete'], collect($preview)->map(fn ($v, $k) => [$k, $v])->values()->all());

        if ($preview['students'] === 0 && $preview['users'] === 0) {
            $this->info('No dummy students found. Nothing to delete.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run only. Nothing was deleted.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Permanently delete these dummy records?')) {
            $this->warn('Cancelled. Nothing was deleted.');

            return self::FAILURE;
        }

        $result = $service->delete();
        $this->info('Deleted '.$result['students'].' dummy students and '.$result['users'].' dummy user accounts (with their related rows).');

        return self::SUCCESS;
    }
}
