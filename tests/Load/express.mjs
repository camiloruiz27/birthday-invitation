#!/usr/bin/env node
/**
 * Carga del modo Express (la demo de feria): grupos de teléfonos jugando rondas
 * COMPLETAS, con el comportamiento de verdad (landing, formularios, un poll cada
 * ~3 s durante los 3 minutos, preguntas, compartir, acusar, pantalla final).
 *
 * Sin dependencias: solo Node 20+. (El resto de tests/Load usa k6; esto no lo
 * necesita porque cada «jugador» tiene que leer el estado y decidir qué hacer.)
 *
 * Responde tres preguntas:
 *  1. ¿Aguanta N jugadores a la vez en partidas separadas? (latencia y errores)
 *  2. ¿Cuántas conexiones nuevas a MySQL gasta cada grupo? (el tope del hosting
 *     es 500 por hora para TODO el sitio)
 *  3. ¿Se ven bien las salas bajo concurrencia? (lista de jugadores y preguntas
 *     iguales en todos los teléfonos, todos ven el final, nada se pierde)
 *
 * Se corre SOLO contra un servidor de pruebas: crea salas, jugadores y contactos
 * falsos. Nunca contra producción. Contra test, tenga en cuenta que gasta el
 * presupuesto de conexiones de ese sitio.
 *
 * Uso (ver tests/Load/EXPRESS.md):
 *   node tests/Load/express.mjs --bases http://127.0.0.1:8101,http://127.0.0.1:8102 \
 *        --scenario burst --groups 4 --size 5 --waves 2
 *   node tests/Load/express.mjs --bases https://test.example.com \
 *        --scenario sustained --arrivals-per-min 0.4 --minutes 12
 *
 * Opciones:
 *   --bases            URLs del servidor (varias = reparte cada petición entre ellas)
 *   --scenario         burst (G grupos a la vez, W oleadas) | sustained (llegadas Poisson)
 *   --groups G         grupos simultáneos por oleada            (burst, 4)
 *   --waves W          oleadas seguidas                          (burst, 1)
 *   --wave-gap S       segundos entre oleadas                    (burst, 5)
 *   --arrivals-per-min grupos que llegan por minuto              (sustained, 0.4)
 *   --minutes M        minutos de llegadas                       (sustained, 10)
 *   --size N           jugadores por grupo, 2-5                  (5)
 *   --event SLUG       evento de config/express.php              (el por defecto)
 *   --mysql-bin / --mysql-args   cliente mysql para contar conexiones, p. ej.
 *        --mysql-bin mysql.exe --mysql-args "-uroot -ppass -h127.0.0.1 -P3307"
 *   --out FILE         guarda el resumen en JSON
 */
import { execFileSync } from "node:child_process";
import { writeFileSync } from "node:fs";

/* ------------------------------------------------------------------ options */

function parseArgs(argv) {
    const out = {};

    for (let i = 0; i < argv.length; i++) {
        if (!argv[i].startsWith("--")) continue;
        const key = argv[i].slice(2);
        const next = argv[i + 1];

        if (next === undefined || next.startsWith("--")) out[key] = true;
        else { out[key] = next; i++; }
    }

    return out;
}

const args = parseArgs(process.argv.slice(2));
const BASES = String(args.bases ?? "http://127.0.0.1:8000").split(",").map((b) => b.trim().replace(/\/$/, ""));
const SCENARIO = String(args.scenario ?? "burst");
const SIZE = Math.min(5, Math.max(2, Number(args.size ?? 5)));
const GROUPS = Number(args.groups ?? 4);
const WAVES = Number(args.waves ?? 1);
const WAVE_GAP_S = Number(args["wave-gap"] ?? 5);
const ARRIVALS_PER_MIN = Number(args["arrivals-per-min"] ?? 0.4);
const MINUTES = Number(args.minutes ?? 10);
const EVENT = args.event ? String(args.event) : null;

/* ------------------------------------------------------------------ helpers */

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const rand = (min, max) => min + Math.random() * (max - min);
const pick = (list) => list[Math.floor(Math.random() * list.length)];

let rr = 0;
const nextBase = () => BASES[rr++ % BASES.length];

/** label -> { ms: number[], codes: Map<status, n> } */
const metrics = new Map();
const perSecond = new Map();

function record(label, ms, status) {
    if (!metrics.has(label)) metrics.set(label, { ms: [], codes: new Map() });
    const m = metrics.get(label);
    m.ms.push(ms);
    m.codes.set(status, (m.codes.get(status) ?? 0) + 1);

    const second = Math.floor(Date.now() / 1000);
    perSecond.set(second, (perSecond.get(second) ?? 0) + 1);
}

