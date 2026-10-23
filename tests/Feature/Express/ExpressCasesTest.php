<?php

namespace Tests\Feature\Express;

use App\Modules\Express\Support\AccusationOptions;
use App\Modules\Express\Support\AuthoredMatcher;
use App\Modules\Express\Support\ExpressCase;
use App\Modules\Express\Support\ExpressCaseRegistry;
use Tests\TestCase;

/**
 * The case files are the product. These tests hold every installed file to the
 * rules the engine leans on, and pin the two pieces that read them: the
 * question matcher and the accusation options.
 */
class ExpressCasesTest extends TestCase
{
    private function registry(): ExpressCaseRegistry
    {
        return app(ExpressCaseRegistry::class);
    }

    public function test_the_seven_cases_are_installed_and_valid(): void
    {
        $this->assertSame([
            'el-apagon-que-no-existio',
            'el-mensaje-de-las-11-03',
            'el-mural-de-medianoche',
            'el-pago-perfecto',
            'la-firma-de-las-17-55',
            'la-obra-del-marco-azul',
            'la-ultima-funcion',
        ], collect($this->registry()->slugs())->sort()->values()->all());
    }

    public function test_a_broken_case_is_reported_in_plain_sentences(): void
    {
        $data = json_decode((string) file_get_contents(base_path('app/Modules/Express/Cases/el-pago-perfecto.json')), true);

        $this->assertSame([], ExpressCaseRegistry::problems($data));

        $data['author_only']['accusation']['how'] = 'algo que no es una opción';
        array_pop($data['evidence']);
        $data['characters'][0]['topics'] = [];

        $problems = implode(' ', ExpressCaseRegistry::problems($data));

        $this->assertStringContainsString('accusation.how must be one of how_options', $problems);
        $this->assertStringContainsString('evidence must be E1..E6', $problems);
        $this->assertStringContainsString('needs exactly two topics', $problems);
    }

    public function test_every_case_can_be_won_and_every_option_is_distinct(): void
    {
        foreach ($this->registry()->all() as $case) {
            $correct = $case->correct();

            $this->assertContains($correct['who'], array_column($case->whoOptions(), 'id'), $case->slug());
            $this->assertArrayHasKey($correct['how'], $case->howOptions(), $case->slug());
            $this->assertArrayHasKey($correct['why'], $case->whyOptions(), $case->slug());
        }
    }

    public function test_clue_logs_are_split_where_a_new_timestamped_entry_starts(): void
    {
        $this->assertSame(
            ['10:58 | Alma P. entra a Sala B.', '11:03 | aviso por altavoz.', '11:10 | Cabina A abierta: sin ocupantes.'],
            ExpressCase::splitLines('10:58 | Alma P. entra a Sala B. 11:03 | aviso por altavoz. 11:10 | Cabina A abierta: sin ocupantes.'),
        );

        $this->assertSame(
            ['#204: Aprobación automática, referencia 9FD2.', '#218: Aprobación MANUAL, sin referencia bancaria asociada.'],
            ExpressCase::splitLines('#204: Aprobación automática, referencia 9FD2. #218: Aprobación MANUAL, sin referencia bancaria asociada.'),
        );

        // A time inside an entry is not a new entry.
        $this->assertCount(1, ExpressCase::splitLines('Movimientos 12:00–13:20 | Una única transacción | 12:03 | Sin segunda referencia.'));
    }

    public function test_the_matcher_understands_every_authored_paraphrase(): void
    {
        $matcher = new AuthoredMatcher();

        foreach ($this->registry()->all() as $case) {
            foreach ($case->suspects() as $suspect) {
                foreach ($suspect['topics'] as $topic) {
                    foreach ($topic['paraphrases'] as $paraphrase) {
                        $this->assertSame(
                            $topic['id'],
                            $matcher->match($paraphrase, $suspect['topics']),
                            "{$case->slug()}/{$suspect['id']}: «{$paraphrase}»",
                        );
                    }
                }
            }
        }
    }

