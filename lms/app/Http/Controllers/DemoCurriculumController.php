<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Curriculum;
use App\Models\CurriculumCourseOutline;
use App\Models\CurriculumDemoMaterial;
use App\Models\CurriculumOptlbRecord;
use App\Models\Teacher;
use App\Services\GradeSubjectCatalogService;
use App\Support\SafeUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DemoCurriculumController extends Controller
{
    public function adminIndex()
    {
        $outlines = CurriculumCourseOutline::query()
            ->with(['subject', 'academicYear', 'optlbRecord', 'materials'])
            ->orderBy('title')
            ->get();

        return view('curriculum.demo-index', [
            'groups' => $this->groupByGrade($outlines),
            'teacherView' => false,
        ]);
    }

    public function teacherIndex()
    {
        $teacher = $this->teacher();
        $subjectIds = $this->assignedSubjectIds($teacher);

        $outlines = CurriculumCourseOutline::query()
            ->with(['subject', 'academicYear', 'optlbRecord', 'materials'])
            ->where('status', 'published')
            ->whereIn('id', $this->visibleOutlineIds($teacher, $subjectIds))
            ->orderBy('title')
            ->get();

        return view('curriculum.demo-index', [
            'groups' => $this->groupByGrade($outlines),
            'teacherView' => true,
        ]);
    }

    public function createOutline(Curriculum $curriculum)
    {
        return view('curriculum.demo-create-outline', [
            'curriculum' => $curriculum,
            'year' => $this->demoYear(),
            'subjects' => $this->subjectsFor($curriculum),
        ]);
    }

    public function createOptlb(Curriculum $curriculum)
    {
        return redirect()->route('curriculum.demo.create', $curriculum);
    }

    public function storeOutline(Request $request, Curriculum $curriculum)
    {
        $year = $this->demoYear();
        $subjectIds = $this->subjectsFor($curriculum)->pluck('id');
        $data = $request->validate([
            'subject_id' => ['required', 'integer', Rule::in($subjectIds->all())],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:2000'],
            'learning_objectives' => ['required', 'string', 'max:2000'],
            'topic_1' => ['required', 'string', 'max:120'],
            'topic_2' => ['required', 'string', 'max:120'],
            'topic_3' => ['required', 'string', 'max:120'],
            'assessment_methods' => ['required', 'string', 'max:1000'],
            'outline_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'optlb_title' => ['required', 'string', 'max:180'],
            'optlb_objectives' => ['required', 'string', 'max:2000'],
            'optlb_sequence' => ['required', 'string', 'max:2000'],
            'optlb_activities' => ['required', 'string', 'max:2000'],
            'optlb_assessment' => ['required', 'string', 'max:1000'],
            'optlb_references' => ['required', 'string', 'max:1000'],
            'optlb_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $subject = $this->subjectsFor($curriculum)->firstWhere('id', (int) $data['subject_id']);
        $token = now()->format('YmdHis').'-'.substr(bin2hex(random_bytes(3)), 0, 6);
        $topics = [];
        foreach (['topic_1', 'topic_2', 'topic_3'] as $index => $key) {
            $topics[] = [
                'unit' => $index + 1,
                'title' => $data[$key],
                'competencies' => $data['learning_objectives'],
                'activities' => $data['optlb_activities'],
                'duration' => '2-3 weeks',
            ];
        }

        $outline = DB::transaction(function () use ($request, $data, $curriculum, $year, $subject, $token, $topics) {
            $outline = CurriculumCourseOutline::query()->create([
                'demo_key' => 'CO-'.$year->id.'-'.$subject->id.'-'.$token,
                'curriculum_id' => $curriculum->id,
                'academic_year_id' => $year->id,
                'subject_id' => $subject->id,
                'grade_level' => $curriculum->grade_level,
                'title' => $data['title'],
                'description' => $data['description'],
                'learning_objectives' => $data['learning_objectives'],
                'topics' => $topics,
                'assessment_methods' => $data['assessment_methods'],
                'sequence_note' => $data['topic_1'].' → '.$data['topic_2'].' → '.$data['topic_3'],
                'status' => 'published',
                'document_type' => 'Course outline',
            ]);

            $optlb = CurriculumOptlbRecord::query()->create([
                'demo_key' => 'OPTLB-'.$year->id.'-'.$subject->id.'-'.$token,
                'course_outline_id' => $outline->id,
                'academic_year_id' => $year->id,
                'subject_id' => $subject->id,
                'grade_level' => $curriculum->grade_level,
                'title' => $data['optlb_title'],
                'learning_objectives' => $data['optlb_objectives'],
                'topic_sequence' => $data['optlb_sequence'],
                'suggested_activities' => $data['optlb_activities'],
                'assessment' => $data['optlb_assessment'],
                'references' => $data['optlb_references'],
                'status' => 'published',
                'demo_label' => 'OPTLB',
            ]);

            $this->attachPdf($request->file('outline_file'), $outline, null, 'Course_Outline', $data['title'], $token);
            $this->attachPdf($request->file('optlb_file'), $outline, $optlb, 'OPTLB', $data['optlb_title'], $token);

            return $outline;
        });

        return redirect()
            ->route('curriculum.demo.show', $outline)
            ->with('success', 'Course outline and OPTLB saved for '.$curriculum->grade_level.' '.$subject->subject_name.'.');
    }

    public function show(CurriculumCourseOutline $outline)
    {
        $this->authorizeOutline($outline);
        $outline->load(['subject', 'academicYear', 'optlbRecord.materials', 'materials', 'curriculum']);

        return view('curriculum.demo-show', [
            'outline' => $outline,
            'teacherView' => Auth::user()?->role_name === 'Teacher',
        ]);
    }

    public function material(CurriculumDemoMaterial $material)
    {
        $outline = $material->courseOutline;
        abort_unless($outline, 404);
        $this->authorizeOutline($outline);
        abort_unless(Storage::disk('public')->exists($material->file_path), 404);

        return response()->file(Storage::disk('public')->path($material->file_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$material->file_name.'"',
        ]);
    }

    private function demoYear(): AcademicYear
    {
        $year = AcademicYear::query()->where('name', 'like', '2026%2027')->first()
            ?: AcademicYear::active();
        abort_unless($year, 422, 'Academic year 2026-2027 is not available.');

        return $year;
    }

    private function subjectsFor(Curriculum $curriculum)
    {
        if ($curriculum->subjects()->exists()) {
            return $curriculum->subjects()->orderBy('subject_name')->get();
        }

        return app(GradeSubjectCatalogService::class)->subjectsForGrade($curriculum->grade_level);
    }

    private function attachPdf($file, CurriculumCourseOutline $outline, ?CurriculumOptlbRecord $optlb, string $prefix, string $title, string $token): void
    {
        $subjectName = $outline->subject->subject_name ?? 'subject';
        $slug = trim(preg_replace('/[^A-Za-z0-9]+/', '_', $outline->grade_level.'_'.$subjectName) ?: 'subject', '_');
        $fileName = $prefix.'_'.$slug.'_'.$token.'.pdf';
        $key = ($optlb ? 'FILE-OPTLB-' : 'FILE-CO-').$token;

        if ($file) {
            $stored = SafeUpload::store($file, 'demo-curriculum/'.$outline->academic_year_id);
            $path = $stored['path'];
            $fileName = $stored['original_name'] ?: $fileName;
        } else {
            $path = 'demo-curriculum/'.$outline->academic_year_id.'/'.$fileName;
            Storage::disk('public')->put($path, $this->simplePdf([
                $title,
                'Grade: '.$outline->grade_level,
                'Subject: '.$subjectName,
                'Academic year record',
            ]));
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
                'demo_label' => $optlb ? 'OPTLB' : 'Course outline',
            ]
        );
    }

    private function simplePdf(array $lines): string
    {
        $commands = "BT /F1 12 Tf\n";
        $y = 740;
        foreach ($lines as $line) {
            $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], substr($line, 0, 90));
            $commands .= "1 0 0 1 48 {$y} Tm ({$safe}) Tj\n";
            $y -= 22;
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
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function authorizeOutline(CurriculumCourseOutline $outline): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->role_name === 'Admin') {
            return;
        }

        $teacher = $this->teacher();
        abort_unless(
            $outline->visibleToTeacher($teacher) || $this->teachesSubjectThisYear($teacher, $outline),
            403
        );
    }

    private function teachesSubjectThisYear(Teacher $teacher, CurriculumCourseOutline $outline): bool
    {
        if ($outline->status !== 'published') {
            return false;
        }

        $yearId = AcademicYear::active()?->id;
        if (! $yearId) {
            return false;
        }

        $assigned = DB::table('subject_teacher')
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $outline->subject_id)
            ->where('academic_year_id', $yearId)
            ->exists();

        if ($assigned) {
            return true;
        }

        return DB::table('class_schedules')
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $outline->subject_id)
            ->where('academic_year_id', $yearId)
            ->where('is_active', true)
            ->exists();
    }

    private function teacher(): Teacher
    {
        $teacher = Auth::user()?->teacher;
        abort_unless($teacher, 403);

        return $teacher;
    }

    private function assignedSubjectIds(Teacher $teacher)
    {
        $fromAssignments = DB::table('subject_teacher')
            ->where('teacher_id', $teacher->id)
            ->pluck('subject_id');

        $fromSchedules = DB::table('class_schedules')
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->pluck('subject_id');

        return $fromAssignments->merge($fromSchedules)->unique()->values();
    }

    private function groupByGrade($outlines)
    {
        $grouped = $outlines->groupBy('grade_level');
        $ordered = collect();
        foreach (\App\Services\GradeSubjectCatalogService::gradeLevels() as $grade) {
            if ($grouped->has($grade)) {
                $ordered->put($grade, $grouped->get($grade));
            }
        }
        foreach ($grouped as $grade => $items) {
            if (! $ordered->has($grade)) {
                $ordered->put($grade, $items);
            }
        }

        return $ordered;
    }

    private function visibleOutlineIds(Teacher $teacher, $subjectIds)
    {
        if ($subjectIds->isEmpty()) {
            return collect();
        }

        return CurriculumCourseOutline::query()
            ->where('status', 'published')
            ->whereIn('subject_id', $subjectIds)
            ->get()
            ->filter(fn (CurriculumCourseOutline $outline) => $outline->visibleToTeacher($teacher))
            ->pluck('id');
    }
}
