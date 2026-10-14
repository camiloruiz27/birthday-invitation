<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Ai\Contracts\SpeechProvider;
use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Jobs\SendPlayerAccessLink;
use App\Modules\Immersion\Jobs\SendPlayerAccessLinks;
use App\Modules\Immersion\Jobs\SendTimelineMail;
use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Mail\PlayerAccessLinkMail;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\MailBudget;
use App\Modules\Immersion\Support\MailFailures;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Hostinger lets one mailbox send a fixed number of messages per rolling 24
 * hours, and a table of three playing a case is thirty-odd of them. These cover
 * what keeps that from being discovered by a player's envelope bouncing: one
 * retryable job per recipient, a daily allowance that holds back what can wait,
 * a notice for the Game Master when an email gives up, and lighter messages.
 */
class MailPipelineTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const CASE_SLUG = 'habitacion-314';

    /** @return array{0: Game, 1: TimelineEvent, 2: Player} */
    private function makeTable(array $gameAttributes = []): array
    {
        $game = Game::create($gameAttributes + [
            'name' => 'Mesa',
            'case_slug' => self::CASE_SLUG,
            'status' => 'running',
            'started_at' => now()->subHour(),
            'paused_seconds_total' => 0,
        ]);

        $game->players()->create(['name' => 'Ana', 'email' => 'ana@example.com', 'access_token' => 'tok-ana']);
        $game->players()->create(['name' => 'Beto', 'email' => 'beto@example.com', 'access_token' => 'tok-beto']);

        $event = $game->timelineEvents()->create([
            'type' => 'email',
            'trigger_offset_minutes' => 0,
            'title' => 'Sobre 1',
            'body_markdown' => 'Contenido',
        ]);

        return [$game, $event, $game->players()->first()];
    }

    private function stubQueueJob(?int $releasedFor = null): Job
    {
        $job = \Mockery::mock(Job::class)->shouldIgnoreMissing();

        if ($releasedFor !== null) {
            $job->shouldReceive('release')->once()->with($releasedFor);
        } else {
            $job->shouldNotReceive('release');
        }

        return $job;
    }

    public function test_an_event_queues_one_mail_job_per_recipient_on_the_mail_queue(): void
    {
        Bus::fake([SendTimelineMail::class]);
        [, $event] = $this->makeTable();

        (new DispatchTimelineEvent($event->id))->handle(app(SpeechProvider::class));

        Bus::assertDispatchedTimes(SendTimelineMail::class, 2);
        Bus::assertDispatched(SendTimelineMail::class, fn (SendTimelineMail $job) => $job->queue === 'emails');
    }

    public function test_the_mail_job_sends_the_envelope_and_it_counts_against_the_day(): void
    {
        config(['immersion.mail.daily_limit' => 100]);
        [$game, $event, $player] = $this->makeTable();

        (new SendTimelineMail($event->id, $player->id))->handle();

        $this->assertSame(1, MailBudget::used());
        $this->assertSame(99, MailBudget::remaining());
    }

    public function test_every_message_counts_not_only_the_games(): void
    {
        // Account mail (verification, receipts) leaves through the same mailbox.
        Mail::raw('hola', fn ($message) => $message->to('alguien@example.com'));

        $this->assertSame(1, MailBudget::used());
    }

    public function test_a_spent_allowance_holds_the_envelope_back_instead_of_sending_into_a_refusal(): void
    {
        Mail::fake();
        config(['immersion.mail.daily_limit' => 2]);
        MailBudget::record();
        MailBudget::record();
        [, $event, $player] = $this->makeTable();

        $job = new SendTimelineMail($event->id, $player->id);
        $job->setJob($this->stubQueueJob(releasedFor: 900));
        $job->handle();

        Mail::assertNothingSent();
    }

    public function test_access_links_and_epilogues_stop_at_the_reserve_so_envelopes_keep_room(): void
    {
        config(['immersion.mail.daily_limit' => 10, 'immersion.mail.reserve_share' => 0.8]);

        foreach (range(1, 8) as $i) {
            MailBudget::record();
        }

        $this->assertTrue(MailBudget::canSend(MailBudget::ENVELOPE));
        $this->assertFalse(MailBudget::canSend(MailBudget::OPTIONAL));
    }

    public function test_a_limit_of_zero_turns_the_guard_off(): void
    {
        config(['immersion.mail.daily_limit' => 0]);
        MailBudget::record();

        $this->assertTrue(MailBudget::canSend(MailBudget::OPTIONAL));
        $this->assertNull(MailBudget::remaining());
    }

    public function test_the_low_allowance_warning_is_logged_once_an_hour(): void
    {
        \Illuminate\Support\Facades\Log::spy();
        config(['immersion.mail.daily_limit' => 10]);

        foreach (range(1, 12) as $i) {
            MailBudget::record();
        }

        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')
            ->with('mail_daily_budget_low', \Mockery::any())
            ->once();
    }

    public function test_a_mail_that_gives_up_is_reported_to_the_game_master_and_can_be_sent_again(): void
    {
        [$game, $event, $player] = $this->makeTable();

        (new SendTimelineMail($event->id, $player->id))->failed(new \RuntimeException('smtp down'));

        $this->assertCount(1, MailFailures::all($game->id));

        $user = $this->gameMaster(self::CASE_SLUG);
        $game->update(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('immersion.gm.game.show', $game))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('mail.failures', 1)
                ->where('mail.failures.0.label', 'Ana: Sobre 1')
                ->has('mail.budget.used'));

        Bus::fake([SendTimelineMail::class]);

        $this->actingAs($user)
            ->post(route('immersion.gm.game.resend-mail', $game))
            ->assertRedirect();

        Bus::assertDispatched(SendTimelineMail::class, fn (SendTimelineMail $job) => $job->eventId === $event->id
            && $job->playerId === $player->id);
        $this->assertSame([], MailFailures::all($game->id));
    }

    public function test_a_delivered_resend_clears_the_notice(): void
    {
        [$game, $event, $player] = $this->makeTable();
        MailFailures::add($game->id, $event->id, $player->id, 'Ana: Sobre 1');

        (new SendTimelineMail($event->id, $player->id))->handle();

        $this->assertSame([], MailFailures::all($game->id));
    }

    public function test_only_the_owner_can_resend(): void
    {
        [$game] = $this->makeTable();
        $stranger = $this->gameMaster(self::CASE_SLUG);

        $this->actingAs($stranger)
            ->post(route('immersion.gm.game.resend-mail', $game))
            ->assertForbidden();
    }

    public function test_the_mail_limiter_spreads_a_burst_and_can_be_switched_off(): void
    {
        config(['immersion.mail.per_minute' => 7]);
        $this->assertSame(7, RateLimiter::limiter('immersion-mail')()->maxAttempts);

        config(['immersion.mail.per_minute' => 0]);
        $this->assertGreaterThan(1000, RateLimiter::limiter('immersion-mail')()->maxAttempts);
    }

    public function test_the_mail_jobs_are_retried_for_hours_and_use_the_limiter(): void
    {
        $job = new SendTimelineMail(1, 1);

        $this->assertSame(0, $job->tries, 'Being put back by the limiter must not use attempts up.');
        $this->assertGreaterThan(now()->addHour()->getTimestamp(), $job->retryUntil()->getTimestamp());
        $this->assertSame([60, 300, 900], $job->backoff());
        $this->assertCount(1, $job->middleware());
    }

    public function test_access_links_fan_out_to_one_job_per_player(): void
    {
        Bus::fake([SendPlayerAccessLink::class]);
        [$game] = $this->makeTable();

        (new SendPlayerAccessLinks($game->players()->pluck('id')->all()))->handle();

        Bus::assertDispatchedTimes(SendPlayerAccessLink::class, 2);
    }

    public function test_an_access_link_is_sent_while_there_is_room_and_waits_when_there_is_not(): void
    {
        [, , $player] = $this->makeTable();

        Mail::fake();
        (new SendPlayerAccessLink($player->id))->handle();
        Mail::assertSent(PlayerAccessLinkMail::class);

        config(['immersion.mail.daily_limit' => 10]);
        foreach (range(1, 9) as $i) {
            MailBudget::record();
        }

        Mail::fake();
        $job = new SendPlayerAccessLink($player->id);
        $job->setJob($this->stubQueueJob(releasedFor: 1800));
        $job->handle();
        Mail::assertNothingSent();
    }

    public function test_an_audio_that_fits_is_attached(): void
    {
        [, $event, $player] = $this->audioEnvelope(sizeBytes: 2000);
        config(['immersion.mail.attach_audio_max_kb' => 5]);

        $mailable = (new CaseTimelineMail($event, $player))->build();

        $this->assertCount(1, $mailable->rawAttachments);
        $mailable->assertSeeInHtml('Se adjunta una grabación');
    }

    public function test_an_audio_over_the_limit_is_linked_to_the_inbox_instead(): void
    {
        [, $event, $player] = $this->audioEnvelope(sizeBytes: 9000);
        config(['immersion.mail.attach_audio_max_kb' => 5]);

        $mailable = (new CaseTimelineMail($event, $player))->build();

        $this->assertCount(0, $mailable->rawAttachments);
        $mailable->assertSeeInHtml(route('immersion.player.inbox', $player->access_token));
        $mailable->assertDontSeeInHtml('Se adjunta una grabación');
    }

    public function test_a_limit_of_zero_always_attaches(): void
    {
        [, $event, $player] = $this->audioEnvelope(sizeBytes: 9000);
        config(['immersion.mail.attach_audio_max_kb' => 0]);

        $this->assertCount(1, (new CaseTimelineMail($event, $player))->build()->rawAttachments);
    }

    public function test_an_event_still_to_be_voiced_goes_to_the_slow_queue_and_an_ordinary_one_does_not(): void
    {
        Bus::fake([DispatchTimelineEvent::class]);
        [$game, $plain] = $this->makeTable();

        $voiced = $game->timelineEvents()->create([
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio',
            'body_markdown' => 'x',
            'audio_script' => 'Hola',
        ]);

        DispatchTimelineEvent::enqueue($plain);
        DispatchTimelineEvent::enqueue($voiced);

        Bus::assertDispatched(DispatchTimelineEvent::class, fn ($job) => $job->eventId === $plain->id && $job->queue === 'default');
        Bus::assertDispatched(DispatchTimelineEvent::class, fn ($job) => $job->eventId === $voiced->id && $job->queue === 'slow');
    }

    public function test_the_slow_jobs_use_the_slow_queue(): void
    {
        foreach ([
            new \App\Modules\Immersion\Jobs\GradeAccusations(1),
            new \App\Modules\Immersion\Jobs\GenerateEndingAudio(1),
            new \App\Modules\Immersion\Jobs\GenerateEventAudio(1),
            new \App\Modules\Immersion\Jobs\SendEpilogue(1),
        ] as $job) {
            $this->assertSame('slow', $job->queue, get_class($job));
        }
    }

    /** @return array{0: Game, 1: TimelineEvent, 2: Player} */
    private function audioEnvelope(int $sizeBytes): array
    {
        Storage::fake('local');
        Storage::disk('local')->put('tts/event.wav', str_repeat('a', $sizeBytes));

        [$game, , $player] = $this->makeTable();

        $event = $game->timelineEvents()->create([
            'type' => 'audio_email',
            'trigger_offset_minutes' => 1,
            'title' => 'Audio',
            'body_markdown' => 'x',
            'audio_path' => 'tts/event.wav',
            'audio_status' => 'ready',
        ]);

        return [$game, $event, $player];
    }
}
