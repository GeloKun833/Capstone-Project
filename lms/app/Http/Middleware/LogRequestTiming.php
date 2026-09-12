<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Request timing. Set LOG_REQUEST_TIMING=true to log query counts (safe fields only).
 * Always adds a Server-Timing header with total duration.
 */
class LogRequestTiming
{
    public function handle(Request $request, Closure $next): Response
    {
        $detail = filter_var(env('LOG_REQUEST_TIMING', false), FILTER_VALIDATE_BOOLEAN);
        $started = microtime(true);
        $queryCount = 0;
        $queryMs = 0.0;

        if ($detail && ! app()->runningInConsole()) {
            try {
                DB::flushQueryLog();
                DB::enableQueryLog();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        /** @var Response $response */
        $response = $next($request);

        $totalMs = round((microtime(true) - $started) * 1000, 1);

        if ($detail) {
            try {
                $log = DB::getQueryLog();
                $queryCount = count($log);
                $queryMs = round(array_sum(array_column($log, 'time')), 1);
                DB::disableQueryLog();
            } catch (\Throwable $e) {
                // ignore
            }

            if ($request->is('dashboard', 'login', 'home') || $totalMs >= 1000) {
                Log::info('perf.request', [
                    'method' => $request->method(),
                    'path' => '/'.$request->path(),
                    'user_id' => optional($request->user())->id,
                    'total_ms' => $totalMs,
                    'db_ms' => $queryMs,
                    'queries' => $queryCount,
                    'status' => $response->getStatusCode(),
                ]);
            }
        }

        $timing = sprintf('app;dur=%.1f', $totalMs);
        if ($detail) {
            $timing .= sprintf(', db;dur=%.1f;desc="%d queries"', $queryMs, $queryCount);
        }
        $response->headers->set('Server-Timing', $timing);

        return $response;
    }
}
