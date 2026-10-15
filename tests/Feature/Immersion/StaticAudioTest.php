<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * A recording that ships with the case is a file under public/. Playing it
 * through the token route turned every range request of the browser (a handful
 * per play) into a PHP request with a database connection behind it, for a file
 * the web server can hand over by itself. Generated audio lives in storage and
 * still goes through the route.
 */
class StaticAudioTest extends TestCase
{
    use RefreshDatabase;

    private function table(): Game
    {
        $game = Game::create([
            'name' => 'Mesa',
            'case_slug' => 'habitacion-314',
            'status' => 'running',
            'interrogation_enabled' => true,
            'started_at' => now()->subMinutes(30),
            'paused_seconds_total' => 0,
        ]);
        $game->players()->create(['name' => 'Ana', 'email' => 'ana@example.com', 'access_token' => 'tok-ana']);

        foreach ([
            'immersion/habitacion-314/audio/a1.mp3' => 'Del caso',
            'tts/event-9.wav' => 'Generado',
        ] as $path => $title) {
            $game->timelineEvents()->create([
                'type' => 'audio_email',
                'trigger_offset_minutes' => 1,
                'title' => $title,
                'body_markdown' => 'x',
                'delivery_mode' => 'all',
                'audio_path' => $path,
                'audio_status' => 'ready',
                'sent_at' => now(),
            ]);
        }

        return $game;
    }

    /** @dataProvider modes */
    public function test_a_case_recording_is_linked_directly_and_a_generated_one_is_not(bool $snapshots): void
    {
        config(['immersion.snapshots.enabled' => $snapshots]);
        $this->table();

        $this->get(route('immersion.player.inbox', 'tok-ana'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('items', 2)
                ->where('items.0.audio_url', asset('immersion/habitacion-314/audio/a1.mp3'))
                ->where('items.1.audio_url', null));
    }

    /** @return array<string, array<int, bool>> */
    public static function modes(): array
    {
        return ['from the database' => [false], 'from snapshots' => [true]];
    }

    public function test_the_item_never_exposes_the_storage_path_of_a_generated_recording(): void
    {
        $this->table();

        $json = $this->get(route('immersion.player.inbox', 'tok-ana'))->assertOk()->getContent();

        $this->assertStringNotContainsString('tts/event-9.wav', $json);
    }
}
