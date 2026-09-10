<?php

namespace App\Modules\Immersion\Cases;

use Illuminate\Support\Str;

/**
 * Reads the narrative markdown of ONE case from its own content directory.
 *
 * The content is never generated or paraphrased here: it is loaded and
 * rendered verbatim, so the in-game email and the player's inbox show exactly
 * the same text. Each case owns its directory, which is what makes a second
 * case possible without touching the engine.
 */
class CaseContent
{
    /** @var array<string, string> */
    private array $cache = [];

    public function __construct(private string $basePath)
    {
    }

    public function path(string $relativePath = ''): string
    {
        return $relativePath === ''
            ? $this->basePath
            : $this->basePath.'/'.ltrim($relativePath, '/');
    }

    public function exists(string $relativePath): bool
    {
        return is_file($this->path($relativePath));
    }

    /**
     * A missing file yields an empty string on purpose: one unwritten envelope
     * should leave that message blank, not break the rest of the timeline.
     */
    public function raw(string $relativePath): string
    {
        if (isset($this->cache[$relativePath])) {
            return $this->cache[$relativePath];
        }

        $path = $this->path($relativePath);

        if (! is_file($path)) {
            return $this->cache[$relativePath] = '';
        }

        return $this->cache[$relativePath] = (string) file_get_contents($path);
    }

    public static function render(string $markdown): string
    {
        return Str::markdown($markdown, ['html_input' => 'strip']);
    }

    public function renderFile(string $relativePath): string
    {
        return self::render($this->raw($relativePath));
    }

    /**
     * Same as renderFile(), but drops the "## Heading" sections whose heading
     * contains any of $excludeHeadingContains. The source file is untouched:
     * this only stops re-showing, in this one output, the sections that are
     * already displayed as gallery images.
     *
     * Evaluated per section, not per "---" block. It used to inspect only the
     * FIRST heading of each block, so two sections sharing a block were kept
     * or dropped together: excluding one silently took its neighbour with it,
     * and an exclusion entry aimed at that neighbour matched nothing and sat
     * there looking like it worked. Authors are not required to put a rule
     * between every section, so the grouping was never a promise the content
     * made.
     *
     * @param  string[]  $excludeHeadingContains
     */
    public function renderFileExcludingSections(string $relativePath, array $excludeHeadingContains): string
    {
        if (empty($excludeHeadingContains)) {
            return $this->renderFile($relativePath);
        }

        $kept = [];

        foreach (preg_split('/^---$/m', $this->raw($relativePath)) as $block) {
            $remaining = $this->dropSections($block, $excludeHeadingContains);

            // A block whose every section was excluded leaves no rule behind:
            // an <hr> with nothing on either side of it is just a scar.
            if (trim($remaining) !== '') {
                $kept[] = $remaining;
            }
        }

        return self::render(trim(implode("\n\n---\n\n", $kept)));
    }

    /**
     * Drops the excluded sections of ONE block, keeping its siblings and
     * whatever text came before the first heading.
     *
     * @param  string[]  $excludeHeadingContains
     */
    private function dropSections(string $block, array $excludeHeadingContains): string
    {
        // Zero-width split before each "## ", so a heading always travels
        // with its own body and the piece order survives untouched.
        $pieces = preg_split('/^(?=##\s+)/m', $block);

        $kept = array_filter($pieces, function (string $piece) use ($excludeHeadingContains) {
            // No heading of its own — lead-in text. It belongs to the block,
            // not to any one section, so it is never dropped.
            if (! preg_match('/^##\s+(.+)$/m', $piece, $matches)) {
                return true;
            }

            $heading = trim($matches[1]);

            foreach ($excludeHeadingContains as $needle) {
                if (str_contains($heading, $needle)) {
                    return false;
                }
            }

            return true;
        });

        return implode('', $kept);
    }
}
