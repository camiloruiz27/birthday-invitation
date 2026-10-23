<?php

namespace Tests\Feature\Express;

use App\Modules\Express\Models\Accusation;
use App\Modules\Express\Models\Lead;
use App\Modules\Express\Models\Player;
use App\Modules\Express\Models\Question;
use App\Modules\Express\Models\Room;
use App\Modules\Express\Support\ExpressCase;
use App\Modules\Express\Support\ExpressCaseRegistry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * A round, played through the real routes: opening a room, joining, the clock
 * that decides what is on each screen, the team's three questions, the
 * accusation and the reveal — plus the promises that make a fair survivable
 * (nothing secret on the wire, the poll costs no queries).
 */
class ExpressRoomTest extends TestCase
{
    use RefreshDatabase;

    private const CASE = 'el-mensaje-de-las-11-03';

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

    private function case(): ExpressCase
    {
        return app(ExpressCaseRegistry::class)->find(self::CASE);
    }

    /** Seconds from now, for the whole test. */
    private function later(int $seconds): void
    {
        Carbon::setTestNow(now()->addSeconds($seconds));
    }

    private function tokenFrom($response): string
    {
        $response->assertRedirect();

        return basename((string) $response->headers->get('Location'));
    }

    /** @return string the host's token */
    private function createRoom(string $nickname = 'Ana', array $extra = []): string
    {
        return $this->tokenFrom($this->post(route('express.rooms.store'), $extra + [
            'case_slug' => self::CASE,
            'nickname' => $nickname,
            'contact_type' => 'none',
        ]));
    }

    private function join(string $code, string $nickname, array $extra = []): string
    {
        return $this->tokenFrom($this->post(route('express.join.store', $code), $extra + [
            'nickname' => $nickname,
            'contact_type' => 'none',
        ]));
    }

    /** @return array{0: string, 1: string} host and friend tokens, round not started */
    private function lobbyOfTwo(): array
    {
        $host = $this->createRoom('Ana');
        $friend = $this->join(Room::query()->firstOrFail()->code, 'Leo');

        return [$host, $friend];
    }

    /** @return array{0: string, 1: string} host and friend tokens, round started */
    private function runningRoom(): array
    {
        [$host, $friend] = $this->lobbyOfTwo();
        $this->postJson(route('express.start', $host))->assertOk();

        return [$host, $friend];
    }

    /** @return array<string, mixed> */
    private function state(string $token): array
    {
        return $this->getJson(route('express.state', $token))->assertOk()->json();
    }

    private function ask(string $token, string $suspect, string $question, ?string $topic = null)
    {
        return $this->postJson(route('express.ask', $token), array_filter([
            'suspect_id' => $suspect,
            'question' => $question,
            'topic_id' => $topic,
        ]));
    }

    /** @return array{who: string, how: string, why: string} the ids that make a 100 */
    private function winningIds(array $state): array
    {
        $case = $this->case();
        $correct = $case->correct();
        $options = $state['accusation']['options'];
        $whoLabel = collect($case->whoOptions())->firstWhere('id', $correct['who'])['label'];

        return [
            'who' => collect($options['who'])->firstWhere('label', $whoLabel)['id'],
            'how' => collect($options['how'])->firstWhere('label', $case->howOptions()[$correct['how']])['id'],
            'why' => collect($options['why'])->firstWhere('label', $case->whyOptions()[$correct['why']])['id'],
        ];
    }

    /** @return array{who: string, how: string, why: string} the first wrong option of each */
    private function losingIds(array $state): array
    {
        $case = $this->case();
        $correct = $case->correct();
        $options = $state['accusation']['options'];
        $rightLabels = [
            collect($case->whoOptions())->firstWhere('id', $correct['who'])['label'],
            $case->howOptions()[$correct['how']],
            $case->whyOptions()[$correct['why']],
        ];

        return [
            'who' => collect($options['who'])->first(fn ($o) => $o['label'] !== $rightLabels[0])['id'],
            'how' => collect($options['how'])->first(fn ($o) => $o['label'] !== $rightLabels[1])['id'],
            'why' => collect($options['why'])->first(fn ($o) => $o['label'] !== $rightLabels[2])['id'],
        ];
    }

