<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * At the end of an interrogation the player used to be shown the suspect's
 * whole source file, which is also the AI persona's prompt: real motive, lies,
 * even a title naming the culprit. What a player may read is now an allowlist
 * the case author writes (`public` in the manifest), and these tests pin down
 * that nothing else reaches the browser.
 */
class SuspectFichaTest extends TestCase
{
    use RefreshDatabase;

    // Words that only the author/AI side of a case should ever use.
    private const FORBIDDEN = [
        'culpable', 'inocente', 'señuelo', 'senuelo', 'motivo real', 'aparente',
        'exonerad', 'prompt', 'mentira', 'miente', 'confrontación', 'confrontacion',
    ];

    private function registry(): CaseRegistry
    {
        return app(CaseRegistry::class);
    }

    /** steve-jacobs is not public and its files are the original material. */
    private function publicCases(): array
    {
        return array_filter($this->registry()->all(), fn ($case) => $case->slug !== 'steve-jacobs');
    }

    public function test_every_person_of_every_published_case_has_a_written_ficha(): void
    {
        $missing = [];

        foreach ($this->publicCases() as $case) {
            foreach ($case->suspects() as $slug => $suspect) {
                $ficha = $case->ficha($slug);

                if (blank($ficha['profile'] ?? null) || count($ficha['facts'] ?? []) < 1) {
                    $missing[] = "{$case->slug}/{$slug}";
                }
            }
        }

        $this->assertSame([], $missing, 'These people have no player-facing ficha (`public` block).');
    }

    public function test_no_ficha_uses_author_or_ai_vocabulary(): void
    {
        $offenders = [];

        foreach ($this->publicCases() as $case) {
            foreach (array_keys($case->suspects()) as $slug) {
                $ficha = $case->ficha($slug);
                $text = implode(' ', array_merge(
                    [$ficha['age'], $ficha['profile'], $ficha['alibi']],
                    $ficha['facts']
                ));

                foreach (self::FORBIDDEN as $word) {
                    if (str_contains(mb_strtolower($text), $word)) {
                        $offenders[] = "{$case->slug}/{$slug}: {$word}";
                    }
                }

                // The abbreviation, as a word of its own.
                if (preg_match('/\bIA\b/u', $text)) {
                    $offenders[] = "{$case->slug}/{$slug}: IA";
                }
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_a_ficha_never_repeats_the_manifest_motive_or_alibi_verbatim(): void
    {
        $offenders = [];

        foreach ($this->publicCases() as $case) {
            foreach ($case->suspects() as $slug => $suspect) {
                $ficha = $case->ficha($slug);
                $texts = array_filter(array_merge([$ficha['profile'], $ficha['alibi']], $ficha['facts']));

                foreach (['motive', 'alibi'] as $field) {
                    if (filled($suspect[$field] ?? null) && in_array($suspect[$field], $texts, true)) {
                        $offenders[] = "{$case->slug}/{$slug}: {$field}";
                    }
                }
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_the_player_roster_carries_no_author_fields(): void
    {
        foreach ($this->registry()->all() as $case) {
            foreach ($case->suspectsForPlayer() as $suspect) {
                $this->assertEqualsCanonicalizing(
                    ['name', 'role', 'connection', 'photo_url'],
                    array_keys($suspect)
                );
            }
        }
    }

    public function test_a_person_without_a_public_block_falls_back_to_the_relation_only(): void
    {
        $ficha = $this->registry()->get('steve-jacobs')->ficha('rachel-miller');

        $this->assertSame('Amiga de la esposa', $ficha['profile']);
        $this->assertSame([], $ficha['facts']);
        $this->assertNull($ficha['alibi']);
    }

    public function test_the_chat_page_ships_the_ficha_and_none_of_the_source_file(): void
    {
        $game = Game::create([
            'name' => 'Mesa',
            'case_slug' => 'habitacion-314',
            'status' => 'running',
            'interrogation_enabled' => true,
        ]);
        $player = $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'access_token' => 'ficha-token',
        ]);

        InterrogationSession::create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'suspect_slug' => 'ramon-alday',
            'started_at' => now(),
            'closed_at' => now(),
            'questions_used' => 5,
            'max_questions' => 5,
        ]);

        $response = $this->get(route('immersion.player.interrogation.show', ['ficha-token', 'ramon-alday']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Player/InterrogationChat')
                ->has('ficha.profile')
                ->missing('originalTestimonyHtml')
                ->missing('suspect.motive')
                ->missing('suspect.alibi')
                ->missing('suspect.file'));

        $body = $response->getContent();
        $this->assertStringNotContainsString('CULPABLE', $body);
        $this->assertStringNotContainsString('Motivo real', $body);
    }
}
