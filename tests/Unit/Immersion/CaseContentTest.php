<?php

namespace Tests\Unit\Immersion;

use App\Modules\Immersion\Cases\CaseContent;
use Tests\TestCase;

class CaseContentTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/case-content-'.uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*'));
        rmdir($this->dir);

        parent::tearDown();
    }

    private function content(string $markdown): CaseContent
    {
        file_put_contents($this->dir.'/sobre.md', $markdown);

        return new CaseContent($this->dir);
    }

    /**
     * The regression this class was written for: two sections sharing one
     * "---" block used to be kept or dropped together, because only the first
     * heading of each block was ever inspected. Excluding the photographed one
     * silently deleted the text section next to it, and an exclusion entry
     * aimed at that neighbour matched nothing while looking like it worked.
     */
    public function test_excluding_a_section_leaves_its_block_siblings_alone(): void
    {
        $content = $this->content(<<<'MD'
            ## Titulares de prensa

            Se muestran como fotos.

            ## Notas del detective

            Esto tiene que sobrevivir.
            MD);

        $html = $content->renderFileExcludingSections('sobre.md', ['Titulares de prensa']);

        $this->assertStringNotContainsString('Titulares de prensa', $html);
        $this->assertStringNotContainsString('Se muestran como fotos', $html);
        $this->assertStringContainsString('Notas del detective', $html);
        $this->assertStringContainsString('Esto tiene que sobrevivir', $html);
    }

    public function test_a_block_separated_by_rules_still_drops_only_what_matches(): void
    {
        $content = $this->content(<<<'MD'
            ## Uno

            Primero.

            ---

            ## Dos

            Segundo.
            MD);

        $html = $content->renderFileExcludingSections('sobre.md', ['Dos']);

        $this->assertStringContainsString('Primero', $html);
        $this->assertStringNotContainsString('Segundo', $html);
        // Nothing on the far side of the rule any more, so no orphan <hr>.
        $this->assertStringNotContainsString('<hr', $html);
    }

    public function test_text_before_the_first_heading_is_never_dropped(): void
    {
        $content = $this->content(<<<'MD'
            # SOBRE 1

            Una carátula que no es de nadie.

            ## Etiquetas de evidencia

            Fotografiado.
            MD);

        $html = $content->renderFileExcludingSections('sobre.md', ['Etiquetas de evidencia']);

        $this->assertStringContainsString('SOBRE 1', $html);
        $this->assertStringContainsString('Una carátula que no es de nadie', $html);
        $this->assertStringNotContainsString('Fotografiado', $html);
    }

    public function test_an_empty_exclusion_list_renders_the_whole_file(): void
    {
        $content = $this->content("## Uno\n\nPrimero.");

        $this->assertSame(
            $content->renderFile('sobre.md'),
            $content->renderFileExcludingSections('sobre.md', []),
        );
    }

    /** A missing envelope blanks that one message instead of breaking the run. */
    public function test_a_missing_file_renders_empty(): void
    {
        $content = $this->content('irrelevante');

        $this->assertSame('', $content->renderFile('no-existe.md'));
        $this->assertSame('', $content->renderFileExcludingSections('no-existe.md', ['algo']));
    }
}
