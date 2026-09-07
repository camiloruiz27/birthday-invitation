<?php

namespace App\Modules\Immersion\Cases;

use RuntimeException;

/**
 * Finds the available cases by scanning app/Modules/Immersion/Cases/ for
 * directories that contain a case.php manifest.
 *
 * Discovery is by convention so that adding a case needs no registration
 * step. Definitions are memoized per request: manifests are plain arrays and
 * do not change while a request is running.
 */
class CaseRegistry
{
    /** @var array<string, CaseDefinition> */
    private array $resolved = [];

    /** @var array<int, string>|null */
    private ?array $slugs = null;

    public function __construct(private ?string $basePath = null)
    {
        $this->basePath ??= __DIR__;
    }

    /**
     * @return array<int, string>
     */
    public function slugs(): array
    {
        if ($this->slugs !== null) {
            return $this->slugs;
        }

        $slugs = [];

        foreach ((array) glob($this->basePath.'/*/case.php') as $manifestPath) {
            $slugs[] = basename(dirname($manifestPath));
        }

        sort($slugs);

        return $this->slugs = $slugs;
    }

    public function has(string $slug): bool
    {
        return in_array($slug, $this->slugs(), true);
    }

    public function find(string $slug): ?CaseDefinition
    {
        if (isset($this->resolved[$slug])) {
            return $this->resolved[$slug];
        }

        if (! $this->has($slug)) {
            return null;
        }

        $basePath = $this->basePath.'/'.$slug;
        $manifest = require $basePath.'/case.php';

        return $this->resolved[$slug] = new CaseDefinition($slug, $basePath, (array) $manifest);
    }

    /**
     * Resolve a case that must exist. Used where a game already references a
     * case: a missing manifest there is a deployment error, not a 404.
     */
    public function get(string $slug): CaseDefinition
    {
        return $this->find($slug) ?? throw new RuntimeException(
            "Unknown mystery case [{$slug}]. Expected a manifest at Cases/{$slug}/case.php."
        );
    }

    /**
     * @return array<int, CaseDefinition>
     */
    public function all(): array
    {
        return array_map(fn (string $slug) => $this->get($slug), $this->slugs());
    }

    public function default(): CaseDefinition
    {
        return $this->get((string) config('immersion.default_case'));
    }
}
