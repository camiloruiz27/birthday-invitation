/**
 * Search, filter and ordering for the case catalog and the library.
 *
 * Everything here runs in the browser on the list the page already received.
 * Both lists are small (the published catalog, or what one person owns), and
 * the host allows only 500 new database connections an hour: every filter the
 * visitor tries used to be a server request, and for a signed-in person a
 * request is a connection. Now trying a filter costs none.
 */

export const DIFFICULTIES = [
    { value: 'easy', label: 'Accesible' },
    { value: 'medium', label: 'Intermedio' },
    { value: 'hard', label: 'Exigente' },
];

export const DURATIONS = [
    { value: 'up_to_60', label: 'Hasta 60 min', test: (minutes) => minutes <= 60 },
    { value: '61_to_75', label: '61–75 min', test: (minutes) => minutes >= 61 && minutes <= 75 },
    { value: '76_to_90', label: '76–90 min', test: (minutes) => minutes >= 76 && minutes <= 90 },
    { value: 'over_90', label: 'Más de 90 min', test: (minutes) => minutes > 90 },
];

export const SORTS = {
    catalog: [
        { value: 'featured', label: 'Destacados' },
        { value: 'published_desc', label: 'Publicados recientemente' },
        { value: 'published_asc', label: 'Publicados primero' },
        { value: 'price_asc', label: 'Menor precio' },
        { value: 'price_desc', label: 'Mayor precio' },
        { value: 'name_asc', label: 'Nombre: A–Z' },
        { value: 'name_desc', label: 'Nombre: Z–A' },
        { value: 'duration_asc', label: 'Menor duración' },
        { value: 'duration_desc', label: 'Mayor duración' },
    ],
    library: [
        { value: 'acquired_desc', label: 'Adquiridos recientemente' },
        { value: 'acquired_asc', label: 'Adquiridos primero' },
        { value: 'price_asc', label: 'Menor precio actual' },
        { value: 'price_desc', label: 'Mayor precio actual' },
        { value: 'name_asc', label: 'Nombre: A–Z' },
        { value: 'name_desc', label: 'Nombre: Z–A' },
        { value: 'duration_asc', label: 'Menor duración' },
        { value: 'duration_desc', label: 'Mayor duración' },
    ],
};

export function defaultSort(area) {
    return area === 'library' ? 'acquired_desc' : 'featured';
}

export function emptyFilters(area) {
    return {
        q: '',
        difficulty: [],
        players: null,
        duration: null,
        mechanics: [],
        ai: false,
        priceMin: '',
        priceMax: '',
        sort: defaultSort(area),
    };
}

/** Lowercase, accent-free text, so "aureo" finds "Áureo". */
export function normalise(value) {
    return String(value ?? '')
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase();
}

function list(params, key, allowed) {
    const values = (params.get(key) ?? '').split(',').map((value) => value.trim());

    return [...new Set(values.filter((value) => allowed.includes(value)))];
}

function priceText(value) {
    if (value === null || value === undefined || String(value).trim() === '') return '';

    const number = Number(value);

    return Number.isFinite(number) && number >= 0 ? String(value).trim() : '';
}

/**
 * The filters a query string describes. Anything unknown or malformed is
 * dropped instead of failing, so an old shared link still opens the page.
 */
export function parseFilters(search, area, { mechanics = [], maxPlayers = 12 } = {}) {
    const params = new URLSearchParams(search);
    const filters = emptyFilters(area);

    filters.q = (params.get('q') ?? '').trim().slice(0, 120);
    filters.difficulty = list(params, 'difficulty', DIFFICULTIES.map((item) => item.value));
    filters.mechanics = list(params, 'mechanics', mechanics);
    filters.ai = ['1', 'true'].includes(params.get('ai') ?? '');

    const players = Number.parseInt(params.get('players') ?? '', 10);
    filters.players = Number.isInteger(players) && players >= 1 && players <= maxPlayers ? players : null;

    const duration = params.get('duration');
    filters.duration = DURATIONS.some((item) => item.value === duration) ? duration : null;

    filters.priceMin = priceText(params.get('price_min'));
    filters.priceMax = priceText(params.get('price_max'));

    const sort = params.get('sort');
    if (SORTS[area].some((item) => item.value === sort)) filters.sort = sort;

    return filters;
}

/** The query string for a set of filters: only what differs from the defaults. */
export function toQuery(filters, area) {
    const params = new URLSearchParams();

    if (filters.q.trim()) params.set('q', filters.q.trim());
    if (filters.difficulty.length) params.set('difficulty', filters.difficulty.join(','));
    if (filters.players) params.set('players', String(filters.players));
    if (filters.duration) params.set('duration', filters.duration);
    if (filters.mechanics.length) params.set('mechanics', filters.mechanics.join(','));
    if (filters.ai) params.set('ai', '1');
    if (filters.priceMin !== '') params.set('price_min', filters.priceMin);
    if (filters.priceMax !== '') params.set('price_max', filters.priceMax);
    if (filters.sort !== defaultSort(area)) params.set('sort', filters.sort);

    return params.toString();
}

