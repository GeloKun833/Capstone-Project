<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Services\GradeSubjectCatalogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurriculumController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Curriculum::class, 'curriculum');
    }

    public function index()
    {
        $catalog = app(GradeSubjectCatalogService::class);
        $this->ensureCurriculaForGrades();

        $curricula = Curriculum::with('subjects')
            ->orderBy('grade_level')
            ->get();

        $subjectsByGrade = $catalog->subjectsGroupedByGrade();
        $gradeLevels = GradeSubjectCatalogService::gradeLevels();

        // Prefer canonical grade order for the grid
        $ordered = collect();
        foreach ($gradeLevels as $grade) {
            $item = $curricula->first(fn ($c) => $c->grade_level === $grade);
            if ($item) {
                $ordered->push($item);
            }
        }
        foreach ($curricula as $item) {
            if (!$ordered->contains('id', $item->id)) {
                $ordered->push($item);
            }
        }

        return view('curriculum.index', [
            'curricula' => $ordered,
            'subjectsByGrade' => $subjectsByGrade,
            'gradeLevels' => $gradeLevels,
        ]);
    }

    public function create()
    {
        $gradeLevels = GradeSubjectCatalogService::gradeLevels();
        $usedGrades = Curriculum::pluck('grade_level')->all();
        $availableGrades = array_values(array_diff($gradeLevels, $usedGrades));

        return view('curriculum.create', compact('gradeLevels', 'availableGrades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'grade_level' => [
                'required',
                'string',
                Rule::in(GradeSubjectCatalogService::gradeLevels()),
                'unique:curricula,grade_level',
            ],
            'description' => 'nullable|string|max:2000',
        ]);

        $curriculum = Curriculum::create($request->only(['grade_level', 'description']));

        // Connect immediately to subjects already in the catalog for this grade
        $this->syncCurriculumToCatalog($curriculum);

        return redirect()
            ->route('curriculum.index')
            ->with('success', 'Curriculum for ' . $curriculum->grade_level . ' created and linked to subject catalog.');
    }

    public function show(Curriculum $curriculum)
    {
        $curriculum->load('subjects');
        $catalogSubjects = app(GradeSubjectCatalogService::class)
            ->subjectsForGrade($curriculum->grade_level);

        return view('curriculum.show', compact('curriculum', 'catalogSubjects'));
    }

    public function edit(Curriculum $curriculum)
    {
        $gradeLevels = GradeSubjectCatalogService::gradeLevels();

        return view('curriculum.edit', compact('curriculum', 'gradeLevels'));
    }

    public function update(Request $request, Curriculum $curriculum)
    {
        $request->validate([
            'grade_level' => [
                'required',
                'string',
                Rule::in(GradeSubjectCatalogService::gradeLevels()),
                Rule::unique('curricula', 'grade_level')->ignore($curriculum->id),
            ],
            'description' => 'nullable|string|max:2000',
        ]);

        $curriculum->update($request->only(['grade_level', 'description']));
        $curriculum->load('subjects');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Curriculum updated successfully.',
                'curriculum' => [
                    'id' => $curriculum->id,
                    'grade_level' => $curriculum->grade_level,
                    'description' => $curriculum->description,
                    'subjects' => $curriculum->subjects->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->subject_name,
                    ])->values(),
                    'linked' => $curriculum->subjects->count(),
                ],
            ]);
        }

        return redirect()
            ->route('curriculum.index')
            ->with('success', 'Curriculum updated successfully.');
    }

    public function destroy(Curriculum $curriculum)
    {
        $grade = $curriculum->grade_level;
        $curriculum->delete();

        return redirect()
            ->route('curriculum.index')
            ->with('success', 'Curriculum for ' . $grade . ' deleted.');
    }

    public function assignSubjectsForm(Curriculum $curriculum)
    {
        $curriculum->load('subjects');
        $catalog = app(GradeSubjectCatalogService::class);

        // Prefer subjects for this grade from the shared catalog
        $subjects = $catalog->subjectsForGrade($curriculum->grade_level);
        $assigned = $curriculum->subjects->pluck('id')->all();

        return view('curriculum.assign_subjects', compact('curriculum', 'subjects', 'assigned'));
    }

    public function assignSubjects(Request $request, Curriculum $curriculum)
    {
        $subjectIds = collect($request->input('subject_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // Only allow subjects that belong to this grade in the catalog
        $allowed = app(GradeSubjectCatalogService::class)
            ->subjectsForGrade($curriculum->grade_level)
            ->pluck('id')
            ->all();

        $subjectIds = array_values(array_intersect($subjectIds, $allowed));
        $curriculum->subjects()->sync($subjectIds);

        if ($request->expectsJson() || $request->ajax()) {
            $curriculum->load('subjects');
            return response()->json([
                'success' => true,
                'message' => 'Subjects updated for ' . $curriculum->grade_level . '.',
                'count' => $curriculum->subjects->count(),
                'subjects' => $curriculum->subjects->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->subject_name,
                ])->values(),
            ]);
        }

        return redirect()
            ->route('curriculum.index')
            ->with('success', 'Subjects assigned for ' . $curriculum->grade_level . '.');
    }

    /**
     * Pull all subjects for this grade from Classes & Subjects catalog into curriculum.
     */
    public function syncFromCatalog(Curriculum $curriculum)
    {
        $this->authorize('update', $curriculum);

        $count = $this->syncCurriculumToCatalog($curriculum);

        if (request()->expectsJson() || request()->ajax()) {
            $curriculum->load('subjects');
            return response()->json([
                'success' => true,
                'message' => 'Synced ' . $count . ' subject(s) from catalog for ' . $curriculum->grade_level . '.',
                'count' => $count,
                'subjects' => $curriculum->subjects->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->subject_name,
                ])->values(),
            ]);
        }

        return redirect()
            ->route('curriculum.index')
            ->with('success', 'Synced ' . $count . ' subject(s) from catalog for ' . $curriculum->grade_level . '.');
    }

    /**
     * One-click: ensure all grades exist and sync every curriculum to catalog.
     */
    public function syncAllFromCatalog()
    {
        $this->authorize('create', Curriculum::class);

        $this->ensureCurriculaForGrades();
        $synced = 0;

        Curriculum::query()->get()->each(function (Curriculum $curriculum) use (&$synced) {
            $synced += $this->syncCurriculumToCatalog($curriculum);
        });

        return redirect()
            ->route('curriculum.index')
            ->with('success', 'All curricula synced with subject catalog (' . $synced . ' subject link(s)).');
    }

    protected function ensureCurriculaForGrades(): void
    {
        // Merge accidental duplicate grade rows (legacy seed / free-text creates)
        $grouped = Curriculum::with('subjects')->orderBy('id')->get()->groupBy('grade_level');
        foreach ($grouped as $rows) {
            if ($rows->count() <= 1) {
                continue;
            }
            $keep = $rows->shift();
            foreach ($rows as $dup) {
                $ids = $dup->subjects->pluck('id')
                    ->merge($keep->subjects->pluck('id'))
                    ->unique()
                    ->all();
                $keep->subjects()->syncWithoutDetaching($ids);
                $dup->subjects()->detach();
                $dup->delete();
            }
        }

        foreach (GradeSubjectCatalogService::gradeLevels() as $grade) {
            Curriculum::firstOrCreate(
                ['grade_level' => $grade],
                ['description' => $grade . ' curriculum linked to the subject catalog.']
            );
        }
    }

    protected function syncCurriculumToCatalog(Curriculum $curriculum): int
    {
        $ids = app(GradeSubjectCatalogService::class)
            ->subjectsForGrade($curriculum->grade_level)
            ->pluck('id')
            ->all();

        $curriculum->subjects()->sync($ids);

        return count($ids);
    }
}
