<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Support\MailBudget;
use App\Modules\Platform\Actions\FirstGameOffer;
use App\Modules\Platform\Mail\FirstGameOfferMail;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Support\MarketingConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The discount mail that follows a person's first closed game.
 *
 * Promotional, so most of these tests are about who must NOT get it.
 */
class FirstGameOfferTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'platform.first_game_offer.enabled' => true,
            'platform.first_game_offer.delay_days' => 5,
            'platform.first_game_offer.discount_percent' => 50,
            'platform.first_game_offer.valid_days' => 30,
            'platform.first_game_offer.batch_size' => 20,
            'immersion.mail.daily_limit' => 0,
        ]);
    }

    /** A user who authorised offers and closed a game $daysAgo days ago. */
    private function dueUser(int $daysAgo = 6, array $attributes = []): User
    {
        $user = User::factory()->marketingConsent()->create($attributes);

        $this->closedGame($user, $daysAgo);

        return $user;
    }

    private function closedGame(User $user, int $daysAgo): Game
    {
        return $this->gameOwnedBy($user, [
            'status' => 'finished',
            'finished_at' => now()->subDays($daysAgo),
        ])[0];
    }

    public function test_it_sends_the_offer_with_a_private_one_use_code(): void
    {
        Mail::fake();
        $user = $this->dueUser();

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        Mail::assertSent(FirstGameOfferMail::class, fn (FirstGameOfferMail $mail) => $mail->hasTo($user->email)
            && $mail->percent === 50
            && str_starts_with($mail->code, 'PRIMERACOMPRA-'));

        $promo = PromoCode::query()->where('assigned_user_id', $user->id)->sole();
        $this->assertMatchesRegularExpression('/^PRIMERACOMPRA-[A-Z2-9]{6}$/', $promo->code);
        $this->assertSame(PromoCode::DISCOUNT_PERCENT, $promo->discount_type);
        $this->assertSame(50, $promo->discount_value);
        $this->assertSame(PromoCode::APPLIES_CASE, $promo->applies_to);
        $this->assertSame(1, $promo->max_redemptions);
        $this->assertSame(1, $promo->max_redemptions_per_user);
        $this->assertTrue($promo->active);
        $this->assertTrue($promo->expires_at->isBetween(now()->addDays(29), now()->addDays(31)));
        $this->assertNotNull($user->fresh()->first_game_offer_sent_at);
    }

    public function test_the_mail_carries_the_code_the_way_out_and_the_unsubscribe_header(): void
    {
        $user = $this->dueUser();

        $mail = new FirstGameOfferMail(
            name: $user->name,
            code: 'PRIMERACOMPRA-ABC234',
            percent: 50,
            expiresAt: now()->addDays(30),
            catalogUrl: route('cases.index'),
            unsubscribeUrl: MarketingConsent::unsubscribeUrl($user),
        );

        $html = $mail->render();

        $this->assertStringContainsString('PRIMERACOMPRA-ABC234', $html);
        $this->assertStringContainsString('50%', $html);
        $this->assertStringContainsString(route('cases.index'), $html);
        $this->assertStringContainsString('/baja-ofertas/'.$user->id, $html);
    }

    public function test_it_waits_the_configured_days_after_the_game_was_closed(): void
    {
        Mail::fake();
        $this->dueUser(daysAgo: 2);
        $this->dueUser(daysAgo: 4);

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_a_game_that_is_not_closed_does_not_trigger_it(): void
    {
        Mail::fake();
        $user = User::factory()->marketingConsent()->create();
        $this->gameOwnedBy($user, ['status' => 'running', 'finished_at' => null]);

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_it_is_sent_once_per_person_however_many_games_they_close(): void
    {
        Mail::fake();
        $user = $this->dueUser(daysAgo: 8);
        $this->closedGame($user, 6);

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);
        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        Mail::assertSent(FirstGameOfferMail::class, 1);
        $this->assertSame(1, PromoCode::query()->where('assigned_user_id', $user->id)->count());
    }

    /**
     * @dataProvider peopleWhoMustNotGetIt
     */
    public function test_it_is_not_sent_to(string $case): void
    {
        Mail::fake();
        $user = $this->dueUser();

        match ($case) {
            'someone who never authorised offers' => $user->forceFill(['marketing_consent_at' => null])->save(),
            'someone who unsubscribed' => MarketingConsent::revoke($user),
            'someone whose address is unconfirmed' => $user->forceFill(['email_verified_at' => null])->save(),
            'an administrator' => $user->forceFill(['is_admin' => true])->save(),
        };

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertSame(0, PromoCode::query()->count());
    }

    public static function peopleWhoMustNotGetIt(): array
    {
        return [
            ['someone who never authorised offers'],
            ['someone who unsubscribed'],
            ['someone whose address is unconfirmed'],
            ['an administrator'],
        ];
    }

    public function test_a_game_without_an_owner_account_sends_nothing(): void
    {
        Mail::fake();
        $this->gameOwnedBy(User::factory()->marketingConsent()->create(), [
            'user_id' => null,
            'status' => 'finished',
            'finished_at' => now()->subDays(9),
        ]);

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_a_failed_send_leaves_no_code_and_no_mark_so_it_is_retried(): void
    {
        $user = $this->dueUser();

        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('smtp down'));

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        $this->assertSame(0, PromoCode::query()->count());
        $this->assertNull($user->fresh()->first_game_offer_sent_at);
        $this->assertCount(1, app(FirstGameOffer::class)->eligible(10));
    }

    public function test_it_respects_the_batch_size(): void
    {
        Mail::fake();
        config(['platform.first_game_offer.batch_size' => 2]);
        foreach (range(1, 3) as $_) {
            $this->dueUser();
        }

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        Mail::assertSent(FirstGameOfferMail::class, 2);

        // The third is served by the next run.
        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);
        Mail::assertSent(FirstGameOfferMail::class, 3);
    }

    public function test_it_stops_when_the_mailbox_allowance_for_optional_mail_is_spent(): void
    {
        Mail::fake();
        config(['immersion.mail.daily_limit' => 10, 'immersion.mail.reserve_share' => 0.8]);
        for ($i = 0; $i < 8; $i++) {
            MailBudget::record();
        }
        $this->dueUser();

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_the_switch_must_be_on_to_send_but_not_to_look(): void
    {
        Mail::fake();
        config(['platform.first_game_offer.enabled' => false]);
        $user = $this->dueUser();

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);
        Mail::assertNothingSent();

        $this->artisan('platform:send-first-game-offers --dry-run')
            ->expectsOutputToContain("#{$user->id}")
            ->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertNull($user->fresh()->first_game_offer_sent_at);
    }

    public function test_the_panel_switch_wins_over_the_env_default_in_both_directions(): void
    {
        Mail::fake();
        $admin = User::factory()->create();
        $this->dueUser();

        // .env says on, an administrator turned it off: nothing is sent.
        config(['platform.first_game_offer.enabled' => true]);
        app(FirstGameOffer::class)->setEnabled(false, $admin->id);
        $this->assertFalse(FirstGameOffer::enabled());
        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);
        Mail::assertNothingSent();

        // .env says off, an administrator turned it on: it sends.
        config(['platform.first_game_offer.enabled' => false]);
        app(FirstGameOffer::class)->setEnabled(true, $admin->id);
        $this->assertTrue(FirstGameOffer::enabled());
        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);
        Mail::assertSent(FirstGameOfferMail::class, 1);
    }

    public function test_with_no_choice_made_the_env_default_decides(): void
    {
        config(['platform.first_game_offer.enabled' => false]);
        $this->assertFalse(FirstGameOffer::enabled());

        config(['platform.first_game_offer.enabled' => true]);
        $this->assertTrue(FirstGameOffer::enabled());
    }

    public function test_the_next_run_is_always_a_future_run_day_at_ten_forty(): void
    {
        foreach (['2026-12-30 09:00', '2026-12-30 11:00', '2026-12-31 23:50', '2027-01-01 10:39'] as $now) {
            $this->travelTo(\Illuminate\Support\Carbon::parse($now));

            $next = \App\Modules\Platform\Console\Commands\SendFirstGameOffers::nextRunAt();

            $this->assertTrue($next->isFuture(), "{$now}: next run is not in the future");
            $this->assertSame('10:40', $next->format('H:i'));
            $this->assertTrue(\App\Modules\Platform\Console\Commands\SendFirstGameOffers::runsOn($next), "{$now}: {$next} is not a run day");
            $this->assertLessThanOrEqual(2, now()->startOfDay()->diffInDays($next->copy()->startOfDay()));
        }
    }

    public function test_the_code_is_private_to_its_owner(): void
    {
        Mail::fake();
        $owner = $this->dueUser();
        $stranger = User::factory()->create();

        $this->artisan('platform:send-first-game-offers')->assertExitCode(0);

        $promo = PromoCode::query()->sole();
        $this->assertSame($owner->id, (int) $promo->assigned_user_id);
        $this->assertNotSame($stranger->id, (int) $promo->assigned_user_id);
    }
}
