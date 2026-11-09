const houseWalls = [
    { x: 620, y: 170, w: 720, h: 16 }, { x: 620, y: 754, w: 720, h: 16 },
    { x: 1324, y: 170, w: 16, h: 600 }, { x: 620, y: 170, w: 16, h: 260 },
    { x: 620, y: 510, w: 16, h: 260 }, { x: 972, y: 186, w: 16, h: 228 },
    { x: 972, y: 526, w: 16, h: 228 }, { x: 636, y: 414, w: 116, h: 16 },
    { x: 828, y: 414, w: 284, h: 16 }, { x: 1188, y: 414, w: 136, h: 16 },
    { x: 636, y: 510, w: 116, h: 16 }, { x: 828, y: 510, w: 284, h: 16 },
    { x: 1188, y: 510, w: 136, h: 16 },
];
const sharedMap = {
    width: 1440, height: 900,
    truck: { x: 160, y: 595, w: 315, h: 142 },
    hydrant: { x: 170, y: 340 }, pump: { x: 190, y: 565 },
    equipment: { x: 505, y: 650 }, safeZone: { x: 480, y: 760, w: 130, h: 95 },
    division: { x: 550, y: 420 },
    rallyPoints: {
        chief: { x: 105, y: 560 }, driver: { x: 105, y: 610 },
        'bat-chief': { x: 125, y: 770 }, 'bat-partner': { x: 85, y: 810 },
        'bal-chief': { x: 85, y: 500 }, 'bal-partner': { x: 125, y: 460 },
    },
};
const houseRooms = [
    { id: 'nw', name: 'Salon', x: 636, y: 186, w: 336, h: 228, searchX: 790, searchY: 300 },
    { id: 'ne', name: 'Cuisine', x: 988, y: 186, w: 336, h: 228, searchX: 1150, searchY: 300 },
    { id: 'sw', name: 'Chambre', x: 636, y: 526, w: 336, h: 228, searchX: 790, searchY: 640 },
    { id: 'se', name: 'Bureau', x: 988, y: 526, w: 336, h: 228, searchX: 1150, searchY: 640 },
];

export const MAP = {
    ...sharedMap, id: 'habitation',
    building: { x: 620, y: 170, w: 720, h: 600 }, entry: { x: 620, y: 480 },
    walls: houseWalls,
    upperWalls: [{ x: 620, y: 430, w: 16, h: 80 }],
    rooms: [0, 1].flatMap((floor) => houseRooms.map((room) => ({
        ...room, id: `${floor}-${room.id}`, name: floor ? `${room.name} étage` : room.name,
        floor, team: floor ? 'BAL' : 'BAT',
    }))),
    ladder: { x: 585, y: 300, equipment: { x: 505, y: 560 }, landing: { x: 665, y: 300 } },
};

const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
const distance = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
const point = (p) => ({ x: p.x, y: p.y });
const clone = (value) => JSON.parse(JSON.stringify(value));
const JET_RANGE = 260;
const JET_ANGLE = 0.24;
const ROLES = [
    ['chief', 'Chef d’agrès', 'command'], ['driver', 'Conducteur', 'driver'],
    ['bat-chief', 'Chef BAT', 'BAT'], ['bat-partner', 'Équipier BAT', 'BAT'],
    ['bal-chief', 'Chef BAL', 'BAL'], ['bal-partner', 'Équipier BAL', 'BAL'],
];

// Coupe un segment contre un obstacle rectangulaire.
function intersects(from, to, rectangle, margin = 0) {
    let low = 0;
    let high = 1;
    for (const [axis, size] of [['x', 'w'], ['y', 'h']]) {
        const delta = to[axis] - from[axis];
        const min = rectangle[axis] - margin;
        const max = rectangle[axis] + rectangle[size] + margin;
        if (Math.abs(delta) < 1e-8) {
            if (from[axis] < min || from[axis] > max) return false;
        } else {
            const a = (min - from[axis]) / delta;
            const b = (max - from[axis]) / delta;
            low = Math.max(low, Math.min(a, b));
            high = Math.min(high, Math.max(a, b));
            if (low > high) return false;
        }
    }
    return high >= 0 && low <= 1;
}

export class FireMission {
    constructor({ random = Math.random } = {}) {
        this.scenario = { id: 'habitation', name: 'Feu d’habitation' };
        this._random = typeof random === 'function' ? random : Math.random;
        this.reset();
    }

