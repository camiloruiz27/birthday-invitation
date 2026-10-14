<?php

namespace Tests\Feature\Platform;

use App\Http\Middleware\ResetDatabaseConnection;
use App\Models\User;
use App\Modules\Immersion\Ai\Contracts\InterrogationProvider;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Support\MailBudget;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Support\ConnectionBudget;
use App\Modules\Platform\Support\PublishedCases;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The small guards around the host's limits that do not belong to any one
 * feature: the cached public pages, what the admin sees of the allowances, a
 * persistent connection that must not inherit a dead request's transaction, and
 * the interrogation question that must come back when anything at all fails.
 */
class HostingLimitsTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    // --- cached public pages -------------------------------------------------

    public function test_the_published_cases_are_read_once_a_minute_not_once_per_visit(): void
    {
        $this->catalogCase('habitacion-314');
        Cache::flush();

        PublishedCases::all();

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        PublishedCases::all();
        PublishedCases::all();

        $this->assertSame(0, $queries);
    }

    public function test_a_case_changed_through_the_application_shows_at_once(): void
    {
        $case = $this->catalogCase('habitacion-314');
        Cache::flush();

        $this->assertNotNull(PublishedCases::find('habitacion-314'));
        $this->assertCount(1, PublishedCases::all());

        $case->update(['published_at' => null]);

        $this->assertNull(PublishedCases::find('habitacion-314'));
        $this->assertCount(0, PublishedCases::all());
    }

    public function test_a_slug_that_cannot_be_a_case_never_reaches_the_cache_or_the_database(): void
    {
        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        foreach (['../../etc/passwd', 'UPPER', 'con espacio', str_repeat('a', 200), ''] as $slug) {
            $this->assertNull(PublishedCases::find($slug));
        }

        $this->assertSame(0, $queries);
    }

    public function test_the_public_pages_still_serve_and_404_correctly(): void
    {
        $case = $this->catalogCase('habitacion-314');

        $this->get(route('cases.show', $case->slug))->assertOk();
        $this->get(route('ads.case', $case->slug))->assertOk();
        $this->get(route('cases.show', 'no-existe'))->assertNotFound();
        $this->get(route('cases.index'))->assertOk();
        $this->get(route('pricing'))->assertOk();
    }

    // --- admin ---------------------------------------------------------------

    public function test_the_admin_sees_how_much_of_each_allowance_is_spent(): void
    {
        config(['platform.connection_budget.per_hour' => 500, 'immersion.mail.daily_limit' => 100]);
        Cache::flush();
        ConnectionBudget::hit();
        MailBudget::record();

        $admin = $this->gameMaster(attributes: ['email' => 'admin@example.com']);
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin->fresh())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('limits.connections.limit', 500)
                ->where('limits.mail.limit', 100)
                ->where('limits.mail.used', 1)
                ->where('limits.mail.remaining', 99)
                ->has('limits.connections.this_hour'));
    }

    // --- persistent connections ---------------------------------------------

    public function test_a_transaction_a_dead_request_left_open_is_rolled_back_before_the_next_one(): void
    {
        config(['database.persistent' => true]);
        // RefreshDatabase wraps the test itself in one transaction; what matters
        // is that the middleware takes the level back to zero.
        DB::beginTransaction();
        $this->assertGreaterThan(0, DB::transactionLevel());

        (new ResetDatabaseConnection())->terminate(Request::create('/x'), response('ok'));

        $this->assertSame(0, DB::transactionLevel());
    }

    public function test_with_ordinary_connections_it_leaves_everything_alone(): void
    {
        config(['database.persistent' => false]);
        DB::beginTransaction();
        $level = DB::transactionLevel();

        (new ResetDatabaseConnection())->terminate(Request::create('/x'), response('ok'));

        $this->assertSame($level, DB::transactionLevel());
        DB::rollBack();
    }

    // --- interrogation -------------------------------------------------------

    public function test_a_question_comes_back_when_anything_unexpected_fails_after_it_was_taken(): void
    {
        $this->withoutExceptionHandling();

        $game = Game::create(['name' => 'Mesa', 'status' => 'running', 'interrogation_enabled' => true]);
        $player = $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'access_token' => 'tok-ana',
        ]);

        // Not InterrogationUnavailable: a missing testimony file, a broken case
        // manifest, a PHP error. Used to leave the question spent.
        $this->app->bind(InterrogationProvider::class, fn () => new class implements InterrogationProvider
        {
            public function ask(InterrogationSession $session, string $question): string
            {
                throw new \RuntimeException('testimony file missing');
            }
        });

        try {
            $this->postJson(
                route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
                ['question' => 'Donde estabas?']
            );
            $this->fail('The unexpected error should have propagated.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('testimony file missing', $exception->getMessage());
        }

        $this->assertSame(0, InterrogationSession::first()->questions_used, 'The player lost a question to our error.');
    }
}