    public function test_the_matcher_tolerates_other_words_and_declines_when_unsure(): void
    {
        $matcher = new AuthoredMatcher();
        $nora = $this->registry()->find('el-mensaje-de-las-11-03')->suspect('nora');

        // Conjugation and word order differ from the file.
        $this->assertSame('creditos', $matcher->match('¿Fuiste tú quien tocó los créditos del corto?', $nora['topics']));
        $this->assertSame('creditos', $matcher->match('quien cambio los creditos', $nora['topics']));

        // Nothing to do with either topic.
        $this->assertNull($matcher->match('¿Cuál es tu comida favorita?', $nora['topics']));
        $this->assertNull($matcher->match('   ', $nora['topics']));
        $this->assertNull($matcher->match('¿?', $nora['topics']));
    }

    public function test_accusation_options_never_reveal_the_file_order_and_resolve_back(): void
    {
        $case = $this->registry()->find('el-mensaje-de-las-11-03');

        $a = AccusationOptions::forPlayer($case, 'K7M2Q', 'token-a');
        $b = AccusationOptions::forPlayer($case, 'K7M2Q', 'token-b');

        // Stable for the same player, and never the position in the file.
        $this->assertSame($a, AccusationOptions::forPlayer($case, 'K7M2Q', 'token-a'));

        foreach (['who', 'how', 'why'] as $kind) {
            $this->assertCount(3, array_unique(array_column($a[$kind], 'id')));

            foreach ($a[$kind] as $option) {
                $this->assertMatchesRegularExpression('/^[0-9a-f]{12}$/', $option['id']);
            }
        }

        // Ids differ between rooms, so one table cannot read another's form.
        $other = AccusationOptions::forPlayer($case, 'ZZZZZ', 'token-a');
        $this->assertNotSame(array_column($a['how'], 'id'), array_column($other['how'], 'id'));

        // Across players the order differs for at least one of the three questions.
        $this->assertTrue(
            array_column($a['who'], 'id') !== array_column($b['who'], 'id')
            || array_column($a['how'], 'id') !== array_column($b['how'], 'id')
            || array_column($a['why'], 'id') !== array_column($b['why'], 'id'),
        );

        $correct = $case->correct();
        $pick = fn (string $kind, string $label) => collect($a[$kind])->firstWhere('label', $label)['id'];

        $resolved = AccusationOptions::resolve($case, 'K7M2Q', [
            'who' => $pick('who', 'Nora Leal'),
            'how' => $pick('how', $case->howOptions()[$correct['how']]),
            'why' => $pick('why', $case->whyOptions()[$correct['why']]),
        ]);

        $this->assertSame($correct, $resolved);
        $this->assertSame(
            ['who' => 40, 'how' => 30, 'why' => 30, 'total' => 100],
            AccusationOptions::score($case, $resolved),
        );

        $this->assertNull(AccusationOptions::resolve($case, 'K7M2Q', ['who' => 'nope', 'how' => 'x', 'why' => 'y']));
    }

    /**
     * A folder in public/ named like a route is served by the web server
     * instead of Laravel (Apache and LiteSpeed answer a directory before the
     * front controller), so /express — the page the fair's QR points at —
     * would 403 or 404. The case media therefore lives in public/express-assets.
     */
    public function test_no_public_folder_shadows_the_express_landing(): void
    {
        $this->assertDirectoryDoesNotExist(public_path('express'), 'public/express would hide the /express route from Laravel; media goes in public/express-assets.');
        $this->assertDirectoryExists(public_path('express-assets'));

        foreach ($this->registry()->slugs() as $slug) {
            $this->assertDirectoryExists(public_path("express-assets/{$slug}/portraits"), $slug);
            $this->assertDirectoryExists(public_path("express-assets/{$slug}/audio"), $slug);
        }
    }

    public function test_media_is_found_by_name(): void
    {
        $case = $this->registry()->find('el-pago-perfecto');
        $assets = $case->assets();

        $this->assertSame('/express-assets/el-pago-perfecto/hero.jpg', $assets['hero']);
        $this->assertSame('/express-assets/el-pago-perfecto/portraits/ivan.jpg', $assets['portraits']['ivan']);
        $this->assertSame('/express-assets/el-pago-perfecto/e5.jpg', $assets['images']['E5']);
        $this->assertSame([], $assets['audio'], 'this case has no audio clue');

        $lastFunction = $this->registry()->find('la-ultima-funcion');
        $this->assertSame('/express-assets/la-ultima-funcion/audio/e3.wav', $lastFunction->assets()['audio']['E3']);
    }
}