const anomalies = new Map();
function anomaly(kind, detail) {
    anomalies.set(kind, [...(anomalies.get(kind) ?? []), detail]);
}

function percentile(sorted, p) {
    if (sorted.length === 0) return 0;

    return sorted[Math.min(sorted.length - 1, Math.floor((p / 100) * sorted.length))];
}

/* ------------------------------------------------------------------- phone */

/** One visitor's browser: a cookie jar and the requests it makes. */
class Phone {
    constructor(name) {
        this.name = name;
        this.jar = new Map();
    }

    cookieHeader() {
        return [...this.jar].map(([k, v]) => `${k}=${v}`).join("; ");
    }

    xsrf() {
        const value = this.jar.get("XSRF-TOKEN");

        return value ? decodeURIComponent(value) : null;
    }

    async req(label, method, path, { json, form, manual = false, accept } = {}) {
        const headers = { Accept: accept ?? (json !== undefined || path.endsWith("/estado") ? "application/json" : "text/html"), Cookie: this.cookieHeader() };
        let body;

        if (json !== undefined) {
            headers["Content-Type"] = "application/json";
            headers["X-Requested-With"] = "XMLHttpRequest";
            body = JSON.stringify(json);
        } else if (form !== undefined) {
            headers["Content-Type"] = "application/x-www-form-urlencoded";
            body = new URLSearchParams(form).toString();
        }

        if (method !== "GET" && this.xsrf()) headers["X-XSRF-TOKEN"] = this.xsrf();

        const started = performance.now();
        let res;
        let text = "";

        try {
            res = await fetch(`${nextBase()}${path}`, { method, headers, body, redirect: manual ? "manual" : "follow" });
            text = await res.text();
        } catch (error) {
            record(label, performance.now() - started, "network");

            return { status: 0, text: "", error };
        }

        const ms = performance.now() - started;
        record(label, ms, res.status);

        for (const cookie of res.headers.getSetCookie?.() ?? []) {
            const [pair] = cookie.split(";");
            const eq = pair.indexOf("=");
            this.jar.set(pair.slice(0, eq).trim(), pair.slice(eq + 1).trim());
        }

        let parsed = null;
        if (text.startsWith("{")) {
            try { parsed = JSON.parse(text); } catch { /* not JSON */ }
        }

        return { status: res.status, headers: res.headers, text, json: parsed };
    }
}

/** The case slugs the "create" page offers, read from the Inertia page data. */
function casesFrom(html) {
    const raw = html.match(/data-page="([^"]*)"/)?.[1] ?? "";
    const json = raw.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">");

    try {
        const slugs = (JSON.parse(json).props.cases ?? []).map((c) => c.slug);

        if (slugs.length) return slugs;
    } catch { /* fall through */ }

    return ["el-mensaje-de-las-11-03"];
}

/* ------------------------------------------------------------------- stats */

const rounds = { started: 0, completed: 0, durations: [], lobbyVisibleMs: [] };

/* ------------------------------------------------------------------- group */

/**
 * One group playing one round: the host creates the room, friends join with
 * the code, the host starts, everybody polls and acts as a person would, and
 * the round is complete when every phone has seen the reveal.
 */
