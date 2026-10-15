<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Cases\CaseRegistry;
use Tests\TestCase;

/**
 * People do not all have the same number of questions: a witness who can
 * confirm two facts needs fewer than a suspect with a full story. The copy
 * that tells a player "how many questions each person gets" therefore speaks
 * of a range, and is worked out from the stages themselves.
 */
class QuestionRangeTest extends TestCase
{
    private function fixture(string $slug)
    {
        return (new CaseRegistry(base_path('tests/Fixtures/cases')))->get($slug);
    }

    public function test_the_range_spans_the_ordinary_questions_of_everyone_who_can_be_questioned(): void
    {
        // la-culpable: 3 now + 2 after V9 (the confrontation round does not
        // count); el-inocente falls back to the case default of 2; el-ausente
        // cannot be questioned at all.
        $this->assertSame(['min' => 2, 'max' => 5], $this->fixture('caso-calificado')->baseQuestionRange());
    }

    public function test_when_everyone_has_the_same_number_there_is_no_range(): void
    {
        $case = $this->fixture('caso-resuelto');

        $this->assertSame(['min' => 2, 'max' => 2], $case->baseQuestionRange());
        $this->assertSame('2 preguntas', $case->questionsPerPersonLabel());
    }

    public function test_the_label_names_the_range_when_there_is_one(): void
    {
        $this->assertSame('entre 2 y 5 preguntas', $this->fixture('caso-calificado')->questionsPerPersonLabel());
    }

    public function test_every_installed_case_stays_within_three_to_five(): void
    {
        foreach ((new CaseRegistry)->all() as $case) {
            $range = $case->baseQuestionRange();

            if ($range['max'] === 0) {
                continue; // a case nobody can question
            }

            $this->assertGreaterThanOrEqual(3, $range['min'], "{$case->slug} has someone with fewer than 3 questions.");
            $this->assertLessThanOrEqual(5, $range['max'], "{$case->slug} has someone with more than 5 questions.");
        }
    }
}
