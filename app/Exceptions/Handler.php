<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use PDOException;
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
        // The database refusing connections is an outage, not a bug: when the
        // host's per-hour connection allowance runs out EVERY request fails the
        // same way, and writing each one's stack trace to the log buries the
        // line that matters under thousands of copies. Say it once.
        $this->reportable(function (Throwable $e) {
            if (! self::databaseUnavailable($e)) {
                return;
            }

            try {
                if (Cache::add('platform:db-unavailable-logged', true, 600)) {
                    Log::critical('db_connection_limit_reached', [
                        'message' => $e->getMessage(),
                        'note' => 'Repeats are not logged for 10 minutes.',
                    ]);
                }
            } catch (Throwable) {
                // Logging the outage must not become a second one.
            }

            return false;
        });
    }

    /**
     * The database would not take another connection: the per-hour or
     * per-user allowance of the host (MySQL 1226 / 1203 / 1040), or no server
     * to talk to (2002 / 2006). All of them clear on their own, which is what
     * "try again in a few minutes" means.
     */
    public static function databaseUnavailable(Throwable $e): bool
    {
        if (! $e instanceof QueryException && ! $e instanceof PDOException) {
            return false;
        }

        $code = $e instanceof QueryException
            ? (int) ($e->errorInfo[1] ?? 0)
            : (int) ($e->errorInfo[1] ?? $e->getCode());

        return in_array($code, [1040, 1203, 1226, 2002, 2006], true);
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
        if (self::databaseUnavailable($e)) {
            return $this->databaseUnavailableResponse($request);
        }

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

    /**
     * 503 + Retry-After instead of a 500: it is the truth (nothing is broken,
     * the database is momentarily out of connections) and tells browsers and
     * the page's own polling to back off instead of hammering.
     */
    private function databaseUnavailableResponse($request): Response
    {
        $headers = ['Retry-After' => '120'];

        // A page that is only refreshing itself in the background (an Inertia
        // partial reload) must NOT be navigated away to an error screen: the
        // player would lose their place for a hiccup. An empty 503 makes the
        // client keep what it has and try again later.
        if ($request->headers->has('X-Inertia-Partial-Data')) {
            return response('', 503, $headers);
        }

        // A visit that is navigating (a link click) is sent to a real page
        // load of the same URL, which answers with the static page below. It
        // cannot loop: that second request is not an Inertia one.
        if ($request->headers->has('X-Inertia')) {
            return Inertia::location($request->fullUrl());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Hay mucha demanda en este momento. No se perdio nada: intentalo de nuevo en unos minutos.',
                'unavailable' => true,
            ], 503, $headers);
        }

        // Plain Blade, not Inertia: Inertia's shared props read the logged-in
        // user from the very database that just refused us, which would turn
        // this into a second failure.
        return response()->view('errors.db-unavailable', [], 503, $headers);
    }
}