async function runGroup(gid) {
    rounds.started++;
    const groupStart = Date.now();
    const host = new Phone(`g${gid}-host`);
    const query = EVENT ? `?e=${EVENT}` : "";

    await host.req("page_landing", "GET", `/express${query}`);
    const create = await host.req("page_create", "GET", `/express/crear${query}`);

    // The case the host would pick: whatever the page offers, at random.
    const caseSlug = pick(casesFrom(create.text));

    const created = await host.req("create_room", "POST", "/express/crear", {
        manual: true,
        form: { case_slug: caseSlug, nickname: `Host${gid}`, contact_type: "none", ...(EVENT ? { event: EVENT } : {}) },
    });

    const hostToken = (created.headers?.get("location") ?? "").split("/").pop();

    if (!/^[0-9a-f-]{36}$/.test(hostToken)) {
        anomaly("create_failed", `g${gid}: status ${created.status}`);

        return;
    }

    await host.req("page_room", "GET", `/express/s/${hostToken}`);
    const first = await host.req("poll", "GET", `/express/s/${hostToken}/estado`);
    const code = first.json?.room?.code;

    if (!code) {
        anomaly("no_room_code", `g${gid}: poll ${first.status} ${first.text.slice(0, 160).replace(/s+/g, " ")}`);

        return;
    }

    const players = [{ phone: host, token: hostToken, index: 0, isHost: true, done: false, lastState: null }];
    let lastJoinAt = 0;

    // Friends scan the QR over the next seconds.
    const joins = [];
    for (let i = 1; i < SIZE; i++) {
        joins.push((async () => {
            await sleep(rand(2000, 12000));
            const phone = new Phone(`g${gid}-p${i}`);

            await phone.req("page_join", "GET", `/express/unirse/${code}`);
            const joined = await phone.req("join_room", "POST", `/express/unirse/${code}`, {
                manual: true,
                form: { nickname: `Amigo${gid}-${i}`, contact_type: "none", ...(EVENT ? { event: EVENT } : {}) },
            });
            const token = (joined.headers?.get("location") ?? "").split("/").pop();

            if (!/^[0-9a-f-]{36}$/.test(token)) {
                anomaly("join_failed", `g${gid}-p${i}: status ${joined.status}`);

                return;
            }

            await phone.req("page_room", "GET", `/express/s/${token}`);
            lastJoinAt = Math.max(lastJoinAt, Date.now());
            players.push({ phone, token, index: i, isHost: false, done: false, lastState: null });
        })());
    }

    // The host's phone loops from the start; the friends' ones once they are in.
    const hostLoop = playerLoop(players[0], players, gid);
    await Promise.all(joins);
    const friendLoops = players.slice(1).map((p) => playerLoop(p, players, gid));

    await Promise.all([hostLoop, ...friendLoops]);

    if (players.length === SIZE && players.every((p) => p.done)) {
        rounds.completed++;
        rounds.durations.push((Date.now() - groupStart) / 1000);
        finalChecks(gid, players);
    } else {
        anomaly("round_incomplete", `g${gid}: ${players.filter((p) => p.done).length}/${SIZE} saw the reveal`);
    }
}

/** What one phone does for as long as the round lasts. */
async function playerLoop(player, group, gid) {
    const { phone, token } = player;
    const seen = { asked: 0, shared: false, accuseAt: null, accused: false, started: false, lobbyAt: Date.now(), polls: 0 };
    let revealPolls = 0;
    const deadline = Date.now() + 5 * 60 * 1000;

    await sleep(rand(0, 2500)); // phones do not poll in lockstep

    while (Date.now() < deadline) {
        const res = await phone.req("poll", "GET", `/express/s/${token}/estado`);
        const state = res.json;
        seen.polls++;

        if (state) {
            player.lastState = state;

            // Lobby: the host starts once everybody is listed.
            if (player.isHost && state.room.status === "lobby" && !seen.started && state.players.length >= SIZE) {
                rounds.lobbyVisibleMs.push(Date.now() - seen.lobbyAt);
                seen.started = true;
                await sleep(rand(1000, 3000));
                await phone.req("start", "POST", `/express/s/${token}/iniciar`, { json: {} });
            }

            if (state.room.status === "playing" || state.room.status === "accusing") {
                await act(player, state, seen);
            }

            if (state.room.status === "reveal") {
                player.done = true;
                revealPolls++;
                if (revealPolls >= 3) return;
            }

            if (state.room.status === "expired") return;
        } else if (res.status === 404) {
            anomaly("token_404", `g${gid}-${player.index}`);

            return;
        }

        await sleep(state?.room?.status === "reveal" ? 6000 : rand(2500, 3500));
    }
}

/** The moves a player makes, once each, when the screen allows them. */
async function act(player, state, seen) {
    const { phone, token, index } = player;

    // The occasional reload of the page, as a nervous thumb does.
    if (Math.random() < 0.01) await phone.req("page_room", "GET", `/express/s/${token}`);

    // Questions: the host asks one suspect twice, the second player another once.
    if (state.interrogation.open && state.interrogation.budget_left > 0) {
        const wanted = index === 0 ? 2 : index === 1 ? 1 : 0;

        if (seen.asked < wanted) {
            const suspect = state.suspects[index === 0 ? 0 : 1];
            const chip = suspect?.chips?.[seen.asked];

            if (suspect && chip && !suspect.locked_for_me) {
                seen.asked++;
                await phone.req("ask", "POST", `/express/s/${token}/preguntar`, {
                    json: { suspect_id: suspect.id, question: chip.text, topic_id: chip.topic_id },
                });
            }
        }
    }

    // One private clue shared with the group.
    if (index === 1 && !seen.shared) {
        const mine = state.evidence.find((e) => e.can_share);

        if (mine) {
            seen.shared = true;
            await sleep(rand(3000, 9000));
            await phone.req("share", "POST", `/express/s/${token}/compartir`, { json: { evidence_id: mine.id } });
        }
    }

    // The accusation, some seconds after it opens.
    if (state.accusation.open && !seen.accused) {
        seen.accuseAt ??= Date.now() + rand(4000, 40000);

        if (Date.now() >= seen.accuseAt && state.accusation.options) {
            const o = state.accusation.options;
            seen.accused = true;
            await phone.req("accuse", "POST", `/express/s/${token}/acusar`, {
                json: { who: pick(o.who).id, how: pick(o.how).id, why: pick(o.why).id },
            });
        }
    }
}

