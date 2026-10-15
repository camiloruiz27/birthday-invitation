// Load test for the player side of MisterioCode, written for k6 (https://k6.io).
//
// It plays tables of players: they open their inbox, keep it fresh the way the
// real page does (a tiny "has anything changed" request every few seconds),
// move between the inbox, the suspects and the accusation, and ask questions.
// It is meant to answer ONE question: how many players at once does this
// hosting plan carry before something gives (errors, slow pages, or the
// MySQL connection allowance)?
//
// Read tests/Load/README.md first. Do NOT point this at production.
//
//   k6 run -e BASE_URL=https://test.misteriocode.com -e LEVEL=50 tests/Load/players.js
//
// Tokens come from `php artisan immersion:load-seed` (written to
// storage/app/load-tokens.json). Pass TOKENS=<path> to read another file.

import http from 'k6/http';
import { check, sleep } from 'k6';
import { SharedArray } from 'k6/data';
import { Counter, Rate, Trend } from 'k6/metrics';

const BASE = (__ENV.BASE_URL || '').replace(/\/$/, '');

if (!BASE) {
    throw new Error('Set BASE_URL, e.g. -e BASE_URL=https://test.misteriocode.com');
}

const players = new SharedArray('players', () =>
    JSON.parse(open(__ENV.TOKENS || '../../storage/app/load-tokens.json')).players
);

// Suspects of the case the games play (habitacion-314 by default). Players
// spread over them: a suspect can only be interrogated by the first player to ask.
const SUSPECTS = (__ENV.SUSPECTS ||
    'ramon-alday,daniel-prieto,sofia-reguera,lucia-ferrer,hugo-valle,carlos-mena,paola-diaz,sistema-hotel'
).split(',');

// ---- what is measured ---------------------------------------------------------------

const pollMs = new Trend('state_poll_ms', true);
const pageMs = new Trend('page_ms', true);
const askMs = new Trend('ask_ms', true);
const askOk = new Rate('ask_ok');
const busy = new Counter('ask_busy_429');
const throttled = new Counter('ask_throttled_429');
const unavailable = new Counter('http_503');
const serverErrors = new Counter('http_5xx_other');

// ---- the shape of the run -----------------------------------------------------------------

// LEVEL = how many players at the top of the run. The ramp is gentle on purpose:
// a plateau you can read beats a spike you cannot.
const LEVEL = parseInt(__ENV.LEVEL || '25', 10);
const RAMP = __ENV.RAMP || '2m';
const HOLD = __ENV.HOLD || '8m';

export const options = {
    scenarios: {
        players: {
            executor: 'ramping-vus',
            startVUs: 1,
            stages: [
                { duration: RAMP, target: LEVEL },
                { duration: HOLD, target: LEVEL },
                { duration: '30s', target: 0 },
            ],
            gracefulRampDown: '30s',
        },
    },
    thresholds: {
        // What "it held" means. Tune these to what you are willing to accept.
        http_req_failed: ['rate<0.01'],
        page_ms: ['p(95)<1500'],
        state_poll_ms: ['p(95)<500'],
        ask_ok: ['rate>0.97'],
        http_503: ['count==0'],
    },
};

// ---- helpers ----------------------------------------------------------------------------------

function url(path) {
    return `${BASE}${path}`;
}

function tag(name) {
    return { tags: { name } };
}

function record(response, kind) {
    if (response.status === 503) {
        unavailable.add(1);
    } else if (response.status >= 500) {
        serverErrors.add(1);
    }

    kind.add(response.timings.duration);
}

function xsrfHeaders() {
    const jar = http.cookieJar();
    const cookie = jar.cookiesForURL(`${BASE}/`)['XSRF-TOKEN'];

    return {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': cookie && cookie.length ? decodeURIComponent(cookie[0]) : '',
    };
}

// Which stamps the page last saw, so a poll can tell whether to reload (as the browser does).
function pollOnce(token) {
    const response = http.get(url(`/jugador/${token}/estado`), {
        headers: { Accept: 'application/json' },
        ...tag('GET /jugador/{token}/estado'),
    });

    record(response, pollMs);
    check(response, { 'poll 200': (r) => r.status === 200 });

    try {
        return response.json('r');
    } catch (e) {
        return null;
    }
}

function visit(token, path, name) {
    const response = http.get(url(`/jugador/${token}${path}`), tag(name));

    record(response, pageMs);
    check(response, { [`${name} 200`]: (r) => r.status === 200 });

    return response;
}

function ask(token, suspect) {
    const response = http.post(
        url(`/jugador/${token}/interrogatorio/${suspect}/preguntar`),
        JSON.stringify({ question: `¿Dónde estaba usted a las diez? (carga ${__VU}-${__ITER})` }),
        { headers: xsrfHeaders(), ...tag('POST …/preguntar') }
    );

    record(response, askMs);

    // Two different 429s. The AI gate ("busy") hands the question back unspent and
    // is by design; the rate limiter ("Too Many Attempts") is NOT: one player has a
    // budget of their own, so seeing it here means some other traffic is spending it.
    let isBusy = false;

    if (response.status === 429) {
        try {
            isBusy = response.json('busy') === true;
        } catch (e) {
            isBusy = false;
        }

        (isBusy ? busy : throttled).add(1);
    }

    // 200 answered; 403 (suspect taken by a teammate), 422 (no questions left) and
    // the AI gate's busy 429 are normal game rules, not failures of the platform.
    askOk.add([200, 403, 422].includes(response.status) || isBusy);
}

// ---- one player's session ---------------------------------------------------------------

export default function () {
    const me = players[(__VU - 1) % players.length];
    const token = me.token;
    const suspect = SUSPECTS[(__VU - 1) % SUSPECTS.length];

    visit(token, '', 'GET inbox');

    // About three minutes of play per iteration: poll like the page does, and every
    // so often do something.
    const endsAt = Date.now() + 180000;
    let nextAction = Date.now() + 15000 + Math.random() * 20000;

    while (Date.now() < endsAt) {
        pollOnce(token);
        sleep(12 + Math.random() * 2);

        if (Date.now() < nextAction) {
            continue;
        }

        const roll = Math.random();

        if (roll < 0.30) {
            visit(token, '', 'GET inbox');
        } else if (roll < 0.55) {
            visit(token, '/interrogatorio', 'GET interrogation board');
        } else if (roll < 0.70) {
            visit(token, `/interrogatorio/${suspect}`, 'GET interrogation chat');
        } else if (roll < 0.78) {
            visit(token, '/acusacion', 'GET accusation');
        } else {
            visit(token, `/interrogatorio/${suspect}`, 'GET interrogation chat');
            ask(token, suspect);
        }

        nextAction = Date.now() + 20000 + Math.random() * 40000;
    }
}
