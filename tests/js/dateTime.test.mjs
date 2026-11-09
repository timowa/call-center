import {test} from 'node:test';
import assert from 'node:assert/strict';
import {formatDateTime, nowInTimezone, parseDateTime} from '../../resources/js/Utils/dateTime.js';

test('calendar accepts arbitrary chronology and preserves seconds', () => {
    assert.equal(parseDateTime('29.02.2024', '23:59:47'), '2024-02-29 23:59:47');
    assert.equal(parseDateTime('01.01.2030', '00:00'), '2030-01-01 00:00:00');
    assert.equal(formatDateTime('2026-11-09 00:00:07'), '09.11.2026 00:00:07');
    assert.equal(formatDateTime(null), 'Время не указано');
});

test('calendar rejects impossible dates and out-of-range times', () => {
    for (const [date, time] of [['29.02.2025','12:00'], ['31.04.2026','12:00'], ['01.01.2026','24:00'], ['01.01.2026','12:60'], ['01.01.2026','12:00:60'], ['', '12:00']]) {
        assert.equal(parseDateTime(date, time), null);
    }
});

test('automatic time follows application timezone across day boundary', () => {
    const RealDate = Date;
    globalThis.Date = class extends RealDate {
        constructor(...args) { super(...(args.length ? args : ['2026-11-08T17:00:07Z'])); }
    };
    try { assert.equal(nowInTimezone('Asia/Bangkok'), '2026-11-09 00:00:07'); }
    finally { globalThis.Date = RealDate; }
});
