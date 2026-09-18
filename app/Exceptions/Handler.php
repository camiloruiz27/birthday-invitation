<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
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
    }

    /**
     * Render the small set of public-facing failures as a real Inertia page.
     *
     * Laravel's detailed exception screen remains the right tool while
     * developing locally. In production, however, an unknown route should
     * still feel like part of MisterioCode, whether it was reached with a
     * full page load or an Inertia visit.
     */
    public function render($request, Throwable $e)
    {
        /** @var Response $response */
        $response = parent::render($request, $e);
        $status = $response->getStatusCode();

        if (config('app.debug') || ! in_array($status, [403, 404, 419, 500, 502], true)) {
            return $response;
        }

        return Inertia::render('Error', [
            'status' => $status,
        ])->toResponse($request)->setStatusCode($status);
    }
}
