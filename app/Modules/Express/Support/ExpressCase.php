<?php

namespace App\Modules\Express\Support;

/**
 * One Express case, read from Cases/<slug>.json.
 *
 * The file mixes what a player may see with the author's secrets
 * (`author_only`: the culprit, the truth, the reconstruction). Nothing here
 * hands the raw array out: each accessor returns one thing, and only
 * solution() touches the secrets — callers use it once the round has been
 * revealed.
 */
final class ExpressCase
{
    /** @param  array<string, mixed>  $data */
    public function __construct(private readonly array $data)
    {
    }

    public function slug(): string
    {
        return (string) $this->data['case_id'];
    }

    public function title(): string
    {
        return (string) $this->data['title'];
    }

    public function tagline(): string
    {
        return (string) ($this->data['tagline'] ?? '');
    }

    public function genre(): string
    {
        return (string) ($this->data['genre'] ?? '');
    }

    public function scenario(): string
    {
        return (string) ($this->data['high_concept']['scenario'] ?? '');
    }

    public function incident(): string
    {
        return (string) ($this->data['high_concept']['incident'] ?? '');
    }

    public function hook(): string
    {
        return (string) ($this->data['high_concept']['hook'] ?? '');
    }

    /**
     * Every clue, in the order of the file. `lines` is the text broken where a
     * new timestamped entry starts, so a log reads as a log.
     *
     * @return array<int, array{id: string, unlock_second: int, audience: string, type: string, title: string, text: string, lines: array<int, string>}>
     */
    public function evidence(): array
    {
        return array_map(fn (array $e) => [
            'id' => (string) $e['id'],
            'unlock_second' => (int) $e['unlock_second'],
            'audience' => (string) $e['audience'],
            'type' => (string) $e['type'],
            'title' => (string) $e['title'],
            'text' => (string) $e['content_exact'],
            'lines' => self::splitLines((string) $e['content_exact']),
        ], $this->data['evidence']);
    }

    /**
     * The people who can be questioned. What is returned is the public sheet
     * (name, role, relation to the incident) plus their answerable topics.
     *
     * @return array<int, array{id: string, name: string, role: string, relation: string, topics: array<int, array{id: string, paraphrases: array<int, string>, answer: string, unlock_second: int}>}>
     */
    public function suspects(): array
    {
        return array_map(fn (array $c) => [
            'id' => (string) $c['id'],
            'name' => (string) $c['name'],
            'role' => (string) $c['role'],
            'relation' => (string) ($c['relation_to_incident'] ?? ''),
            'topics' => array_map(fn (array $t) => [
                'id' => (string) $t['id'],
                'paraphrases' => array_values(array_map('strval', $t['paraphrases'])),
                'answer' => (string) $t['response_exact'],
                'unlock_second' => (int) ($t['requires_unlock_second'] ?? 0),
            ], $c['topics']),
        ], $this->data['characters']);
    }

    /**
     * What the AI needs to play one suspect: how they talk and what they know,
     * believe, claim and hide. It goes to the gateway and nowhere else — it
     * holds the suspect's secret, so PublicState must never call this. The
     * case's overall truth (who did it, how, why) is deliberately NOT here:
     * a suspect knows their own part, not the author's solution.
     *
     * @return array<string, string>
     */
    public function persona(string $id): array
    {
        $person = collect($this->data['characters'])->firstWhere('id', $id) ?? [];

        // The case file's names -> the names the gateway's prompt reads.
        $fields = [
            'relation_to_incident' => 'relation_to_incident',
            'opportunity_alibi' => 'alibi',
            'tone' => 'tone',
            'knows' => 'knows',
            'believes' => 'believes',
            'does_not_know' => 'does_not_know',
            'lie' => 'stance',
            'secret' => 'hidden',
        ];

        $persona = [];
        foreach ($fields as $from => $to) {
            if (isset($person[$from]) && is_string($person[$from]) && $person[$from] !== '') {
                $persona[$to] = $person[$from];
            }
        }

        return $persona;
    }

