<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Ai\GatewayInterrogationProvider;
use App\Modules\Immersion\Ai\NullInterrogationProvider;
use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * "Level 2": a case can tag its timeline events with evidence codes, so an
 * interrogation can be told which ones a given game has actually delivered
 * so far, instead of trusting whatever the player claims. Entirely opt-in —
 * a case that never declares `evidence_codes` must behave exactly as before.
 */
class EvidenceDeliveredTest extends TestCase
{
    use RefreshDatabase;

    public function test_evidence_code_map_collects_codes_per_offset_and_ignores_events_without_any(): void
    {
        $case = new CaseDefinition('test-case', '', [
            'timeline' => [
                ['trigger_offset_minutes' => 36, 'evidence_codes' => ['V8', 'V10']],
                ['trigger_offset_minutes' => 36, 'evidence_codes' => ['V16']],
                ['trigger_offset_minutes' => 50, 'evidence_codes' => ['V9']],
                ['trigger_offset_minutes' => 65],
            ],
        ]);

        $this->assertSame(
            [36 => ['V8', 'V10', 'V16'], 50 => ['V9']],
            $case->evidenceCodeMap()
        );
    }

    public function test_a_case_without_evidence_codes_gets_an_empty_map(): void
    {
        $case = new CaseDefinition('test-case', '', [
            'timeline' => [
                ['trigger_offset_minutes' => 1, 'title' => 'Sin evidencia con codigo'],
            ],
        ]);

        $this->assertSame([], $case->evidenceCodeMap());
    }

    public function test_evidence_codes_never_reach_the_persisted_timeline_event(): void
    {
        $case = new CaseDefinition('test-case', '', [
            'timeline' => [
                ['type' => 'email', 'trigger_offset_minutes' => 36, 'title' => 'Complicacion', 'evidence_codes' => ['V8']],
            ],
        ]);

        $this->assertArrayNotHasKey('evidence_codes', $case->timeline()[0]);
    }

    public function test_a_game_only_counts_evidence_from_timeline_events_already_sent(): void
    {
        $game = $this->makeGameForFakeCase();

        $game->timelineEvents()->create([
            'type' => 'email', 'trigger_offset_minutes' => 1, 'title' => 'Gancho',
            'delivery_mode' => 'all', 'sent_at' => now(),
        ]);
        $game->timelineEvents()->create([
            'type' => 'email', 'trigger_offset_minutes' => 36, 'title' => 'Complicacion',
            'delivery_mode' => 'all', 'sent_at' => now(),
        ]);
        // Not sent yet: its codes must not count.
        $game->timelineEvents()->create([
            'type' => 'email', 'trigger_offset_minutes' => 50, 'title' => 'Giro',
            'delivery_mode' => 'all', 'sent_at' => null,
        ]);

        $codes = $game->deliveredEvidenceCodes();
        sort($codes);

        $this->assertSame(['V1', 'V2', 'V4', 'V8'], $codes);
    }

    public function test_an_existing_case_with_no_evidence_codes_always_returns_an_empty_list(): void
    {
        $game = Game::create(['name' => 'Sin nivel 2', 'status' => 'running']);
        $this->assertSame('steve-jacobs', $game->case_slug);

        $game->timelineEvents()->create([
            'type' => 'email', 'trigger_offset_minutes' => 0, 'title' => 'Cualquier cosa',
            'delivery_mode' => 'all', 'sent_at' => now(),
        ]);

        $this->assertSame([], $game->deliveredEvidenceCodes());
    }

    public function test_the_interrogation_payload_carries_case_identity_and_delivered_evidence(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);
        config([
            'immersion.ai.base_url' => 'http://ai.test',
            'immersion.ai.api_key' => 'a-real-key',
        ]);

        $game = $this->makeGameForFakeCase();
        $game->timelineEvents()->create([
            'type' => 'email', 'trigger_offset_minutes' => 36, 'title' => 'Complicacion',
            'delivery_mode' => 'all', 'sent_at' => now(),
        ]);

        $player = $game->players()->create([
            'name' => 'Ana', 'email' => 'ana@example.com', 'access_token' => 'tok-evidence',
        ]);

        $session = InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'suspect_slug' => 'un-sospechoso',
            'started_at' => now(),
        ]);

        $reply = app(GatewayInterrogationProvider::class)->ask($session, '¿Dónde estabas?');

        $this->assertNotSame(NullInterrogationProvider::REPLY, $reply);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $body['case_code'] === 'PB-01'
                && $body['authority'] === 'Direccion de Operaciones'
                && $body['victim_name'] === 'Vic Tima'
                && $body['evidence_delivered'] === ['V4', 'V8'];
        });
    }

    /**
     * A minimal on-disk case (CaseRegistry only resolves manifests it can
     * `require` from a real case.php) with two timeline events tagged with
     * evidence codes, one suspect, so the Game-level aggregation and the
     * gateway payload can be exercised end to end without touching a real
     * shipped case.
     */
    private function makeGameForFakeCase(): Game
    {
        $slug = 'evidence-test-case';
        $basePath = storage_path('framework/testing/cases/'.$slug);
        File::ensureDirectoryExists($basePath);

        File::put($basePath.'/case.php', <<<'PHP'
            <?php

            return [
                'code' => 'PB-01',
                'authority' => 'Direccion de Operaciones',
                'victim' => ['name' => 'Vic Tima'],
                'mechanics' => ['interrogation'],
                'suspects' => [
                    'un-sospechoso' => ['name' => 'Un Sospechoso', 'role' => 'sospechoso', 'file' => 'suspects/un-sospechoso.md'],
                ],
                'timeline' => [
                    ['trigger_offset_minutes' => 1, 'evidence_codes' => ['V1', 'V2']],
                    ['trigger_offset_minutes' => 36, 'evidence_codes' => ['V4', 'V8']],
                    ['trigger_offset_minutes' => 50, 'evidence_codes' => ['V9']],
                ],
            ];
            PHP);

        File::ensureDirectoryExists($basePath.'/content/suspects');
        File::put($basePath.'/content/suspects/un-sospechoso.md', '# Un Sospechoso');

        $registry = new CaseRegistry(dirname($basePath));
        $this->app->instance(CaseRegistry::class, $registry);

        $game = Game::create(['name' => 'Nivel 2', 'status' => 'running', 'case_slug' => $slug]);
        $this->assertSame($slug, $game->case_slug);

        return $game;
    }
}
