<?php

namespace App\Console\Commands;

use App\Services\DummyStudentService;
use Illuminate\Console\Command;

class DummyStudentsVerifyCommand extends Command
{
    protected $signature = 'dummy:students:verify';

    protected $description = 'Read-only validation of the 400 dummy students (profiles, accounts, sections, enrollments, grades, attendance, alerts).';

    public function handle(DummyStudentService $service): int
    {
        $result = $service->verify();

        if ($result['academic_year']) {
            $this->info('Academic year: '.$result['academic_year']->name.' | Semester: '.optional($result['semester'])->name);
        }

        $this->table(
            ['Check', 'Result', 'Detail'],
            array_map(fn ($c) => [$c['check'], $c['passed'] ? 'PASS' : 'FAIL', $c['detail']], $result['checks'])
        );

        if (! empty($result['section_distribution'])) {
            $this->table(['Section ID', 'Section', 'Grade', 'Dummy students'], array_map('array_values', $result['section_distribution']));
        }

        if (! empty($result['low_performers'])) {
            $this->info('Low-performing dummy students:');
            $this->table(
                ['Admission ID', 'Name', 'Email', 'Grade', 'Section', 'General average', 'Lowest subject'],
                array_map('array_values', $result['low_performers'])
            );
        }

        $failed = collect($result['checks'])->where('passed', false)->count();
        $failed === 0 ? $this->info('All checks passed.') : $this->error($failed.' check(s) failed.');

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
