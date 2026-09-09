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
     * @param  string[]  $excludeHeadingContains
     */
    public function renderFileExcludingSections(string $relativePath, array $excludeHeadingContains): string
    {
        if (empty($excludeHeadingContains)) {
            return $this->renderFile($relativePath);
        }

        $blocks = preg_split('/^---$/m', $this->raw($relativePath));

        $kept = array_filter($blocks, function (string $block) use ($excludeHeadingContains) {
            if (! preg_match('/^##\s+(.+)$/m', $block, $matches)) {
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

        return self::render(trim(implode("\n\n---\n\n", $kept)));
    }
}