    /** @return array<string, mixed>|null */
    public function suspect(string $id): ?array
    {
        foreach ($this->suspects() as $suspect) {
            if ($suspect['id'] === $id) {
                return $suspect;
            }
        }

        return null;
    }

    /** @return array<int, array{id: string, label: string}> */
    public function whoOptions(): array
    {
        return array_map(fn (array $o) => [
            'id' => (string) $o['id'],
            'label' => (string) $o['label'],
        ], $this->data['accusation_ui']['who_options']);
    }

    /** @return array<int, string> */
    public function howOptions(): array
    {
        return array_values(array_map('strval', $this->data['accusation_ui']['how_options']));
    }

    /** @return array<int, string> */
    public function whyOptions(): array
    {
        return array_values(array_map('strval', $this->data['accusation_ui']['why_options']));
    }

    /**
     * What the right answer is, as the same shapes a stored accusation uses:
     * a suspect id and two option positions.
     *
     * @return array{who: string, how: int, why: int}
     */
    public function correct(): array
    {
        $accusation = $this->data['author_only']['accusation'];

        return [
            'who' => (string) $this->data['author_only']['culprit_id'],
            'how' => (int) array_search($accusation['how'], $this->howOptions(), true),
            'why' => (int) array_search($accusation['why'], $this->whyOptions(), true),
        ];
    }

    /**
     * The author's resolution. Only for a round that has been revealed.
     *
     * @return array{headline: string, truth: string, reconstruction: string, nodes: array<int, string>}
     */
    public function solution(): array
    {
        $secret = $this->data['author_only'];

        return [
            'headline' => $this->tagline(),
            'truth' => (string) $secret['truth'],
            'reconstruction' => (string) $secret['accusation']['reconstruction'],
            'nodes' => array_values(array_map('strval', $secret['deduction_nodes'] ?? [])),
        ];
    }

    /**
     * The media files that exist for this case, as site-relative URLs. A case
     * with none still plays: the pages fall back to initials and plain text.
     *
     * @return array{hero: ?string, portraits: array<string, string>, images: array<string, string>, audio: array<string, string>}
     */
    public function assets(): array
    {
        $base = public_path('express-assets/'.$this->slug());
        $url = '/express-assets/'.$this->slug();

        $find = function (string $relative, array $extensions) use ($base, $url): ?string {
            foreach ($extensions as $extension) {
                if (is_file("{$base}/{$relative}.{$extension}")) {
                    return "{$url}/{$relative}.{$extension}";
                }
            }

            return null;
        };

        $portraits = [];
        foreach ($this->suspects() as $suspect) {
            if ($found = $find('portraits/'.$suspect['id'], ['jpg', 'jpeg', 'png', 'webp'])) {
                $portraits[$suspect['id']] = $found;
            }
        }

        $images = [];
        $audio = [];
        foreach ($this->evidence() as $evidence) {
            $key = strtolower($evidence['id']);

            if (in_array($evidence['type'], ['photo', 'cctv'], true)
                && ($found = $find($key, ['jpg', 'jpeg', 'png', 'webp']))) {
                $images[$evidence['id']] = $found;
            }

            if ($evidence['type'] === 'audio' && ($found = $find('audio/'.$key, ['mp3', 'm4a', 'ogg', 'wav']))) {
                $audio[$evidence['id']] = $found;
            }
        }

        return [
            'hero' => $find('hero', ['jpg', 'jpeg', 'png', 'webp']),
            'portraits' => $portraits,
            'images' => $images,
            'audio' => $audio,
        ];
    }

    /**
     * "10:58 | A. 11:03 | B." -> ["10:58 | A.", "11:03 | B."]: a new line
     * wherever a sentence ends and the next entry starts with a time (or a
     * "#204:" order number).
     *
     * @return array<int, string>
     */
    public static function splitLines(string $text): array
    {
        $lines = preg_split('/(?<=[.»])\s+(?=\d{1,2}:\d{2}|#\d+:)/u', trim($text));

        return array_values(array_filter(array_map('trim', $lines ?: []), fn ($l) => $l !== ''));
    }
}
