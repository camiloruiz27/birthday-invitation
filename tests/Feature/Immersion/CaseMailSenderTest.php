<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Mail\CaseTimelineMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The sender NAME on a case email varies by case (its manifest `authority`);
 * the sender ADDRESS never does — it stays the single authenticated SMTP
 * account no matter which case an email belongs to. Changing only the
 * display name is what keeps this safe for SPF/DKIM: the address the mail
 * server actually authenticates against never moves.
 */
class CaseMailSenderTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_a_timeline_email_uses_the_configured_address_with_the_case_authority_as_the_name(): void
    {
        config([
            'mail.from.address' => 'hello@example.com',
            // Deliberately generic and different from the case's own
            // authority, so a passing assertion proves the case actually
            // drove the name rather than this default leaking through.
            'mail.from.name' => 'Nombre Generico De La App',
        ]);

        $user = $this->gameMaster('steve-jacobs');
        [$game, $player] = $this->gameOwnedBy($user, ['case_slug' => 'steve-jacobs']);

        $event = $game->timelineEvents()->create([
            'type' => 'email',
            'title' => 'Sobre 1 - Expediente del caso',
            'source_file' => null,
            'trigger_offset_minutes' => 0,
            'sent_at' => now(),
        ]);

        $mail = (new CaseTimelineMail($event, $player))->build();

        $this->assertSame('hello@example.com', $mail->from[0]['address']);
        $this->assertSame($game->caseDefinition()->authority(), $mail->from[0]['name']);

        // The fixture's real value, so a rename of the manifest's authority
        // would be caught here rather than the assertion silently chasing it.
        $this->assertSame('Departamento de Policía de San Francisco', $mail->from[0]['name']);
        $this->assertNotSame('Nombre Generico De La App', $mail->from[0]['name']);
    }
}
