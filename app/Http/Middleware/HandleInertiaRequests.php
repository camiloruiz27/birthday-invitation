<?php

namespace App\Http\Middleware;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\GameRevision;
use App\Modules\Immersion\Support\PlayerNotices;
use App\Modules\Platform\Support\Captcha;
use App\Modules\Platform\Support\InAppBrowser;
use App\Modules\Platform\Support\Market;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array{url: string, rev: ?array<string, string>}|null
     */
    private function pollState(Request $request): ?array
    {
        $player = $request->route('player');

        if ($player instanceof Player) {
            return [
                'url' => route('immersion.poll.player', $player->access_token, false),
                'rev' => GameRevision::current((int) $player->game_id),
            ];
        }

        $game = $request->route('game');

        if ($game instanceof Game) {
            // The route middleware already proved this user may see the game;
            // the poll endpoint only needs to know it was this browser.
            GameRevision::allowConsole($request, (int) $game->getKey());

            return [
                'url' => route('immersion.poll.console', $game->getKey(), false),
                'rev' => GameRevision::current((int) $game->getKey()),
            ];
        }

        return null;
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // Only the fields the UI actually renders. The User model hides
            // password and remember_token, but there is no reason to ship
            // timestamps and verification state to every page either.
            'auth' => [
                'user' => fn () => $request->user()
                    ? $request->user()->only(['id', 'name', 'email', 'is_admin'])
                        + ['email_verified' => $request->user()->hasVerifiedEmail()]
                    : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],

            // Public by definition — the widget needs it in the page to draw
            // itself. The secret key is what must never leave the server, and
            // it is not here. Empty means no captcha is configured, which is
            // how the widget knows to render nothing.
            // Public contact address for the footer (questions, personal-data
            // requests, terms). Same value the legal pages cite.
            'contactEmail' => fn () => config('legal.email') ?: null,

            'captchaSiteKey' => fn () => app(Captcha::class)->siteKey(),

            // Which price list this visitor is on. `selectable` is false while
            // the international market is switched off, so the footer shows
            // no selector for a market that does not exist yet.
            'market' => fn () => [
                'code' => Market::fromRequest($request),
                'currency' => Market::currency(Market::fromRequest($request)),
                'selectable' => (bool) config('platform.market.international_enabled'),
            ],

            // Which app's browser this visit is inside (tiktok, instagram,
            // facebook, other) or null for an ordinary browser. Decided here,
            // from the User-Agent, so the first response already knows and
            // the pages can tell the visitor what to do when the anti-bot
            // check or the payment page misbehaves in there. See InAppBrowser.
            'inAppBrowser' => fn () => InAppBrowser::detect($request->userAgent()),

            // Where a page that keeps itself current asks "has anything
            // changed?" and the stamp it starts from. Built from the route
            // parameter that is ALREADY resolved, so it adds no query. See
            // GameRevision for why polling goes through this instead of
            // reloading the page on a timer.
            'pollState' => fn () => $this->pollState($request),

            // What is new for a player, said on whatever screen they are
            // looking at (see PlayerNotices). Only on a player's pages: the
            // route parameter that is already resolved is the player, so there
            // is nothing to look up for anyone else.
            'notices' => fn () => ($player = $request->route('player')) instanceof Player
                ? PlayerNotices::for($player)
                : null,
        ]);
    }
}
