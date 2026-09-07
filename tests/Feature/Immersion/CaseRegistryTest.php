<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CaseRegistryTest extends TestCase
{
    use RefreshDatabase;

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
        $this->withSession(['immersion_gm_ok' => true]);

        $this->post(route('immersion.gm.games.store'), [
            'name' => 'Partida nueva',
            'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
        ])->assertRedirect(route('immersion.gm.dashboard'));

        $game = Game::firstWhere('name', 'Partida nueva');

        $this->assertSame('steve-jacobs', $game->case_slug);
        $this->assertSame('1.0', $game->case_version);
        $this->assertSame(8, $game->timelineEvents()->count());
    }

    public function test_creating_a_game_rejects_an_unknown_case(): void
    {
        $this->withSession(['immersion_gm_ok' => true]);

        $this->post(route('immersion.gm.games.store'), [
            'name' => 'Partida invalida',
            'case_slug' => 'not-a-case',
            'players' => [['name' => 'Ana', 'email' => 'ana@example.com']],
        ])->assertSessionHasErrors('case_slug');

        $this->assertDatabaseMissing('immersion_games', ['name' => 'Partida invalida']);
    }
}
