export function nowInTimezone(timezone) {
    const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
        timeZone: timezone, year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23',
    }).formatToParts(new Date()).map(part => [part.type, part.value]));
    return `${parts.year}-${parts.month}-${parts.day} ${parts.hour}:${parts.minute}:${parts.second}`;
}

export function formatDateTime(value) {
    if (!value) return 'Время не указано';
    const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}:\d{2})(:\d{2})?/);
    return match ? `${match[3]}.${match[2]}.${match[1]} ${match[4]}${match[5] ?? ':00'}` : String(value);
}

export function parseDateTime(date, time) {
    const d = /^(\d{2})\.(\d{2})\.(\d{4})$/.exec(date);
    const t = /^(\d{2}):(\d{2})(?::(\d{2}))?$/.exec(time);
    if (!d || !t) return null;
    const [day, month, year] = d.slice(1).map(Number);
    if (year < 1000 || month < 1 || month > 12 || day < 1 || day > new Date(year, month, 0).getDate() || +t[1] > 23 || +t[2] > 59 || +(t[3] ?? 0) > 59) return null;
    return `${d[3]}-${d[2]}-${d[1]} ${t[1]}:${t[2]}:${t[3] ?? '00'}`;
}
