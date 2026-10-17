<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Curriculum;
use App\Models\CurriculumCourseOutline;
use App\Models\CurriculumDemoMaterial;
use App\Models\CurriculumOptlbRecord;
use App\Models\Subject;
use App\Services\GradeSubjectCatalogService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoCurriculumOutlineSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::query()
            ->where('name', 'like', '2026%2027')
            ->first();

        if (! $year) {
            $this->command?->error('Academic year 2026-2027 was not found. No demo curriculum records were created.');

            return;
        }

        $createdOutlines = 0;
        $createdOptlb = 0;
        $createdFiles = 0;
        $gradesPopulated = [];
        $skipped = [];

        foreach (GradeSubjectCatalogService::gradeLevels() as $grade) {
            $curriculum = Curriculum::query()->where('grade_level', $grade)->first();
            $subjects = Subject::query()->where('class', $grade)->orderBy('subject_name')->get();

            if ($subjects->isEmpty()) {
                $skipped[] = $grade.' — no subjects configured for this grade';

                continue;
            }

            if (! $curriculum) {
                $skipped[] = $grade.' — curriculum record missing; subjects were not given demo outlines';

                continue;
            }

            $gradesPopulated[] = $grade;

            foreach ($subjects as $subject) {
                $pack = $this->pack($grade, $subject->subject_name);
                $outline = CurriculumCourseOutline::query()->updateOrCreate(
                    ['demo_key' => 'DEMO-CO-'.$year->id.'-'.$subject->id],
                    [
                        'curriculum_id' => $curriculum->id,
                        'academic_year_id' => $year->id,
                        'subject_id' => $subject->id,
                        'grade_level' => $grade,
                        'title' => $pack['title'],
                        'description' => $pack['description'],
                        'learning_objectives' => $pack['objectives'],
                        'topics' => $pack['topics'],
                        'assessment_methods' => $pack['assessment'],
                        'sequence_note' => $pack['sequence'],
                        'status' => 'published',
                        'document_type' => 'DEMO — NOT OFFICIAL',
                    ]
                );
                $createdOutlines++;

                $optlb = CurriculumOptlbRecord::query()->updateOrCreate(
                    ['demo_key' => 'DEMO-OPTLB-'.$year->id.'-'.$subject->id],
                    [
                        'course_outline_id' => $outline->id,
                        'academic_year_id' => $year->id,
                        'subject_id' => $subject->id,
                        'grade_level' => $grade,
                        'title' => 'DEMO OPTLB — '.$grade.' '.$subject->subject_name,
                        'learning_objectives' => $pack['objectives'],
                        'topic_sequence' => $pack['sequence_list'],
                        'suggested_activities' => $pack['activities'],
                        'assessment' => $pack['assessment'],
                        'references' => 'Demonstration references only. This is not an official OPTLB, DepEd, or school document.',
                        'status' => 'published',
                        'demo_label' => 'NOT OFFICIAL',
                    ]
                );
                $createdOptlb++;

                $createdFiles += $this->storePdf(
                    'DEMO-FILE-CO-'.$year->id.'-'.$subject->id,
                    $outline,
                    null,
                    $this->fileName('DEMO_Course_Outline', $grade, $subject->subject_name),
                    $this->pdfLines($outline->title, $grade, $subject->subject_name, 'Course outline', $pack)
                );
                $createdFiles += $this->storePdf(
                    'DEMO-FILE-OPTLB-'.$year->id.'-'.$subject->id,
                    $outline,
                    $optlb,
                    $this->fileName('DEMO_OPTLB', $grade, $subject->subject_name),
                    $this->pdfLines($optlb->title, $grade, $subject->subject_name, 'OPTLB sample', $pack)
                );
            }
        }

        $this->command?->info('Demo grades: '.implode(', ', $gradesPopulated));
        $this->command?->info("Outlines touched: {$createdOutlines}; OPTLB touched: {$createdOptlb}; files written or reused: {$createdFiles}");
        foreach ($skipped as $line) {
            $this->command?->warn($line);
        }
    }

    private function storePdf(string $key, CurriculumCourseOutline $outline, ?CurriculumOptlbRecord $optlb, string $fileName, array $lines): int
    {
        $path = 'demo-curriculum/'.$outline->academic_year_id.'/'.$fileName;
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, $this->pdf($lines));
        }

        CurriculumDemoMaterial::query()->updateOrCreate(
            ['demo_key' => $key],
            [
                'course_outline_id' => $outline->id,
                'optlb_record_id' => $optlb?->id,
                'academic_year_id' => $outline->academic_year_id,
                'subject_id' => $outline->subject_id,
                'grade_level' => $outline->grade_level,
                'title' => $fileName,
                'file_path' => $path,
                'file_name' => $fileName,
                'demo_label' => 'NOT OFFICIAL',
            ]
        );

        return 1;
    }

    private function fileName(string $prefix, string $grade, string $subject): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '_', $grade.'_'.$subject) ?: 'subject';

        return $prefix.'_'.trim($slug, '_').'.pdf';
    }

    private function pack(string $grade, string $subject): array
    {
        $topics = $this->topics($grade, $subject);
        $names = array_column($topics, 'title');

        return [
            'title' => 'DEMO Course Outline — '.$grade.' '.$subject,
            'description' => 'Demonstration course description for '.$grade.' '.$subject.'. DEMO — NOT OFFICIAL. This is not a DepEd curriculum, an approved school curriculum, or an official OPTLB document.',
            'objectives' => 'Learners practice the sample topics for '.$subject.' through guided activities. These objectives are demonstration text only.',
            'topics' => $topics,
            'assessment' => 'Observation, short checks, and a simple performance task. Demonstration assessment only.',
            'sequence' => 'Suggested order: '.implode(' → ', $names).'. Times are samples for 2026–2027.',
            'sequence_list' => implode("\n", array_map(fn ($topic, $i) => ($i + 1).'. '.$topic['title'].' ('.$topic['duration'].')', $topics, array_keys($topics))),
            'activities' => implode(' ', array_column($topics, 'activities')),
        ];
    }

    private function topics(string $grade, string $subject): array
    {
        $name = strtolower($subject);
        $samples = [
            'nursery|literacy' => ['Listening and sounds', 'Picture vocabulary', 'Shared storytelling'],
            'nursery|numeracy' => ['Sorting objects', 'Shapes around us', 'Counting small groups'],
            'kindergarten|literacy|language' => ['Letter recognition', 'Beginning sounds', 'Simple vocabulary'],
            'kindergarten|numeracy|math' => ['Number recognition', 'Counting', 'Patterns'],
            'grade 1|english' => ['Phonics', 'Reading simple sentences', 'Basic writing'],
            'grade 1|math' => ['Addition', 'Subtraction', 'Number patterns'],
            'grade 2|english' => ['Reading comprehension', 'Sentence construction', 'Word meaning'],
            'grade 2|math' => ['Place value', 'Addition', 'Subtraction'],
            'grade 3|english' => ['Paragraph reading', 'Vocabulary', 'Written responses'],
            'grade 3|math' => ['Multiplication', 'Division', 'Basic fractions'],
            'grade 4|english' => ['Reading comprehension', 'Grammar', 'Paragraph writing'],
            'grade 4|math' => ['Multi-digit operations', 'Fractions', 'Measurement'],
            'grade 5|science' => ['Matter', 'Ecosystems', 'Scientific observation'],
            'grade 5|math' => ['Fractions', 'Decimals', 'Problem solving'],
            'grade 6|english' => ['Text analysis', 'Composition', 'Communication'],
            'grade 6|science' => ['Energy', 'Ecosystems', 'Scientific investigation'],
            'grade 7|math' => ['Number systems', 'Algebraic expressions', 'Geometry'],
            'grade 7|science' => ['Scientific investigation', 'Matter', 'Living systems'],
            'grade 8|math' => ['Linear equations', 'Geometry', 'Statistics'],
            'grade 8|science' => ['Forces', 'Energy', 'Ecosystems'],
            'grade 9|math' => ['Quadratic relationships', 'Geometry', 'Probability'],
            'grade 9|science' => ['Chemical reactions', 'Ecosystems', 'Physical science'],
            'grade 10|math' => ['Functions', 'Trigonometry', 'Statistics'],
            'grade 10|science' => ['Genetics', 'Chemical processes', 'Electricity'],
        ];

        $gradeKey = strtolower($grade);
        foreach ($samples as $rule => $titles) {
            $parts = explode('|', $rule);
            $gradePart = array_shift($parts);
            if ($gradeKey !== $gradePart) {
                continue;
            }
            foreach ($parts as $word) {
                if (str_contains($name, $word)) {
                    return $this->units($titles, $this->band($grade));
                }
            }
        }

        return $this->units($this->fallbackTitles($grade, $subject), $this->band($grade));
    }

    private function units(array $titles, string $band): array
    {
        $units = [];
        foreach (array_slice($titles, 0, 3) as $index => $title) {
            $units[] = [
                'unit' => $index + 1,
                'title' => $title,
                'competencies' => 'Sample competency: learners take part in '.$title.' at a '.$band.' level.',
                'activities' => $band === 'play-based'
                    ? 'Play, songs, and hands-on practice for '.$title.'.'
                    : 'Guided practice, discussion, and a short task for '.$title.'.',
                'duration' => '2-3 weeks',
            ];
        }

        return $units;
    }

    private function fallbackTitles(string $grade, string $subject): array
    {
        $band = $this->band($grade);
        if ($band === 'play-based') {
            return [$subject.' through play', 'Language and friends', 'Movement and routines'];
        }
        if ($band === 'primary') {
            return ['Getting started with '.$subject, 'Practice in '.$subject, 'Sharing what we learned'];
        }

        return ['Introduction to '.$subject, 'Working with '.$subject, 'Review and application'];
    }

    private function band(string $grade): string
    {
        return in_array($grade, ['Nursery', 'Kindergarten'], true) ? 'play-based' : (
            in_array($grade, ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'], true) ? 'primary' : 'junior high'
        );
    }

    private function pdfLines(string $title, string $grade, string $subject, string $kind, array $pack): array
    {
        $lines = [
            'DEMO - NOT OFFICIAL',
            $kind.' for demonstration only',
            'Not a DepEd, school, or OPTLB document',
            'Academic year: 2026-2027',
            'Grade: '.$grade,
            'Subject: '.$subject,
            $title,
            'Objectives: '.$pack['objectives'],
        ];
        foreach ($pack['topics'] as $topic) {
            $lines[] = 'Unit '.$topic['unit'].': '.$topic['title'].' ('.$topic['duration'].')';
        }
        $lines[] = 'Assessment: '.$pack['assessment'];

        return $lines;
    }

    private function pdf(array $lines): string
    {
        $commands = "BT /F1 11 Tf\n";
        $y = 750;
        foreach ($lines as $line) {
            $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], substr($line, 0, 95));
            $commands .= "1 0 0 1 48 {$y} Tm ({$safe}) Tj\n";
            $y -= 18;
        }
        $commands .= 'ET';
        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n",
            '4 0 obj << /Length '.strlen($commands)." >> stream\n".$commands."\nendstream endobj\n",
            "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}
