// Une LAT conserve trois tuyaux de 20 m, quels que soient les déplacements.
export const HOSE_SEGMENTS = 3;
export const HOSE_SEGMENT_METERS = 20;
export const HOSE_MAX_METERS = HOSE_SEGMENTS * HOSE_SEGMENT_METERS;
export const PIXELS_PER_METER = 20;

const EPSILON = 1e-7;
const copy = ({ x, y }) => ({ x, y });
const distance = (a, b) => Math.hypot(b.x - a.x, b.y - a.y);
const mix = (a, b, fraction) => ({ x: a.x + (b.x - a.x) * fraction, y: a.y + (b.y - a.y) * fraction });

function cleanPath(path) {
    const result = [];
    for (const p of path || []) {
        if (!Number.isFinite(p?.x) || !Number.isFinite(p?.y)) throw new TypeError('Coordonnées de tuyau invalides.');
        if (!result.length || distance(result[result.length - 1], p) > EPSILON) result.push(copy(p));
    }
    return result;
}

export function hoseLength(path) {
    let length = 0;
    for (let i = 1; i < (path?.length || 0); i++) length += distance(path[i - 1], path[i]);
    return length;
}

// Tendre le tuyau retire les anciennes positions et conserve les angles utiles.
// Chaque raccourci est validé contre les murs : l'origine ne change jamais.
function pullTight(path, isClear) {
    if (path.length < 2) return path.map(copy);
    const result = [copy(path[0])];
    for (let start = 0; start < path.length - 1;) {
        let end = path.length - 1;
        while (end > start + 1 && !isClear(path[start], path[end])) end--;
        result.push(copy(path[end]));
        start = end;
    }
    return cleanPath(result);
}

function prefix(path, budget) {
    const result = [copy(path[0])];
    let remaining = Math.max(0, budget);
    for (let i = 1; i < path.length && remaining > EPSILON; i++) {
        const length = distance(path[i - 1], path[i]);
        if (length <= remaining) {
            result.push(copy(path[i]));
            remaining -= length;
        } else {
            result.push(mix(path[i - 1], path[i], remaining / length));
            break;
        }
    }
    return cleanPath(result);
}

/**
 * Update a fixed-origin hose before committing an actor's movement.
 * Seed paths must have clear consecutive segments. isClear checks hose clearance.
 * reservedPixels accounts for the ground feed and ladder on another floor.
 * lengthPixels includes that reservation; pathLengthPixels only measures this path.
 * Inputs are never mutated. An empty path starts at target with no deployed hose.
 */
export function updateHosePath(path, target, {
    isClear = () => true,
    maxPixels = HOSE_MAX_METERS * PIXELS_PER_METER,
    reservedPixels = 0,
} = {}) {
    if (!Number.isFinite(target?.x) || !Number.isFinite(target?.y)) throw new TypeError('Position de lance invalide.');
    if (!Number.isFinite(maxPixels) || maxPixels < 0 || !Number.isFinite(reservedPixels) || reservedPixels < 0)
        throw new RangeError('Longueur de tuyau invalide.');
    const available = Math.max(0, maxPixels - reservedPixels);
    const base = pullTight(cleanPath(path), isClear);
    if (!base.length) base.push(copy(target));
    const previous = base[base.length - 1];
    let goal = copy(target);

    // Un déplacement refusé par un mur s'arrête sur son côté accessible.
    if (!isClear(previous, goal)) {
        let low = 0, high = 1;
        for (let i = 0; i < 36; i++) {
            const middle = (low + high) / 2;
            if (isClear(previous, mix(previous, goal, middle))) low = middle;
            else high = middle;
        }
        goal = mix(previous, goal, low);
    }

    const candidateAt = position => pullTight(cleanPath([...base, position]), isClear);
    let result = candidateAt(goal);
    if (hoseLength(result) > available) {
        if (hoseLength(base) > available) {
            // Une réservation accrue peut consommer une partie déjà déployée.
            // Reprendre le préfixe garde l'ancrage et tous les segments sûrs.
            result = prefix(result, available);
        } else {
            let low = 0, high = 1;
            let best = base;
            for (let i = 0; i < 36; i++) {
                const middle = (low + high) / 2;
                const next = candidateAt(mix(previous, goal, middle));
                if (hoseLength(next) <= available) { low = middle; best = next; }
                else high = middle;
            }
            result = best;
        }
    }
    const position = copy(result[result.length - 1]);
    const pathLengthPixels = hoseLength(result);
    return {
        path: result, position, pathLengthPixels,
        lengthPixels: pathLengthPixels + reservedPixels,
        limited: distance(position, target) > EPSILON || reservedPixels > maxPixels,
    };
}

/** Couplings at 20 m and 40 m measured from the complete line's fixed origin. */
export function hoseCouplings(path, { reservedPixels = 0 } = {}) {
    const result = [];
    let travelled = 0;
    const interval = HOSE_SEGMENT_METERS * PIXELS_PER_METER;
    for (let i = 1; i < (path?.length || 0); i++) {
        const a = path[i - 1], b = path[i], length = distance(a, b);
        if (length <= EPSILON) continue;
        for (let coupling = 1; coupling < HOSE_SEGMENTS; coupling++) {
            const at = coupling * interval - reservedPixels;
            if (at > travelled + EPSILON && at <= travelled + length + EPSILON) {
                result.push({ ...mix(a, b, Math.min(1, (at - travelled) / length)), meters: coupling * HOSE_SEGMENT_METERS });
            }
        }
        travelled += length;
    }
    return result;
}
