<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Mail\CronDiagnosticMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * This mail exists to prove a round trip end to end AND to let a person look
 * at the platform's own email design — the second half only a human opening
 * an inbox can actually check. These tests cover the half a computer can:
 * the template renders without error, and the command reaches the right
 * inbox.
 */
class CronDiagnosticMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_template_renders_without_error(): void
    {
        $html = (new CronDiagnosticMail(7))->render();

        $this->assertStringContainsString('MisterioCode', $html);
        $this->assertStringContainsString('Latido del scheduler', $html);
        $this->assertStringContainsString('#7', $html);
    }

    public function test_it_sends_to_the_configured_recipient_by_default(): void
    {
        Mail::fake();
        config(['platform.cron_diagnostic.email' => 'destino@example.com']);

        $this->artisan('platform:send-cron-diagnostic')->assertExitCode(0);

        Mail::assertSent(CronDiagnosticMail::class, fn ($mail) => $mail->hasTo('destino@example.com'));
    }

    public function test_the_to_option_overrides_the_configured_recipient(): void
    {
        Mail::fake();

        $this->artisan('platform:send-cron-diagnostic', ['--to' => 'otro@example.com'])
            ->assertExitCode(0);

        Mail::assertSent(CronDiagnosticMail::class, fn ($mail) => $mail->hasTo('otro@example.com'));
    }

    public function test_it_fails_without_any_recipient(): void
    {
        Mail::fake();
        config(['platform.cron_diagnostic.email' => null]);

        $this->artisan('platform:send-cron-diagnostic')->assertExitCode(1);

        Mail::assertNothingSent();
    }

    /**
     * The whole feature is opt-in. A default of true here would mean every
     * fresh environment — including a developer's first `php artisan serve`
     * — starts mailing someone every 7 minutes.
     */
    public function test_the_diagnostic_schedule_is_off_by_default(): void
    {
        $this->assertFalse(config('platform.cron_diagnostic.enabled'));
    }
}
