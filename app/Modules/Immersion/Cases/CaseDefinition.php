<?php

namespace App\Modules\Immersion\Cases;

/**
 * One mystery case, as declared by its own case.php manifest.
 *
 * A case is a directory, not a class: app/Modules/Immersion/Cases/<slug>/
 * holds case.php (this manifest) plus content/ (the narrative markdown).
 * Adding a case means adding a directory — the engine has no per-case code.
 *
 * Everything a case can vary lives here: identity, presentation strings that
 * used to be hardcoded in views ("Caso SF 554301"), which mechanics are
 * available, the interrogation budget, the suspect roster, the default
 * timeline, and the gallery mapping.
 */
class CaseDefinition
{
    /**
     * Marker a case author leaves in place of content they have not written
     * yet. A solution still carrying it is treated as absent, so an unfinished
     * case can never reveal a placeholder to a table.
     */
    public const PENDING = 'PENDIENTE';

    private ?CaseContent $content = null;

    /**
     * Promoted properties without `readonly` (which needs php 8.1) while
     * composer.json still declares php ^8.0.2. Runtime and production are
     * both 8.2, so this can tighten once that constraint is bumped.
     *
     * @param  array<string, mixed>  $manifest
     */
    public function __construct(
        public string $slug,
        private string $basePath,
        private array $manifest,
    ) {
    }

    public function content(): CaseContent
    {
        return $this->content ??= new CaseContent($this->basePath.'/content');
    }

    public function name(): string
    {
        return (string) ($this->manifest['name'] ?? $this->slug);
    }

    public function version(): string
    {
        return (string) ($this->manifest['version'] ?? '1.0');
    }

    /**
     * Short case reference shown in email subjects and headers
     * (e.g. "SF 554301").
     */
    public function code(): string
    {
        return (string) ($this->manifest['code'] ?? '');
    }

    /**
     * The in-fiction body that "sends" the case material, shown in the email
     * footer (e.g. "Departamento de Policia de San Francisco").
     */
    public function authority(): string
    {
        return (string) ($this->manifest['authority'] ?? '');
    }

    /**
     * Shopper-facing presentation for the catalog. Consumed by the platform's
     * sync command; the engine itself never reads this.
     *
     * @return array<string, mixed>
     */
    public function catalog(): array
    {
        $catalog = (array) ($this->manifest['catalog'] ?? []);
        $cover = $catalog['cover'] ?? null;

        return [
            'tagline' => $catalog['tagline'] ?? null,
            'description' => isset($catalog['description'])
                ? trim((string) $catalog['description'])
                : null,
            'cover_path' => $cover ? $this->assetUrl('cover/'.$cover) : null,
            'difficulty' => $catalog['difficulty'] ?? null,
            'duration_minutes' => $catalog['duration_minutes'] ?? null,
            'min_players' => $catalog['min_players'] ?? null,
            'max_players' => $catalog['max_players'] ?? null,
            'price_amount' => (int) ($catalog['price_amount'] ?? 0),
            'currency' => strtoupper((string) ($catalog['currency'] ?? 'COP')),
            'published' => (bool) ($catalog['published'] ?? false),
            'sort_order' => (int) ($catalog['sort_order'] ?? 0),
        ];
    }

    /**
     * @return array{name: string, photo_url: string}
     */
    public function victim(): array
    {
        $victim = $this->manifest['victim'] ?? ['name' => '', 'photo' => null];

        return [
            'name' => (string) ($victim['name'] ?? ''),
            'photo_url' => $this->photoUrl($victim['photo'] ?? null),
        ];
    }

    /**
     * Is this mechanic part of this case at all? Distinct from whether the
     * Game Master has enabled it for a given game.
     */
    public function hasMechanic(string $mechanic): bool
    {
        return in_array($mechanic, (array) ($this->manifest['mechanics'] ?? []), true);
    }

    /**
     * @return string[]
     */
    public function mechanics(): array
    {
        return array_values((array) ($this->manifest['mechanics'] ?? []));
    }

    /**
     * Question budget per suspect for this case, falling back to the
     * module-wide default.
     */
    public function interrogationQuestions(): int
    {
        return (int) ($this->manifest['limits']['interrogation_questions']
            ?? config('immersion.interrogation.max_questions', 5));
    }

