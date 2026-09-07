<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RetryAudioTest extends TestCase
{
    use RefreshDatabase;

    private function makeGameWithAudioEvent(): array
    {
        $game = Game::create(['name' => 'Test Game', 'status' => 'running']);

        $game->players()->create([
            'name' => 'Player One',
            'email' => 'player@example.com',
            'access_token' => 'test-token-1',
        ]);

        $event = $game->timelineEvents()->create([
            'type' => 'audio_email',
            'trigger_offset_minutes' => 5,
            'title' => 'Audio event',
            'audio_script' => 'Hola, esto es una prueba.',
            'delivery_mode' => 'all',
            'sent_at' => now(),
        ]);

        return [$game, $event];
    }

    public function test_retry_audio_generates_audio_and_resends_mail(): void
    {
        Storage::fake('local');
        Mail::fake();
        Http::fake([
            '*' => Http::response('fake-wav-bytes', 200),
        ]);

        $this->withSession(['immersion_gm_ok' => true]);
        [$game, $event] = $this->makeGameWithAudioEvent();

        $response = $this->postJson(route('immersion.gm.game.event.retry-audio', [$game, $event]));

        $response->assertOk()->assertJson(['status' => 'ok']);
        Storage::disk('local')->assertExists("audio/{$event->id}.wav");
        Mail::assertSent(CaseTimelineMail::class);
    }

    public function test_retry_audio_reports_error_when_service_unavailable(): void
    {
        Storage::fake('local');
        Mail::fake();
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $this->withSession(['immersion_gm_ok' => true]);
        [$game, $event] = $this->makeGameWithAudioEvent();

        $response = $this->postJson(route('immersion.gm.game.event.retry-audio', [$game, $event]));

        $response->assertOk()->assertJson(['status' => 'error']);
        Mail::assertNothingSent();
    }

    public function test_retry_audio_rejects_non_audio_events(): void
    {
        $this->withSession(['immersion_gm_ok' => true]);
        $game = Game::create(['name' => 'Test Game', 'status' => 'running']);
        $event = $game->timelineEvents()->create([
            'type' => 'email',
            'trigger_offset_minutes' => 5,
            'title' => 'Text event',
            'delivery_mode' => 'all',
            'sent_at' => now(),
        ]);

        $this->postJson(route('immersion.gm.game.event.retry-audio', [$game, $event]))
            ->assertStatus(400);
    }

    public function test_retry_audio_without_gm_session_returns_401_json(): void
    {
        [$game, $event] = $this->makeGameWithAudioEvent();

        $this->postJson(route('immersion.gm.game.event.retry-audio', [$game, $event]))
            ->assertStatus(401)
            ->assertJsonStructure(['message', 'redirect']);
    }
}
