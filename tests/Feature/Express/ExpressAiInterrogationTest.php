<?php

namespace Tests\Feature\Express;

use App\Modules\Express\Ai\ExpressInterrogationProvider;
use App\Modules\Express\Models\Question;
use App\Modules\Express\Models\Room;
use App\Modules\Express\Support\ExpressCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The suspects, played by the AI gateway: what is sent (one suspect's sheet,
 * never the solution), what comes back, what a question costs, and what
 * happens when the gateway cannot answer.
 */
class ExpressAiInterrogationTest extends TestCase
{
    use RefreshDatabase;

    private const CASE = 'el-mensaje-de-las-11-03';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-28 15:00:00');

        config([
            'express.events.usb-cali-2026.ends_at' => '2026-12-31 23:59:59',
            'express.ai.enabled' => true,
            'immersion.ai.base_url' => 'https://gateway.test',
            'immersion.ai.project_id' => 'mystery-case',
            'immersion.ai.api_key' => 'secret-key',
            // No slot ceiling in these tests.
            'immersion.ai.max_concurrent' => 0,
        ]);

        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function later(int $seconds): void
    {
        Carbon::setTestNow(now()->addSeconds($seconds));
    }

    /** @return array{0: string, 1: string} host and friend tokens, interrogations open */
    private function table(): array
    {
        $host = basename((string) $this->post(route('express.rooms.store'), [
            'case_slug' => self::CASE, 'nickname' => 'Ana', 'contact_type' => 'none',
        ])->headers->get('Location'));

        $friend = basename((string) $this->post(route('express.join.store', Room::query()->firstOrFail()->code), [
            'nickname' => 'Leo', 'contact_type' => 'none',
        ])->headers->get('Location'));

        $this->postJson(route('express.start', $host))->assertOk();
        $this->later(50);

        return [$host, $friend];
    }

    private function ask(string $token, string $suspect, string $question, ?string $topic = null)
    {
        return $this->postJson(route('express.ask', $token), array_filter([
            'suspect_id' => $suspect, 'question' => $question, 'topic_id' => $topic,
        ]));
    }

    private function gateway(string $reply = 'Eso lo revisó producción, yo solo envié la hoja.'): void
    {
        Http::fake(['gateway.test/*' => Http::response(['reply' => $reply, 'model' => 'm'])]);
    }

    private function case(): ExpressCase
    {
        return app(\App\Modules\Express\Support\ExpressCaseRegistry::class)->find(self::CASE);
    }

    public function test_a_question_is_answered_by_the_gateway_in_the_suspects_voice(): void
    {
        [$host] = $this->table();
        $this->gateway('Yo solo preparé una versión de créditos más justa, nada más.');

        $this->ask($host, 'nora', '¿Por qué tocaste los créditos del corto?')
            ->assertOk()
            ->assertJsonPath('result.status', 'answered')
            ->assertJsonPath('result.source', 'ai')
            ->assertJsonPath('result.answer', 'Yo solo preparé una versión de créditos más justa, nada más.')
            ->assertJsonPath('state.interrogation.budget_used', 1);

        $log = $this->getJson(route('express.state', $host))->json('interrogation.log');
        $this->assertCount(1, $log);
        $this->assertSame('Yo solo preparé una versión de créditos más justa, nada más.', $log[0]['answer']);
        $this->assertSame('creditos', $log[0]['topic_id'], 'the question was recognised as about the credits');

        $stored = Question::query()->firstOrFail();
        $this->assertSame('ai', $stored->source);
    }

