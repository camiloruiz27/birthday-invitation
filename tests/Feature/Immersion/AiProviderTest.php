<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Ai\Contracts\InterrogationProvider;
use App\Modules\Immersion\Ai\Contracts\SpeechProvider;
use App\Modules\Immersion\Ai\GatewayInterrogationProvider;
use App\Modules\Immersion\Ai\GatewaySpeechProvider;
use App\Modules\Immersion\Ai\NullInterrogationProvider;
use App\Modules\Immersion\Ai\NullSpeechProvider;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The AI capabilities sit behind contracts with two real implementations, so
 * "the experience without AI" is something the app can actually do rather than
 * a claim on a marketing page.
 */
class AiProviderTest extends TestCase
{
    use RefreshDatabase;

    private function configureGateway(): void
    {
        config([
            'immersion.ai.base_url' => 'http://ai.test',
            'immersion.ai.api_key' => 'a-real-key',
            'immersion.ai.interrogation_enabled' => true,
            'immersion.ai.speech_enabled' => true,
        ]);
    }

    public function test_a_configured_gateway_is_used(): void
    {
        $this->configureGateway();

        $this->assertInstanceOf(GatewayInterrogationProvider::class, app(InterrogationProvider::class));
        $this->assertInstanceOf(GatewaySpeechProvider::class, app(SpeechProvider::class));
    }

    public function test_each_capability_can_be_switched_off_on_its_own(): void
    {
        $this->configureGateway();
        config(['immersion.ai.speech_enabled' => false]);

        // Interrogations stay, generated audio goes.
        $this->assertInstanceOf(GatewayInterrogationProvider::class, app(InterrogationProvider::class));
        $this->assertInstanceOf(NullSpeechProvider::class, app(SpeechProvider::class));
    }

    public function test_an_unconfigured_gateway_falls_back_to_the_no_ai_providers(): void
    {
        config(['immersion.ai.base_url' => '', 'immersion.ai.api_key' => '']);

        $this->assertInstanceOf(NullInterrogationProvider::class, app(InterrogationProvider::class));
        $this->assertInstanceOf(NullSpeechProvider::class, app(SpeechProvider::class));
    }

    public function test_a_placeholder_api_key_counts_as_unconfigured(): void
    {
        config([
            'immersion.ai.base_url' => 'http://ai.test',
            'immersion.ai.api_key' => 'CHANGE_ME_MYSTERY_CASE_AI_KEY',
        ]);

        $this->assertInstanceOf(NullInterrogationProvider::class, app(InterrogationProvider::class));
    }

    public function test_the_no_ai_interrogation_keeps_the_case_playable(): void
    {
        config(['immersion.ai.interrogation_enabled' => false]);
        Http::fake();

        $game = Game::create([
            'name' => 'Sin IA',
            'status' => 'running',
            'interrogation_enabled' => true,
        ]);

        $player = $game->players()->create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'access_token' => 'token-sin-ia',
        ]);

        $response = $this->postJson(
            route('immersion.player.interrogation.ask', [$player->access_token, 'elizabeth-foster']),
            ['question' => '¿Dónde estabas esa noche?']
        )->assertOk();

        // The suspect deflects in character, the question is still spent, and
        // the transcript is recorded exactly as with AI.
        $this->assertSame(NullInterrogationProvider::REPLY, $response->json('suspect_message.content'));
        $this->assertSame(1, $response->json('questions_used'));
        $this->assertDatabaseCount('immersion_interrogation_messages', 2);

        // No outbound call was made at all.
        Http::assertNothingSent();

        $this->assertSame(1, InterrogationSession::count());
    }

    public function test_the_no_ai_speech_provider_produces_no_file(): void
    {
        $this->assertNull(app(NullSpeechProvider::class)->synthesize(1, 'un guion'));
    }
}
