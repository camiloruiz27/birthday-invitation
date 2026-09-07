/**
 * Price in the case's own currency.
 *
 * `amount` is in the currency's minor unit. COP has no practical minor unit,
 * so for COP those are whole pesos and the formatter must not invent decimals.
 */
export function formatPrice(amount, currency = 'COP') {
    const hasCents = !['COP', 'CLP', 'JPY', 'KRW'].includes(currency);
    const value = hasCents ? amount / 100 : amount;

    try {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: hasCents ? 2 : 0,
        }).format(value);
    } catch {
        // An unknown currency code must not blank out a price.
        return `${value} ${currency}`;
    }
}

/** "60–90 min" style duration, from a minute count. */
export function formatDuration(minutes) {
    if (!minutes) return null;
    if (minutes < 60) return `${minutes} min`;

    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;

    return rest === 0 ? `${hours} h` : `${hours} h ${rest} min`;
}

export function formatPlayerRange(min, max) {
    if (!min && !max) return null;
    if (min && max) return min === max ? `${min} jugadores` : `${min}–${max} jugadores`;

    return `${min || max} jugadores`;
}

export function formatDateTime(value) {
    if (!value) return null;

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;

    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(date.getDate())}/${pad(date.getMonth() + 1)}/${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

export function formatTime(value) {
    if (!value) return null;

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return null;

    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

export function formatDateTimeShort(value) {
    if (!value) return null;

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;

    const pad = (n) => String(n).padStart(2, '0');

    return `${pad(date.getDate())}/${pad(date.getMonth() + 1)} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
}
