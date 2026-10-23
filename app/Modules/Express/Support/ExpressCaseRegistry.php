<?php

namespace App\Modules\Express\Support;

use InvalidArgumentException;

/**
 * The Express cases installed in Cases/*.json.
 *
 * Kept apart from Immersion's CaseRegistry on purpose: these files use their
 * own schema and must never show up in the paid catalog, the game master's
 * case picker or CaseRegistryTest.
 */
final class ExpressCaseRegistry
{
    public const SCHEMA = 'misteriocode.express.author.v1';

    /** @var array<string, ExpressCase>|null */
    private ?array $cases = null;

    public function __construct(private readonly ?string $basePath = null)
    {
    }

    private function path(): string
    {
        return $this->basePath ?? dirname(__DIR__).'/Cases';
    }

    /** @return array<int, string> slugs in file order */
    public function slugs(): array
    {
        return array_keys($this->all());
    }

    /** @return array<string, ExpressCase> */
    public function all(): array
    {
        if ($this->cases === null) {
            $this->cases = [];

            $files = glob($this->path().'/*.json') ?: [];
            sort($files);

            foreach ($files as $file) {
                $data = json_decode((string) file_get_contents($file), true);

                if (! is_array($data)) {
                    throw new InvalidArgumentException(basename($file).' is not valid JSON.');
                }

                if ($problems = self::problems($data)) {
                    throw new InvalidArgumentException(basename($file).': '.implode(' ', $problems));
                }

                $case = new ExpressCase($data);
                $this->cases[$case->slug()] = $case;
            }
        }

        return $this->cases;
    }

    public function find(string $slug): ?ExpressCase
    {
        return $this->all()[$slug] ?? null;
    }

    /**
     * What is wrong with a case file, in plain sentences (empty = valid).
     * The rules are the ones the engine leans on: six clues unlocked in order,
     * three people with two answerable topics each, and an accusation whose
     * right answer is one of the options offered.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    public static function problems(array $data): array
    {
        $problems = [];

        if (($data['schema'] ?? null) !== self::SCHEMA) {
            $problems[] = 'schema must be '.self::SCHEMA.'.';
        }

        foreach (['case_id', 'title', 'tagline'] as $key) {
            if (! is_string($data[$key] ?? null) || $data[$key] === '') {
                $problems[] = "{$key} is required.";
            }
        }

        foreach (['scenario', 'incident', 'hook'] as $key) {
            if (! is_string($data['high_concept'][$key] ?? null) || $data['high_concept'][$key] === '') {
                $problems[] = "high_concept.{$key} is required.";
            }
        }

        $evidence = $data['evidence'] ?? [];
        $ids = array_column($evidence, 'id');

        if ($ids !== ['E1', 'E2', 'E3', 'E4', 'E5', 'E6']) {
            $problems[] = 'evidence must be E1..E6 in order.';
        }

        $previous = -1;
        foreach ($evidence as $clue) {
            if (! in_array($clue['audience'] ?? null, ['A', 'B', 'global'], true)) {
                $problems[] = ($clue['id'] ?? '?').' has an invalid audience.';
            }

            if (! in_array($clue['type'] ?? null, ['audio', 'document', 'screenshot', 'photo', 'cctv'], true)) {
                $problems[] = ($clue['id'] ?? '?').' has an invalid type.';
            }

            if (! isset($clue['unlock_second']) || $clue['unlock_second'] < $previous) {
                $problems[] = ($clue['id'] ?? '?').' unlocks out of order.';
            }
            $previous = (int) ($clue['unlock_second'] ?? 0);

            if (! is_string($clue['content_exact'] ?? null) || trim($clue['content_exact']) === '') {
                $problems[] = ($clue['id'] ?? '?').' has no content.';
            }
        }

        $audiences = array_column($evidence, 'audience');
        if (! in_array('A', $audiences, true) || ! in_array('B', $audiences, true)) {
            $problems[] = 'the case needs private clues for both A and B.';
        }

        $people = $data['characters'] ?? [];
        $personIds = array_column($people, 'id');

        if (count($people) !== 3 || count(array_unique($personIds)) !== 3) {
            $problems[] = 'exactly three distinct characters are required.';
        }

        foreach ($people as $person) {
            $topics = $person['topics'] ?? [];

            if (count($topics) !== 2) {
                $problems[] = ($person['id'] ?? '?').' needs exactly two topics.';
            }

            foreach ($topics as $topic) {
                if (empty($topic['paraphrases']) || ! is_string($topic['response_exact'] ?? null) || $topic['response_exact'] === '') {
                    $problems[] = ($person['id'] ?? '?').'/'.($topic['id'] ?? '?').' needs paraphrases and an answer.';
                }
            }
        }

        $ui = $data['accusation_ui'] ?? [];
        $whoIds = array_column($ui['who_options'] ?? [], 'id');
        $culprit = $data['author_only']['culprit_id'] ?? null;

        if ($whoIds !== $personIds) {
            $problems[] = 'who_options must list the characters, in order.';
        }

        if (! in_array($culprit, $personIds, true)) {
            $problems[] = 'culprit_id must be one of the characters.';
        }

        $accusation = $data['author_only']['accusation'] ?? [];

        if (($accusation['who'] ?? null) !== $culprit) {
            $problems[] = 'accusation.who must match culprit_id.';
        }

        foreach (['how' => 'how_options', 'why' => 'why_options'] as $key => $list) {
            $options = $ui[$list] ?? [];

            if (count($options) !== 3 || count(array_unique($options)) !== 3) {
                $problems[] = "{$list} must hold three distinct options.";
            }

            if (! in_array($accusation[$key] ?? null, $options, true)) {
                $problems[] = "accusation.{$key} must be one of {$list}.";
            }
        }

        if (! is_string($data['author_only']['truth'] ?? null) || $data['author_only']['truth'] === '') {
            $problems[] = 'author_only.truth is required.';
        }

        return $problems;
    }
}
