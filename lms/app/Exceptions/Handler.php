<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            if (config('app.debug') || $request->expectsJson()) {
                return null;
            }

            if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return null;
            }

            \Log::error('Database operation failed: '.$e->getMessage());

            if (class_exists(\Brian2694\Toastr\Facades\Toastr::class)) {
                \Brian2694\Toastr\Facades\Toastr::error('Unable to complete the operation. Please try again.', 'Error');
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Unable to complete the operation. Please try again.');
        });
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function render($request, Throwable $exception)
    // {
    //     if($this->isHttpException($exception)) {
    //         return response()->view('errors.404');
    //     } else {
    //         return response()->view('errors.500');
    //     }
    // }
}
