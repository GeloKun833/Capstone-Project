<?php

namespace App\Console\Commands;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class ProfileRequestPerformance extends Command
{
    protected $signature = 'lms:profile-requests';

    protected $description = 'Measure query count and render time for representative LMS pages (local profiling only)';

    public function handle(): int
    {
        $connectStart = microtime(true);
        DB::connection()->getPdo();
        $connectMs = (microtime(true) - $connectStart) * 1000;

        $this->info(sprintf('PDO connect: %.1f ms  host=%s', $connectMs, config('database.connections.'.config('database.default').'.host')));
        $this->newLine();

        $roles = ['Admin', 'Teacher', 'Student', 'Parent', 'Registrar'];
        $users = [];
        foreach ($roles as $role) {
            $users[$role] = User::where('role_name', $role)->first();
        }

        $this->table(
            ['Page', 'Role', 'Status', 'Queries', 'Query ms', 'Render ms', 'HTML KB', 'Bottleneck'],
            $this->collectRows($users)
        );

        $this->newLine();
        $this->comment('Local query ms is NOT Railway TTFB. On Railway each query is typically one WAN RTT to Aiven MySQL (often 150–400ms) plus TLS on a new connection.');
        $this->comment('Estimated Railway TTFB ≈ PDO TLS connect + (query count × remote RTT) + PHP render.');

        return self::SUCCESS;
    }

    private function collectRows(array $users): array
    {
        $rows = [];

        $pages = [
            ['Login GET view', null, fn () => view('auth.login', ['errors' => new \Illuminate\Support\ViewErrorBag])->render()],
            ['Dashboard', 'Admin', fn () => app(HomeController::class)->dashboard()->render()],
            ['Student list', 'Admin', fn () => app(StudentController::class)->student()->render()],
            ['Student create', 'Admin', fn () => view('student.add-student', ['errors' => new \Illuminate\Support\ViewErrorBag])->render()],
            ['Teacher list', 'Admin', fn () => app(TeacherController::class)->teacherList()->render()],
            ['Enrollment list', 'Admin', fn () => app(EnrollmentController::class)->index()->render()],
            ['Dashboard', 'Teacher', fn () => app(HomeController::class)->dashboard()->render()],
            ['Attendance', 'Teacher', fn () => app(AttendanceController::class)->index(Request::create('/teacher/attendance', 'GET'))->render()],
            ['Grade entry', 'Teacher', fn () => app(GradingController::class)->gradeEntryForm(Request::create('/grading/grade-entry', 'GET'))->render()],
            ['Dashboard', 'Student', fn () => app(HomeController::class)->dashboard()->render()],
            ['Dashboard', 'Parent', fn () => app(HomeController::class)->dashboard()->render()],
            ['Dashboard', 'Registrar', fn () => app(HomeController::class)->dashboard()->render()],
        ];

        foreach ($pages as [$page, $role, $callback]) {
            $rows[] = $this->measure($page, $role, $users[$role] ?? null, $callback);
        }

        return $rows;
    }

    private function measure(string $page, ?string $role, ?User $user, callable $callback): array
    {
        if ($role && !$user) {
            return [$page, $role, 'NO USER', '-', '-', '-', '-', 'skip'];
        }

        if (Auth::check()) {
            Auth::logout();
        }
        Session::flush();
        View::share([]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            if ($user) {
                $fresh = User::find($user->id);
                Auth::setUser($fresh);
                Session::put('role_name', $fresh->role_name);
                Session::put('name', $fresh->name);
                Session::put('status', $fresh->status);
            }

            $request = Request::create('/dashboard', 'GET');
            if ($page === 'Student list') {
                $request = Request::create('/student/list', 'GET');
            } elseif ($page === 'Teacher list') {
                $request = Request::create('/teacher/list/page', 'GET');
            } elseif ($page === 'Enrollment list') {
                $request = Request::create('/enrollments', 'GET');
            } elseif ($page === 'Attendance') {
                $request = Request::create('/teacher/attendance', 'GET');
            } elseif ($page === 'Grade entry') {
                $request = Request::create('/grading/grade-entry', 'GET');
            }
            $this->laravel->instance('request', $request);
            $request->setRouteResolver(function () use ($request) {
                try {
                    return app('router')->getRoutes()->match($request);
                } catch (\Throwable $e) {
                    return null;
                }
            });

            $start = microtime(true);
            $html = $callback();
            $renderMs = (microtime(true) - $start) * 1000;
            $log = DB::getQueryLog();
            $queries = array_column($log, 'query');
            $queryMs = array_sum(array_column($log, 'time'));
            $count = count($log);
            $htmlKb = round(strlen(is_string($html) ? $html : '') / 1024, 1);
            $layoutQueries = count(array_filter($queries, function ($sql) {
                $s = strtolower((string) $sql);
                return str_contains($s, 'class_schedules')
                    || str_contains($s, 'parent_email')
                    || str_contains($s, 'from `notifications`')
                    || str_contains($s, 'from notifications');
            }));

            $bottleneck = 'controller/view';
            if ($page === 'Login GET view') {
                $bottleneck = 'no DB (auth layout)';
            } elseif ($role === 'Teacher' && $layoutQueries >= 4) {
                $bottleneck = 'sidebar parent-list queries';
            } elseif ($role === 'Student' && $count >= 4) {
                $bottleneck = 'sidebar enrollments + layout';
            } elseif ($role === 'Parent') {
                $bottleneck = 'sidebar children + dashboard';
            } elseif ($count >= 8) {
                $bottleneck = 'many sequential queries';
            } elseif ($count <= 4) {
                $bottleneck = 'auth + layout + page query';
            }

            return [
                $page,
                $role ?? '-',
                'ok',
                (string) $count,
                sprintf('%.1f', $queryMs),
                sprintf('%.0f', $renderMs),
                (string) $htmlKb,
                $bottleneck,
            ];
        } catch (\Throwable $e) {
            $log = DB::getQueryLog();
            return [$page, $role ?? '-', 'ERR '.$e->getMessage(), (string) count($log), sprintf('%.1f', array_sum(array_column($log, 'time'))), '-', '-', 'error'];
        } finally {
            DB::disableQueryLog();
            if (Auth::check()) {
                Auth::logout();
            }
            Session::flush();
        }
    }
}
