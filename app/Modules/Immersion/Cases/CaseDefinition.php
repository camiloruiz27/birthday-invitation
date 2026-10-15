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
            'ad' => $this->adCopy((array) ($catalog['ad'] ?? [])),
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
     * The optional `catalog.ad` block: copy for an advertisement pointing at
     * this case. Anything not written stays null so the ad landing can fall
     * back field by field, and what IS written is cleaned (trimmed, empty
     * lines dropped, at most three points) so a stray blank in a manifest
     * cannot become an empty bullet on a paid landing page.
     *
     * @param  array<string, mixed>  $ad
     * @return array{hook: ?string, bullets: array<int, string>, cta: ?string}|null
     */
    private function adCopy(array $ad): ?array
    {
        $clean = fn (mixed $value): ?string => is_string($value) && trim($value) !== '' ? trim($value) : null;

        $bullets = array_values(array_filter(
            array_map($clean, (array) ($ad['bullets'] ?? [])),
            fn (?string $bullet) => $bullet !== null
        ));

        $copy = [
            'hook' => $clean($ad['hook'] ?? null),
            'bullets' => array_slice($bullets, 0, 3),
            'cta' => $clean($ad['cta'] ?? null),
        ];

        return $copy['hook'] === null && $copy['bullets'] === [] && $copy['cta'] === null ? null : $copy;
    }

    /**
     * The person the case is about. `label` is the stamp shown next to them on
     * the interrogation board: "Víctima" by default, but a missing person who
     * may still be alive is not a victim to the table, and stamping them as one
     * can give away the very thing the case hides.
     *
     * @return array{name: string, photo_url: string, label: string}
     */
    public function victim(): array
    {
        $victim = $this->manifest['victim'] ?? ['name' => '', 'photo' => null];

        return [
            'name' => (string) ($victim['name'] ?? ''),
            'photo_url' => $this->photoUrl($victim['photo'] ?? null),
            'label' => $this->plainString($victim['label'] ?? null) ?? 'Víctima',
        ];
    }

    /**
     * What happened to the person the case is about, as a noun phrase the
     * shared AI prompts can drop into a sentence: "la muerte de X" or "la
     * desaparición de X".
     *
     * Derived from the victim's `label` so a case that already says it is
     * about someone missing needs nothing else, and the shared prompts never
     * talk about a death in a case where nobody is known to be dead. A case
     * can still state it itself with `victim.incident` ("el secuestro de…").
     */
    public function incident(): string
    {
        $victim = $this->manifest['victim'] ?? [];
        $name = (string) ($victim['name'] ?? '');

        $written = $this->plainString($victim['incident'] ?? null);

        if ($written !== null) {
            return str_contains($written, '{name}') ? str_replace('{name}', $name, $written) : "{$written} {$name}";
        }

        $label = mb_strtolower($this->victim()['label']);

        return str_contains($label, 'desaparec')
            ? "la desaparición de {$name}"
            : "la muerte de {$name}";
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
                'interrogable' => ($suspect['interrogable'] ?? true) !== false,
            ];
        }

        return $suspects;
    }

    /**
     * The people the table can question: the roster minus anyone marked
     * `'interrogable' => false`.
     *
     * That flag is for someone who belongs in the accusation but cannot be
     * reached — a culprit nobody managed to locate. Putting them on the board
     * would only spend the table's questions on silence. They stay in
     * suspects(), so they can still be accused and still write an epilogue.
     *
     * @return array<string, array<string, mixed>>
     */
    public function interrogableSuspects(): array
    {
        return array_filter($this->suspects(), fn (array $suspect) => $suspect['interrogable']);
    }

    public function isInterrogable(string $slug): bool
    {
        return (bool) ($this->suspect($slug)['interrogable'] ?? false);
    }

    /** Extra questions a person gets when their case has a confrontation for them. */
    public const CONFRONTATION_QUESTIONS = 3;

    /**
     * How a person's questions open up over the case, in order.
     *
     * Optional `interrogation.stages` in their manifest entry:
     *
     *   ['questions' => 3],                                    // from the start
     *   ['questions' => 2, 'after' => ['V6']],                 // once V6 is delivered
     *   ['questions' => 3, 'after' => ['V14'], 'confrontation' => true],
     *
     * Without it, the person has one stage with the case's base budget,
     * open from the start — exactly how every case behaved before stages.
     *
     * @return array<int, array{questions: int, after: string[], confrontation: bool, note: ?string}>
     */
    public function interrogationStages(string $slug): array
    {
        $stages = (array) ($this->manifest['suspects'][$slug]['interrogation']['stages'] ?? []);

        $normalised = [];

        foreach ($stages as $stage) {
            $questions = max(0, (int) ($stage['questions'] ?? 0));

            if ($questions === 0) {
                continue;
            }

            $normalised[] = [
                'questions' => $questions,
                'after' => array_values(array_filter(array_map('strval', (array) ($stage['after'] ?? [])))),
                'confrontation' => (bool) ($stage['confrontation'] ?? false),
                'note' => $this->plainString($stage['note'] ?? null),
            ];
        }

        return $normalised !== [] ? $normalised : [[
            'questions' => $this->interrogationQuestions(),
            'after' => [],
            'confrontation' => false,
            'note' => null,
        ]];
    }

    /**
     * Every question a person can ever be asked in this case: what a game
     * reserves AI credits for, and the session's capacity.
     */
    public function totalQuestionsFor(string $slug): int
    {
        return array_sum(array_column($this->interrogationStages($slug), 'questions'));
    }

    /**
     * The fewest and the most ordinary questions (confrontations left out) any
     * interrogable person of this case has. People differ: a witness who can
     * confirm two facts does not need the five a suspect with a full story
     * does, so copy that promises "N questions per person" has to speak of a
     * range, not of the case default.
     *
     * @return array{min: int, max: int}
     */
    public function baseQuestionRange(): array
    {
        $totals = [];

        foreach (array_keys($this->interrogableSuspects()) as $slug) {
            $totals[] = array_sum(array_column(
                array_filter($this->interrogationStages($slug), fn (array $stage) => ! $stage['confrontation']),
                'questions'
            ));
        }

        if ($totals === []) {
            return ['min' => 0, 'max' => 0];
        }

        return ['min' => min($totals), 'max' => max($totals)];
    }

    /**
     * "5 preguntas" when everyone has the same number, "entre 3 y 5 preguntas"
     * when they do not: the words used wherever a player is told how many
     * questions each person gets.
     */
    public function questionsPerPersonLabel(): string
    {
        ['min' => $min, 'max' => $max] = $this->baseQuestionRange();

        return $min === $max ? "{$max} preguntas" : "entre {$min} y {$max} preguntas";
    }

    /**
     * Questions open right now, given the evidence codes already delivered
     * to the table: every stage whose `after` codes have all arrived.
     */
    public function availableQuestionsFor(string $slug, array $deliveredCodes): int
    {
        $available = 0;

        foreach ($this->interrogationStages($slug) as $stage) {
            if (array_diff($stage['after'], $deliveredCodes) === []) {
                $available += $stage['questions'];
            }
        }

        return $available;
    }

    /**
     * The people whose questions grow once THESE codes are delivered: used
     * to tell the table "con esta evidencia se habilitan nuevas preguntas
     * para…" on the event that brings them.
     *
     * A stage counts when this delivery completes it — all its codes are in
     * $deliveredAfter and at least one of them was not in $deliveredBefore.
     *
     * @param  string[]  $deliveredBefore
     * @param  string[]  $deliveredAfter
     * @return array<int, array{slug: string, name: string, questions: int, confrontation: bool}>
     */
    public function stagesUnlockedBetween(array $deliveredBefore, array $deliveredAfter): array
    {
        $unlocked = [];

        foreach ($this->interrogableSuspects() as $slug => $suspect) {
            $questions = 0;
            $confrontation = false;

            foreach ($this->interrogationStages($slug) as $stage) {
                if ($stage['after'] === []) {
                    continue;
                }

                $completeNow = array_diff($stage['after'], $deliveredAfter) === [];
                $completeBefore = array_diff($stage['after'], $deliveredBefore) === [];

                if ($completeNow && ! $completeBefore) {
                    $questions += $stage['questions'];
                    $confrontation = $confrontation || $stage['confrontation'];
                }
            }

            if ($questions > 0) {
                $unlocked[] = [
                    'slug' => $slug,
                    'name' => $suspect['name'],
                    'questions' => $questions,
                    'confrontation' => $confrontation,
                ];
            }
        }

        return $unlocked;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function suspect(string $slug): ?array
    {
        return $this->suspects()[$slug] ?? null;
    }

    /**
     * The same roster as suspects(), cut down to what a PLAYER's browser may
     * hold: name, role, relation and photo. `motive`, `alibi` and `file` are
     * author/AI material (the file IS the persona's prompt), so they never go
     * into a player's page props — anything that is not listed here stays out
     * by default.
     *
     * This is the interrogation board, so only people who can be questioned
     * are on it (see interrogableSuspects()).
     *
     * @return array<string, array<string, mixed>>
     */
    public function suspectsForPlayer(): array
    {
        return array_map(
            fn (array $suspect) => $this->playerView($suspect),
            $this->interrogableSuspects()
        );
    }

    /**
     * Null for anyone who is not on the interrogation board — unknown, or
     * marked not interrogable — so the chat page and the ask endpoint 404 the
     * same way for both.
     *
     * @return array<string, mixed>|null
     */
    public function suspectForPlayer(string $slug): ?array
    {
        $suspect = $this->suspect($slug);

        return $suspect && $suspect['interrogable'] ? $this->playerView($suspect) : null;
    }

    /**
     * @param  array<string, mixed>  $suspect
     * @return array<string, mixed>
     */
    private function playerView(array $suspect): array
    {
        return [
            'name' => $suspect['name'],
            'role' => $suspect['role'],
            'connection' => $suspect['connection'],
            'photo_url' => $suspect['photo_url'],
        ];
    }

    /**
     * The player-facing "ficha" of a person, shown once their interrogation is
     * over. It is an allowlist read from the manifest's `public` block — never
     * a rendering of the suspect's .md, which holds the persona's secrets.
     * Anything not listed is dropped, and a person without a block falls back
     * to the single relation line, so a missing entry fails closed.
     *
     * @return array{age: ?string, profile: ?string, alibi: ?string, facts: string[]}|null
     */
    public function ficha(string $slug): ?array
    {
        $entry = $this->manifest['suspects'][$slug] ?? null;

        if (! is_array($entry)) {
            return null;
        }

        $public = (array) ($entry['public'] ?? []);

        $ficha = [
            'age' => $this->plainString($public['age'] ?? null),
            'profile' => $this->plainString($public['profile'] ?? null),
            'alibi' => $this->plainString($public['alibi'] ?? null),
            'facts' => array_values(array_filter(array_map(
                fn ($fact) => $this->plainString($fact),
                (array) ($public['facts'] ?? [])
            ))),
        ];

        if ($ficha['profile'] === null && $ficha['facts'] === []) {
            $ficha['profile'] = $this->plainString($entry['connection'] ?? null);
        }

        return $ficha;
    }

    private function plainString(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
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
    | The accusation form and how it is graded
    |--------------------------------------------------------------------------
    |
    | Optional `accusation` block in the manifest:
    |
    |   'accusation' => [
    |       'culprit_points' => 30,
    |       'weapon' => ['label' => '¿Con qué?', 'hint' => '…', 'points' => 15,
    |                    'answer' => '…' (defaults to solution.method), 'guidance' => '…'],
    |       'motive' => ['label' => '¿Por qué?', 'hint' => '…', 'points' => 20,
    |                    'answer' => '…' (defaults to solution.motive), 'guidance' => '…'],
    |       'questions' => [
    |           ['key' => 'origen', 'label' => '¿Quién originó la avería?', 'hint' => '…',
    |            'answer' => '…', 'points' => 15, 'guidance' => '…'],
    |       ],
    |   ],
    |
    | A case without the block still gets graded: method and motive against the
    | authored solution, with default points.
    |
    */

    private const DEFAULT_POINTS = ['culprit' => 40, 'weapon' => 30, 'motive' => 30];

    private const DEFAULT_LABELS = [
        'weapon' => ['¿Con qué?', 'El método o el arma: qué usó para hacerlo.'],
        'motive' => ['¿Por qué?', 'Tu razonamiento. Lo leerán los demás cuando se revele la solución.'],
    ];

    /**
     * What the PLAYER's form shows: labels, hints and what each part is worth,
     * never the reference answers.
     *
     * The points are shown on purpose: they are what tells a player where the
     * effort pays off, and they are the same for everyone. The method and the
     * motive are required; the case's own extra questions are optional (an
     * empty one simply scores zero).
     *
     * @return array{culprit_points: int, max: int, weapon: array{label: string, hint: string, points: int}, motive: array{label: string, hint: string, points: int}, questions: array<int, array{key: string, label: string, hint: ?string, points: int}>}
     */
    public function accusationForm(): array
    {
        $rubric = $this->accusationRubric();
        $points = array_column($rubric['criteria'], 'points', 'key');

        $field = fn (string $name) => $this->accusationFieldTexts($name) + [
            'points' => (int) ($points[$name] ?? 0),
        ];

        return [
            'culprit_points' => $rubric['culprit_points'],
            'max' => $rubric['max'],
            'weapon' => $field('weapon'),
            'motive' => $field('motive'),
            'questions' => array_map(fn (array $question) => [
                'key' => $question['key'],
                'label' => $question['label'],
                'hint' => $question['hint'],
                'points' => $question['points'],
            ], $this->accusationQuestions()),
        ];
    }

    /**
     * Label and hint of the built-in `weapon` / `motive` fields. Kept apart
     * from accusationForm() because accusationRubric() needs the labels too,
     * and the form needs the rubric's points: each calling the other would
     * never end.
     *
     * @return array{label: string, hint: string}
     */
    private function accusationFieldTexts(string $name): array
    {
        $block = (array) ($this->manifest['accusation'][$name] ?? []);

        return [
            'label' => $this->plainString($block['label'] ?? null) ?? self::DEFAULT_LABELS[$name][0],
            'hint' => $this->plainString($block['hint'] ?? null) ?? self::DEFAULT_LABELS[$name][1],
        ];
    }

    /**
     * The case's extra questions, with their reference answers. Server-side
     * only: this is graded material.
     *
     * A question without a key, a label or an answer is skipped rather than
     * shown: asking something nobody can grade would only cost the table.
     *
     * @return array<int, array{key: string, label: string, hint: ?string, answer: string, points: int, guidance: ?string}>
     */
    public function accusationQuestions(): array
    {
        $questions = [];

        foreach ((array) ($this->manifest['accusation']['questions'] ?? []) as $question) {
            $key = $this->plainString($question['key'] ?? null);
            $label = $this->plainString($question['label'] ?? null);
            $answer = $this->plainString($question['answer'] ?? null);

            if ($key === null || $label === null || $answer === null || ! preg_match('/^[a-z0-9_]+$/', $key)) {
                continue;
            }

            $questions[] = [
                'key' => $key,
                'label' => $label,
                'hint' => $this->plainString($question['hint'] ?? null),
                'answer' => $answer,
                'points' => max(0, (int) ($question['points'] ?? 10)),
                'guidance' => $this->plainString($question['guidance'] ?? null),
            ];
        }

        return $questions;
    }

    /**
     * Everything the grader needs: the culprit's points (scored exactly, never
     * by the model) and every free-text criterion with its reference answer.
     *
     * @return array{culprit_points: int, criteria: array<int, array{key: string, label: string, answer: string, points: int, guidance: ?string}>, max: int}
     */
    public function accusationRubric(): array
    {
        $block = (array) ($this->manifest['accusation'] ?? []);
        $solution = (array) ($this->manifest['solution'] ?? []);

        $criteria = [];

        foreach (['weapon' => 'method', 'motive' => 'motive'] as $field => $solutionKey) {
            $answer = $this->plainString($block[$field]['answer'] ?? null)
                ?? $this->plainString($solution[$solutionKey] ?? null);

            if ($answer === null) {
                continue;
            }

            $criteria[] = [
                'key' => $field,
                'label' => $this->accusationFieldTexts($field)['label'],
                'answer' => $answer,
                'points' => max(0, (int) ($block[$field]['points'] ?? self::DEFAULT_POINTS[$field])),
                'guidance' => $this->plainString($block[$field]['guidance'] ?? null),
            ];
        }

        foreach ($this->accusationQuestions() as $question) {
            $criteria[] = [
                'key' => $question['key'],
                'label' => $question['label'],
                'answer' => $question['answer'],
                'points' => $question['points'],
                'guidance' => $question['guidance'],
            ];
        }

        $culpritPoints = max(0, (int) ($block['culprit_points'] ?? self::DEFAULT_POINTS['culprit']));

        return [
            'culprit_points' => $culpritPoints,
            'criteria' => $criteria,
            'max' => $culpritPoints + array_sum(array_column($criteria, 'points')),
        ];
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
     * Who writes the winners' epilogue when the culprit cannot: a culprit
     * nobody can question has no voice to write in, so the case names someone
     * else (`solution.epilogue_speaker`) to tell the table what happened.
     * Null when the case does not name one, or names a person who cannot be
     * questioned either.
     */
    public function epilogueSpeakerSlug(): ?string
    {
        $slug = $this->manifest['solution']['epilogue_speaker'] ?? null;

        return is_string($slug) && $this->suspect($slug) && $this->isInterrogable($slug) ? $slug : null;
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
     * Which voice reads the confession. Null lets the gateway pick its default.
     */
    public function confessionVoice(): ?string
    {
        $voice = $this->manifest['solution']['confession_voice'] ?? null;

        return is_string($voice) && $voice !== '' ? $voice : null;
    }

    /**
     * Which endings this case can actually deliver.
     *
     * An ending is a capability of the CONTENT, not of the build: the epilogue
     * needs a written line for every innocent suspect, and the confession audio
     * needs a script. Offering one the case cannot fulfil would sell a Game
     * Master an ending that silently degrades at the table, so the creation
     * form and the server both ask here.
     *
     * @return string[]
     */
    public function supportedEndings(): array
    {
        if (! $this->hasSolution()) {
            return [];
        }

        $endings = ['classic'];

        if ($this->hasEveryExoneration()) {
            $endings[] = 'epilogue';
        }

        // The confession now weaves in what the table asked during the
        // interrogation, so a case without that mechanic cannot deliver it.
        if ($this->confessionScript() !== null && $this->hasMechanic('interrogation')) {
            $endings[] = 'confession_audio';
        }

        return $endings;
    }

    public function supportsEnding(string $endingType): bool
    {
        return in_array($endingType, $this->supportedEndings(), true);
    }

    /**
     * Does every suspect who is not the culprit have an authored reason they
     * could not have done it? The epilogue has no fallback: a missing line
     * would leave the model to reason out the player's mistake by itself,
     * which is exactly inventing the ending.
     */
    private function hasEveryExoneration(): bool
    {
        $culprit = $this->culpritSlug();

        foreach (array_keys($this->suspects()) as $slug) {
            if ($slug !== $culprit && $this->exonerationFor($slug) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Timeline attached to every new game of this case, ready to be written as
     * event rows.
     *
     * The manifest is authoring vocabulary and the table is engine vocabulary;
     * this is where one becomes the other. `audio_file` names a recording that
     * shipped with the case, so the event starts life already pointing at it —
     * no generation, no queue, and nothing for a busy TTS model to fail at.
     *
     * @return array<int, array<string, mixed>>
     */
    public function timeline(): array
    {
        $events = [];

        foreach ((array) ($this->manifest['timeline'] ?? []) as $event) {
            // Authoring-only keys: they describe how the recording was made,
            // not what the engine has to store.
            unset($event['audio_scene'], $event['audio_context'], $event['audio_speaker'], $event['audio_voice']);

            // Manifest-only: which evidence codes this event carries. Read by
            // evidenceCodeMap() straight from the manifest, never persisted as
            // a column on the event row.
            unset($event['evidence_codes']);

            $file = $event['audio_file'] ?? null;
            unset($event['audio_file']);

            if (is_string($file) && $file !== '') {
                $event['audio_path'] = 'immersion/'.$this->slug.'/audio/'.$file;
                $event['audio_status'] = 'ready';
            }

            $events[] = $event;
        }

        return $events;
    }

    /**
     * The audio the case needs recorded, with everything the studio form asks
     * for. Consumed by `immersion:export-audio-scripts`, never by the engine.
     *
     * @return array<int, array<string, mixed>>
     */
    public function audioScripts(): array
    {
        $scripts = [];

        foreach ((array) ($this->manifest['timeline'] ?? []) as $index => $event) {
            if (($event['type'] ?? null) !== 'audio_email' || empty($event['audio_script'])) {
                continue;
            }

            $scripts[] = [
                'index' => $index + 1,
                'title' => (string) ($event['title'] ?? ''),
                'minute' => (int) ($event['trigger_offset_minutes'] ?? 0),
                'scene' => (string) ($event['audio_scene'] ?? ''),
                'context' => (string) ($event['audio_context'] ?? ''),
                'speaker' => (string) ($event['audio_speaker'] ?? 'Speaker 1'),
                'voice' => (string) ($event['audio_voice'] ?? ''),
                'script' => trim((string) $event['audio_script']),
                'file' => (string) ($event['audio_file'] ?? ''),
            ];
        }

        return $scripts;
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
     * Evidence codes (e.g. "V8", "A3"), keyed by the timeline offset that
     * delivers them.
     *
     * Manifest-only, like the gallery: a case author tags a timeline event
     * with the evidence it hands the table, so an interrogation can later be
     * told which codes actually exist in THIS game yet — see
     * Game::deliveredEvidenceCodes(). A case that never declares
     * `evidence_codes` gets an empty map here, which is the point: this is
     * purely additive and changes nothing for a case that does not opt in.
     *
     * @return array<int, string[]>
     */
    public function evidenceCodeMap(): array
    {
        $map = [];

        foreach ((array) ($this->manifest['timeline'] ?? []) as $event) {
            $codes = array_values((array) ($event['evidence_codes'] ?? []));

            if ($codes === []) {
                continue;
            }

            $offset = (int) ($event['trigger_offset_minutes'] ?? 0);
            $map[$offset] = array_values(array_unique(array_merge($map[$offset] ?? [], $codes)));
        }

        return $map;
    }

    /**
     * Render a timeline event's body: verbatim from its source file when it
     * cites one, otherwise its inline markdown.
     */
    public function renderEventBody(?string $sourceFile, ?string $bodyMarkdown, ?int $offset = null): string
    {
        $html = $sourceFile
            ? $this->content()->renderFileExcludingSections(
                $sourceFile,
                $this->excludedHeadingsFor($sourceFile)
            )
            : CaseContent::render((string) $bodyMarkdown);

        // Derived from the manifest, so no case has to write it by hand and it
        // can never drift from the stages it describes.
        $notice = $offset !== null ? $this->questionsUnlockedNotice($offset) : null;

        return $notice ? $html.CaseContent::render($notice) : $html;
    }

    /**
     * "Con esta evidencia se habilitan nuevas preguntas para…", for the event
     * at this offset, or null when it opens nobody's questions.
     */
    public function questionsUnlockedNotice(int $offset): ?string
    {
        $map = $this->evidenceCodeMap();

        $before = [];
        $after = [];

        foreach ($map as $at => $codes) {
            if ($at < $offset) {
                $before = array_merge($before, $codes);
            }

            if ($at <= $offset) {
                $after = array_merge($after, $codes);
            }
        }

        $unlocked = $this->stagesUnlockedBetween($before, $after);

        if ($unlocked === []) {
            return null;
        }

        $lines = array_map(function (array $person) {
            $what = $person['confrontation']
                ? "{$person['questions']} preguntas, para confrontarla con lo nuevo"
                : ($person['questions'] === 1 ? '1 pregunta más' : "{$person['questions']} preguntas más");

            return "- **{$person['name']}**: {$what}";
        }, $unlocked);

        return "---\n\n"
            ."**Nuevas preguntas en el interrogatorio.** Con esta evidencia se habilitan más preguntas para:\n\n"
            .implode("\n", $lines)."\n\n"
            .'Si ya habías terminado de interrogar a alguna de estas personas, puedes volver con ella: '
            .'sigue siendo tu interrogatorio. Cada persona habla con un solo investigador.';
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
