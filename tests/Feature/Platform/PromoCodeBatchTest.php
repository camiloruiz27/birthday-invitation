<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Console\Commands\CreatePromoCodeBatch;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class PromoCodeBatchTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_a_batch_creates_the_requested_number_of_single_use_codes(): void
    {
        $this->artisan(CreatePromoCodeBatch::class, [
            'prefix' => 'giveaway',
            '--count' => 5,
            '--credits' => '10',
        ])->assertSuccessful();

        $codes = PromoCode::where('code', 'like', 'GIVEAWAY-%')->get();

        $this->assertCount(5, $codes);
        $this->assertSame(5, $codes->pluck('code')->unique()->count());

        foreach ($codes as $promo) {
            $this->assertSame(10, $promo->grants_credits);
            $this->assertSame(1, $promo->max_redemptions);
            $this->assertSame(1, $promo->max_redemptions_per_user);
        }
    }

    public function test_each_code_in_a_batch_is_good_for_exactly_one_redemption(): void
    {
        $this->artisan(CreatePromoCodeBatch::class, [
            'prefix' => 'UNICO',
            '--count' => 1,
            '--credits' => '15',
        ])->assertSuccessful();

        $code = PromoCode::where('code', 'like', 'UNICO-%')->sole();

        $first = $this->gameMaster();
        app(RedeemPromoCode::class)->redeemGift($first, $code->code);

        $this->assertSame(1, $code->fresh()->redemptions_count);
        $this->assertTrue($code->fresh()->isExhausted());

        $second = $this->userWithoutAccess();

        $this->expectException(PromoCodeException::class);
        app(RedeemPromoCode::class)->redeemGift($second, $code->code);
    }

    public function test_running_the_same_prefix_twice_never_collides(): void
    {
        $this->artisan(CreatePromoCodeBatch::class, [
            'prefix' => 'CAMPANA',
            '--count' => 20,
            '--credits' => '5',
        ])->assertSuccessful();

        $this->artisan(CreatePromoCodeBatch::class, [
            'prefix' => 'CAMPANA',
            '--count' => 20,
            '--credits' => '5',
        ])->assertSuccessful();

        $codes = PromoCode::where('code', 'like', 'CAMPANA-%')->pluck('code');

        $this->assertCount(40, $codes);
        $this->assertSame(40, $codes->unique()->count());
    }

    public function test_a_batch_rejects_mixing_gift_and_discount(): void
    {
        $this->artisan(CreatePromoCodeBatch::class, [
            'prefix' => 'MEZCLADO',
            '--case' => 'steve-jacobs',
            '--discount-percent' => '10',
        ])->assertFailed();

        $this->assertSame(0, PromoCode::count());
    }

    public function test_a_batch_rejects_a_count_outside_the_allowed_range(): void
    {
        $this->artisan(CreatePromoCodeBatch::class, [
            'prefix' => 'DEMASIADOS',
            '--count' => 5000,
            '--credits' => '10',
        ])->assertFailed();

        $this->assertSame(0, PromoCode::count());
    }
}