    // Réinitialise le scénario et les six équipiers.
    reset() {
        this.map = clone(MAP);
        this.phase = 'arrival'; this.phaseTime = 0; this.time = 0; this.floor = 0; this.team = null;
        this.water = 100; this.health = 100; this.truckX = -400; this.spraying = false;
        this.supplyConnected = false; this.supplyHoseStarted = false;
        this.attackHoseStarted = false; this.hoseEstablished = false;
        this.divisionReady = false; this.ladderReady = false;
        this.supplyPath = []; this.hosePath = []; this.npcHoses = [];
        this._done = new Set(); this._driverStep = 0; this._driverWait = 0;
        this._npcStep = 0; this._npcWait = 0; this._missionTime = 0; this._rallyHold = 0;
        this.crew = ROLES.map(([id, role, team]) => ({ id, role, team,
            x: 140, y: 655, r: 13, dir: 0, walking: false, visible: false, floor: 0,
            activity: 'À bord', carrying: null, spraying: false, aim: { x: 0, y: 0 },
        }));
        this.player = this.crew.find((actor) => actor.id === 'bat-chief');
        this.rooms = this.map.rooms.map((room) => ({ ...room, searched: false }));
        this._makeObjectives(); this._makeTasks();
        this.aim = point(this.map.entry); this.message = 'Le FPT arrive. Les équipes attendent les ordres.';
        this.radio = 'Arrivée sur intervention.'; this.bubble = null; this.smoke = 0.8;
        this.dialogue = null; this._dialogueQueue = []; this._dialogueRemaining = 0; this._dialogueId = 0;
        this._spoken = new Set();
        return this;
    }

    _makeObjectives() {
        const makeFire = (id, x, y, floor, team, work, hp = 100) => ({ id, x, y, floor, team,
            hp, maxHp: hp, extinguished: false, seed: Number(this._random()) || 0, work });
        this.fires = [0, 1].flatMap((floor) => [
            makeFire(`${floor}-nw`, 710, 250, floor, floor ? 'BAL' : 'BAT', { x: 790, y: 300 }),
            makeFire(`${floor}-ne`, 1250, 270, floor, floor ? 'BAL' : 'BAT', { x: 1150, y: 300 }),
            makeFire(`${floor}-sw`, 880, 690, floor, floor ? 'BAL' : 'BAT', { x: 790, y: 640 }),
            makeFire(`${floor}-se`, 1240, 680, floor, floor ? 'BAL' : 'BAT', { x: 1150, y: 640 }),
        ]);
        this.victims = [
            { id: 'resident', roomId: '0-sw', x: 755, y: 650, floor: 0, team: 'BAT', kind: 'person' },
            { id: 'resident-upper', roomId: '1-ne', x: 1190, y: 320, floor: 1, team: 'BAL', kind: 'person' },
        ];
        this.victims = this.victims.map((victim) => ({ ...victim, visible: false, rescued: false, carried: false }));
    }

    _makeTasks() {
        const task = (id, label, team, type = 'manual') => ({ id, label, team, type, done: false });
        const attack = (team) => [task(`${team}-fires`, 'Éteindre les foyers du secteur', team, 'fires'),
            task(`${team}-search`, 'Reconnaître toutes les zones du secteur', team, 'search'),
            task(`${team}-rescue`, 'Évacuer les victimes du secteur', team, 'rescue')];
        const bat = [task('BAT-gear', 'Prendre la ligne au FPT', 'BAT'),
            task('BAT-line', 'Établir la ligne à l’entrée', 'BAT'), ...attack('BAT')];
        const bal = [task('BAL-division', 'Installer la division extérieure', 'BAL'), task('BAL-ladder-gear', 'Prendre l’échelle au FPT', 'BAL'),
            task('BAL-ladder', 'Dresser l’échelle à la façade', 'BAL'), ...attack('BAL')];
        this.tasks = [...bat, ...bal];
    }

    // Chaque étage conserve ses murs et ses accès.
    _obstacles(floor = this.floor) {
        return [...this.map.walls, ...(floor === 1 ? this.map.upperWalls : [this.map.truck])];
    }

    _blocked(x, y, radius, floor) {
        if (x < radius || y < radius || x > 1440 - radius || y > 900 - radius) return true;
        const building = this.map.building;
        if (floor === 1 && building && (x < building.x + 16 + radius || y < building.y + 16 + radius
            || x > building.x + building.w - 16 - radius || y > building.y + building.h - 16 - radius)) return true;
        return this._obstacles(floor).some((wall) => {
            const nx = clamp(x, wall.x, wall.x + wall.w);
            const ny = clamp(y, wall.y, wall.y + wall.h);
            return (x - nx) ** 2 + (y - ny) ** 2 < radius ** 2 - 1e-6;
        });
    }

