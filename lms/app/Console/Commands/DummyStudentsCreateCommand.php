<?php

namespace App\Console\Commands;

use App\Services\DummyStudentService;
use Illuminate\Console\Command;

class DummyStudentsCreateCommand extends Command
{
    protected $signature = 'dummy:students:create
        {--dry-run : Show the section/subject/teacher plan without writing anything}
        {--force : Skip the confirmation prompt}
        {--skip-unassigned : Leave out sections where a subject has no existing teacher}
        {--academic-year-id= : Existing academic year ID (default: current, else latest)}
        {--semester-id= : Existing semester ID in that academic year (default: its first semester)}
        {--attendance-days='.DummyStudentService::DEFAULT_ATTENDANCE_DAYS.' : Recent school days of attendance per subject}
        {--seed='.DummyStudentService::DEFAULT_SEED.' : Random seed so the generated data is reproducible}';

    protected $description = 'Create 400 dummy/test students (DUMMY-STU-*, @'.DummyStudentService::EMAIL_DOMAIN.') with enrollments, grades and attendance in existing sections. Never deletes data.';

    public function handle(DummyStudentService $service): int
    {
        $service->setLogger(fn (string $message) => $this->line($message));
        $options = [
            'academic_year_id' => $this->option('academic-year-id') ? (int) $this->option('academic-year-id') : null,
            'semester_id' => $this->option('semester-id') ? (int) $this->option('semester-id') : null,
            'attendance_days' => (int) $this->option('attendance-days'),
            'seed' => (int) $this->option('seed'),
            'skip_unassigned' => (bool) $this->option('skip-unassigned'),
        ];

        try {
            $service->assertNoExistingDummyData();
            $summary = $service->summarizePlan($service->plan($options));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            if ($e->getCode() !== DummyStudentService::MISSING_TEACHER_ERROR || $options['skip_unassigned'] || $this->option('force')
                || ! $this->confirm('Skip the sections listed above and put all '.DummyStudentService::TOTAL_STUDENTS.' dummy students in the other sections?')) {
                return self::FAILURE;
            }

            $options['skip_unassigned'] = true;
            try {
                $summary = $service->summarizePlan($service->plan($options));
            } catch (\RuntimeException $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
        }

        $this->renderSummary($summary);

        if ($this->option('dry-run')) {
            $this->info('Dry run only. Nothing was written.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Create '.DummyStudentService::TOTAL_STUDENTS.' dummy students with this plan?')) {
            $this->warn('Cancelled. Nothing was written.');

            return self::FAILURE;
        }

        try {
            $summary = $service->create($options);
        } catch (\Throwable $e) {
            $this->error('Generation failed and was rolled back: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Created:');
        $this->table(['Item', 'Rows'], collect($summary['counts'])->map(fn ($v, $k) => [$k, $v])->values()->all());
        $this->info('Low-performing students:');
        $this->table(['Admission ID', 'Name', 'Email', 'Grade', 'Section', 'General average'], $summary['low_performers']);
        $this->line('High performer general averages: '.$summary['high_average_range']['min'].' – '.$summary['high_average_range']['max']);
        $this->newLine();
        $this->info('Login: '.DummyStudentService::email(1).' / '.DummyStudentService::PASSWORD.' (all dummy students share this password)');
        $this->info('Validate: php artisan dummy:students:verify');
        $this->info('Remove:   php artisan dummy:students:delete');

        return self::SUCCESS;
    }

    protected function renderSummary(array $summary): void
    {
        $this->info('Academic year: '.$summary['academic_year']->name.' (#'.$summary['academic_year']->id.')'
            .' | Semester: '.$summary['semester']->name.' (#'.$summary['semester']->id.')'
            .' | Attendance days: '.$summary['attendance_days']);

        $this->table(['Grade', 'Dummy students'], collect($summary['grade_distribution'])->map(fn ($v, $k) => [$k, $v])->values()->all());
        $this->table(
            ['Section ID', 'Section', 'Grade', 'Capacity', 'Existing', 'Dummy', 'Total'],
            array_map('array_values', $summary['section_distribution'])
        );
        $this->table(['Grade', 'Subject', 'Teacher per section'], array_map('array_values', $summary['teacher_assignments']));
        $this->line('Planned low performers: '.implode(', ', array_column($summary['low_performers'], 'admission_id')));

        foreach ($summary['warnings'] as $warning) {
            $this->warn($warning);
        }
    }
}
