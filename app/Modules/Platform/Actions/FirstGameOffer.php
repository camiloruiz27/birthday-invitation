<?php

namespace App\Modules\Platform\Actions;

use App\Models\User;
use App\Modules\Platform\Mail\FirstGameOfferMail;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Support\MarketingConsent;
use App\Modules\Platform\Support\PlatformSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * The offer that follows a person's first closed game: a private discount code
 * in a promotional email, once per person, some days after the close.
 *
 * "First" is not computed anywhere. The offer is sent once per account
 * (`first_game_offer_sent_at`), and goes to whoever has a closed game old
 * enough, so the first game to age past the delay is the one that triggers it
 * and every later one finds the flag already set.
 *
 * Only to the account that owns the game (`games.user_id`): a guest player has
 * no account and is never written to from here. Promotional, so only to
 * accounts that authorised it and did not leave (User::scopeAcceptingMarketing),
 * and only to an address that was confirmed.
 */
class FirstGameOffer
{
    /** Codes are stored upper-case (CreatePromoCodes), so this reads PRIMERACOMPRA-XXXXXX. */
    public const CODE_PREFIX = 'Primeracompra';

    /** The switch's key in platform_settings. */
    public const SETTING = 'first_game_offer.enabled';

    public function __construct(private readonly CreatePromoCodes $promoCodes)
    {
    }

    /**
     * Is the offer on? What an administrator chose in the panel, and until
     * someone chooses, the .env default (PLATFORM_FIRST_GAME_OFFER_ENABLED).
     */
    public static function enabled(): bool
    {
        return PlatformSettings::bool(self::SETTING, (bool) config('platform.first_game_offer.enabled'));
    }

    /** Turns it on or off from the panel, remembering who did. */
    public function setEnabled(bool $enabled, ?int $userId): void
    {
        PlatformSettings::setBool(self::SETTING, $enabled, $userId);
    }

    /**
     * Who is due an offer right now, oldest account first.
     *
     * @return Collection<int, User>
     */
    public function eligible(int $limit): Collection
    {
        return $this->dueQuery()->orderBy('id')->limit(max(1, $limit))->get();
    }

    /** How many people are due right now (what the admin panel shows). */
    public function pendingCount(): int
    {
        return $this->dueQuery()->count();
    }

    private function dueQuery(): Builder
    {
        $cutoff = now()->subDays(max(0, (int) config('platform.first_game_offer.delay_days')));

        return User::query()
            ->acceptingMarketing()
            ->where('is_admin', false)
            ->whereNotNull('email_verified_at')
            ->whereNull('first_game_offer_sent_at')
            ->whereHas('games', fn ($games) => $games
                ->where('status', 'finished')
                ->where('finished_at', '<=', $cutoff));
    }

    /**
     * The mail as a person would receive it, with an example code that was
     * never created and links that do nothing. For the admin preview: nothing
     * is written, nothing is sent.
     */
    public function sampleMail(string $name): FirstGameOfferMail
    {
        return new FirstGameOfferMail(
            name: $name,
            code: strtoupper(self::CODE_PREFIX).'-A1B2C3',
            percent: (int) config('platform.first_game_offer.discount_percent'),
            expiresAt: now()->addDays((int) config('platform.first_game_offer.valid_days'))->endOfDay(),
            catalogUrl: route('cases.index'),
            unsubscribeUrl: '#',
        );
    }

    /**
     * Creates the code, sends the mail and records it, all or nothing: if the
     * mail cannot leave, no code is left behind and nothing is marked, so the
     * next run tries again. Returns the code, or null when the person was
     * already served (two runs overlapping) or is no longer eligible.
     */
    public function send(User $user): ?string
    {
        return DB::transaction(function () use ($user): ?string {
            $current = User::query()->whereKey($user->id)->lockForUpdate()->first();

            if ($current === null
                || $current->first_game_offer_sent_at !== null
                || ! $current->acceptsMarketing()
                || ! filter_var($current->email, FILTER_VALIDATE_EMAIL)) {
                return null;
            }

            $percent = (int) config('platform.first_game_offer.discount_percent');
            $expiresAt = now()->addDays((int) config('platform.first_game_offer.valid_days'))->endOfDay();

            [$code] = $this->promoCodes->batch(self::CODE_PREFIX, 1, [
                'discount_type' => PromoCode::DISCOUNT_PERCENT,
                'discount_value' => $percent,
                'applies_to' => PromoCode::APPLIES_CASE,
                'assigned_user_id' => $current->id,
                'active' => true,
                'expires_at' => $expiresAt,
                'note' => 'Oferta de primera partida',
            ]);

            Mail::to($current->email)->send(new FirstGameOfferMail(
                name: $current->name,
                code: $code,
                percent: $percent,
                expiresAt: $expiresAt,
                catalogUrl: route('cases.index'),
                unsubscribeUrl: MarketingConsent::unsubscribeUrl($current),
            ));

            $current->forceFill(['first_game_offer_sent_at' => now()])->save();

            return $code;
        });
    }
}