/** Every phone of a finished round should tell the same story. */
function finalChecks(gid, players) {
    const states = players.map((p) => p.lastState).filter(Boolean);
    const logs = new Set(states.map((s) => s.interrogation.log.length));
    const budgets = new Set(states.map((s) => s.interrogation.budget_used));
    const listed = new Set(states.map((s) => s.players.length));
    const ranked = new Set(states.map((s) => s.reveal?.ranking?.length ?? -1));

    if (logs.size > 1) anomaly("questions_log_differs", `g${gid}: ${[...logs]}`);
    if (budgets.size > 1) anomaly("budget_differs", `g${gid}: ${[...budgets]}`);
    if (listed.size > 1 || !listed.has(SIZE)) anomaly("players_list_differs", `g${gid}: ${[...listed]}`);
    if (ranked.size > 1 || !ranked.has(SIZE)) anomaly("ranking_incomplete", `g${gid}: ${[...ranked]}`);

    for (const s of states) {
        if (s.interrogation.budget_used > 3) anomaly("budget_overspent", `g${gid}: ${s.interrogation.budget_used}`);
    }
}

/* ------------------------------------------------------------------- mysql */

let statusCalls = 0;

function mysqlStatus() {
    if (!args["mysql-bin"]) return null;
    statusCalls++;

    try {
        const out = execFileSync(String(args["mysql-bin"]), [
            ...String(args["mysql-args"] ?? "").split(/\s+/).filter(Boolean),
            "-N", "-e",
            "SHOW GLOBAL STATUS WHERE Variable_name IN ('Connections','Max_used_connections','Threads_connected','Aborted_connects');",
        ], { encoding: "utf8" });

        return Object.fromEntries(out.trim().split("\n").map((line) => line.split("\t")).map(([k, v]) => [k, Number(v)]));
    } catch {
        return null;
    }
}

/* -------------------------------------------------------------------- main */

async function main() {
    console.log(`Express load · ${SCENARIO} · ${BASES.length} server(s) · groups of ${SIZE}\n`);

    const before = mysqlStatus();
    const callsAtStart = statusCalls;
    let peakThreads = before?.Threads_connected ?? 0;
    const sampler = setInterval(() => {
        const now = mysqlStatus();
        if (now) peakThreads = Math.max(peakThreads, now.Threads_connected);
    }, 5000);

    const started = Date.now();
    let gid = 0;
    const running = [];

    if (SCENARIO === "burst") {
        for (let wave = 1; wave <= WAVES; wave++) {
            console.log(`wave ${wave}/${WAVES}: ${GROUPS} groups (${GROUPS * SIZE} players) at once`);
            const batch = Array.from({ length: GROUPS }, () => runGroup(++gid));
            await Promise.all(batch);
            if (wave < WAVES) await sleep(WAVE_GAP_S * 1000);
        }
    } else {
        // Poisson arrivals: exponential gaps between groups.
        const endAt = Date.now() + MINUTES * 60 * 1000;
        console.log(`arrivals: ~${ARRIVALS_PER_MIN} groups/min (~${Math.round(ARRIVALS_PER_MIN * SIZE * 60)} players/hour) for ${MINUTES} min`);

        while (Date.now() < endAt) {
            running.push(runGroup(++gid));
            await sleep(-Math.log(1 - Math.random()) / ARRIVALS_PER_MIN * 60 * 1000);
        }

        await Promise.all(running);
    }

    clearInterval(sampler);
    const seconds = (Date.now() - started) / 1000;
    const after = mysqlStatus();
    const monitorConnections = statusCalls - callsAtStart; // the sampler and the closing read each opened one

    report({ seconds, before, after, peakThreads, groups: gid, monitorConnections });
}