    _clear(from, to, floor, radius = 0) {
        return !this._obstacles(floor).some((wall) => intersects(from, to, wall, radius));
    }

    // Une grille évite les cloisons pour les déplacements autonomes.
    _route(actor, goal) {
        const radius = actor.r + 1;
        if (this._clear(actor, goal, actor.floor, radius)) return [point(goal)];
        const step = 20, cols = 72, rows = 45;
        const position = (id) => ({ x: (id % cols) * step + 10, y: Math.floor(id / cols) * step + 10 });
        const near = (p) => {
            let result = -1, best = Infinity;
            for (let y = Math.max(0, Math.floor(p.y / step) - 5); y <= Math.min(rows - 1, Math.floor(p.y / step) + 5); y++) {
                for (let x = Math.max(0, Math.floor(p.x / step) - 5); x <= Math.min(cols - 1, Math.floor(p.x / step) + 5); x++) {
                    const id = y * cols + x, q = position(id), d = distance(p, q);
                    if (d < best && !this._blocked(q.x, q.y, radius, actor.floor) && this._clear(p, q, actor.floor, radius)) { result = id; best = d; }
                }
            }
            return result;
        };
        const start = near(actor), end = near(goal);
        if (start < 0 || end < 0) return [];
        const queue = [start], previous = new Map([[start, -1]]);
        for (let head = 0; head < queue.length && !previous.has(end); head++) {
            const id = queue[head], p = position(id), cx = id % cols, cy = Math.floor(id / cols);
            for (const [dx, dy] of [[1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [-1, 1], [1, -1], [-1, -1]]) {
                const x = cx + dx, y = cy + dy, next = y * cols + x;
                if (x < 0 || y < 0 || x >= cols || y >= rows || previous.has(next)) continue;
                const q = position(next);
                if (this._blocked(q.x, q.y, radius, actor.floor) || !this._clear(p, q, actor.floor, radius)) continue;
                previous.set(next, id); queue.push(next);
            }
        }
        if (!previous.has(end)) return [];
        const path = [point(goal)];
        for (let id = end; id !== -1; id = previous.get(id)) path.unshift(position(id));
        const smooth = []; let from = point(actor);
        for (let index = 0; index < path.length;) {
            let last = index;
            while (last + 1 < path.length && this._clear(from, path[last + 1], actor.floor, radius)) last++;
            smooth.push(path[last]); from = path[last]; index = last + 1;
        }
        return smooth;
    }

    _walk(actor, goal, dt, speed = 190) {
        if (distance(actor, goal) < 3) { actor.walking = false; return true; }
        const key = `${goal.x},${goal.y},${actor.floor}`;
        if (actor._routeKey !== key) { actor._routeKey = key; actor._route = this._route(actor, goal); }
        const next = actor._route[0];
        if (!next) { actor.walking = false; return false; }
        const d = distance(actor, next), amount = Math.min(d, speed * dt);
        actor.dir = Math.atan2(next.y - actor.y, next.x - actor.x);
        actor.x += Math.cos(actor.dir) * amount; actor.y += Math.sin(actor.dir) * amount;
        actor.walking = amount > 0;
        if (amount >= d - 0.001) actor._route.shift();
        return distance(actor, goal) < 3;
    }

    _move(input, dt) {
        let x = clamp(Number(input.moveX) || 0, -1, 1), y = clamp(Number(input.moveY) || 0, -1, 1);
        const magnitude = Math.hypot(x, y);
        if (magnitude > 1) { x /= magnitude; y /= magnitude; }
        const speed = this.player.carrying ? 110 : 185;
        const before = point(this.player);
        for (const [axis, delta] of [['x', x * speed * dt], ['y', y * speed * dt]]) {
            const steps = Math.max(1, Math.ceil(Math.abs(delta) / 4));
            for (let i = 0; i < steps; i++) {
                const next = point(this.player); next[axis] += delta / steps;
                if (!this._blocked(next.x, next.y, this.player.r, this.floor)) this.player[axis] = next[axis];
            }
        }
        this.player.walking = distance(before, this.player) > 0.01;
        if (this.player.walking) this.player.dir = Math.atan2(y, x);
    }

    _enter(phase, message) {
        this.phase = phase; this.phaseTime = 0; this.message = message;
        if (phase === 'won' || phase === 'lost') {
            this.spraying = false;
            for (const actor of this.crew) { actor.walking = false; actor.spraying = false; }
        }
    }

    // Une réplique reste lisible, même si plusieurs événements arrivent ensemble.
    _talk(actorId, text) {
        const actor = this.crew.find((member) => member.id === actorId);
        if (!actor || typeof text !== 'string') return false;
        text = text.trim().slice(0, 140);
        if (!text || (this.dialogue?.actorId === actorId && this.dialogue.text === text)
            || this._dialogueQueue.some((line) => line.actorId === actorId && line.text === text)) return false;
        if (this._dialogueQueue.length >= 4) this._dialogueQueue.shift();
        this._dialogueQueue.push({ actorId, speaker: actor.role, text });
        if (!this.dialogue) this._nextDialogue();
        return true;
    }

    _talkOnce(event, actorId, text) {
        if (this._spoken.has(event)) return;
        this._spoken.add(event);
        this._talk(actorId, text);
    }

    _nextDialogue() {
        const line = this._dialogueQueue.shift();
        this.dialogue = line ? { id: ++this._dialogueId, ...line } : null;
        this.bubble = line ? { actorId: line.actorId, text: line.text } : null;
        this._dialogueRemaining = line ? clamp(2.1 + line.text.length * 0.025, 2.7, 5) : 0;
        if (line) this.radio = `${line.speaker} : ${line.text}`;
    }

    _tickDialogue(dt) {
        if (!this.dialogue) return;
        this._dialogueRemaining -= dt;
        if (this._dialogueRemaining <= 0) this._nextDialogue();
    }

    _finalTalk(text) {
        this._dialogueQueue = []; this.dialogue = null; this.bubble = null;
        this._talk('chief', text);
    }

    // Le choix du binôme lance une mission différente.
    chooseTeam(team) {
        if (this.phase !== 'orders' || !['BAT', 'BAL'].includes(team)) return false;
        this.team = team; this.player = this.crew.find((actor) => actor.id === `${team.toLowerCase()}-chief`);
        this.floor = this.player.floor;
        this.player.activity = 'Attend vos commandes';
        this._npcTeam = team === 'BAT' ? 'BAL' : 'BAT';
        this._npcLeader = this.crew.find((actor) => actor.id === `${this._npcTeam.toLowerCase()}-chief`);
        this._npcActions = this._buildActions(this._npcTeam);
        this._enter('mission', 'Rejoignez le premier repère et appuyez sur E.');
        this._dialogueQueue = []; this.dialogue = null; this.bubble = null;
        this._talk('chief', this._orders(team));
        this._talk('driver', 'Reçu. Je raccorde la borne puis la pompe.');
        this._talk(this.player.id, team === 'BAT' ? 'Bien reçu. Le BAT prend le rez-de-chaussée.' : 'Bien reçu. Le BAL prend le premier étage.');
        return true;
    }

    _orders(team) {
        return team === 'BAT' ? 'BAT, établissez la ligne et reconnaissez le rez-de-chaussée.'
            : 'BAL, division et échelle. Vous reconnaissez le premier étage.';
    }

    _mark(id) { this._done.add(id); this._syncTasks(); }

    _syncTasks() {
        for (const task of this.tasks) {
            task.done = task.type === 'fires' ? this.fires.filter((f) => f.team === task.team).every((f) => f.extinguished)
                : task.type === 'search' ? this.rooms.filter((r) => r.team === task.team).every((r) => r.searched)
                    : task.type === 'rescue' ? this.victims.filter((v) => v.team === task.team).every((v) => v.rescued)
                        : this._done.has(task.id);
        }
    }

    _lineReady(team) {
        if (team === 'BAT') return this._done.has('BAT-line');
        return this.divisionReady && this.ladderReady;
    }

    // L’équipier proche soutient le jet et protège la progression.
    _supported(actor) {
        return this.crew.some((member) => member !== actor && member.team === actor.team
            && member.floor === actor.floor && member.visible && distance(member, actor) < 90);
    }

    _search(room) {
        room.searched = true;
        const leaderId = `${room.team.toLowerCase()}-chief`;
        this._talkOnce(`search-${room.team}`, leaderId, 'Première pièce reconnue. Nous poursuivons.');
        for (const victim of this.victims.filter((v) => v.roomId === room.id)) {
            victim.visible = true;
            this._talkOnce(`found-${victim.id}`, leaderId, 'Une victime est localisée. Nous préparons son évacuation.');
        }
        this._syncTasks();
    }

    _climb(actor, upstairs) {
        actor.floor = upstairs ? 1 : 0;
        const p = upstairs ? this.map.ladder.landing : this.map.ladder;
        actor.x = p.x; actor.y = p.y; actor._routeKey = null;
        const partner = this.crew.find((a) => a.team === actor.team && a !== actor);
        partner.floor = actor.floor; partner.x = actor.x + (upstairs ? 26 : -28); partner.y = actor.y + 18; partner._routeKey = null;
        actor._hosePath = [point(p)]; actor._hoseFloor = actor.floor;
        if (actor === this.player) { this.floor = actor.floor; this.hosePath = [point(p)]; }
    }

    _carry(actor, victim) {
        actor.carrying = victim.id; victim.carried = true; victim.visible = true;
        this._talkOnce(`carry-${victim.id}`, `${actor.team.toLowerCase()}-partner`, 'Je sécurise le chemin. On la ramène ensemble.');
    }

    _evacuate(actor) {
        const zone = this.map.safeZone;
        if (!actor.carrying || actor.floor !== 0 || actor.x < zone.x || actor.x > zone.x + zone.w || actor.y < zone.y || actor.y > zone.y + zone.h) return;
        const victim = this.victims.find((v) => v.id === actor.carrying);
        victim.rescued = true; victim.carried = false; victim.x = zone.x + 25; victim.y = zone.y + 25 + this.victims.filter((v) => v.rescued).length * 22; victim.floor = 0;
        actor.carrying = null; this._syncTasks();
        this._talkOnce(`rescued-${victim.id}`, actor.id, 'Victime à l’abri dans la zone de secours.');
    }

    _interact() {
        const p = this.player, near = (q, radius = 54) => distance(p, q) <= radius;
        const mark = (id, message) => { this._mark(id); this.message = message; this._talk(p.id, message); };
        if (this.team === 'BAL' && this.ladderReady) {
            if (this.floor === 1 && near(this.map.ladder.landing, 48)) { this._climb(p, false); this.message = 'Retour au sol par l’échelle.'; return; }
            if (this.floor === 0 && near(this.map.ladder, 48)) { this._climb(p, true); this.message = 'Vous êtes au premier étage.'; return; }
        }
        if (p.carrying) { this.message = 'Rejoignez la zone sûre ; l’évacuation y est automatique.'; return; }
        if (this.team === 'BAT') {
            if (!this._done.has('BAT-gear')) {
                if (near(this.map.equipment)) {
                    this.attackHoseStarted = true;
                    mark('BAT-gear', 'Ligne prise. Rejoignez le repère d’attaque.');
                }
                else this.message = 'Prenez d’abord la ligne au repère du FPT.';
                return;
            }
            if (!this._done.has('BAT-line')) {
                if (!near(this.map.entry)) this.message = 'Rejoignez le repère d’attaque avant d’établir la ligne.';
                else if (!this.supplyConnected) this.message = 'Le conducteur raccorde encore la pompe.';
                else {
                    this.hoseEstablished = true;
                    this.hosePath = [point(this.map.equipment), ...this._route({ ...p, x: this.map.equipment.x, y: this.map.equipment.y }, this.map.entry)];
                    mark('BAT-line', 'Ligne établie. Visez les foyers et maintenez le jet.');
                }
                return;
            }
        } else {
            if (!this.divisionReady) {
                if (near(this.map.division)) { this.divisionReady = true; mark('BAL-division', 'Division installée. Prenez maintenant l’échelle.'); }
                else this.message = 'Installez d’abord la division au repère extérieur.';
                return;
            }
            if (!this._done.has('BAL-ladder-gear')) {
                if (near(this.map.ladder.equipment)) mark('BAL-ladder-gear', 'Échelle prise. Rejoignez la façade.');
                else this.message = 'Prenez l’échelle au repère proche du FPT.';
                return;
            }
            if (!this.ladderReady) {
                if (near(this.map.ladder)) { this.ladderReady = true; this.hoseEstablished = true; mark('BAL-ladder', 'Échelle dressée. E au pied pour monter ou au palier pour descendre.'); }
                else this.message = 'Dressez l’échelle au repère de façade.';
                return;
            }
        }
        const victim = this.victims.find((v) => v.team === this.team && v.floor === this.floor && v.visible && !v.rescued && !v.carried && near(v, 54));
        if (victim) { this._carry(p, victim); this.message = 'Victime prise en charge. Rejoignez la zone sûre.'; return; }
        const room = this.rooms.find((r) => r.team === this.team && r.floor === this.floor && !r.searched && near({ x: r.searchX, y: r.searchY }, 66));
        if (room) {
            const danger = this.fires.some((f) => !f.extinguished && f.floor === room.floor && f.x >= room.x && f.x <= room.x + room.w && f.y >= room.y && f.y <= room.y + room.h);
            if (danger) this.message = 'Éteignez le foyer de cette zone avant la recherche.';
            else { this._search(room); this.message = `Reconnaissance : ${room.name}. ${this.victims.some((v) => v.roomId === room.id) ? 'Une victime est repérée ; E près d’elle.' : 'Personne à évacuer ici.'}`; }
            return;
        }
        this.message = this.floor === 0 && this.team === 'BAL'
            ? 'Montez par l’échelle pour rejoindre votre secteur.' : 'Rejoignez une zone non reconnue ou une victime repérée.';
    }

    // Les jets respectent leur portée, leur cône et les murs.
    _jet(actor, target, dt, rate) {
        const angle = Math.atan2(target.y - actor.y, target.x - actor.x);
        for (const fire of this.fires) {
            if (fire.extinguished || fire.floor !== actor.floor || distance(actor, fire) > JET_RANGE) continue;
            const delta = Math.atan2(Math.sin(Math.atan2(fire.y - actor.y, fire.x - actor.x) - angle), Math.cos(Math.atan2(fire.y - actor.y, fire.x - actor.x) - angle));
            if (Math.abs(delta) > JET_ANGLE || !this._clear(actor, fire, actor.floor)) continue;
            fire.hp = Math.max(0, fire.hp - rate * dt);
            if (fire.hp === 0) { fire.extinguished = true; this._syncTasks(); }
        }
    }

    _buildActions(team) {
        const a = [], add = (kind, goal, extra = {}) => a.push({ kind, goal, ...extra });
        if (team === 'BAT') { add('gear', this.map.equipment, { task: 'BAT-gear' }); add('line', this.map.entry, { task: 'BAT-line' }); }
        else {
            add('division', this.map.division, { task: 'BAL-division' });
            add('work', this.map.ladder.equipment, { task: 'BAL-ladder-gear' });
            add('ladder', this.map.ladder, { task: 'BAL-ladder' }); add('up', this.map.ladder);
        }
        for (const fire of this.fires.filter((f) => f.team === team)) add('fire', fire.work, { fire });
        for (const room of this.rooms.filter((r) => r.team === team)) add('search', { x: room.searchX, y: room.searchY }, { room });
        for (const victim of this.victims.filter((v) => v.team === team)) {
            add('take', victim, { victim });
            if (victim.floor === 1) add('down', this.map.ladder.landing);
            add('evacuate', { x: this.map.safeZone.x + 65, y: this.map.safeZone.y + 48 });
        }
        add('finish', team === 'BAT' ? { x: 540, y: 715 } : { x: 555, y: 390 });
        return a;
    }

    _driver(dt) {
        const actor = this.crew.find((a) => a.id === 'driver');
        if (this._driverStep >= 2) { actor.activity = 'Surveille la pompe et l’alimentation'; actor.walking = false; return; }
        actor.activity = this._driverStep === 0 ? 'Raccorde la borne' : 'Met la pompe en service';
        const goal = this._driverStep === 0 ? this.map.hydrant : this.map.pump;
        if (!this._walk(actor, goal, dt, 165)) { if (this.supplyHoseStarted) this._append(this.supplyPath, actor); return; }
        this._driverWait += dt;
        if (this._driverWait < 1.2) return;
        this._driverWait = 0;
        if (this._driverStep === 0) { this.supplyHoseStarted = true; this.supplyPath = [point(this.map.hydrant)]; }
        else { this.supplyConnected = true; this._append(this.supplyPath, actor); this._talkOnce('water-ready', 'driver', 'La pompe est alimentée. Vous avez de l’eau.'); }
        this._driverStep++;
    }

    _append(path, actor) {
        if (!path.length || distance(path[path.length - 1], actor) > 10) path.push(point(actor));
    }

    _npc(dt) {
        const actor = this._npcLeader;
        actor.spraying = false;
        const action = this._npcActions[this._npcStep];
        if (!action) { actor.walking = false; actor.activity = 'Secteur traité, reste disponible'; return; }
        if (this._missionTime < 2) { actor.activity = 'Reçoit son ordre'; return; }
        actor.activity = { gear: 'Prend la ligne', line: 'Établit la ligne', division: 'Installe la division',
            ladder: 'Dresse l’échelle', up: 'Monte à l’étage', down: 'Redescend avec la victime',
            fire: 'Attaque un foyer', search: 'Reconnaît les lieux', take: 'Prend une victime',
            evacuate: 'Évacue vers la zone sûre', work: 'Prépare le soutien', finish: 'Rend compte au chef' }[action.kind];
        if (!this._walk(actor, action.goal, dt, actor.carrying ? 125 : 205)) return;
        if (action.kind === 'line' && !this.supplyConnected) return;
        if (action.kind === 'fire') {
            if (!action.fire.extinguished) {
                actor.spraying = true; actor.aim = point(action.fire);
                this._jet(actor, actor.aim, dt, this.supplyConnected ? (this._supported(actor) ? 38 : 31) : 20);
                return;
            }
        }
        this._npcWait += dt;
        if (this._npcWait < (['ladder', 'search'].includes(action.kind) ? 1.3 : 0.7)) return;
        this._npcWait = 0;
        if (action.kind === 'gear') {
            this.attackHoseStarted = true;
        }
        if (action.kind === 'line') {
            this.hoseEstablished = true;
            actor._hosePath = [point(this.map.equipment), ...this._route({ ...actor, ...point(this.map.equipment) }, this.map.entry)];
            actor._hoseFloor = actor.floor;
        }
        if (action.kind === 'division') this.divisionReady = true;
        if (action.kind === 'ladder') { this.ladderReady = true; this.hoseEstablished = true; }
        if (action.kind === 'up') this._climb(actor, true);
        if (action.kind === 'down') this._climb(actor, false);
        if (action.kind === 'search') this._search(action.room);
        if (action.kind === 'take') this._carry(actor, action.victim);
        if (action.kind === 'evacuate') this._evacuate(actor);
        if (action.task) this._mark(action.task);
        this._npcStep++;
    }

    _followers(dt) {
        for (const team of ['BAT', 'BAL']) {
            const leader = this.crew.find((a) => a.id === `${team.toLowerCase()}-chief`);
            const partner = this.crew.find((a) => a.id === `${team.toLowerCase()}-partner`);
            partner.activity = leader.carrying ? 'Aide à l’évacuation' : leader.spraying || (leader === this.player && this.spraying) ? 'Soutient le porte-lance' : 'Suit et assiste son chef';
            if (partner.floor !== leader.floor) continue;
            if (distance(partner, leader) > 58) {
                this._talkOnce(`follow-${team}`, partner.id, 'Je te suis. Je soutiens ta progression.');
                if (!partner._followGoal || distance(partner._followGoal, leader) > 32) partner._followGoal = point(leader);
                this._walk(partner, partner._followGoal, dt, leader.carrying ? 132 : 215);
            }
            else partner.walking = false;
        }
    }

    update(delta, input = {}) {
        const dt = clamp(Number(delta) || 0, 0, 0.1);
        if (!dt || this.phase === 'won' || this.phase === 'lost') return;
        this.time += dt; this.phaseTime += dt;
        this._tickDialogue(dt);
        if (this.phase === 'arrival') {
            this._talkOnce('received', 'chief', 'Reçu : feu d’habitation. Nous arrivons.');
            this.truckX = -400 + (this.map.truck.x + 400) * Math.min(1, this.phaseTime / 4);
            if (this.phaseTime >= 4) { this.truckX = this.map.truck.x; this._enter('unload', 'Les six équipiers descendent du FPT.'); this._talkOnce('rally', 'chief', 'À vos rangs ! BAT au sud, BAL au nord.'); }
            return;
        }
        if (this.phase === 'unload' || this.phase === 'rally') {
            let arrived = 0;
            for (let i = 0; i < this.crew.length; i++) {
                const actor = this.crew[i];
                if (this.phase === 'unload' && this.phaseTime < i * 0.42) continue;
                actor.visible = true; actor.activity = 'Rejoint le rassemblement';
                if (this._walk(actor, this.map.rallyPoints[actor.id], dt, 140)) { actor.activity = 'Écoute le chef d’agrès'; arrived++; }
            }
            if (this.phase === 'unload' && this.phaseTime >= 2.6) this._enter('rally', 'BAT au sud, BAL au nord. Le chef prépare les ordres.');
            if (arrived === 6) {
                if (this._rallyHold === 0) this._talkOnce('assembled', 'chief', 'Tout le monde est là. Écoutez les ordres.');
                this._rallyHold += dt;
                if (this._rallyHold >= 3) { this._enter('orders', 'Choisissez BAT ou BAL pour recevoir votre mission.'); this._talkOnce('orders', 'chief', 'BAT au RDC, BAL à l’étage. Conducteur, alimentation.'); }
            }
            return;
        }
        if (this.phase === 'orders') return;
        this._missionTime += dt;
        this._driver(dt); this._npc(dt);
        const chief = this.crew.find((a) => a.id === 'chief');
        chief.activity = 'Coordonne BAT, BAL et le conducteur'; chief.walking = false;
        this._move(input, dt);
        this.aim.x = Number.isFinite(input.aimX) ? input.aimX : this.aim.x;
        this.aim.y = Number.isFinite(input.aimY) ? input.aimY : this.aim.y;
        if (this.supplyConnected) this.water = Math.min(100, this.water + dt * 9);
        this.spraying = Boolean(input.spray && this._lineReady(this.team) && this.water > 0 && !this.player.carrying);
        if (this.spraying) {
            this.player.dir = Math.atan2(this.aim.y - this.player.y, this.aim.x - this.player.x);
            this.water = Math.max(0, this.water - dt * 19);
            this._jet(this.player, this.aim, dt, this._supported(this.player) ? 45 : 37);
        }
        this.player.activity = this.player.carrying ? 'Évacue une victime' : this.spraying ? 'Attaque au jet' : this.player.walking ? 'Progresse dans son secteur' : 'Attend votre action';
        if (input.interact) this._interact();
        if (this._lineReady(this.team)) this._append(this.hosePath, this.player);
        for (const actor of this.crew) {
            if (actor.carrying) {
                const victim = this.victims.find((v) => v.id === actor.carrying);
                victim.x = actor.x; victim.y = actor.y + 16; victim.floor = actor.floor;
            }
            this._evacuate(actor);
        }
        this._followers(dt);
        if (this._lineReady(this._npcTeam)) {
            if (!this._npcLeader._hosePath || this._npcLeader._hoseFloor !== this._npcLeader.floor) {
                this._npcLeader._hoseFloor = this._npcLeader.floor;
                this._npcLeader._hosePath = [point(this._npcLeader)];
            }
            this._append(this._npcLeader._hosePath, this._npcLeader);
        }
        this.npcHoses = [{ team: this._npcTeam, actorId: this._npcLeader.id, floor: this._npcLeader.floor,
            path: this._npcLeader._hosePath || [],
            spraying: this._npcLeader.spraying, x: this._npcLeader.x, y: this._npcLeader.y, aim: point(this._npcLeader.aim) }];
        for (const fire of this.fires) {
            if (!fire.extinguished && fire.floor === this.floor && distance(this.player, fire) < 78 && this._clear(this.player, fire, this.floor))
                this.health = Math.max(0, this.health - dt * 8 * (1 - distance(this.player, fire) / 100) * (this._supported(this.player) ? 0.8 : 1));
        }
        if (this.health > 0 && this.health < 40 && !this._spoken.has('heat-warning')) {
            this._dialogueQueue = []; this.dialogue = null; this.bubble = null;
            this._talkOnce('heat-warning', `${this.team.toLowerCase()}-partner`, 'Ça chauffe, recule !');
        }
        this.smoke = this.fires.filter((f) => f.floor === this.floor && !f.extinguished).reduce((sum, f) => sum + f.hp / f.maxHp, 0)
            / Math.max(1, this.fires.filter((f) => f.floor === this.floor).length) * 0.8;
        this._syncTasks();
        if (this.health <= 0) { this._enter('lost', 'Votre santé est épuisée. Gardez vos distances face aux flammes.'); this._finalTalk('Repli immédiat. Nous reprenons depuis le FPT.'); }
        else if (this.tasks.every((task) => task.done)) { this._enter('won', 'Intervention terminée : les deux binômes ont traité leurs secteurs.'); this._finalTalk('Les deux secteurs sont sûrs. Bon travail à tous.'); }
    }

    getHUD() {
        const progress = ['arrival', 'unload'].includes(this.phase) ? 0 : this.phase === 'rally' ? 1 : this.phase === 'orders' ? 2 : 3;
        const task = this.tasks.find((t) => t.team === this.team && !t.done);
        return { progress, label: ['Départ', 'Rassemblement', 'Ordres', 'Intervention'][progress],
            objective: task ? task.label : this.team ? 'Attendre le compte rendu de l’autre binôme' : 'Choisir votre binôme après le rassemblement',
            hint: this.message, water: Math.round(this.water), health: Math.ceil(this.health),
            found: this.victims.filter((v) => v.visible).length, rescued: this.victims.filter((v) => v.rescued).length,
            totalVictims: this.victims.length, roomsSearched: this.rooms.filter((r) => r.searched).length,
            roomsTotal: this.rooms.length, time: this.time, scenarioName: this.scenario.name,
            team: this.team, floor: this.floor, radio: this.radio, dialogue: this.dialogue ? { ...this.dialogue } : null,
            crew: this.crew.map(({ id, role, activity }) => ({ id, role, activity })),
            tasks: this.tasks.map(({ id, label, done, team }) => ({ id, label, done, team })),
            terminal: this.phase === 'won' || this.phase === 'lost' ? this.phase : null };
    }
}