    public function test_the_request_to_the_gateway_carries_one_suspects_sheet_and_nothing_of_the_solution(): void
    {
        [$host] = $this->table();
        $this->gateway();

        $this->ask($host, 'nora', '¿Quién cambió los créditos?', 'creditos')->assertOk();

        Http::assertSent(function (Request $request) {
            $body = $request->data();
            $raw = json_encode($body, JSON_UNESCAPED_UNICODE);
            $file = json_decode((string) file_get_contents(base_path('app/Modules/Express/Cases/'.self::CASE.'.json')), true);

            $this->assertSame('https://gateway.test/api/projects/mystery-case/interrogate-express', $request->url());
            $this->assertSame('secret-key', $request->header('x-internal-api-key')[0]);
            $this->assertSame('mystery-case', $request->header('x-project-id')[0]);

            $this->assertSame('Nora Leal', $body['suspect_name']);
            $this->assertSame('productora', $body['suspect_role']);
            $this->assertSame('creditos', $body['topic_hint']);
            $this->assertSame('¿Quién cambió los créditos?', $body['question']);

            // Her own sheet: how she talks and what she hides...
            $this->assertSame('Editó créditos sin autorización.', $body['persona']['hidden']);
            $this->assertSame('«Solo transmitimos lo que Alma dijo en directo».', $body['persona']['stance']);
            $this->assertArrayHasKey('tone', $body['persona']);

            // ...and what she has already declared, to stay faithful to it.
            $this->assertCount(2, $body['canonical_topics']);

            // Never the author's solution, nor anyone else's secrets.
            $this->assertStringNotContainsString($file['author_only']['truth'], $raw);
            $this->assertStringNotContainsString($file['author_only']['accusation']['reconstruction'], $raw);
            $this->assertStringNotContainsString('culprit', $raw);
            $this->assertStringNotContainsString('Durante un ensayo etiquetó mal un archivo', $raw, 'Luisa\'s secret');
            $this->assertStringNotContainsString('Tardó varios minutos en conseguir la llave maestra', $raw, 'Emilio\'s secret');

            return true;
        });
    }

    public function test_the_gateway_is_told_only_what_the_whole_table_has_seen(): void
    {
        [$host, $friend] = $this->table();
        $this->gateway();

        // 25 s: E3 (global) is out; E1 (A) and E2 (B) are private.
        $this->ask($host, 'nora', '¿Cómo recibieron el audio?')->assertOk();

        Http::assertSent(fn (Request $request) => $request->data()['evidence_delivered'] === [['id' => 'E3', 'title' => 'Registro de seguridad']]);

        // Once the friend shares E2 the suspect may react to it too.
        $this->postJson(route('express.share', $friend), ['evidence_id' => 'E2'])->assertOk();
        $this->ask($host, 'nora', '¿Quién cambió los créditos?')->assertOk();

        Http::assertSent(fn (Request $request) => collect($request->data()['evidence_delivered'])->pluck('id')->all() === ['E2', 'E3']);
    }

    public function test_earlier_turns_with_the_same_suspect_travel_as_history(): void
    {
        [$host] = $this->table();
        Http::fake(['gateway.test/*' => Http::sequence()->push(['reply' => 'Primera respuesta.'])->push(['reply' => 'Segunda respuesta.'])]);

        $this->ask($host, 'nora', 'Primera pregunta sobre el audio')->assertOk();
        $this->ask($host, 'nora', 'Segunda pregunta sobre los créditos')->assertOk()
            ->assertJsonPath('result.answer', 'Segunda respuesta.');

        Http::assertSent(fn (Request $request) => $request->data()['question'] === 'Segunda pregunta sobre los créditos'
            && $request->data()['history'] === [
                ['role' => 'player', 'content' => 'Primera pregunta sobre el audio'],
                ['role' => 'suspect', 'content' => 'Primera respuesta.'],
            ]);
    }

    public function test_every_question_costs_one_even_about_a_topic_already_asked(): void
    {
        [$host] = $this->table();
        $this->gateway();

        $this->ask($host, 'nora', 'x', 'reproduccion')->assertOk();
        $this->ask($host, 'nora', 'x', 'reproduccion')->assertOk();
        $this->ask($host, 'nora', 'x', 'reproduccion')->assertOk();

        $this->assertSame(3, Question::query()->count());
        $this->ask($host, 'nora', 'x', 'creditos')->assertStatus(422)->assertJsonPath('code', 'budget');
        Http::assertSentCount(3);
    }

    public function test_the_question_holds_its_place_while_the_gateway_is_still_thinking(): void
    {
        [$host, $friend] = $this->table();

        // A provider that, mid-call, looks at what the OTHER phone would see.
        $seen = [];
        $this->app->bind(ExpressInterrogationProvider::class, function () use ($friend, &$seen) {
            return new class($this, $friend, $seen) implements ExpressInterrogationProvider
            {
                public function __construct(private $test, private string $friend, private array &$seen)
                {
                }

                public function available(): bool
                {
                    return true;
                }

                public function ask(ExpressCase $case, array $suspect, array $history, string $question, ?string $topicId, array $evidence): string
                {
                    $this->seen = $this->test->getJson(route('express.state', $this->friend))->json();

                    return 'Respuesta lenta.';
                }
            };
        });

        $this->ask($host, 'nora', '¿Quién puso ese mensaje?')->assertOk();

        $this->assertSame(1, $seen['interrogation']['budget_used'], 'the place is taken before the answer arrives');
        $this->assertSame([], $seen['interrogation']['log'], 'but nobody sees a question without an answer');
        $this->assertSame([], collect($seen['suspects'])->firstWhere('id', 'nora')['answered_topics']);
        $this->assertTrue(collect($seen['suspects'])->firstWhere('id', 'nora')['locked_for_me'], 'and the suspect is already Ana\'s');
    }

