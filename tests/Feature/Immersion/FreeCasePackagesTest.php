<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Cases\CaseRegistry;
use Tests\TestCase;

class FreeCasePackagesTest extends TestCase
{
    private const CASES = [
        'la-final-sin-tablero' => ['difficulty' => 'medium', 'players' => [3, 5], 'minutes' => 25, 'suspects' => 3],
        'la-caja-de-donaciones' => ['difficulty' => 'easy', 'players' => [3, 4], 'minutes' => 19, 'suspects' => 3],
        'la-boya-naranja' => ['difficulty' => 'medium', 'players' => [3, 5], 'minutes' => 25, 'suspects' => 3],
        'modo-demostracion' => ['difficulty' => 'medium', 'players' => [3, 5], 'minutes' => 25, 'suspects' => 3],
        'el-bingo-del-jueves' => ['difficulty' => 'easy', 'players' => [3, 4], 'minutes' => 19, 'suspects' => 3],
        'bajo-la-cabeza-del-dragon' => ['difficulty' => 'easy', 'players' => [3, 5], 'minutes' => 24, 'suspects' => 2],
    ];

    public function test_the_six_free_cases_are_complete_playable_packages(): void
    {
        $registry = app(CaseRegistry::class);

        foreach (self::CASES as $slug => $expected) {
            $case = $registry->get($slug);
            $catalog = $case->catalog();

            $this->assertSame(0, $catalog['price_amount'], "$slug must remain free");
            $this->assertSame($expected['difficulty'], $catalog['difficulty'], "$slug difficulty");
            $this->assertSame($expected['players'][0], $catalog['min_players'], "$slug min players");
            $this->assertSame($expected['players'][1], $catalog['max_players'], "$slug max players");
            $this->assertSame($expected['minutes'], $catalog['duration_minutes'], "$slug duration");
            $this->assertContains('Gratis', $catalog['tags'], "$slug free tag");
            $this->assertNotNull($catalog['cover_path'], "$slug cover path");

            $this->assertSame(3, $case->interrogationQuestions(), "$slug questions per suspect");
            $this->assertCount($expected['suspects'], $case->interrogableSuspects(), "$slug suspects");
            $this->assertTrue($case->hasMechanic('inbox'));
            $this->assertTrue($case->hasMechanic('timeline'));
            $this->assertTrue($case->hasMechanic('gallery'));
            $this->assertTrue($case->hasMechanic('interrogation'));
            $this->assertTrue($case->hasMechanic('accusation'));
            $this->assertFalse($case->hasMechanic('audio'));

            $timeline = $case->timeline();
            $this->assertCount(10, $timeline, "$slug inbox/timeline deliveries");
            $this->assertCount(3, array_filter($timeline, fn (array $event) => $event['delivery_mode'] === 'random_player'), "$slug private clues");
            $this->assertNotEmpty(array_filter($timeline, fn (array $event) => ! empty($event['cta_interrogation'])), "$slug interrogation unlock");
            $this->assertNotEmpty(array_filter($timeline, fn (array $event) => $event['type'] === 'unlock'), "$slug accusation unlock");

            foreach ($timeline as $event) {
                $this->assertTrue($case->content()->exists($event['source_file']), "$slug missing {$event['source_file']}");
            }

            foreach ($case->suspects() as $suspect) {
                $this->assertTrue($case->content()->exists($suspect['file']), "$slug missing {$suspect['file']}");
                $this->assertNotSame('', trim($case->content()->raw($suspect['file'])));
            }

            $this->assertTrue($case->hasSolution(), "$slug solution");
            $this->assertNotSame('', trim($case->renderSolution()), "$slug solution body");
            $this->assertCount(5, $case->solution()['gallery'], "$slug solution evidence");

            $promptPath = app_path("Modules/Immersion/Cases/$slug/ASSET_PROMPTS.md");
            $prompts = file_get_contents($promptPath);
            $this->assertIsString($prompts, "$slug asset prompts");
            $this->assertStringContainsString(basename($catalog['cover_path']), $prompts);
            $this->assertStringContainsString(basename($case->victim()['photo_url']), $prompts);

            foreach ($case->suspects() as $suspect) {
                $this->assertStringContainsString(basename($suspect['photo_url']), $prompts);
            }

            foreach ($case->solution()['gallery'] as $image) {
                $this->assertStringContainsString(basename($image['url']), $prompts);
            }

            $this->assertDirectoryExists(public_path("immersion/$slug/cover"));
            $this->assertDirectoryExists(public_path("immersion/$slug/photos"));
            $this->assertDirectoryExists(public_path("immersion/$slug/gallery"));
        }
    }
}
