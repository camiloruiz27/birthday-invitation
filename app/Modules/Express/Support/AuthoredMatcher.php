<?php

namespace App\Modules\Express\Support;

use Illuminate\Support\Str;

/**
 * Matches a free-text question to a topic by the words it shares with the
 * topic's authored paraphrases. No AI, no cost, same answer every time.
 *
 * Both sides are reduced to their content words: lower-cased, accents and
 * punctuation gone, filler and question words dropped, and every word cut to
 * its first five letters so "reinició", "reiniciaste" and "reinicio" are the
 * same word. A topic scores by how much of its best paraphrase the question
 * covers. When two topics tie, or none covers enough, the answer is "not
 * clear": the player is asked to rephrase and nothing is spent.
 */
final class AuthoredMatcher implements QuestionMatcher
{
    /** Share of a paraphrase's content words a question must contain. */
    private const THRESHOLD = 0.5;

    /** Words that carry no topic (already without accents). */
    private const STOPWORDS = [
        'a', 'al', 'algo', 'ante', 'con', 'contra', 'cual', 'cuales', 'cuando', 'cuanto', 'cuantos',
        'de', 'del', 'desde', 'donde', 'e', 'el', 'ella', 'ellos', 'en', 'entre', 'era', 'eres', 'es',
        'esa', 'ese', 'eso', 'esta', 'este', 'esto', 'fue', 'fui', 'fuiste', 'ha', 'han', 'has', 'hay',
        'la', 'las', 'le', 'les', 'lo', 'los', 'me', 'mi', 'mis', 'mas', 'muy', 'nada', 'ni', 'no', 'nos',
        'o', 'para', 'pero', 'por', 'porque', 'que', 'quien', 'quienes', 'se', 'ser', 'si', 'sin', 'sobre',
        'su', 'sus', 'te', 'tu', 'tus', 'un', 'una', 'uno', 'unas', 'unos', 'y', 'ya', 'yo', 'como',
    ];

    public function match(string $question, array $topics): ?string
    {
        $words = $this->words($question);

        if ($words === []) {
            return null;
        }

        $scores = [];

        foreach ($topics as $topic) {
            $best = 0.0;

            foreach ($topic['paraphrases'] as $paraphrase) {
                $target = $this->words($paraphrase);

                if ($target === []) {
                    continue;
                }

                $shared = count(array_intersect($target, $words));

                // Coverage first; among equally covered paraphrases the one
                // that shares MORE words is the more specific match ("guardaste
                // la grabación" covers two words of one topic and the single
                // word of another: both fully, but only one is what was asked).
                $best = max($best, $shared / count($target) + $shared / 100);
            }

            $scores[$topic['id']] = $best;
        }

        arsort($scores);
        $ranked = array_keys($scores);
        $top = $scores[$ranked[0]] ?? 0.0;

        // The score above carries a small specificity bonus; the threshold is
        // about coverage alone.
        if ($top < self::THRESHOLD) {
            return null;
        }

        // Two topics equally covered: better to ask again than to guess and
        // spend one of the team's three questions on the wrong answer.
        if (isset($ranked[1]) && abs($scores[$ranked[1]] - $top) < 0.0001) {
            return null;
        }

        return $ranked[0];
    }

    /** @return array<int, string> distinct stems */
    private function words(string $text): array
    {
        $ascii = Str::lower(Str::ascii($text));
        $clean = preg_replace('/[^a-z0-9]+/', ' ', $ascii) ?? '';
        $stems = [];

        foreach (explode(' ', trim($clean)) as $word) {
            if ($word === '' || in_array($word, self::STOPWORDS, true)) {
                continue;
            }

            // Numbers are content ("20:17"); one-letter leftovers are not.
            if (strlen($word) < 2) {
                continue;
            }

            $stems[] = substr($word, 0, 5);
        }

        return array_values(array_unique($stems));
    }
}
