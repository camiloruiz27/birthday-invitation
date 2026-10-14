<?php

namespace Tests\Feature\Platform;

use App\Exceptions\Handler;
use App\Modules\Platform\Support\ConnectionBudget;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use PDOException;
use Tests\TestCase;

/**
 * When the host's per-hour database connection allowance (MySQL error 1226)
 * runs out, every request fails the same way until the hour turns over. These
 * cover what the app does about it: answer honestly (503, not 500), keep the
 * log readable, warn before it happens, and not spend connections on pages
 * that do not need them.
 */
class ConnectionBudgetTest extends TestCase
{
    use RefreshDatabase;

    private function limitReached(int $code = 1226): QueryException
    {
        $pdo = new PDOException("SQLSTATE[HY000] [{$code}] User has exceeded the 'max_connections_per_hour' resource");
        $pdo->errorInfo = ['HY000', $code, 'exceeded'];

        return new QueryException('select 1', [], $pdo);
    }

    /**
     * An Inertia visit with the asset version the server expects: with any
     * other, Inertia answers 409 itself before the route ever runs.
     *
     * @return array<string, string>
     */
    private function inertiaHeaders(): array
    {
        $version = app(\App\Http\Middleware\HandleInertiaRequests::class)->version(request());

        return ['X-Inertia' => 'true'] + ($version === null ? [] : ['X-Inertia-Version' => $version]);
    }

    public function test_the_connection_errors_that_clear_on_their_own_are_recognised(): void
    {
        foreach ([1226, 1203, 1040, 2002, 2006] as $code) {
            $this->assertTrue(Handler::databaseUnavailable($this->limitReached($code)), "MySQL {$code}");
        }

        // A bug in a query is a 500 and must stay one.
        $this->assertFalse(Handler::databaseUnavailable($this->limitReached(1064)));
        $this->assertFalse(Handler::databaseUnavailable(new \RuntimeException('boom')));
    }

    public function test_a_page_load_answers_503_with_retry_after_instead_of_500(): void
    {
        Route::middleware('web')->get('/__db-limit', fn () => throw $this->limitReached());

        $response = $this->get('/__db-limit');

        $response->assertStatus(503)
            ->assertHeader('Retry-After', '120')
            ->assertSee('Mucha demanda en este momento');
    }

    public function test_a_background_refresh_gets_an_empty_503_so_the_page_keeps_what_it_has(): void
    {
        Route::middleware('web')->get('/__db-limit', fn () => throw $this->limitReached());

        $response = $this->get('/__db-limit', $this->inertiaHeaders() + [
            'X-Inertia-Partial-Data' => 'items',
            'X-Inertia-Partial-Component' => 'Player/Inbox',
        ]);

        $response->assertStatus(503)->assertHeader('Retry-After', '120');
        $this->assertSame('', $response->getContent());
    }

    public function test_a_link_click_is_sent_to_a_real_page_load(): void
    {
        Route::middleware('web')->get('/__db-limit', fn () => throw $this->limitReached());

        $this->get('/__db-limit', $this->inertiaHeaders())
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location');
    }

    public function test_a_json_request_gets_a_json_503(): void
    {
        Route::middleware('web')->post('/__db-limit', fn () => throw $this->limitReached());

        $this->postJson('/__db-limit')
            ->assertStatus(503)
            ->assertHeader('Retry-After', '120')
            ->assertJson(['unavailable' => true]);
    }

    public function test_the_outage_is_logged_once_not_once_per_request(): void
    {
        Route::middleware('web')->get('/__db-limit', fn () => throw $this->limitReached());

        Log::spy();

        $this->get('/__db-limit');
        $this->get('/__db-limit');
        $this->get('/__db-limit');

        Log::shouldHaveReceived('critical')->with('db_connection_limit_reached', \Mockery::any())->once();
    }

    public function test_an_ordinary_error_is_still_a_500(): void
    {
        Route::middleware('web')->get('/__boom', fn () => throw new \RuntimeException('boom'));

        $this->get('/__boom')->assertStatus(500);
    }

    public function test_the_counter_warns_once_at_eighty_percent_counting_requests_that_ran_a_query(): void
    {
        config(['platform.connection_budget.per_hour' => 10]);
        Cache::flush();
        Log::spy();

        // An unknown case is never cached, so each visit runs its query.
        foreach (range(1, 12) as $i) {
            $this->get(route('cases.show', 'no-existe'))->assertNotFound();
        }

        Log::shouldHaveReceived('warning')->with('db_connection_budget_high', \Mockery::any())->once();
        $this->assertSame(12, ConnectionBudget::summary()['this_hour']);
    }

    public function test_a_request_answered_from_the_cache_costs_no_connection(): void
    {
        Cache::flush();
        $this->get('/')->assertOk();
        $afterWarmUp = ConnectionBudget::summary()['this_hour'];

        $this->get('/')->assertOk();
        $this->get('/')->assertOk();

        $this->assertSame($afterWarmUp, ConnectionBudget::summary()['this_hour']);
    }

    public function test_the_poll_endpoints_do_not_count_against_the_budget(): void
    {
        config(['platform.connection_budget.per_hour' => 10]);
        Cache::flush();
        Log::spy();

        foreach (range(1, 20) as $i) {
            $this->getJson(route('immersion.poll.player', 'whoever'));
        }

        Log::shouldNotHaveReceived('warning', ['db_connection_budget_high', \Mockery::any()]);
    }

    public function test_the_landing_reads_the_catalog_once_a_minute_not_once_per_visit(): void
    {
        Cache::flush();

        $this->get('/')->assertOk();

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            if (str_contains($query->sql, 'mystery_cases')) {
                $queries[] = $query->sql;
            }
        });

        $this->get('/')->assertOk();
        $this->get('/')->assertOk();

        $this->assertSame([], $queries, 'Anonymous ad traffic must not hit the catalog table on every visit.');
    }
}
