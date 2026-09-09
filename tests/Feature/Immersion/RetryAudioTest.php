<?php

namespace Tests\Feature\Immersion;

use App\Models\User;
use App\Modules\Immersion\Jobs\GenerateEventAudio;
use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\TimelineEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Audio generation was moved off the web request: the endpoint enqueues and
 * answers immediately, and the job does the slow part.
 */
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

    public function test_retrying_queues_the_work_instead_of_doing_it_inline(): void
    {
        Queue::fake();

        $this->actingAs($this->owner);
        [$game, $event] = $this->makeGameWithAudioEvent();

        $this->postJson(route('immersion.gm.game.event.retry-audio', [$game, $event]))
            ->assertOk()
            ->assertJson(['status' => 'pending']);

        Queue::assertPushed(
            GenerateEventAudio::class,
            fn (GenerateEventAudio $job) => $job->eventId === $event->id
        );

        // The console needs to be able to tell "working on it" from "failed".
        $this->assertSame(TimelineEvent::AUDIO_PENDING, $event->fresh()->audio_status);
    }

    public function test_the_job_generates_the_audio_and_resends_the_mail(): void
    {
        Storage::fake('local');
        Mail::fake();
        Http::fake(['*' => Http::response('fake-wav-bytes', 200)]);

        [, $event] = $this->makeGameWithAudioEvent();

        (new GenerateEventAudio($event->id))->handle(app(\App\Modules\Immersion\Ai\Contracts\SpeechProvider::class));

        // Namespaced by kind: not every recording belongs to a timeline event.
        Storage::disk('local')->assertExists("audio/event-{$event->id}.wav");
        Mail::assertSent(CaseTimelineMail::class);

        $this->assertSame(TimelineEvent::AUDIO_READY, $event->fresh()->audio_status);
    }

    public function test_the_job_records_a_failure_without_sending_mail(): void
    {
        Storage::fake('local');
        Mail::fake();
        Http::fake(['*' => Http::response(null, 500)]);

        [, $event] = $this->makeGameWithAudioEvent();

        (new GenerateEventAudio($event->id))->handle(app(\App\Modules\Immersion\Ai\Contracts\SpeechProvider::class));

        Mail::assertNothingSent();
        $this->assertSame(TimelineEvent::AUDIO_FAILED, $event->fresh()->audio_status);
        $this->assertNull($event->fresh()->audio_path);
    }

    public function test_retrying_an_event_that_already_has_audio_does_no_work(): void
    {
        Storage::fake('local');
        Queue::fake();

        $this->actingAs($this->owner);
        [$game, $event] = $this->makeGameWithAudioEvent();

        Storage::disk('local')->put("audio/{$event->id}.wav", 'bytes');
        $event->update(['audio_path' => "audio/{$event->id}.wav"]);

        $this->postJson(route('immersion.gm.game.event.retry-audio', [$game, $event]))
            ->assertOk()
            ->assertJson(['status' => 'ready']);

        Queue::assertNothingPushed();
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