    // ------------------------------------------------------------- the door

    public function test_the_host_opens_a_room_and_is_seated_as_a_player(): void
    {
        $token = $this->createRoom('Ana');

        $room = Room::query()->firstOrFail();
        $this->assertSame(Room::LOBBY, $room->status);
        $this->assertSame(self::CASE, $room->case_slug);
        $this->assertMatchesRegularExpression('/^[A-HJ-KM-NP-Z2-9]{5}$/', $room->code);

        $state = $this->state($token);

        $this->assertSame('lobby', $state['room']['status']);
        $this->assertTrue($state['me']['is_gm']);
        $this->assertSame('A', $state['me']['audience']);
        $this->assertFalse($state['me']['can_start'], 'one person cannot start');
        $this->assertSame([], $state['evidence']);
    }

    public function test_a_friend_joins_with_the_code_and_audiences_alternate(): void
    {
        $host = $this->createRoom('Ana');
        $code = Room::query()->firstOrFail()->code;

        $this->get(route('express.join', $code))->assertOk();
        $friend = $this->join($code, 'Leo');
        $third = $this->join($code, 'Eva');

        $this->assertSame('B', $this->state($friend)['me']['audience']);
        $this->assertSame('A', $this->state($third)['me']['audience']);
        $this->assertTrue($this->state($host)['me']['can_start']);
        $this->assertCount(3, $this->state($host)['players']);
    }

    public function test_a_room_holds_five_people(): void
    {
        $this->createRoom('Ana');
        $code = Room::query()->firstOrFail()->code;

        foreach (['B', 'C', 'D', 'E'] as $name) {
            $this->join($code, 'Amigo '.$name);
        }

        $this->post(route('express.join.store', $code), ['nickname' => 'Sexto', 'contact_type' => 'none'])
            ->assertSessionHasErrors('nickname');

        $this->assertSame(5, Player::query()->count());
    }

    public function test_two_people_with_the_same_nickname_are_told_apart(): void
    {
        $this->createRoom('Ana');
        $code = Room::query()->firstOrFail()->code;
        $this->join($code, 'ana');
        $this->join($code, 'Ana');

        $this->assertSame(['Ana', 'ana 2', 'Ana 3'], Player::query()->orderBy('joined_order')->pluck('nickname')->all());
    }

    public function test_the_nickname_is_cleaned_and_required(): void
    {
        $this->post(route('express.rooms.store'), ['case_slug' => self::CASE, 'nickname' => ' ', 'contact_type' => 'none'])
            ->assertSessionHasErrors('nickname');

        $this->createRoom("  <b>Ana</b>\n  María  ");

        $this->assertSame('Ana María', Player::query()->firstOrFail()->nickname);
    }

    public function test_an_unknown_case_cannot_open_a_room(): void
    {
        $this->post(route('express.rooms.store'), ['case_slug' => 'no-existe', 'nickname' => 'Ana', 'contact_type' => 'none'])
            ->assertSessionHasErrors('case_slug');

        $this->assertSame(0, Room::query()->count());
    }

