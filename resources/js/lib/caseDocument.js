/**
 * Splits one server-rendered case document into the sections a player can
 * open one at a time.
 *
 * Parsed with a detached <template>, never with a regex over the string.
 * Slicing "…<h2>" out of HTML text can cut through an open element and hand
 * React a fragment the parser then repairs into the wrong tree; moving
 * already-parsed nodes into a fresh <div> and reading its innerHTML cannot,
 * because the repair happened before the split. That guarantee is the whole
 * reason this lives in its own file instead of being a split() at the call
 * site.
 *
 * Cutting on headings rather than on <hr>: every section in every file under
 * Cases/*\/content opens with a "## ", but the "---" rules are not authored
 * reliably (sobre-1.md has two sections with no rule between them), and
 * suspect testimonies have no rules at all. Headings are the contract.
 */

const HEADING_TAG = /^H([1-6])$/;

/** 0 for anything that is not a heading element. */
function headingLevel(node) {
    if (node.nodeType !== 1) {
        return 0;
    }

    const match = HEADING_TAG.exec(node.tagName);

    return match ? Number(match[1]) : 0;
}

function isRule(node) {
    return node.nodeType === 1 && node.tagName === 'HR';
}

/**
 * Fragment-safe id from a Spanish heading: accents and em dashes out.
 *
 * NFD splits "ó" into "o" + a combining mark, and \p{Mn} (nonspacing mark)
 * then drops the mark — so "transcripción" slugs to "transcripcion" rather
 * than "transcripci-n", which is what the [^a-z0-9] pass below would give
 * on its own.
 */
function slugify(text, fallback) {
    const slug = text
        .normalize('NFD')
        .replace(/\p{Mn}/gu, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 48);

    return slug || fallback;
}

function countWords(text) {
    return text.split(/\s+/).filter(Boolean).length;
}

/** ~200 wpm in Spanish, floored at a minute so nothing reads "0 min". */
function readingMinutes(words) {
    return Math.max(1, Math.round(words / 200));
}

function previewOf(text) {
    const clean = text.replace(/\s+/g, ' ').trim();

    if (clean.length <= 130) {
        return clean;
    }

    return `${clean.slice(0, 130).replace(/\s+\S*$/, '')}…`;
}

/**
 * Moves `nodes` into a throwaway container and returns its HTML.
 *
 * Tables get wrapped here rather than in CSS because a scroll box has to be
 * an element: `.case-prose` cannot both style the table and be the thing
 * that scrolls without turning the whole document into a scroll container
 * (see the .case-scroll-x comment in app.css).
 */
function serialize(nodes) {
    const holder = document.createElement('div');

    nodes.forEach((node) => holder.appendChild(node));

    holder.querySelectorAll('table').forEach((table) => {
        const scroller = document.createElement('div');
        scroller.className = 'case-scroll-x';
        table.replaceWith(scroller);
        scroller.appendChild(table);
    });

    return holder.innerHTML;
}

function emptyDocument(idPrefix) {
    return {
        idPrefix,
        title: null,
        splitLevel: 0,
        words: 0,
        isEmpty: true,
        isSplit: false,
        pieces: [],
    };
}

/** One indivisible blob — the shape this module returns when there is
 *  nothing to navigate, so the caller renders exactly what it did before
 *  CaseDocument existed. */
function singleDocument(idPrefix, html) {
    return {
        idPrefix,
        title: null,
        splitLevel: 0,
        words: 0,
        isEmpty: false,
        isSplit: false,
        pieces: [
            {
                id: 'todo',
                anchorId: `${idPrefix}-todo`,
                panelId: `${idPrefix}-todo-panel`,
                title: null,
                preview: '',
                words: 0,
                minutes: 1,
                collapsible: false,
                html,
            },
        ],
    };
}

/**
 * @param {string} html Server-rendered CommonMark output.
 * @param {{ idPrefix?: string }} options
 */