/** A reversed range is a typing slip, not a reason to show nothing. */
export function priceRangeError(filters) {
    return filters.priceMin !== '' && filters.priceMax !== '' && Number(filters.priceMin) > Number(filters.priceMax)
        ? 'El precio mínimo no puede ser mayor que el máximo.'
        : '';
}

export function isActive(filters) {
    return Boolean(
        filters.q.trim() || filters.difficulty.length || filters.players || filters.duration ||
        filters.mechanics.length || filters.ai || filters.priceMin !== '' || filters.priceMax !== ''
    );
}

function priceValue(item) {
    const hasCents = !['COP', 'CLP', 'JPY', 'KRW'].includes(item.currency);

    return hasCents ? item.price_amount / 100 : item.price_amount;
}

function time(value) {
    const stamp = value ? Date.parse(value) : NaN;

    return Number.isNaN(stamp) ? 0 : stamp;
}

/**
 * What each item is searched and sorted by, computed once per list instead of
 * once per keystroke.
 */
export function indexCases(items) {
    return items.map((item, position) => ({
        item,
        position,
        text: normalise([item.name, item.tagline, item.description, item.search].filter(Boolean).join(' ')),
        name: normalise(item.name),
        mechanics: (item.mechanics ?? []).map((mechanic) => mechanic.slug),
        ai: (item.mechanics ?? []).some((mechanic) => mechanic.ai),
        price: priceValue(item),
        duration: Number(item.duration_minutes) || 0,
    }));
}

/** The values the controls offer, taken from the cases themselves. */
export function optionsFor(items) {
    const mechanics = new Map();
    let minPlayers = Infinity;
    let maxPlayers = 0;

    for (const item of items) {
        for (const mechanic of item.mechanics ?? []) mechanics.set(mechanic.slug, mechanic.name);
        if (item.min_players) minPlayers = Math.min(minPlayers, item.min_players);
        if (item.max_players) maxPlayers = Math.max(maxPlayers, item.max_players);
    }

    return {
        mechanics: [...mechanics].map(([slug, name]) => ({ slug, name })),
        players: Number.isFinite(minPlayers) && maxPlayers >= minPlayers
            ? Array.from({ length: maxPlayers - minPlayers + 1 }, (_, index) => minPlayers + index)
            : [],
        currency: items[0]?.currency ?? 'COP',
    };
}

const COLLATOR = new Intl.Collator('es', { sensitivity: 'base' });

function sortKey(entry, field) {
    switch (field) {
        case 'published': return time(entry.item.published_at);
        case 'acquired': return time(entry.item.acquired_at);
        case 'price': return entry.price;
        case 'duration': return entry.duration;
        case 'name': return entry.name;
        default: return entry.position;
    }
}

const SORT_FIELDS = {
    featured: ['featured', false],
    published_desc: ['published', true],
    published_asc: ['published', false],
    acquired_desc: ['acquired', true],
    acquired_asc: ['acquired', false],
    price_asc: ['price', false],
    price_desc: ['price', true],
    name_asc: ['name', false],
    name_desc: ['name', true],
    duration_asc: ['duration', false],
    duration_desc: ['duration', true],
};

function matches(entry, filters, terms, usePrice) {
    if (terms.some((term) => !entry.text.includes(term))) return false;
    if (filters.difficulty.length && !filters.difficulty.includes(entry.item.difficulty)) return false;

    if (filters.players) {
        const { min_players: min, max_players: max } = entry.item;
        if (!min || !max || filters.players < min || filters.players > max) return false;
    }

    if (filters.duration) {
        const range = DURATIONS.find((item) => item.value === filters.duration);
        if (range && !range.test(entry.duration)) return false;
    }

    if (filters.mechanics.length && !filters.mechanics.some((slug) => entry.mechanics.includes(slug))) return false;
    if (filters.ai && !entry.ai) return false;

    if (usePrice) {
        if (filters.priceMin !== '' && entry.price < Number(filters.priceMin)) return false;
        if (filters.priceMax !== '' && entry.price > Number(filters.priceMax)) return false;
    }

    return true;
}

/** Filtered and ordered items from an `indexCases` list. */
export function applyFilters(entries, filters) {
    // Every word has to appear, in any order: "caso hotel" finds "El hotel…".
    const terms = normalise(filters.q).split(/\s+/).filter(Boolean);
    const usePrice = priceRangeError(filters) === '';
    const [field, descending] = SORT_FIELDS[filters.sort] ?? SORT_FIELDS.featured;

    return entries
        .filter((entry) => matches(entry, filters, terms, usePrice))
        .sort((left, right) => {
            const a = sortKey(left, field);
            const b = sortKey(right, field);
            let comparison = typeof a === 'string' ? COLLATOR.compare(a, b) : a - b;

            if (comparison === 0) comparison = COLLATOR.compare(left.name, right.name);

            return descending ? -comparison : comparison;
        })
        .map((entry) => entry.item);
}
