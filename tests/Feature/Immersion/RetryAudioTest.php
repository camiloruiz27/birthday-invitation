<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class RetryAudioTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->gameMaster();
    }

    private function makeGameWithAudioEvent(): array
    {
        [$game] = $this->gameOwnedBy($this->owner, ['status' => 'running']);

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

        $this->actingAs($this->owner);
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

        $this->actingAs($this->owner);
        [$game, $event] = $this->makeGameWithAudioEvent();

        $response = $this->postJson(route('immersion.gm.game.event.retry-audio', [$game, $event]));

        $response->assertOk()->assertJson(['status' => 'error']);
        Mail::assertNothingSent();
    }

    public function test_retry_audio_rejects_non_audio_events(): void
    {
        $this->actingAs($this->owner);
        [$game] = $this->gameOwnedBy($this->owner, ['status' => 'running']);

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

    public function test_retry_audio_requires_authentication(): void
    {
        [$game, $event] = $this->makeGameWithAudioEvent();

        $this->postJson(route('immersion.gm.game.event.retry-audio', [$game, $event]))
            ->assertStatus(401);
    }

    public function test_retry_audio_is_denied_to_another_game_master(): void
    {
        [$game, $event] = $this->makeGameWithAudioEvent();

        $intruder = $this->gameMaster(attributes: ['email' => 'intruder@example.com']);

        // Owning a case is not owning a game: the intruder has the same
        // entitlement but not this run.
        $this->actingAs($intruder)
            ->postJson(route('immersion.gm.game.event.retry-audio', [$game, $event]))
            ->assertForbidden();
    }
}
