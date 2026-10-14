<?php

namespace Tests\Feature\Platform;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * platform:bold-probe-usd: the check that settles, against Bold itself,
 * whether a USD link with cents is accepted.
 */
class ProbeBoldUsdTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['platform.payments.identity_key' => 'sandbox-identity']);
    }

    private function postedTotals(): array
    {
        return Http::recorded(fn (Request $request) => $request->method() === 'POST')
            ->map(fn ($pair) => $pair[0]['amount']['total_amount'])
            ->values()
            ->all();
    }

    public function test_it_sends_whole_dollar_usd_amounts_and_reads_each_link_back(): void
    {
        Http::fake([
            '*/online/link/v1/payment_methods' => Http::response(['payload' => ['payment_methods' => ['CREDIT_CARD' => ['min' => 1000, 'max' => 5000000]]]]),
            '*/online/link/v1/LNK*' => Http::response(['id' => 'LNK_X', 'total' => 10.5, 'status' => 'ACTIVE']),
            '*/online/link/v1' => Http::response(['payload' => ['payment_link' => 'LNK_X', 'url' => 'https://checkout.bold.co/LNK_X'], 'errors' => []]),
        ]);

        $this->artisan('platform:bold-probe-usd')
            ->expectsOutputToContain('Aceptado')
            ->assertExitCode(0);

        $this->assertSame([28, 5, 1], $this->postedTotals());

        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request['amount']['currency'] === 'USD');
    }

    public function test_it_fails_and_says_so_when_bold_rejects_an_amount(): void
    {
        Http::fake([
            '*/online/link/v1/payment_methods' => Http::response([]),
            '*/online/link/v1' => Http::sequence()
                ->push(['payload' => ['payment_link' => 'LNK_1', 'url' => 'https://checkout.bold.co/LNK_1'], 'errors' => []])
                ->push(['errors' => [['message' => 'total_amount must be an integer']]], 400)
                ->push(['errors' => [['message' => 'total_amount must be an integer']]], 400)
                ->push(['errors' => [['message' => 'total_amount must be an integer']]], 400),
            '*/online/link/v1/LNK_1' => Http::response(['total' => 28]),
        ]);

        $this->artisan('platform:bold-probe-usd')
            ->expectsOutputToContain('Rechazado (400)')
            ->assertExitCode(1);
    }

    public function test_it_refuses_to_run_in_production_without_force(): void
    {
        Http::fake();
        $this->app['env'] = 'production';

        $this->artisan('platform:bold-probe-usd')->assertExitCode(1);

        Http::assertNothingSent();
    }

    public function test_it_needs_the_identity_key(): void
    {
        Http::fake();
        config(['platform.payments.identity_key' => '']);

        $this->artisan('platform:bold-probe-usd')->assertExitCode(1);

        Http::assertNothingSent();
    }
}
