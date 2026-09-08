<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class CaseRegistryTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function registry(): CaseRegistry
    {
        return app(CaseRegistry::class);
    }

    public function test_it_discovers_cases_by_directory_convention(): void
    {
        $this->assertContains('steve-jacobs', $this->registry()->slugs());
        $this->assertTrue($this->registry()->has('steve-jacobs'));
        $this->assertFalse($this->registry()->has('not-a-case'));
    }

    public function test_unknown_case_is_a_deployment_error_not_a_null(): void
    {
        $this->assertNull($this->registry()->find('not-a-case'));

        $this->expectException(RuntimeException::class);
        $this->registry()->get('not-a-case');
    }

    public function test_steve_jacobs_manifest_exposes_the_full_case(): void
    {
        $case = $this->registry()->get('steve-jacobs');

        $this->assertSame('SF 554301', $case->code());
        $this->assertSame('Steve Jacobs', $case->victim()['name']);
        $this->assertCount(9, $case->suspects());
        $this->assertCount(8, $case->timeline());
        $this->assertSame(5, $case->interrogationQuestions());
        $this->assertTrue($case->hasMechanic('interrogation'));
        $this->assertFalse($case->hasMechanic('seance'));
    }

    public function test_a_written_culprit_must_exist_in_the_roster(): void
    {
        $problems = [];

        foreach ($this->registry()->all() as $case) {
            $declared = $case->declaredCulpritSlug();

            // Null means "not written yet", which is fine. A slug that IS
            // written but names nobody is a typo: the case would be unwinnable
            // and the reveal would silently never appear.
            if ($declared !== null && ! $case->suspect($declared)) {
                $problems[] = "{$case->slug} → culprit \"{$declared}\" is not in suspects";
            }
        }

        $this->assertSame([], $problems, implode("\n", $problems));
    }

    public function test_a_solved_case_exonerates_every_innocent_suspect(): void
    {
        $problems = [];

        foreach ($this->registry()->all() as $case) {
            if (! $case->hasSolution()) {
                continue;
            }

            foreach (array_keys($case->suspects()) as $slug) {
                $exoneration = $case->exonerationFor($slug);

                if ($slug === $case->culpritSlug()) {
                    // Exonerating the culprit would feed the epilogue a line
                    // arguing they could not have done it.
                    if ($exoneration !== null) {
                        $problems[] = "{$case->slug} → the culprit \"{$slug}\" has an exoneration";
                    }

                    continue;
                }

                // The personalised epilogue has no fallback: a missing line
                // would leave the model to reason out the player's mistake,
                // which is exactly inventing the ending.
                if ($exoneration === null || $exoneration === CaseDefinition::PENDING) {
                    $problems[] = "{$case->slug} → \"{$slug}\" has no written exoneration";
                }
            }
        }

        $this->assertSame([], $problems, implode("\n", $problems));
    }

    public function test_steve_jacobs_has_a_revealable_ending(): void
    {
        $case = $this->registry()->get('steve-jacobs');

        $this->assertTrue($case->hasSolution());
        $this->assertSame('rachel-miller', $case->culpritSlug());
        $this->assertSame('Rachel Miller', $case->solution()['culprit']['name']);

        // The long reveal is real prose, not the leftover template.
        $body = $case->renderSolution();
        $this->assertNotSame('', $body);
        $this->assertStringNotContainsString(CaseDefinition::PENDING, $body);

        // And it is illustrated by evidence the table already received.
        $this->assertNotEmpty($case->solution()['gallery']);
    }

    public function test_an_unwritten_solution_is_treated_as_absent(): void
    {
        // The placeholder must never be revealable: a table would be shown
        // "PENDIENTE" as the answer.
        $pending = new CaseDefinition('unwritten', '', [
            'suspects' => ['alguien' => ['name' => 'Alguien']],
            'solution' => ['culprit_slug' => CaseDefinition::PENDING],
        ]);

        $this->assertFalse($pending->hasSolution());
        $this->assertNull($pending->culpritSlug());

        // A slug that names nobody is equally unrevealable.
        $typo = new CaseDefinition('typo', '', [
            'suspects' => ['alguien' => ['name' => 'Alguien']],
            'solution' => ['culprit_slug' => 'nadie'],
        ]);

        $this->assertFalse($typo->hasSolution());

        // And a case with no solution block at all.
        $none = new CaseDefinition('no-ending', '', []);

        $this->assertFalse($none->hasSolution());
        $this->assertNull($none->culpritSlug());
        $this->assertSame('', $none->renderSolution());
    }

    public function test_asset_urls_are_namespaced_per_case(): void
    {
        $case = $this->registry()->get('steve-jacobs');

        $this->assertSame(
            '/immersion/steve-jacobs/photos/steve-jacobs.jpg',
            $case->victim()['photo_url']
        );
        $this->assertSame(
            '/immersion/steve-jacobs/photos/elizabeth-foster.jpg',
            $case->suspect('elizabeth-foster')['photo_url']
        );

        $gallery = $case->galleryFor('sobres/sobre-2.md');
        $this->assertCount(1, $gallery);
        $this->assertStringStartsWith('/immersion/steve-jacobs/gallery/', $gallery[0]['url']);
    }

    public function test_case_content_files_are_readable_from_the_case_package(): void
    {
        $content = $this->registry()->get('steve-jacobs')->content();

        $this->assertTrue($content->exists('sobres/sobre-1.md'));
        $this->assertTrue($content->exists('suspects/elizabeth-foster.md'));
        $this->assertNotSame('', $content->raw('suspects/elizabeth-foster.md'));
    }

    public function test_a_missing_content_file_renders_empty_instead_of_failing(): void
    {
        $content = $this->registry()->get('steve-jacobs')->content();

        $this->assertSame('', $content->raw('sobres/sobre-99.md'));
        $this->assertSame('', $content->renderFile('sobres/sobre-99.md'));
    }

    public function test_a_game_resolves_content_through_its_own_case(): void
    {
        $game = Game::create(['name' => 'Test Game', 'status' => 'draft']);

        $this->assertSame('steve-jacobs', $game->case_slug);
        $this->assertSame('steve-jacobs', $game->caseDefinition()->slug);
        $this->assertSame('SF 554301', $game->caseDefinition()->code());
    }

    public function test_creating_a_game_attaches_the_cases_timeline(): void
    {
        $user = $this->gameMaster();

        $this->actingAs($user)->post(route('immersion.gm.games.store'), [
            'name' => 'Partida nueva',
            'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
        ])->assertRedirect();

        $game = Game::firstWhere('name', 'Partida nueva');

        $this->assertSame($user->id, $game->user_id);
        $this->assertSame('steve-jacobs', $game->case_slug);
        $this->assertSame('1.0', $game->case_version);
        $this->assertSame(8, $game->timelineEvents()->count());
    }

    public function test_creating_a_game_rejects_an_unknown_case(): void
    {
        $this->actingAs($this->gameMaster())->post(route('immersion.gm.games.store'), [
            'name' => 'Partida invalida',
            'case_slug' => 'not-a-case',
            'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
        ])->assertSessionHasErrors('case_slug');

        $this->assertDatabaseMissing('immersion_games', ['name' => 'Partida invalida']);
    }

    public function test_creating_a_game_requires_owning_the_case(): void
    {
        // A real, installed case — but this account does not own it.
        $this->actingAs($this->userWithoutAccess())->post(route('immersion.gm.games.store'), [
            'name' => 'Partida sin licencia',
            'case_slug' => 'steve-jacobs',
            'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
        ])->assertSessionHasErrors('case_slug');

        $this->assertDatabaseMissing('immersion_games', ['name' => 'Partida sin licencia']);
    }
}
