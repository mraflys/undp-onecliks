<?php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Context to always be appended to exception logs.
     *
     * @return array
     */
    protected function context()
    {
        // Return context without sensitive SQL query data
        return array_merge(parent::context(), [
            // Add custom context here
        ]);
    }

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Handle session expired (419 Page Expired)
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException  && $exception->getStatusCode() == 419) {
            return redirect()->route('login')->with('message_error', 'Your session has expired. Please login again.');
        }

        // Handle TokenMismatchException
        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return redirect()->route('login')->with('message_error', 'Your session has expired. Please login again.');
        }

        // Hide SQL queries in production/non-debug mode
        if (! config('app.debug')) {
            // For QueryException, show generic message without SQL details
            if ($exception instanceof \Illuminate\Database\QueryException) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'A database error occurred. Please contact support.',
                        'error'   => 'Database Error',
                    ], 500);
                }

                // For web requests, check if custom error view exists
                if (view()->exists('errors.database')) {
                    return response()->view('errors.database', [], 500);
                }

                // Fallback to simple error response
                return response('A database error occurred. Please contact support.', 500);
            }
        }

        return parent::render($request, $exception);
    }
}