    public function test_when_the_gateway_fails_a_recognised_question_gets_the_authors_answer(): void
    {
        [$host] = $this->table();
        Http::fake(['gateway.test/*' => Http::response(['code' => 'boom'], 500)]);

        $this->ask($host, 'nora', '¿Quién cambió los créditos?')
            ->assertOk()
            ->assertJsonPath('result.source', 'authored')
            ->assertJsonPath('result.answer', 'Preparé una versión de créditos más justa. Estaba trabajando en producción desde mucho antes del incidente.')
            ->assertJsonPath('state.interrogation.budget_used', 1);

        $this->assertSame('authored', Question::query()->firstOrFail()->source);
    }

    public function test_when_the_gateway_fails_an_unrecognised_question_is_handed_back_free(): void
    {
        [$host] = $this->table();
        // The first call fails, the next one works (a second Http::fake would
        // not replace the first: the first matching stub wins).
        Http::fake(['gateway.test/*' => Http::sequence()->push([], 503)->push(['reply' => 'Hola.'])]);

        $this->ask($host, 'nora', '¿Cuál es tu comida favorita?')
            ->assertStatus(422)
            ->assertJsonPath('code', 'ai_unavailable');

        $this->assertSame(0, Question::query()->count(), 'the place was given back');
        $this->assertSame(3, $this->getJson(route('express.state', $host))->json('interrogation.budget_left'));

        // A later question can still claim the suspect and be answered.
        $this->ask($host, 'nora', '¿Cuál es tu comida favorita?')->assertOk()->assertJsonPath('result.source', 'ai');
    }

    public function test_an_empty_or_unreachable_gateway_is_treated_as_unavailable(): void
    {
        [$host] = $this->table();

        Http::fake(['gateway.test/*' => Http::response(['reply' => '   '])]);
        $this->ask($host, 'nora', '¿Cuál es tu color favorito?')->assertStatus(422)->assertJsonPath('code', 'ai_unavailable');

        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));
        $this->ask($host, 'nora', '¿Cuál es tu color favorito?')->assertStatus(422)->assertJsonPath('code', 'ai_unavailable');

        $this->assertSame(0, Question::query()->count());
    }

    public function test_with_the_ai_off_the_authors_text_answers_and_the_gateway_is_never_called(): void
    {
        config(['express.ai.enabled' => false]);
        [$host] = $this->table();
        Http::fake();

        $this->ask($host, 'nora', '¿Cómo recibieron el audio?', 'reproduccion')
            ->assertOk()
            ->assertJsonPath('result.source', 'authored');

        // The authored rules apply again: a repeat is free, an unclear one too.
        $this->ask($host, 'nora', 'x', 'reproduccion')->assertOk()->assertJsonPath('result.status', 'repeat');
        $this->ask($host, 'nora', '¿Cuál es tu comida favorita?')->assertStatus(422)->assertJsonPath('code', 'unclear');

        Http::assertNothingSent();
    }

    public function test_without_a_configured_gateway_it_degrades_to_the_authors_text(): void
    {
        config(['immersion.ai.base_url' => null, 'immersion.ai.api_key' => 'CHANGE_ME']);
        [$host] = $this->table();
        Http::fake();

        $this->ask($host, 'nora', '¿Quién puso ese mensaje?')->assertOk()->assertJsonPath('result.source', 'authored');

        Http::assertNothingSent();
    }

    public function test_the_private_persona_never_reaches_a_player(): void
    {
        [$host, $friend] = $this->table();
        $this->gateway();
        $this->ask($host, 'nora', '¿Quién cambió los créditos?')->assertOk();

        foreach ([$host, $friend] as $token) {
            $payload = json_encode($this->getJson(route('express.state', $token))->json(), JSON_UNESCAPED_UNICODE);

            foreach (array_values($this->case()->persona('nora')) as $private) {
                if (in_array($private, [$this->case()->suspect('nora')['relation']], true)) {
                    continue; // the public sheet shows this one on purpose
                }

                $this->assertStringNotContainsString($private, $payload);
            }
        }
    }
}
