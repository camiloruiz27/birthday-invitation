<?php

namespace Tests\Feature\Express;

use App\Modules\Express\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The host gives the database user 500 connections an hour for the WHOLE site,
 * and every request that runs a query costs one. This plays a complete round of
 * five people the way phones really behave (landing, forms, a poll every few
 * seconds for the five minutes, every action) and counts how many requests
 * touched the database — a number a fair can be planned with.
 */
class ExpressBudgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-28 15:00:00');
        config(['express.events.usb-cali-2026.ends_at' => '2026-12-31 23:59:59']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** @var array<int, int> queries run by each request that ran any */
    private array $touching = [];

    private int $requests = 0;

    /** Runs one request and remembers whether it touched the database. */
    private function counted(callable $request)
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $request();

        $queries = count(DB::getQueryLog());
        $this->requests++;

        if ($queries > 0) {
            $this->touching[] = $queries;
        }

        return $response;
    }

    public function test_a_round_of_five_costs_few_database_connections(): void
    {
        // Entry: landing, the host's form, the host creates the room.
        $this->counted(fn () => $this->get(route('express.home')));
        $this->counted(fn () => $this->get(route('express.create')));
        $host = basename((string) $this->counted(fn () => $this->post(route('express.rooms.store'), [
            'case_slug' => 'el-mensaje-de-las-11-03', 'nickname' => 'Ana', 'contact_type' => 'none',
        ]))->headers->get('Location'));

        $code = Room::query()->firstOrFail()->code;
        $tokens = [$host];

        // Four friends: the join form, then joining; each opens their page.
        foreach (['Leo', 'Eva', 'Max', 'Sol'] as $name) {
            $this->counted(fn () => $this->get(route('express.join', $code)));
            $tokens[] = basename((string) $this->counted(fn () => $this->post(route('express.join.store', $code), [
                'nickname' => $name, 'contact_type' => 'none',
            ]))->headers->get('Location'));
        }

        foreach ($tokens as $token) {
            $this->counted(fn () => $this->get(route('express.room', $token)));
        }

        $this->counted(fn () => $this->postJson(route('express.start', $host)));

        // The five minutes: everybody polls every 3 s until accusations open.
        $poll = function () use ($tokens) {
            foreach ($tokens as $token) {
                $this->counted(fn () => $this->getJson(route('express.state', $token)));
            }
        };

        $ticks = 0;
        for ($second = 3; $second <= 222; $second += 3, $ticks++) {
            Carbon::setTestNow(Carbon::parse('2026-10-28 15:00:00')->addSeconds($second));
            $poll();

            if ($second === 48) {
                // Three questions to two suspects (authored mode keeps this deterministic).
                $this->counted(fn () => $this->postJson(route('express.ask', $host), ['suspect_id' => 'nora', 'question' => 'x', 'topic_id' => 'reproduccion']));
                $this->counted(fn () => $this->postJson(route('express.ask', $host), ['suspect_id' => 'nora', 'question' => 'x', 'topic_id' => 'creditos']));
                $this->counted(fn () => $this->postJson(route('express.ask', $tokens[1]), ['suspect_id' => 'luisa', 'question' => 'x', 'topic_id' => 'ensayo']));
            }

            if ($second === 90) {
                $this->counted(fn () => $this->postJson(route('express.share', $tokens[1]), ['evidence_id' => 'E2']));
            }
        }

        // Accusing: each player loads the form state and sends their answer.
        $case = app(\App\Modules\Express\Support\ExpressCaseRegistry::class)->find('el-mensaje-de-las-11-03');

        foreach ($tokens as $token) {
            $state = $this->counted(fn () => $this->getJson(route('express.state', $token)))->json();
            $this->counted(fn () => $this->postJson(route('express.accuse', $token), [
                'who' => $state['accusation']['options']['who'][0]['id'],
                'how' => $state['accusation']['options']['how'][0]['id'],
                'why' => $state['accusation']['options']['why'][0]['id'],
            ]));
        }

        // Everybody sees the reveal.
        foreach ($tokens as $token) {
            $this->counted(fn () => $this->getJson(route('express.state', $token)));
        }

        $this->assertSame('reveal', $this->getJson(route('express.state', $host))->json('room.status'));
        $this->assertNotNull($case);

        $connections = count($this->touching);

        fwrite(STDERR, sprintf(
            "\n[express budget] %d requests in one round of 5 people (%d polls); %d touched the database, %d queries in total.\n",
            $this->requests,
            $ticks * 5,
            $connections,
            array_sum($this->touching),
        ));

        // The bound a fair is planned with: well under the 500 an hour for one
        // group. If a change pushes this up, 15 groups an hour stops fitting.
        $this->assertLessThanOrEqual(45, $connections, 'a round of five must stay cheap in connections');
    }
}
