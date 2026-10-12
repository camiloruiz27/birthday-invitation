<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Ai\Contracts\EpilogueProvider;
use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * What happened to the person the case is about ("la muerte de" or "la
 * desaparición de"), and who writes the winners' epilogue when the culprit
 * cannot be questioned.
 */
class IncidentAndNarratorTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const CASE_SLUG = 'caso-narrado';

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(
            CaseRegistry::class,
            fn () => new CaseRegistry(base_path('tests/Fixtures/cases'))
        );

        config([
            'immersion.default_case' => self::CASE_SLUG,
            'immersion.ai.base_url' => 'https://gateway.test',
            'immersion.ai.api_key' => 'test-key',
            'immersion.ai.interrogation_enabled' => true,
        ]);
    }

    private function case(string $slug = self::CASE_SLUG): CaseDefinition
    {
        return app(CaseRegistry::class)->get($slug);
    }

    private function accuse(string $suspectSlug): \App\Modules\Immersion\Models\Accusation
    {
        $owner = $this->gameMaster(self::CASE_SLUG);

        $game = Game::create([
            'user_id' => $owner->id,
            'name' => 'Mesa',
            'case_slug' => self::CASE_SLUG,
            'ending_type' => Game::ENDING_EPILOGUE,
            'status' => 'draft',
        ]);

        $player = $game->players()->create([
            'name' => 'Jugadora',
            'email' => 'j@example.test',
            'access_token' => 'narrado-'.$game->id,
        ]);

        return $game->accusations()->create([
            'player_id' => $player->id,
            'suspect_slug' => $suspectSlug,
            'suspect_name' => $this->case()->suspect($suspectSlug)['name'],
            'weapon' => 'Un nombre falso',
            'motive' => 'Quería desaparecer',
            'submitted_at' => now(),
        ]);
    }

    public function test_the_incident_follows_the_victims_label(): void
    {
        // A missing person is not a death, whatever the shared prompts used to say.
        $this->assertSame('la desaparición de El huésped', $this->case()->incident());
        $this->assertSame('la desaparición de La desaparecida', $this->case('caso-calificado')->incident());

        // The default stamp is "Víctima": that is a death.
        $this->assertSame('la muerte de La victima', $this->case('caso-resuelto')->incident());
    }

    public function test_a_case_can_state_the_incident_itself(): void
    {
        $withOverride = new CaseDefinition('x', base_path('tests/Fixtures/cases/caso-narrado'), [
            'victim' => ['name' => 'Ana', 'label' => 'Desaparecida', 'incident' => 'el secuestro de'],
        ]);

        $this->assertSame('el secuestro de Ana', $withOverride->incident());

        $withPlaceholder = new CaseDefinition('x', base_path('tests/Fixtures/cases/caso-narrado'), [
            'victim' => ['name' => 'Ana', 'incident' => 'lo que le pasó a {name}'],
        ]);

        $this->assertSame('lo que le pasó a Ana', $withPlaceholder->incident());
    }

    public function test_only_someone_who_can_be_questioned_can_narrate(): void
    {
        $this->assertSame('la-duena', $this->case()->epilogueSpeakerSlug());
        $this->assertNull($this->case('caso-resuelto')->epilogueSpeakerSlug());

        $unreachable = new CaseDefinition('x', base_path('tests/Fixtures/cases/caso-narrado'), [
            'suspects' => [
                'el-alias' => ['name' => 'A', 'role' => 'x', 'file' => 'suspects/el-alias.md', 'interrogable' => false],
            ],
            'solution' => ['epilogue_speaker' => 'el-alias'],
        ]);

        $this->assertNull($unreachable->epilogueSpeakerSlug());
    }

    public function test_the_winners_epilogue_is_told_by_the_named_speaker_not_the_culprit(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Acertaste. Te cuento lo que supimos.'], 200)]);

        $message = app(EpilogueProvider::class)->write($this->accuse('el-alias'));

        $this->assertSame('Acertaste. Te cuento lo que supimos.', $message);

        Http::assertSent(function ($request) {
            $body = $request->data();

            // The voice is the owner's, not the alias's.
            $this->assertTrue($body['narrator']);
            $this->assertSame('La Dueña', $body['suspect_name']);
            $this->assertSame('gerente del hotel', $body['suspect_role']);
            $this->assertStringContainsString('La dueña del hotel', $body['testimony']);
            $this->assertStringNotContainsString('No se puede interrogar', $body['testimony']);

            // The culprit is only a name in what she tells, with the authored solution.
            $this->assertTrue($body['correct']);
            $this->assertSame('El Alias', $body['culprit_name']);
            $this->assertSame('Cambio de identidad.', $body['method']);
            $this->assertSame('Necesitaba desaparecer.', $body['motive']);

            // And the prompt is told it is a disappearance.
            $this->assertSame('la desaparición de El huésped', $body['incident']);

            $this->assertStringNotContainsString('MARCADOR-SOLUCION-NARRADA', json_encode($body));

            return true;
        });
    }

    public function test_accusing_someone_else_still_gets_that_persons_own_epilogue(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Yo no fui.'], 200)]);

        app(EpilogueProvider::class)->write($this->accuse('la-duena'));

        Http::assertSent(function ($request) {
            $body = $request->data();

            $this->assertArrayNotHasKey('narrator', $body);
            $this->assertFalse($body['correct']);
            $this->assertSame('La Dueña', $body['suspect_name']);
            $this->assertSame('El Alias', $body['culprit_name']);
            $this->assertSame('la desaparición de El huésped', $body['incident']);
            $this->assertArrayNotHasKey('method', $body);

            return true;
        });
    }
}
