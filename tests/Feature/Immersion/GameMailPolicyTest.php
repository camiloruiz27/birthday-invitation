<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Ai\Contracts\SpeechProvider;
use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Jobs\GenerateEventAudio;
use App\Modules\Immersion\Jobs\SendPlayerAccessLinks;
use App\Modules\Immersion\Jobs\SendTimelineMail;
use App\Modules\Immersion\Mail\PlayerAccessLinkMail;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\GameMailPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Real tables said the email of every envelope was too much: the game already
 * delivers everything inside the platform. By default only the "accusations are
 * open" email leaves (plus, on demand, the personal access link). Everything
 * else is switched off, not removed — `IMMERSION_MAIL_TIMELINE=all` brings it
 * back.
 *
 * The suite runs in the full mode (see phpunit.xml) so the older tests keep
 * checking what the emails contain; these set the product defaults on purpose.
 */
class GameMailPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['immersion.mail.timeline' => 'unlock', 'immersion.mail.epilogue' => false]);
    }

    /** @return array{0: Game, 1: TimelineEvent, 2: TimelineEvent, 3: TimelineEvent} */
    private function table(): array
    {
        $game = Game::create([
            'name' => 'Mesa',
            'case_slug' => 'habitacion-314',
            'status' => 'running',
            'started_at' => now()->subHour(),
            'paused_seconds_total' => 0,
        ]);
        $game->players()->create(['name' => 'Ana', 'email' => 'ana@example.com', 'access_token' => 'tok-ana']);
        $game->players()->create(['name' => 'Beto', 'email' => 'beto@example.com', 'access_token' => 'tok-beto']);

        $make = fn (string $type, int $minute) => $game->timelineEvents()->create([
            'type' => $type,
            'trigger_offset_minutes' => $minute,
            'title' => "Evento {$type}",
            'body_markdown' => 'x',
            'delivery_mode' => 'all',
        ]);

        return [$game, $make('email', 0), $make('audio_email', 1), $make('unlock', 2)];
    }

    private function fire(TimelineEvent $event): void
    {
        (new DispatchTimelineEvent($event->id))->handle(app(SpeechProvider::class));
    }

    public function test_by_default_an_ordinary_envelope_is_not_emailed_but_is_still_sent(): void
    {
        Bus::fake([SendTimelineMail::class]);
        [, $email, $audio] = $this->table();

        $this->fire($email);
        $this->fire($audio);

        Bus::assertNotDispatched(SendTimelineMail::class);
        // Delivered: it is in the inbox, and it opened what it opens.
        $this->assertTrue($email->fresh()->isSent());
        $this->assertTrue($audio->fresh()->isSent());
    }

    public function test_by_default_the_accusations_open_envelope_is_emailed_to_everyone(): void
    {
        Bus::fake([SendTimelineMail::class]);
        [, , , $unlock] = $this->table();

        $this->fire($unlock);

        Bus::assertDispatchedTimes(SendTimelineMail::class, 2);
        $this->assertTrue($unlock->fresh()->isSent());
    }

    public function test_all_brings_every_envelope_email_back(): void
    {
        config(['immersion.mail.timeline' => 'all']);
        Bus::fake([SendTimelineMail::class]);
        [, $email, $audio, $unlock] = $this->table();

        $this->fire($email);
        $this->fire($audio);
        $this->fire($unlock);

        Bus::assertDispatchedTimes(SendTimelineMail::class, 6);
    }

    public function test_off_sends_none_not_even_the_accusations_one(): void
    {
        config(['immersion.mail.timeline' => 'off']);
        Bus::fake([SendTimelineMail::class]);
        [, , , $unlock] = $this->table();

        $this->fire($unlock);

        Bus::assertNotDispatched(SendTimelineMail::class);
        $this->assertTrue($unlock->fresh()->isSent(), 'Accusations must still open without the email.');
    }

    public function test_a_typo_in_the_setting_falls_back_to_the_quietest_useful_default(): void
    {
        config(['immersion.mail.timeline' => 'tdos']);
        [, $email, , $unlock] = $this->table();

        $this->assertFalse(GameMailPolicy::timelineEvent($email));
        $this->assertTrue(GameMailPolicy::timelineEvent($unlock));
    }

    public function test_the_accusations_email_still_carries_the_button_to_accuse(): void
    {
        [, , , $unlock] = $this->table();

        $this->fire($unlock);

        // The array mailer keeps what it "sent", so the real message can be read.
        $messages = app('mail.manager')->mailer()->getSymfonyTransport()->messages();

        $this->assertCount(2, $messages);
        $this->assertStringContainsString('Hacer mi acusación', $messages[0]->getOriginalMessage()->getHtmlBody());
    }

    public function test_a_retried_audio_is_only_re_emailed_when_that_event_is_emailed(): void
    {
        Bus::fake([SendTimelineMail::class]);
        [, , $audio] = $this->table();
        $audio->update(['sent_at' => now(), 'audio_script' => 'Hola', 'audio_status' => 'failed']);

        $speech = \Mockery::mock(SpeechProvider::class);
        $speech->shouldReceive('synthesize')->andReturn('tts/event.wav');

        (new GenerateEventAudio($audio->id))->handle($speech);

        Bus::assertNotDispatched(SendTimelineMail::class);
        $this->assertSame('tts/event.wav', $audio->fresh()->audio_path, 'The recording is still generated.');

        config(['immersion.mail.timeline' => 'all']);
        $audio->update(['audio_path' => null]);

        (new GenerateEventAudio($audio->id))->handle($speech);

        Bus::assertDispatchedTimes(SendTimelineMail::class, 2);
    }

    public function test_the_personal_access_link_is_always_emailed(): void
    {
        config(['immersion.mail.timeline' => 'off', 'immersion.mail.epilogue' => false]);
        Mail::fake();
        [$game] = $this->table();

        SendPlayerAccessLinks::dispatchSync($game->players()->pluck('id')->all());

        Mail::assertSent(PlayerAccessLinkMail::class, 2);
    }

    public function test_the_policy_reads_the_epilogue_switch(): void
    {
        $this->assertFalse(GameMailPolicy::epilogue());

        config(['immersion.mail.epilogue' => true]);

        $this->assertTrue(GameMailPolicy::epilogue());
    }
}