    /**
     * Interrogable people, keyed by slug, with photo URLs already resolved so
     * that no view has to know how case assets are laid out.
     *
     * @return array<string, array<string, mixed>>
     */
    public function suspects(): array
    {
        $suspects = [];

        foreach ((array) ($this->manifest['suspects'] ?? []) as $slug => $suspect) {
            $suspects[$slug] = [
                'name' => (string) ($suspect['name'] ?? $slug),
                'role' => (string) ($suspect['role'] ?? ''),
                'connection' => $suspect['connection'] ?? null,
                'motive' => $suspect['motive'] ?? null,
                'alibi' => $suspect['alibi'] ?? null,
                'file' => (string) ($suspect['file'] ?? ''),
                'photo_url' => $this->photoUrl($suspect['photo'] ?? null),
            ];
        }

        return $suspects;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function suspect(string $slug): ?array
    {
        return $this->suspects()[$slug] ?? null;
    }

    /**
     * People a player is allowed to accuse.
     *
     * Everyone on the board by default — the witness included, since a case
     * author is free to make them the culprit. A suspect can opt out with
     * `'accusable' => false` in their entry.
     *
     * @return array<string, array<string, mixed>>
     */
    public function accusableSuspects(): array
    {
        $accusable = [];

        foreach ((array) ($this->manifest['suspects'] ?? []) as $slug => $suspect) {
            if (($suspect['accusable'] ?? true) === false) {
                continue;
            }

            $accusable[$slug] = $this->suspects()[$slug];
        }

        return $accusable;
    }

    /*
    |--------------------------------------------------------------------------
    | The ending
    |--------------------------------------------------------------------------
    |
    | The solution is authored, never generated. Everything below is only ever
    | reached after the ending has been revealed — see SolutionController and
    | GamePolicy::viewSpoilers.
    |
    */

    /**
     * Is there an ending that can actually be revealed?
     *
     * False while the culprit is still the placeholder, and false if the slug
     * does not name a real suspect. Both mean the same thing in practice: no
     * table could win, so the reveal must not be offered at all. A typo here
     * is caught by the manifest integrity test rather than failing silently in
     * front of players.
     */
    public function hasSolution(): bool
    {
        $culprit = $this->declaredCulpritSlug();

        return $culprit !== null && isset($this->manifest['suspects'][$culprit]);
    }

    /**
     * Just the culprit's slug.
     *
     * Deliberately separate from solution(): scoring an accusation needs only
     * this, so the scoreboard never loads the reveal into memory and therefore
     * cannot leak it.
     */
    public function culpritSlug(): ?string
    {
        return $this->hasSolution() ? $this->declaredCulpritSlug() : null;
    }

    /**
     * What the manifest says, before checking it against the roster. Used by
     * the integrity test to tell "not written yet" from "written wrong".
     */
    public function declaredCulpritSlug(): ?string
    {
        $culprit = $this->manifest['solution']['culprit_slug'] ?? null;

        if (! is_string($culprit) || $culprit === '' || $culprit === self::PENDING) {
            return null;
        }

        return $culprit;
    }

    /**
     * The full reveal, with the culprit resolved and the prose rendered.
     *
     * @return array<string, mixed>
     */
    public function solution(): array
    {
        $solution = (array) ($this->manifest['solution'] ?? []);
        $slug = $this->culpritSlug();

        return [
            'culprit' => $slug ? ($this->suspect($slug) + ['slug' => $slug]) : null,
            'headline' => $solution['headline'] ?? null,
            'motive' => $solution['motive'] ?? null,
            'method' => $solution['method'] ?? null,
            'key_evidence' => array_values((array) ($solution['key_evidence'] ?? [])),
            'body_html' => $this->renderSolution(),
            'gallery' => $this->galleryFor($solution['file'] ?? null),
        ];
    }

    /**
     * The reveal's prose as HTML.
     *
     * Shared on purpose: the screen renders it today and the epilogue emails
     * will render it later. Two renderers would drift.
     */
    public function renderSolution(): string
    {
        $file = $this->manifest['solution']['file'] ?? null;

        return $file ? $this->content()->renderFile($file) : '';
    }

    /**
     * Why a given innocent suspect could not have done it. Authored, one per
     * suspect; consumed by the personalised epilogue.
     */
    public function exonerationFor(string $slug): ?string
    {
        $text = $this->manifest['solution']['exonerations'][$slug] ?? null;

        return is_string($text) && $text !== '' ? $text : null;
    }

    /**
     * The culprit's confession, written to be playable as-is so the table
     * still gets an ending when the AI is unavailable.
     */
    public function confessionScript(): ?string
    {
        $script = $this->manifest['solution']['confession_script'] ?? null;

        return is_string($script) && trim($script) !== '' ? trim($script) : null;
    }

    /**
     * Timeline attached to every new game of this case.
     *
     * @return array<int, array<string, mixed>>
     */
    public function timeline(): array
    {
        return array_values((array) ($this->manifest['timeline'] ?? []));
    }

    /**
     * Images that accompany the verbatim text of a given source file, with
     * URLs already resolved.
     *
     * @return array<int, array{url: string, caption: string}>
     */
    public function galleryFor(?string $sourceFile): array
    {
        if (! $sourceFile) {
            return [];
        }

        $items = (array) ($this->manifest['gallery'][$sourceFile] ?? []);

        return array_map(fn (array $item) => [
            'url' => $this->galleryUrl($item['file'] ?? null),
            'caption' => (string) ($item['caption'] ?? ''),
        ], array_values($items));
    }

    /**
     * Section headings already shown as gallery images, so the text version is
     * omitted and the same content is not repeated twice.
     *
     * @return string[]
     */
    public function excludedHeadingsFor(?string $sourceFile): array
    {
        if (! $sourceFile) {
            return [];
        }

        return array_values((array) ($this->manifest['gallery_excluded_headings'][$sourceFile] ?? []));
    }

    /**
     * Render a timeline event's body: verbatim from its source file when it
     * cites one, otherwise its inline markdown.
     */
    public function renderEventBody(?string $sourceFile, ?string $bodyMarkdown): string
    {
        if ($sourceFile) {
            return $this->content()->renderFileExcludingSections(
                $sourceFile,
                $this->excludedHeadingsFor($sourceFile)
            );
        }

        return CaseContent::render((string) $bodyMarkdown);
    }

    private function photoUrl(?string $file): string
    {
        return $file ? $this->assetUrl('photos/'.$file) : '';
    }

    private function galleryUrl(?string $file): string
    {
        return $file ? $this->assetUrl('gallery/'.$file) : '';
    }

    private function assetUrl(string $relativePath): string
    {
        return '/immersion/'.$this->slug.'/'.$relativePath;
    }

    /**
     * Filesystem path of a public asset, for embedding images into emails.
     */
    public function assetPath(string $url): string
    {
        return public_path(ltrim($url, '/'));
    }
}