    public function test_the_honeypot_stops_bots(): void
    {
        $this->post(route('express.rooms.store'), [
            'case_slug' => self::CASE, 'nickname' => 'Bot', 'contact_type' => 'none', 'website' => 'http://spam',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, Room::query()->count());
    }

    public function test_a_closed_event_takes_no_rooms(): void
    {
        config(['express.events.usb-cali-2026.ends_at' => '2026-10-01 00:00:00']);

        $this->get(route('express.home'))->assertOk()
            ->assertInertia(fn ($page) => $page->where('enabled', false)->where('closed_reason', fn ($r) => str_contains($r, 'terminó')));

        $this->post(route('express.rooms.store'), ['case_slug' => self::CASE, 'nickname' => 'Ana', 'contact_type' => 'none']);

        $this->assertSame(0, Room::query()->count());
    }

    // --------------------------------------------------------------- contact

    public function test_a_contact_needs_authorisation_and_is_stored_encrypted(): void
    {
        $this->post(route('express.rooms.store'), [
            'case_slug' => self::CASE, 'nickname' => 'Ana', 'contact_type' => 'email', 'contact' => 'ana@example.com',
        ])->assertSessionHasErrors(['consent', 'adult']);

        $this->assertSame(0, Lead::query()->count());

        $this->createRoom('Ana', [
            'contact_type' => 'email', 'contact' => ' Ana@Example.COM ', 'consent' => true, 'adult' => true,
        ]);

        $lead = Lead::query()->firstOrFail();
        $this->assertSame('ana@example.com', $lead->contact);
        $this->assertSame('email', $lead->contact_type);
        $this->assertSame(config('express.consent.version'), $lead->consent_version);
        $this->assertNotNull($lead->consent_at);

        $raw = DB::table('express_leads')->value('contact');
        $this->assertStringNotContainsString('ana@example.com', $raw, 'stored encrypted');
        $this->assertSame($lead->id, Player::query()->firstOrFail()->lead_id);
    }

    public function test_the_same_contact_is_one_lead_and_a_whatsapp_number_is_normalised(): void
    {
        $this->createRoom('Ana', ['contact_type' => 'whatsapp', 'contact' => '300 123 4567', 'consent' => true, 'adult' => true]);
        $code = Room::query()->firstOrFail()->code;
        $this->join($code, 'Ana 2', ['contact_type' => 'whatsapp', 'contact' => '+57 (300) 123-4567', 'consent' => true, 'adult' => true]);

        $this->assertSame(1, Lead::query()->count());
        $this->assertSame('573001234567', Lead::query()->firstOrFail()->contact);
    }

    public function test_an_invalid_contact_is_refused(): void
    {
        $this->post(route('express.rooms.store'), [
            'case_slug' => self::CASE, 'nickname' => 'Ana', 'contact_type' => 'email', 'contact' => 'no-es-correo',
            'consent' => true, 'adult' => true,
        ])->assertSessionHasErrors('contact');
    }

    // ----------------------------------------------------------------- start

    public function test_only_the_host_starts_and_needs_two_people(): void
    {
        $host = $this->createRoom('Ana');

        $this->postJson(route('express.start', $host))->assertStatus(422)->assertJsonPath('code', 'not_enough_players');

        $friend = $this->join(Room::query()->firstOrFail()->code, 'Leo');

        $this->postJson(route('express.start', $friend))->assertStatus(403)->assertJsonPath('code', 'not_gm');

        $this->postJson(route('express.start', $host))->assertOk()->assertJsonPath('state.room.status', 'playing');
        $this->assertSame(Room::RUNNING, Room::query()->firstOrFail()->status);
        $this->assertNotNull(Room::query()->firstOrFail()->started_at_ms);

        // A second tap changes nothing.
        $startedAt = Room::query()->firstOrFail()->started_at_ms;
        $this->later(5);
        $this->postJson(route('express.start', $host))->assertOk();
        $this->assertSame($startedAt, Room::query()->firstOrFail()->started_at_ms);
    }

    public function test_nothing_can_be_done_before_the_round_starts(): void
    {
        [$host] = $this->lobbyOfTwo();

        $this->ask($host, 'nora', 'x', 'reproduccion')->assertStatus(422)->assertJsonPath('code', 'not_started');
        $this->postJson(route('express.share', $host), ['evidence_id' => 'E1'])->assertStatus(422)->assertJsonPath('code', 'not_started');
        $this->postJson(route('express.accuse', $host), ['who' => 'a', 'how' => 'b', 'why' => 'c'])->assertStatus(422)->assertJsonPath('code', 'not_started');
    }

    // ----------------------------------------------------------- the clock

    public function test_each_side_sees_its_own_clues_and_the_clock_unlocks_the_rest(): void
    {
        [$host, $friend] = $this->runningRoom();

        // 0 s: A has E1, B has E2.
        $this->assertSame(['E1'], array_column($this->state($host)['evidence'], 'id'));
        $this->assertSame(['E2'], array_column($this->state($friend)['evidence'], 'id'));

        // 45 s: E3 for everyone, and the interrogations open.
        $this->later(45);
        $this->assertSame(['E1', 'E3'], array_column($this->state($host)['evidence'], 'id'));
        $this->assertSame(['E2', 'E3'], array_column($this->state($friend)['evidence'], 'id'));
        $this->assertTrue($this->state($host)['interrogation']['open']);

        // 120 s: E4 to A, E5 to B.
        $this->later(75);
        $this->assertSame(['E1', 'E3', 'E4'], array_column($this->state($host)['evidence'], 'id'));
        $this->assertSame(['E2', 'E3', 'E5'], array_column($this->state($friend)['evidence'], 'id'));

        // 180 s: E6 for everyone.
        $this->later(60);
        $this->assertSame(['E1', 'E3', 'E4', 'E6'], array_column($this->state($host)['evidence'], 'id'));
        $this->assertSame('playing', $this->state($host)['room']['status']);

        // 220 s: the accusation opens.
        $this->later(40);
        $state = $this->state($host);
        $this->assertSame('accusing', $state['room']['status']);
        $this->assertTrue($state['accusation']['open']);
        $this->assertCount(3, $state['accusation']['options']['who']);
    }

    public function test_nothing_is_open_before_its_time(): void
    {
        [$host] = $this->runningRoom();

        $this->later(10);
        $this->assertFalse($this->state($host)['interrogation']['open']);
        $this->ask($host, 'nora', 'x', 'reproduccion')->assertStatus(422)->assertJsonPath('code', 'too_early');

        $this->later(60);
        $this->assertNull($this->state($host)['accusation']['options']);
        $this->postJson(route('express.accuse', $host), ['who' => 'a', 'how' => 'b', 'why' => 'c'])->assertStatus(422)->assertJsonPath('code', 'too_early');
    }

    public function test_the_next_unlock_counts_down(): void
    {
        [$host] = $this->runningRoom();

        $this->later(5);

        $this->assertSame(['at_s' => 45, 'in_s' => 40], $this->state($host)['next_unlock']);
    }

    public function test_a_late_arrival_still_gets_everything_unlocked_so_far(): void
    {
        [$host] = $this->runningRoom();
        $this->later(60);

        $third = $this->join(Room::query()->firstOrFail()->code, 'Eva');

        $this->assertSame(['E1', 'E3'], array_column($this->state($third)['evidence'], 'id'), 'third player is A');
    }

    public function test_nobody_can_join_after_the_cut_off(): void
    {
        [$host] = $this->runningRoom();
        $code = Room::query()->firstOrFail()->code;

        $this->later(131);

        $this->post(route('express.join.store', $code), ['nickname' => 'Tarde', 'contact_type' => 'none'])
            ->assertSessionHasErrors('nickname');
        $this->assertFalse($this->state($host)['room']['joinable']);
        $this->assertSame(2, Player::query()->count());
    }

    // --------------------------------------------------------------- sharing

    public function test_a_private_clue_can_be_shared_with_the_group(): void
    {
        [$host, $friend] = $this->runningRoom();

        $this->assertSame(['E2'], array_column($this->state($friend)['evidence'], 'id'));

        $response = $this->postJson(route('express.share', $friend), ['evidence_id' => 'E2'])->assertOk();
        $this->assertTrue(collect($response->json('state.evidence'))->firstWhere('id', 'E2')['shared']);

        $seen = collect($this->state($host)['evidence'])->firstWhere('id', 'E2');
        $this->assertSame('Leo', $seen['shared_by']);
        $this->assertFalse($seen['mine']);
        $this->assertFalse($seen['can_share']);
    }

    public function test_only_your_own_unlocked_clue_can_be_shared(): void
    {
        [$host] = $this->runningRoom();

        // E2 belongs to the other side, E4 is not unlocked yet.
        $this->postJson(route('express.share', $host), ['evidence_id' => 'E2'])->assertStatus(422)->assertJsonPath('code', 'invalid');
        $this->postJson(route('express.share', $host), ['evidence_id' => 'E4'])->assertStatus(422)->assertJsonPath('code', 'invalid');
        $this->postJson(route('express.share', $host), ['evidence_id' => 'E3'])->assertStatus(422);
    }

    // ---------------------------------------------------------- interrogation

    public function test_a_suggested_question_is_answered_from_the_case_file(): void
    {
        [$host] = $this->runningRoom();
        $this->later(50);

        $this->ask($host, 'nora', '¿Cómo recibieron el audio?', 'reproduccion')
            ->assertOk()
            ->assertJsonPath('result.status', 'answered')
            ->assertJsonPath('state.interrogation.budget_used', 1)
            ->assertJsonPath('state.interrogation.budget_left', 2);

        $log = $this->state($host)['interrogation']['log'];
        $this->assertSame('La consola recibió un clip, sí, pero yo pensé que venía del sistema de la cabina. No revisé la programación.', $log[0]['answer']);
        $this->assertSame('Ana', $log[0]['nickname']);
    }

    public function test_a_free_text_question_is_matched_and_an_unclear_one_costs_nothing(): void
    {
        [$host] = $this->runningRoom();
        $this->later(50);

        $this->ask($host, 'nora', '¿Quién cambió los créditos del corto?')
            ->assertOk()
            ->assertJsonPath('result.topic_id', 'creditos');

        $this->ask($host, 'nora', '¿Cuál es tu comida favorita?')
            ->assertStatus(422)
            ->assertJsonPath('code', 'unclear');

        $this->assertSame(1, Question::query()->count());
        $this->assertSame(2, $this->state($host)['interrogation']['budget_left']);
    }

    public function test_asking_again_about_a_known_topic_is_free(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(50);

        $this->ask($host, 'nora', 'x', 'reproduccion')->assertOk();

        $this->ask($host, 'nora', 'x', 'reproduccion')->assertOk()->assertJsonPath('result.status', 'repeat');
        $this->ask($friend, 'nora', 'x', 'reproduccion')->assertOk()->assertJsonPath('result.status', 'repeat');

        $this->assertSame(1, Question::query()->count());
    }

    public function test_the_first_to_ask_a_suspect_keeps_them(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(50);

        $this->ask($host, 'nora', 'x', 'reproduccion')->assertOk();

        $this->ask($friend, 'nora', 'x', 'creditos')->assertStatus(422)->assertJsonPath('code', 'suspect_locked');
        $this->ask($friend, 'luisa', 'x', 'ensayo')->assertOk();

        $state = $this->state($friend);
        $nora = collect($state['suspects'])->firstWhere('id', 'nora');
        $this->assertSame('Ana', $nora['claimed_by']['nickname']);
        $this->assertTrue($nora['locked_for_me']);
        $this->assertFalse(collect($state['suspects'])->firstWhere('id', 'luisa')['locked_for_me']);
    }

    public function test_the_team_has_three_questions_in_total(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(50);

        $this->ask($host, 'nora', 'x', 'reproduccion')->assertOk();
        $this->ask($host, 'nora', 'x', 'creditos')->assertOk();
        $this->ask($friend, 'luisa', 'x', 'ensayo')->assertOk();

        $this->ask($friend, 'luisa', 'x', 'archivo')->assertStatus(422)->assertJsonPath('code', 'budget');

        $this->assertSame(3, Question::query()->count());
        $this->assertSame(0, $this->state($host)['interrogation']['budget_left']);
    }

    public function test_a_topic_must_belong_to_the_suspect_asked(): void
    {
        [$host] = $this->runningRoom();
        $this->later(50);

        $this->ask($host, 'nora', 'x', 'ensayo')->assertStatus(422)->assertJsonPath('code', 'invalid');
        $this->ask($host, 'fantasma', 'x', 'ensayo')->assertStatus(422)->assertJsonPath('code', 'invalid');
        $this->assertSame(0, Question::query()->count());
    }

    // -------------------------------------------------------------- accusing

    public function test_a_perfect_accusation_scores_100_and_the_round_ends_when_everyone_has_accused(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(225);

        $this->postJson(route('express.accuse', $host), $this->winningIds($this->state($host)))
            ->assertOk()
            ->assertJsonPath('state.accusation.submitted', true)
            ->assertJsonPath('state.room.status', 'accusing')
            ->assertJsonPath('state.reveal', null);

        // One answer in, and nothing is revealed — not even to the one who answered.
        $waiting = $this->state($friend);
        $this->assertSame(['Ana'], collect($waiting['players'])->where('accused', true)->pluck('nickname')->values()->all());
        $this->assertNull($waiting['reveal']);
        $this->assertSame([], $waiting['accusation']['waiting_for'], 'the friend waits for nobody but themselves');
        $this->assertSame(['Leo'], $this->state($host)['accusation']['waiting_for']);

        $this->postJson(route('express.accuse', $friend), $this->losingIds($this->state($friend)))->assertOk();

        $revealed = $this->state($host);
        $this->assertSame('reveal', $revealed['room']['status']);
        $this->assertSame(Room::FINISHED, Room::query()->firstOrFail()->status);

        $this->assertSame(100, $revealed['reveal']['my']['total']);
        $this->assertTrue($revealed['reveal']['my']['who']['correct']);
        $this->assertSame(40, $revealed['reveal']['my']['who']['points']);
        $this->assertSame('Ana', $revealed['reveal']['ranking'][0]['nickname']);
        $this->assertSame(100, $revealed['reveal']['ranking'][0]['total']);
        $this->assertSame('Nora Leal', $revealed['reveal']['solution']['correct']['who']);
        $this->assertNotEmpty($revealed['reveal']['solution']['truth']);

        $loser = $this->state($friend)['reveal']['my'];
        $this->assertSame(0, $loser['total']);
        $this->assertFalse($loser['who']['correct']);
    }

    public function test_a_wrong_answer_scores_only_what_it_got_right(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(225);

        $ids = $this->winningIds($this->state($host));
        $ids['why'] = $this->losingIds($this->state($host))['why'];

        $this->postJson(route('express.accuse', $host), $ids)->assertOk();
        $this->postJson(route('express.accuse', $friend), $this->winningIds($this->state($friend)))->assertOk();

        $my = $this->state($host)['reveal']['my'];
        $this->assertSame(70, $my['total']);
        $this->assertFalse($my['why']['correct']);
        $this->assertSame(0, $my['why']['points']);
        $this->assertTrue($my['who']['correct']);
    }

    public function test_the_round_closes_by_itself_at_300_seconds_and_silence_scores_zero(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(225);

        $this->postJson(route('express.accuse', $host), $this->winningIds($this->state($host)))->assertOk();
        $late = $this->winningIds($this->state($friend)); // what the friend could have sent in time

        $this->later(40); // 265 s: the window is still open for the one who has not answered
        $this->assertSame('accusing', $this->state($friend)['room']['status']);

        $this->later(40); // 305 s
        $closed = $this->state($friend);

        $this->assertSame('reveal', $closed['room']['status']);
        $this->assertSame(0, $closed['reveal']['my']['total']);
        $this->assertNull($closed['reveal']['my']['who']['label']);
        $this->assertSame(['Ana', 'Leo'], collect($closed['reveal']['ranking'])->pluck('nickname')->all());

        $this->postJson(route('express.accuse', $friend), $late)
            ->assertStatus(422)
            ->assertJsonPath('code', 'closed');
    }

    public function test_an_accusation_cannot_be_changed_and_garbage_is_refused(): void
    {
        [$host] = $this->runningRoom();
        $this->later(225);

        $this->postJson(route('express.accuse', $host), ['who' => 'x', 'how' => 'y', 'why' => 'z'])
            ->assertStatus(422)->assertJsonPath('code', 'invalid');
        $this->postJson(route('express.accuse', $host), ['who' => 'x'])->assertStatus(422)->assertJsonPath('code', 'invalid');
        $this->assertSame(0, Accusation::query()->count());

        $this->postJson(route('express.accuse', $host), $this->winningIds($this->state($host)))->assertOk();
        $this->postJson(route('express.accuse', $host), $this->losingIds($this->state($host)))
            ->assertStatus(422)->assertJsonPath('code', 'already_accused');

        $this->assertSame(1, Accusation::query()->count());
    }

    // ------------------------------------------------------ nothing leaks out

    public function test_no_secret_of_the_case_file_is_ever_sent_before_the_reveal(): void
    {
        [$host, $friend] = $this->runningRoom();
        $secret = json_decode((string) file_get_contents(base_path('app/Modules/Express/Cases/'.self::CASE.'.json')), true)['author_only'];

        $forbiddenKeys = ['author_only', 'culprit_id', 'apparent_interpretation', 'actual_meaning', 'deduction_nodes', 'lie', 'secret', 'response_exact'];

        foreach ([0, 25, 50, 40, 15] as $step) {
            $this->later($step);

            foreach ([$host, $friend] as $token) {
                $payload = json_encode($this->state($token), JSON_UNESCAPED_UNICODE);

                foreach ($forbiddenKeys as $key) {
                    $this->assertStringNotContainsString('"'.$key.'"', $payload);
                }

                $this->assertStringNotContainsString($secret['truth'], $payload);
                $this->assertStringNotContainsString($secret['accusation']['reconstruction'], $payload);
                $this->assertNull($this->state($token)['reveal']);
            }
        }

        // The page carries the same state, so the same rule.
        $page = $this->get(route('express.room', $host))->assertOk()->getContent();
        $this->assertStringNotContainsString($secret['truth'], html_entity_decode($page));
    }

    public function test_the_correct_option_is_not_first_on_the_wire(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(225);

        $case = $this->case();
        $correct = $case->correct();
        $positions = [];

        foreach ([$host, $friend] as $token) {
            $how = collect($this->state($token)['accusation']['options']['how'])->pluck('label')->all();
            $positions[] = array_search($case->howOptions()[$correct['how']], $how, true);
        }

        // The file always lists the right answer first; the wire must not.
        $this->assertNotSame([0, 0], $positions);
    }

    // ------------------------------------------------------------ the budget

    public function test_the_poll_and_the_page_cost_no_database_queries(): void
    {
        [$host] = $this->runningRoom();
        $this->state($host); // warms the cache like a page load does

        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->state($host);
        $this->later(30);
        $this->state($host);

        $queries = DB::getQueryLog();

        $this->assertSame([], $queries, 'the poll must not touch the database: '.json_encode($queries));
    }

    public function test_the_player_page_costs_no_database_queries_once_the_room_is_cached(): void
    {
        [$host] = $this->runningRoom();
        $this->state($host);

        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->get(route('express.room', $host))->assertOk();

        $this->assertSame([], DB::getQueryLog(), 'reloading the page must not touch the database');
    }

    public function test_the_player_page_is_not_indexable_and_does_not_echo_the_token(): void
    {
        [$host] = $this->runningRoom();

        $html = $this->get(route('express.room', $host))->assertOk()->getContent();

        $this->assertStringContainsString('noindex', $html);
        $this->assertStringNotContainsString('rel="canonical"', $html);
        $this->assertStringNotContainsString('og:url', $html);
    }

    public function test_the_poll_opens_no_session(): void
    {
        [$host] = $this->runningRoom();

        $response = $this->getJson(route('express.state', $host));

        $this->assertEmpty($response->headers->getCookies(), 'no session cookie on the poll');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_an_unknown_token_is_a_404(): void
    {
        $this->getJson(route('express.state', '00000000-0000-0000-0000-000000000000'))->assertNotFound();
        $this->get(route('express.room', '00000000-0000-0000-0000-000000000000'))->assertNotFound();
    }

    public function test_the_poll_survives_a_wiped_cache(): void
    {
        [$host] = $this->runningRoom();

        \App\Modules\Platform\Support\Ops::store()->flush();

        $this->assertSame('playing', $this->state($host)['room']['status']);
    }

    // ------------------------------------------------------------ next round

    public function test_the_group_can_play_another_round(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(225);
        $this->postJson(route('express.accuse', $host), $this->winningIds($this->state($host)))->assertOk();
        $this->postJson(route('express.accuse', $friend), $this->winningIds($this->state($friend)))->assertOk();

        // Not before the round is over.
        $this->assertNull($this->state($host)['reveal']['next_round']['code']);

        $this->postJson(route('express.next.create', $friend), ['case_slug' => 'la-ultima-funcion'])->assertOk();

        $next = Room::query()->latest('id')->firstOrFail();
        $this->assertSame('la-ultima-funcion', $next->case_slug);
        $this->assertSame($next->code, $this->state($host)['reveal']['next_round']['code']);

        // A second tap by someone else reuses the room instead of opening another.
        $this->postJson(route('express.next.create', $host), ['case_slug' => self::CASE])->assertOk();
        $this->assertSame(2, Room::query()->count());

        $redirect = $this->postJson(route('express.next.join', $host))->assertOk()->json('redirect');
        $this->assertNotEmpty($redirect);

        $this->assertSame(2, Player::query()->where('room_id', $next->id)->count());
        $this->assertSame(['Leo', 'Ana'], Player::query()->where('room_id', $next->id)->orderBy('joined_order')->pluck('nickname')->all());
        $this->assertTrue(Player::query()->where('room_id', $next->id)->where('nickname', 'Leo')->firstOrFail()->is_gm);
    }

    public function test_another_round_is_refused_while_this_one_is_running(): void
    {
        [$host] = $this->runningRoom();

        $this->postJson(route('express.next.create', $host), [])->assertStatus(422)->assertJsonPath('code', 'invalid');
    }

    // -------------------------------------------------------------- upkeep

    public function test_stale_rooms_expire_and_old_contacts_are_deleted(): void
    {
        $this->createRoom('Ana', ['contact_type' => 'email', 'contact' => 'ana@example.com', 'consent' => true, 'adult' => true]);

        Carbon::setTestNow(now()->addHours(3));
        $this->artisan('express:prune')->assertSuccessful();

        $this->assertSame(Room::EXPIRED, Room::query()->firstOrFail()->status);
        $this->assertSame(1, Lead::query()->count(), 'still inside the retention period');

        Carbon::setTestNow(now()->addMonths(13));
        $this->artisan('express:prune')->assertSuccessful();

        $this->assertSame(0, Lead::query()->count());
    }

    public function test_the_report_and_the_export_run(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(225);
        $this->postJson(route('express.accuse', $host), $this->winningIds($this->state($host)))->assertOk();
        $this->postJson(route('express.accuse', $friend), $this->losingIds($this->state($friend)))->assertOk();

        $this->artisan('express:report')
            ->expectsOutputToContain('Salas terminadas:    1')
            ->assertSuccessful();

        $this->artisan('express:export-leads')->assertSuccessful();
        $this->artisan('express:report', ['event' => 'no-existe'])->assertFailed();
    }

    public function test_the_poster_and_the_landing_render(): void
    {
        $this->get(route('express.home'))->assertOk()->assertInertia(fn ($page) => $page->component('Express/Landing')->where('enabled', true));

        $this->get(route('express.create'))->assertOk()->assertInertia(fn ($page) => $page
            ->component('Express/Create')
            ->has('cases', 7)
            ->where('consent.version', config('express.consent.version')));

        $this->get(route('express.poster', 'usb-cali-2026'))->assertOk()->assertInertia(fn ($page) => $page
            ->component('Express/Poster')
            ->where('url', fn ($url) => str_contains($url, 'utm_source=feria') && str_contains($url, 'utm_campaign=feria_usb_20261028')));

        $this->get(route('express.poster', 'no-existe'))->assertNotFound();
    }

    public function test_the_end_screen_sends_people_to_the_catalog_with_the_fairs_promo(): void
    {
        [$host, $friend] = $this->runningRoom();
        $this->later(225);
        $this->postJson(route('express.accuse', $host), $this->winningIds($this->state($host)))->assertOk();
        $this->postJson(route('express.accuse', $friend), $this->winningIds($this->state($friend)))->assertOk();

        $cta = $this->state($host)['reveal']['cta'];

        $this->assertStringStartsWith('https://misteriocode.com/jugar?', $cta['url']);
        $this->assertStringContainsString('promo_code=FERIA-USBCALI2026', $cta['url']);
        $this->assertStringContainsString('utm_source=express', $cta['url']);
        $this->assertStringContainsString('utm_campaign=feria_usb_20261028', $cta['url']);
        $this->assertSame('FERIA-USBCALI2026', $cta['promo_code']);
    }
}
