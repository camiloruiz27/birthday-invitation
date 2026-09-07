<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A case manifest points at files it does not itself contain: portraits and
 * evidence scans under public/, testimonies and envelopes under content/.
 *
 * Nothing else notices when one of those is missing — the page renders, the
 * email sends, and the player just sees a broken image or a blank envelope. So
 * this walks every reference in every installed case and checks the file is
 * really there.
 */
class CaseAssetsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, CaseDefinition>
     */
    private function cases(): array
    {
        return app(CaseRegistry::class)->all();
    }

    public function test_every_suspect_portrait_exists(): void
    {
        $missing = [];

        foreach ($this->cases() as $case) {
            foreach ($case->suspects() as $slug => $suspect) {
                $path = public_path(ltrim($suspect['photo_url'], '/'));

                if (! is_file($path)) {
                    $missing[] = "{$case->slug}/{$slug} → {$suspect['photo_url']}";
                }
            }
        }

        $this->assertSame([], $missing, "Missing portraits:\n".implode("\n", $missing));
    }

    public function test_every_victim_portrait_exists(): void
    {
        $missing = [];

        foreach ($this->cases() as $case) {
            $url = $case->victim()['photo_url'];

            if ($url && ! is_file(public_path(ltrim($url, '/')))) {
                $missing[] = "{$case->slug} → {$url}";
            }
        }

        $this->assertSame([], $missing, "Missing victim portraits:\n".implode("\n", $missing));
    }

    public function test_every_gallery_image_exists(): void
    {
        $missing = [];

        foreach ($this->cases() as $case) {
            foreach ($case->timeline() as $event) {
                foreach ($case->galleryFor($event['source_file'] ?? null) as $image) {
                    if (! is_file(public_path(ltrim($image['url'], '/')))) {
                        $missing[] = "{$case->slug} → {$image['url']}";
                    }
                }
            }
        }

        $this->assertSame([], array_unique($missing), "Missing gallery images:\n".implode("\n", array_unique($missing)));
    }

    public function test_every_suspect_testimony_file_exists_and_has_content(): void
    {
        $problems = [];

        foreach ($this->cases() as $case) {
            foreach ($case->suspects() as $slug => $suspect) {
                if (! $case->content()->exists($suspect['file'])) {
                    $problems[] = "{$case->slug}/{$slug} → missing {$suspect['file']}";

                    continue;
                }

                // An empty testimony is what the AI would be handed, so it is
                // as broken as a missing file.
                if (trim($case->content()->raw($suspect['file'])) === '') {
                    $problems[] = "{$case->slug}/{$slug} → {$suspect['file']} is empty";
                }
            }
        }

        $this->assertSame([], $problems, "Testimony problems:\n".implode("\n", $problems));
    }

    public function test_every_timeline_event_has_something_to_deliver(): void
    {
        $problems = [];

        foreach ($this->cases() as $case) {
            foreach ($case->timeline() as $index => $event) {
                $label = "{$case->slug} event #{$index} \"{$event['title']}\"";

                if (! empty($event['source_file'])) {
                    if (! $case->content()->exists($event['source_file'])) {
                        $problems[] = "{$label} → missing {$event['source_file']}";
                    } elseif (trim($case->content()->raw($event['source_file'])) === '') {
                        $problems[] = "{$label} → {$event['source_file']} is empty";
                    }

                    continue;
                }

                // No source file: it must carry its own body or audio script,
                // otherwise it delivers an empty email.
                if (empty($event['body_markdown']) && empty($event['audio_script'])) {
                    $problems[] = "{$label} → nothing to send";
                }
            }
        }

        $this->assertSame([], $problems, "Timeline problems:\n".implode("\n", $problems));
    }

    public function test_audio_events_carry_a_script_and_others_do_not(): void
    {
        $problems = [];

        foreach ($this->cases() as $case) {
            foreach ($case->timeline() as $event) {
                $isAudio = ($event['type'] ?? null) === 'audio_email';
                $hasScript = ! empty($event['audio_script']);

                if ($isAudio && ! $hasScript) {
                    $problems[] = "{$case->slug} → \"{$event['title']}\" is audio with no script";
                }

                if (! $isAudio && $hasScript) {
                    $problems[] = "{$case->slug} → \"{$event['title']}\" has a script but is not an audio event";
                }
            }
        }

        $this->assertSame([], $problems, implode("\n", $problems));
    }

    public function test_the_timeline_email_renders_for_every_event_of_every_case(): void
    {
        foreach ($this->cases() as $case) {
            $game = Game::create([
                'name' => "Render {$case->slug}",
                'case_slug' => $case->slug,
                'status' => 'running',
            ]);

            $player = $game->players()->create([
                'name' => 'Ana',
                'email' => 'ana@example.com',
                'access_token' => "render-{$case->slug}",
            ]);

            foreach ($case->timeline() as $index => $definition) {
                $event = $game->timelineEvents()->create($definition + ['sent_at' => now()]);

                // Rendering is where a missing gallery file or a broken Blade
                // reference would actually blow up, and it would do so inside
                // a queued job where nobody sees it.
                $html = (new CaseTimelineMail($event, $player))->render();

                $this->assertNotSame('', trim($html), "Empty email for {$case->slug} event #{$index}");

                // Compared escaped: titles carry quotes (Canal "Torre Blanca")
                // and Blade rightly turns them into entities.
                $this->assertStringContainsString(e($event->title), $html);
            }
        }
    }
}
