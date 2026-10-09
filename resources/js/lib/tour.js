/**
 * Memory for the guided tours: which ones this browser has already seen.
 *
 * One first-party cookie, `mc_tour`, holding `{ <tourId>: <version> }`, kept
 * for a year. A cookie rather than localStorage because it is what was asked
 * for ("no volver a mostrarlo hasta dentro de un año") and because a player
 * never has an account to remember it on: the browser is all there is.
 *
 * It is a preference cookie — it only remembers that a help screen was shown,
 * says nothing about the person and is never sent anywhere else — so, like
 * `mc_consent`, it is not behind the analytics choices; the Cookies policy
 * lists it.
 *
 * Bumping a tour's version in TOUR_VERSIONS shows that tour again to everyone
 * who saw an older one, which is how a tour that changed gets re-announced.
 */

const COOKIE_NAME = 'mc_tour';
const COOKIE_MAX_AGE = 60 * 60 * 24 * 365; // one year

export const TOUR_VERSIONS = {
    // First visit to a case as a player.
    player: 1,
    // First time the interrogation area is open.
    interrogation: 1,
    // First game page as a Game Master.
    gm: 1,
};

export const START_TOUR_EVENT = 'mc:start-tour';

function readAll() {
    if (typeof document === 'undefined') return {};

    try {
        const entry = document.cookie
            .split('; ')
            .find((item) => item.startsWith(`${COOKIE_NAME}=`));

        if (!entry) return {};

        const parsed = JSON.parse(decodeURIComponent(entry.slice(COOKIE_NAME.length + 1)));

        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch {
        return {};
    }
}

function writeAll(value) {
    try {
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';

        document.cookie =
            `${COOKIE_NAME}=${encodeURIComponent(JSON.stringify(value))}` +
            `; Max-Age=${COOKIE_MAX_AGE}; Path=/; SameSite=Lax${secure}`;
    } catch {
        // Cookies blocked: the tour just shows again next time.
    }
}

export function hasSeenTour(id) {
    return readAll()[id] === TOUR_VERSIONS[id];
}

export function markTourSeen(id) {
    writeAll({ ...readAll(), [id]: TOUR_VERSIONS[id] });
}

/** Opens a tour on demand ("Ver el tutorial"), whether or not it was seen. */
export function startTour(id) {
    window.dispatchEvent(new CustomEvent(START_TOUR_EVENT, { detail: { id } }));
}
