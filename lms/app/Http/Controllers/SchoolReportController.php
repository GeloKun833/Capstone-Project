<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Services\SchoolReportService;
use App\Services\StudentPerformanceService;
use App\Support\SchoolReportCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class SchoolReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(SchoolReportService $reports)
    {
        $user = Auth::user();
        if (! in_array($user->role_name, ['Admin', 'Principal', 'Registrar', 'Teacher'], true)) {
            abort(403);
        }

        return view('reports.school-index', [
            'reports' => collect($reports->available($user))->groupBy('category'),
            'year' => AcademicYear::active(),
        ]);
    }

    public function show(Request $request, string $report, SchoolReportService $reports, StudentPerformanceService $performance)
    {
        $prepared = $this->prepare($request, $report, $reports, $performance);
        if ($prepared instanceof \Illuminate\Http\RedirectResponse) {
            return $prepared;
        }
        if ($prepared['loadError']) {
            return view('reports.school-show', array_merge($prepared, [
                'rows' => new LengthAwarePaginator([], 0, 25),
            ]));
        }

        $filtered = $prepared['rows'];
        $page = max(1, (int) $request->get('page', 1));
        $prepared['rows'] = new LengthAwarePaginator(
            $filtered->slice(($page - 1) * 25, 25)->values(),
            $filtered->count(),
            25,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $prepared['matchCount'] = $filtered->count();

        return view('reports.school-show', $prepared);
    }

    public function download(Request $request, string $report, SchoolReportService $reports, StudentPerformanceService $performance)
    {
        $prepared = $this->prepare($request, $report, $reports, $performance);
        if ($prepared instanceof \Illuminate\Http\RedirectResponse) {
            return $prepared;
        }
        if ($prepared['loadError']) {
            return redirect()
                ->route('reports.school.show', $report)
                ->with('error', $prepared['note']);
        }

        @set_time_limit(300);
        $memory = trim((string) ini_get('memory_limit'));
        if ($memory !== '-1' && $this->memoryBytes($memory) < 512 * 1024 * 1024) {
            @ini_set('memory_limit', '512M');
        }

        $title = $prepared['report']['title'];
        $pdf = Pdf::loadView('reports.school-pdf', [
            'title' => $title,
            'year' => $prepared['year'],
            'sectionLabel' => $prepared['sectionLabel'],
            'search' => $prepared['search'],
            'columns' => $prepared['columns'],
            'rows' => $prepared['rows'],
            'summary' => $prepared['summary'],
            'note' => $prepared['note'],
            'generatedAt' => now('Asia/Manila')->format('M j, Y g:i A'),
        ])->setPaper('a4', count($prepared['columns']) > 5 ? 'landscape' : 'portrait');

        $filename = trim(preg_replace('/[^A-Za-z0-9_\-]+/', '_', $title), '_');
        $filename = ($filename !== '' ? $filename : 'report').'_'.now('Asia/Manila')->toDateString().'.pdf';

        return $pdf->download($filename);
    }

    private function prepare(Request $request, string $report, SchoolReportService $reports, StudentPerformanceService $performance)
    {
        $user = Auth::user();
        $definition = SchoolReportCatalog::find($report);
        if (! $definition || ! $reports->allows($user, $definition['roles'])) {
            abort(403);
        }
        if ($definition['href']) {
            return redirect()->route($definition['href']);
        }

        $year = AcademicYear::active();
        if ($user->role_name === 'Teacher') {
            $sections = $performance->sectionsForUser($user, $year?->id);
        } else {
            $gradeOrder = array_flip(\App\Services\GradeSubjectCatalogService::gradeLevels());
            $sections = Section::query()
                ->when($year, function ($query) use ($year) {
                    $query->where(function ($inner) use ($year) {
                        $inner->where('academic_year_id', $year->id)
                            ->orWhereIn('id', function ($assigned) use ($year) {
                                $assigned->select('section_id')
                                    ->from('student_section_assignments')
                                    ->where('academic_year_id', $year->id);
                            });
                    });
                })
                ->get()
                ->sortBy(fn ($section) => sprintf('%03d-%s', $gradeOrder[$section->grade_level] ?? 999, $section->name))
                ->values();
        }
        $sectionId = $request->filled('section_id') ? (int) $request->get('section_id') : null;
        if ($sectionId && ! $sections->contains(fn ($section) => (int) $section->id === $sectionId)) {
            $sectionId = null;
        }

        $search = mb_substr(trim(strip_tags((string) $request->query('q', ''))), 0, 80);
        $filterIds = $sectionId ? [$sectionId] : [];
        $searchIsSection = false;
        if ($search !== '' && ! $sectionId) {
            $needle = mb_strtolower($search);
            $matched = $sections->filter(function ($section) use ($needle) {
                $grade = mb_strtolower(trim((string) $section->grade_level));
                $name = mb_strtolower(trim((string) $section->name));
                $label = trim($grade.' '.$name);

                return $needle === $label || ($needle === $name && $name !== '' && $name !== $grade);
            })->values();
            if ($matched->isNotEmpty()) {
                $filterIds = $matched->map(fn ($section) => (int) $section->id)->all();
                $searchIsSection = true;
                if (count($filterIds) === 1) {
                    $sectionId = $filterIds[0];
                }
            }
        }

        $sectionLabel = 'All sections';
        if ($filterIds !== []) {
            $sectionLabel = $sections
                ->filter(fn ($section) => in_array((int) $section->id, $filterIds, true))
                ->map(fn ($section) => trim(($section->grade_level ? $section->grade_level.' — ' : '').$section->name))
                ->implode(', ');
            if ($sectionLabel === '') {
                $sectionLabel = 'All sections';
            }
        }

        try {
            $payload = $reports->make($user, $report, $filterIds);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'report' => $definition,
                'year' => $year,
                'sections' => $sections,
                'sectionId' => $sectionId,
                'sectionLabel' => $sectionLabel,
                'columns' => [],
                'rows' => collect(),
                'summary' => [],
                'note' => 'This report could not be loaded. Refresh the page to try again.',
                'search' => '',
                'loadError' => true,
            ];
        }

        $filtered = collect($payload['rows']);
        if ($search !== '' && ! $searchIsSection) {
            $words = preg_split('/\s+/u', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $filtered = $filtered->filter(function (array $row) use ($words) {
                $haystack = mb_strtolower(implode(' ', array_map(
                    fn ($cell) => is_scalar($cell) ? (string) $cell : '',
                    $row
                )));
                $haystack = str_replace([',', '—', '–', '-'], ' ', $haystack);
                foreach ($words as $word) {
                    if (! str_contains($haystack, $word)) {
                        return false;
                    }
                }

                return true;
            })->values();
        }

        return [
            'report' => $payload['report'],
            'year' => $payload['year'],
            'sections' => $sections,
            'sectionId' => $sectionId,
            'sectionLabel' => $sectionLabel,
            'columns' => $payload['columns'],
            'rows' => $filtered,
            'summary' => $payload['summary'],
            'note' => $payload['note'],
            'search' => $search,
            'loadError' => false,
        ];
    }

    private function memoryBytes(string $limit): int
    {
        $number = (int) $limit;
        $unit = strtolower(substr($limit, -1));

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
