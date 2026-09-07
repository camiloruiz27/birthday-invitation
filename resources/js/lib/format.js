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
