import { router, usePage } from '@inertiajs/react';

const OPTIONS = [
    { code: 'CO', label: 'Colombia · COP' },
    { code: 'INTL', label: 'Internacional · USD' },
];

/**
 * Lets a visitor correct which price list they were put on (a VPN, a trip, a
 * Colombian living abroad). The server decides the market from the connection;
 * this only overrides it. Renders nothing while the international market is
 * switched off, so there is no selector for a market that does not exist.
 */
export default function MarketSelector({ className = '' }) {
    const { market } = usePage().props;

    if (!market?.selectable) {
        return null;
    }

    function choose(event) {
        const code = event.target.value;

        if (code === market.code) {
            return;
        }

        // The server answers with a redirect back, so the page reloads with
        // every price in the new currency.
        router.post(route('market.update'), { market: code }, { preserveScroll: true });
    }

    return (
        <label className={`inline-flex min-h-11 items-center gap-2 text-xs text-ink-subtle ${className}`}>
            <span className="sr-only">Región y moneda</span>
            <select
                value={market.code}
                onChange={choose}
                className="rounded-md border border-line bg-surface px-2 py-1 text-xs text-ink-muted hover:text-ink"
            >
                {OPTIONS.map((option) => (
                    <option key={option.code} value={option.code}>
                        {option.label}
                    </option>
                ))}
            </select>
        </label>
    );
}
