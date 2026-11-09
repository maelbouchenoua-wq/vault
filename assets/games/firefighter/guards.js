const recordPrefix = 'vault.intervention.gardes.v1.';
const sessionRecords = new Map();
const pad = value => String(value).padStart(2, '0');

export function dailyGuard(date = new Date()) {
    const day = [date.getFullYear(), pad(date.getMonth() + 1), pad(date.getDate())].join('-');
    const label = date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' });
    return { seed: 'jour-' + day, label: 'Garde du ' + label };
}

export function newGuard(previousSeed = '') {
    let number = 1 + Math.floor(Math.random() * 9999);
    if ('libre-' + number === previousSeed) number = number % 9999 + 1;
    return { seed: 'libre-' + number, label: 'Garde libre nº ' + String(number).padStart(4, '0') };
}

export function formatTime(tenths) {
    const minutes = Math.floor(tenths / 600);
    const seconds = Math.floor(tenths / 10) % 60;
    return minutes + ' min ' + pad(seconds) + ',' + tenths % 10 + ' s';
}

export function bestTime(seed, team) {
    const key = recordPrefix + seed + '.' + team;
    const memory = sessionRecords.get(key) ?? Infinity;
    let stored;
    try { stored = Number(localStorage.getItem(key)); } catch { /* Stockage privé indisponible. */ }
    const best = Math.min(memory, Number.isSafeInteger(stored) && stored > 0 ? stored : Infinity);
    return Number.isFinite(best) ? best : null;
}

export function recordVictory(seed, team, tenths) {
    const previous = bestTime(seed, team);
    if (!Number.isSafeInteger(tenths) || tenths <= 0 || (previous !== null && previous <= tenths)) return previous;
    const key = recordPrefix + seed + '.' + team;
    sessionRecords.set(key, tenths);
    try { localStorage.setItem(key, String(tenths)); } catch { /* Le record reste disponible pour cette session. */ }
    return previous;
}