export function splitCaseDocument(html, { idPrefix = 'doc' } = {}) {
    if (typeof html !== 'string' || html.trim() === '') {
        return emptyDocument(idPrefix);
    }

    // No DOM (node, a future SSR build): degrade to today's behaviour rather
    // than throwing. One blob is always a valid answer.
    if (typeof document === 'undefined') {
        return singleDocument(idPrefix, html);
    }

    const template = document.createElement('template');
    template.innerHTML = html;

    // CommonMark separates blocks with "\n", so the top level is littered
    // with whitespace-only text nodes. Comments go; real bare text stays.
    const nodes = Array.from(template.content.childNodes).filter(
        (node) => node.nodeType === 1 || (node.nodeType === 3 && node.textContent.trim() !== '')
    );

    const documentText = template.content.textContent.replace(/\s+/g, ' ').trim();

    if (nodes.length === 0 || documentText === '') {
        return emptyDocument(idPrefix);
    }

    // Cut on the shallowest heading that actually repeats. sobre-1 opens with
    // a lone <h1>SOBRE 1</h1> over three <h2> sections: cutting on <h1> would
    // give one piece, so that <h1> becomes the document's own title instead.
    // sobre-2 has no <h1> at all — the ---block carrying it is excluded
    // server-side as a gallery duplicate.
    const counts = [0, 0, 0, 0, 0, 0, 0];
    nodes.forEach((node) => {
        counts[headingLevel(node)] += 1;
    });

    let splitLevel = 0;

    for (let level = 1; level <= 6; level += 1) {
        if (counts[level] >= 2) {
            splitLevel = level;
            break;
        }
    }

    if (splitLevel === 0) {
        for (let level = 1; level <= 6; level += 1) {
            if (counts[level] === 1) {
                splitLevel = level;
                break;
            }
        }
    }

    if (splitLevel === 0) {
        // serialize(), not the raw html: a heading-less document still needs
        // its tables wrapped for scrolling, and this path is how inline
        // body_markdown events arrive.
        return singleDocument(idPrefix, serialize(nodes));
    }

    const groups = [];
    let lead = [];
    let current = null;

    nodes.forEach((node) => {
        if (headingLevel(node) === splitLevel) {
            // The heading element itself never enters the body: it becomes
            // the section's label, and rendering it in both places would
            // show every title twice.
            current = { heading: node, nodes: [] };
            groups.push(current);

            return;
        }

        (current ? current.nodes : lead).push(node);
    });

    // A single shallower heading ahead of the first section is the
    // document's title, not a section of its own.
    let title = null;

    if (lead.length > 0) {
        const level = headingLevel(lead[0]);

        if (level > 0 && level < splitLevel) {
            title = lead[0].textContent.trim();
            lead = lead.slice(1);
        }
    }

    // A rule sitting on a section boundary was the author's separator; the
    // sheet edge is that separator now. Rules inside a section are content.
    const trimRules = (list) => {
        const out = list.slice();

        while (out.length > 0 && isRule(out[0])) {
            out.shift();
        }

        while (out.length > 0 && isRule(out[out.length - 1])) {
            out.pop();
        }

        return out;
    };

    const pieces = [];
    const used = new Map();

    const push = ({ heading, nodes: body, collapsible }) => {
        const kept = trimRules(body);

        if (!heading && kept.length === 0) {
            return;
        }

        const label = heading ? heading.textContent.trim() : null;
        const bodyText = kept.map((node) => node.textContent).join(' ');
        const words = countWords(bodyText) + (label ? countWords(label) : 0);

        const base = label ? slugify(label, `s${pieces.length}`) : `s${pieces.length}`;
        const seen = (used.get(base) ?? 0) + 1;
        used.set(base, seen);
        const key = seen > 1 ? `${base}-${seen}` : base;

        pieces.push({
            id: key,
            anchorId: `${idPrefix}-${key}`,
            panelId: `${idPrefix}-${key}-panel`,
            title: label,
            preview: previewOf(bodyText),
            words,
            minutes: readingMinutes(words),
            collapsible: collapsible && Boolean(label),
            // Serialize LAST: it moves the nodes out of the fragment.
            html: serialize(kept),
        });
    };

    // Content before the first heading has no label, so it could never be
    // summarised in a collapsed row — it stays open, always.
    push({ heading: null, nodes: lead, collapsible: false });
    groups.forEach((group) => push({ ...group, collapsible: true }));

    if (pieces.length === 0) {
        return emptyDocument(idPrefix);
    }

    return {
        idPrefix,
        title,
        splitLevel,
        words: countWords(documentText),
        // Chrome is earned: one section is just a document.
        isSplit: pieces.filter((piece) => piece.collapsible).length >= 2,
        isEmpty: false,
        pieces,
    };
}