function report({ seconds, before, after, peakThreads, groups, monitorConnections = 0 }) {
    const lines = [];
    const rows = [...metrics].sort(([a], [b]) => a.localeCompare(b)).map(([label, m]) => {
        const sorted = [...m.ms].sort((a, b) => a - b);
        const bad = [...m.codes].filter(([code]) => !(typeof code === "number" && code >= 200 && code < 400) && code !== 422 && code !== 403).reduce((n, [, c]) => n + c, 0);

        return { label, n: sorted.length, bad, p50: percentile(sorted, 50), p95: percentile(sorted, 95), p99: percentile(sorted, 99), max: sorted.at(-1) ?? 0, codes: Object.fromEntries(m.codes) };
    });

    lines.push("endpoint".padEnd(14) + "n".padStart(7) + "bad".padStart(6) + "p50".padStart(8) + "p95".padStart(8) + "p99".padStart(8) + "max".padStart(8) + "  (ms)");
    for (const r of rows) {
        const odd = Object.entries(r.codes).filter(([code]) => !["200", "302"].includes(code));

        lines.push(r.label.padEnd(14) + String(r.n).padStart(7) + String(r.bad).padStart(6) + r.p50.toFixed(0).padStart(8) + r.p95.toFixed(0).padStart(8) + r.p99.toFixed(0).padStart(8) + r.max.toFixed(0).padStart(8)
            + (odd.length ? `   status: ${odd.map(([code, n]) => `${code}×${n}`).join(" ")}` : ""));
    }

    const total = rows.reduce((n, r) => n + r.n, 0);
    const bad = rows.reduce((n, r) => n + r.bad, 0);
    const peakRps = Math.max(0, ...perSecond.values());
    const poll = rows.find((r) => r.label === "poll");
    const pages = rows.filter((r) => r.label.startsWith("page_"));
    const pageP95 = Math.max(0, ...pages.map((r) => r.p95));

    lines.push("");
    lines.push(`requests: ${total} in ${seconds.toFixed(0)} s = ${(total / seconds).toFixed(1)} req/s on average, peak ${peakRps} req/s; failed: ${bad} (${((bad / Math.max(1, total)) * 100).toFixed(2)} %)`);
    lines.push(`rounds: ${rounds.started} started, ${rounds.completed} completed (${rounds.durations.length ? (rounds.durations.reduce((a, b) => a + b, 0) / rounds.durations.length).toFixed(0) : 0} s on average)`);

    let connections = null;
    if (before && after) {
        connections = after.Connections - before.Connections - monitorConnections; // minus the monitor's own
        const perGroup = connections / Math.max(1, rounds.started);

        lines.push("");
        lines.push(`MySQL new connections: ${connections} for ${rounds.started} groups = ${perGroup.toFixed(1)} per group (${(perGroup / SIZE).toFixed(1)} per player)`);
        lines.push(`  peak simultaneous connections seen: ${peakThreads} · max used: ${after.Max_used_connections} · aborted: ${after.Aborted_connects - before.Aborted_connects}`);
        lines.push(`  at 22 groups/hour (~${22 * SIZE} players): ${(perGroup * 22).toFixed(0)} connections/hour · at 40 groups/hour: ${(perGroup * 40).toFixed(0)}  (budget: 500/hour for the whole site)`);
    }

    lines.push("");
    if (anomalies.size === 0) lines.push("consistency: no anomalies (every phone told the same story)");
    else for (const [kind, details] of anomalies) lines.push(`ANOMALY ${kind} ×${details.length}: ${details.slice(0, 4).join(" | ")}`);

    const checks = [
        ["no failed requests (<1 %)", bad / Math.max(1, total) < 0.01],
        ["poll p95 < 500 ms", (poll?.p95 ?? 0) < 500],
        ["pages p95 < 1500 ms", pageP95 < 1500],
        ["every round completed", rounds.completed === rounds.started && rounds.started > 0],
        ["no anomalies", anomalies.size === 0],
    ];

    lines.push("");
    for (const [name, ok] of checks) lines.push(`${ok ? "PASS" : "FAIL"}  ${name}`);

    console.log("\n" + lines.join("\n"));

    if (args.out) {
        writeFileSync(String(args.out), JSON.stringify({ scenario: SCENARIO, size: SIZE, groups, seconds, rows, rounds, connections, anomalies: Object.fromEntries(anomalies), checks }, null, 2));
    }

    process.exit(checks.every(([, ok]) => ok) ? 0 : 1);
}

main();
