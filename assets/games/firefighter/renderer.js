import { MAP } from './mission.js';

const P = Object.freeze({ ink: '#07141D', navy: '#142B39', slate: '#344B5D', grass: '#244D44',
    grassLight: '#315F4D', road: '#293643', white: '#E8F3ED', yellow: '#F7D35C',
    orange: '#FF9139', red: '#E8504B', redDark: '#A32E3A', blue: '#6BD4DC', steel: '#91AFB8' });
const GLYPHS = {
    A:'01110/10001/10001/11111/10001/10001/10001',B:'11110/10001/10001/11110/10001/10001/11110',
    C:'01111/10000/10000/10000/10000/10000/01111',D:'11110/10001/10001/10001/10001/10001/11110',
    E:'11111/10000/10000/11110/10000/10000/11111',F:'11111/10000/10000/11110/10000/10000/10000',
    G:'01111/10000/10000/10111/10001/10001/01111',H:'10001/10001/10001/11111/10001/10001/10001',
    I:'11111/00100/00100/00100/00100/00100/11111',J:'00111/00010/00010/00010/10010/10010/01100',
    K:'10001/10010/10100/11000/10100/10010/10001',L:'10000/10000/10000/10000/10000/10000/11111',
    M:'10001/11011/10101/10101/10001/10001/10001',N:'10001/11001/11001/10101/10011/10011/10001',
    O:'01110/10001/10001/10001/10001/10001/01110',P:'11110/10001/10001/11110/10000/10000/10000',
    Q:'01110/10001/10001/10001/10101/10010/01101',R:'11110/10001/10001/11110/10100/10010/10001',
    S:'01111/10000/10000/01110/00001/00001/11110',T:'11111/00100/00100/00100/00100/00100/00100',
    U:'10001/10001/10001/10001/10001/10001/01110',V:'10001/10001/10001/10001/10001/01010/00100',
    W:'10001/10001/10001/10101/10101/10101/01010',X:'10001/10001/01010/00100/01010/10001/10001',
    Y:'10001/10001/01010/00100/00100/00100/00100',Z:'11111/00001/00010/00100/01000/10000/11111',
    0:'01110/10001/10011/10101/11001/10001/01110',1:'00100/01100/00100/00100/00100/00100/01110',
    2:'01110/10001/00001/00010/00100/01000/11111',3:'11110/00001/00001/01110/00001/00001/11110',
    4:'00010/00110/01010/10010/11111/00010/00010',5:'11111/10000/10000/11110/00001/00001/11110',
    6:'01110/10000/10000/11110/10001/10001/01110',7:'11111/00001/00010/00100/01000/01000/01000',
    8:'01110/10001/10001/01110/10001/10001/01110',9:'01110/10001/10001/01111/00001/00001/01110',
    ':':'00000/00100/00100/00000/00100/00100/00000','-':'00000/00000/00000/11111/00000/00000/00000',
    '.':'00000/00000/00000/00000/00000/00100/00100','!':'00100/00100/00100/00100/00100/00000/00100',
    '?':'01110/10001/00001/00010/00100/00000/00100','/':'00001/00010/00010/00100/01000/01000/10000',
    '+':'00000/00100/00100/11111/00100/00100/00000',"'":'00100/00100/00000/00000/00000/00000/00000',
    '>':'10000/01000/00100/00010/00100/01000/10000',
    '%':'11001/11010/00100/01000/10110/00110/00000',
};
const n = (v, fallback = 0) => Number.isFinite(v) ? v : fallback;
const limit = (v, low, high) => Math.max(low, Math.min(high, v));
const floorOf = object => n(object?.floor);
const dimensions = rect => ({ ...rect, w: rect?.w ?? rect?.width ?? 0, h: rect?.h ?? rect?.height ?? 0 });
const colorOf = actor => actor?.team === 'BAL' ? P.blue : actor?.team === 'BAT' ? P.orange : actor?.team === 'driver' ? '#9CE3A0' : P.white;
const hash = (x, y) => ((Math.imul(x + 71, 374761393) ^ Math.imul(y + 29, 668265263)) >>> 0);

// Le monde est dessiné en basse résolution, puis agrandi sans lissage.
export class FireRenderer {
    constructor(context, assets = {}) {
        this.context = context;
        this.assets = assets;
        this.sprites = new Map();
        this.buffer = typeof OffscreenCanvas === 'function' ? new OffscreenCanvas(720, 450) : null;
        this.bufferContext = this.buffer?.getContext('2d') || null;
        this.ctx = context;
    }

    draw(mission, timeSeconds = 0) {
        if (!mission) return;
        const ctx = this.bufferContext || this.context;
        const map = mission.map || MAP;
        const time = n(timeSeconds, n(mission.time));
        const floor = floorOf(mission);
        this.ctx = ctx;
        ctx.save();
        try {
            if (this.bufferContext) ctx.setTransform(0.5, 0, 0, 0.5, 0, 0);
            ctx.imageSmoothingEnabled = false;
            ctx.globalAlpha = 1;
            ctx.globalCompositeOperation = 'source-over';
            this._terrain(map, mission);
            this._interior(map, mission, floor);
            this._hoses(mission, floor);
            this._walls(map, floor);
            this._areaSmoke(map, mission, time, floor);
            this._mapObjects(map, mission, time, floor);
            this._fires(mission, time, floor);
            this._jets(map, mission, time, floor);
            this._victims(mission, time, floor);
            this._crew(mission, time, floor);
            this._bubble(mission, floor);
        } finally {
            ctx.restore();
        }
        if (this.bufferContext) {
            const target = this.context;
            target.save();
            try {
                target.imageSmoothingEnabled = false;
                target.drawImage(this.buffer, 0, 0, 720, 450, 0, 0, map.width || 1440, map.height || 900);
            } finally { target.restore(); }
        }
        this.ctx = this.context;
    }

    _box(x, y, w, h, color) {
        this.ctx.fillStyle = color;
        this.ctx.fillRect(Math.round(x / 2) * 2, Math.round(y / 2) * 2, Math.max(2, Math.round(w / 2) * 2), Math.max(2, Math.round(h / 2) * 2));
    }

    _panel(x, y, w, h, color, edge = P.ink, border = 2) {
        this._box(x, y, w, h, edge);
        this._box(x + border, y + border, w - border * 2, h - border * 2, color);
    }

    _line(points, color, width = 4, dash = []) {
        if (!points?.length) return;
        const ctx = this.ctx;
        ctx.save();
        ctx.strokeStyle = color;
        ctx.lineWidth = width;
        ctx.lineCap = 'square';
        ctx.lineJoin = 'bevel';
        ctx.setLineDash(dash);
        ctx.beginPath();
        ctx.moveTo(Math.round(points[0].x / 2) * 2, Math.round(points[0].y / 2) * 2);
        for (let i = 1; i < points.length; i++) ctx.lineTo(Math.round(points[i].x / 2) * 2, Math.round(points[i].y / 2) * 2);
        ctx.stroke();
        ctx.restore();
    }

    _text(value, x, y, color = P.white, scale = 1, align = 'left') {
        const text = String(value).normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[’]/g, "'").toUpperCase();
        const size = 2 * scale;
        const width = Math.max(0, text.length * 6 - 1) * size;
        const start = align === 'center' ? x - width / 2 : align === 'right' ? x - width : x;
        for (let letter = 0; letter < text.length; letter++) {
            const rows = GLYPHS[text[letter]]?.split('/');
            if (!rows) continue;
            rows.forEach((row, rowIndex) => {
                for (let column = 0; column < row.length; column++) {
                    if (row[column] === '1') this._box(start + letter * 6 * size + column * size, y + rowIndex * size, size, size, color);
                }
            });
        }
    }

    _tag(text, x, y, color = P.white, center = true) {
        const width = text.length * 12 + 16;
        const left = center ? x - width / 2 : x;
        this._box(left + 4, y + 4, width, 26, '#00000045');
        this._panel(left, y, width, 26, P.navy, color, 2);
        this._text(text, left + 8, y + 6, color);
    }

    _sprite(key, w, h, paint, x, y, mirror = false) {
        const ctx = this.ctx;
        let sprite = this.sprites.get(key);
        if (!sprite && typeof OffscreenCanvas === 'function') {
            sprite = new OffscreenCanvas(w, h);
            const spriteContext = sprite.getContext('2d');
            if (spriteContext) { spriteContext.imageSmoothingEnabled = false; paint(spriteContext); this.sprites.set(key, sprite); }
            else sprite = null;
        }
        const left = Math.round(x / 2) * 2;
        const top = Math.round(y / 2) * 2;
        ctx.save();
        try {
            ctx.translate(left + (mirror ? w * 2 : 0), top);
            if (mirror) ctx.scale(-1, 1);
            if (sprite) ctx.drawImage(sprite, 0, 0, w * 2, h * 2);
            else { ctx.scale(2, 2); paint(ctx); }
        } finally { ctx.restore(); }
    }

    _terrain(map) {
        this._box(0,0,1440,900,P.grass);
        for(let y=0;y<900;y+=32)for(let x=0;x<1440;x+=32){
            const seed=hash(x,y);
            if(seed%4===0){this._box(x+4+seed%17,y+8,4,2,P.grassLight);this._box(x+6+seed%17,y+6,2,4,'#3B7051');}
        }
        this._box(0,550,1440,350,P.road);this._box(0,550,1440,8,'#78918F');this._box(0,558,1440,4,P.ink);
        for(let x=26;x<1440;x+=118)this._box(x,870,58,6,'#B8C6B2');
        for(let i=0;i<24;i++)this._box(hash(i,49)%1400,574+hash(i,103)%302,10,2,'#3B4D56');
        this._panel(80,180,450,352,'#556D6A','#172F35',6);
        for(let y=192;y<526;y+=40)this._box(88,y,432,2,'#40595D');
        for(let x=90;x<525;x+=44)this._box(x,188,2,336,'#40595D');
        this._tree(40,100);this._tree(530,100);this._tree(1370,95);
        this._tag('RUE DES TILLEULS',90,112,'#9BC6AC',false);
    }

    _tree(x, y) {
        this._box(x + 6, y + 34, 50, 14, '#07141D55');
        this._box(x + 26, y + 16, 12, 38, '#6B4C42');
        this._box(x + 10, y - 6, 46, 36, '#143B36');
        this._box(x, y + 2, 66, 22, '#245D43');
        this._box(x + 12, y - 12, 42, 28, '#37784E');
        this._box(x + 20, y - 8, 16, 8, '#5A9A5F');
    }

    _interior(map,mission,floor) {
        const building=dimensions(map.building||MAP.building);
        this._box(building.x+8,building.y+10,building.w,building.h,'#07141D65');
        this._box(building.x,building.y,building.w,building.h,'#425665');
        const rooms=(mission.rooms||map.rooms||[]).filter(room=>floorOf(room)===floor);
        rooms.forEach((room,index)=>{
            const r=dimensions(room);
            this._box(r.x,r.y,r.w,r.h,['#45656B','#3D6257','#5C5354','#3D526A'][index%4]);
            for(let y=r.y+20;y<r.y+r.h-6;y+=32)this._box(r.x+6,y,r.w-12,2,'#203A4A35');
            this._text(room.name||'PIECE',Math.max(r.x+14,building.x+26),r.y+16,'#A9C9C5');
            this._furniture(r,index,floor);
            const doorY=r.y<430?r.y+r.h:r.y-16;
            this._box(room.searchX-38,doorY,76,16,'#889690');this._box(room.searchX-34,doorY+6,68,4,'#B5C9B7');
        });
        this._box(636,430,688,80,'#435765');
        for(let x=650;x<1310;x+=48)this._box(x,468,16,2,'#6B838A');
        this._tag(floor===1?'1ER ETAGE':'RDC',building.x+building.w/2,building.y-44,P.white);
    }

    _furniture(r, index, floor) {
        const x = r.x, y = r.y;
        if (index % 4 === 0) {
            this._box(x + 34, y + 92, 180, 100, '#294C54');
            this._panel(x + 32, y + 54, 108, 50, '#3E8B8D', P.ink, 4);
            this._box(x + 38, y + 60, 96, 16, '#63B2A3');
            this._box(x + 80, y + 78, 4, 20, '#214A59');
            this._panel(x + 46, y + 126, 72, 30, '#9B634B', P.ink, 4);
            this._box(x + 54, y + 132, 52, 4, '#CB9163');
            this._panel(x + 246, y + 72, 42, 58, '#436F84', P.ink, 4);
            this._plant(x + 294, y + 184);
        } else if (index % 4 === 1) {
            this._panel(x + 26, y + 52, 282, 34, '#82AAA5', P.ink, 4);
            this._panel(x + 276, y + 84, 32, 90, '#82AAA5', P.ink, 4);
            this._box(x + 40, y + 58, 52, 22, '#243843');
            for (let i = 0; i < 4; i++) this._box(x + 48 + i % 2 * 22, y + 62 + Math.floor(i / 2) * 10, 10, 6, '#AFC6BA');
            this._panel(x + 188, y + 58, 50, 22, '#35596A', '#B7D3CD', 2);
            this._panel(x + 28, y + 150, 80, 36, '#9A6B4B', P.ink, 4);
            this._box(x + 36, y + 156, 48, 4, '#CD935C');
        } else if (index % 4 === 2) {
            this._panel(x + 30, y + 56, 96, 128, '#935C48', P.ink, 4);
            this._box(x + 36, y + 62, 84, 112, '#C8D4C9');
            this._box(x + 42, y + 66, 72, 22, '#E8F3ED');
            this._box(x + 36, y + 96, 84, 78, floor ? '#4E8EA6' : '#668C70');
            this._box(x + 36, y + 102, 84, 6, '#99C1B3');
            this._panel(x + 244, y + 152, 58, 44, '#885B45', P.ink, 4);
            this._plant(x + 288, y + 72);
        } else {
            this._panel(x + 30, y + 58, 100, 42, '#9D664C', P.ink, 4);
            this._panel(x + 52, y + 62, 44, 26, '#6BD4DC', P.ink, 4);
            this._box(x + 64, y + 90, 24, 4, '#A8C7BC');
            this._panel(x + 250, y + 52, 48, 130, '#8D5A42', P.ink, 4);
            for (let row = 0; row < 4; row++) {
                for (let book = 0; book < 4; book++) this._box(x + 256 + book * 8, y + 58 + row * 30, 6, 20, ['#FF9139', '#6BD4DC', '#9AC77A', '#C8DBD1'][book]);
                this._box(x + 254, y + 80 + row * 30, 40, 4, '#422F30');
            }
        }
    }



    _plant(x, y) {
        this._box(x - 10, y + 4, 22, 14, '#9A644C');
        this._box(x - 14, y - 6, 30, 16, '#1E503E');
        this._box(x - 8, y - 12, 18, 18, '#4E9A5E');
        this._box(x - 2, y - 8, 8, 6, '#8FB665');
    }

    _walls(map, floor) {
        for (const value of [...(map.walls || []), ...(floor === 1 ? map.upperWalls || [] : [])]) {
            if (value.floor !== undefined && floorOf(value) !== floor) continue;
            const wall = dimensions(value);
            this._box(wall.x + 6, wall.y + 8, wall.w, wall.h, '#07141D65');
            this._panel(wall.x, wall.y, wall.w, wall.h, '#8E5948', P.ink, 2);
            if (wall.w >= wall.h) {
                this._box(wall.x + 2, wall.y + 2, wall.w - 4, 4, '#D18A60');
                for (let x = wall.x + 22; x < wall.x + wall.w - 4; x += 28) this._box(x, wall.y + 6, 2, wall.h - 8, '#643D3D');
            } else {
                this._box(wall.x + 2, wall.y + 2, 4, wall.h - 4, '#D18A60');
                for (let y = wall.y + 22; y < wall.y + wall.h - 4; y += 24) this._box(wall.x + 6, y, wall.w - 8, 2, '#643D3D');
            }
        }
        if (map.building) {
            for (const fraction of [0.17, 0.58, 0.84]) {
                const x = map.building.x + map.building.w * fraction;
                this._panel(x, map.building.y + 2, 48, 12, '#67BCC7', P.ink, 2);
                this._box(x + 22, map.building.y + 3, 4, 10, '#D6EADC');
            }
        }
    }

    _hoses(mission, floor) {
        const hose = (path, color) => {
            if (!Array.isArray(path) || path.length < 2) return;
            this._line(path, P.ink, 10);
            this._line(path, color, 6);
            this._line(path, '#E8F3ED70', 2);
        };
        if (floor === 0) hose(mission.supplyPath, P.blue);
        hose(mission.hosePath, P.yellow);
        for (const item of mission.npcHoses || []) {
            if (!Array.isArray(item) && item.floor !== undefined && floorOf(item) !== floor) continue;
            hose(Array.isArray(item) ? item : item.path, item.team === 'BAL' ? '#C9E3A3' : '#ECB252');
        }
    }

    _truck(x, y, time, moving) {
        this._box(x + 8, y + 130, 316, 24, '#07141D65');
        this._sprite(`fpt-${moving ? Math.floor(time * 9) % 2 : 0}`, 160, 76, ctx => {
            const b = (x, y, w, h, c) => { ctx.fillStyle = c; ctx.fillRect(x, y, w, h); };
            b(2, 7, 155, 63, P.ink); b(5, 4, 148, 68, P.ink);
            b(7, 7, 100, 61, '#B62D3D'); b(8, 7, 98, 9, '#EF6953');
            for (const wx of [29, 127]) { b(wx - 9, 1, 20, 8, '#101E2D'); b(wx - 9, 67, 20, 9, '#101E2D'); b(wx - 5, 70, 12, 3, '#6A8291'); }
            b(9, 18, 94, 45, '#536D7A'); b(12, 20, 88, 40, '#B0C0BE');
            for (let row = 21; row < 60; row += 4) b(14, row, 84, 1, '#788F99');
            b(15, 25, 83, 19, '#435967'); b(15, 25, 83, 2, P.white); b(15, 42, 83, 2, P.white);
            for (let rung = 20; rung < 98; rung += 9) b(rung, 27, 2, 15, P.white);
            b(16, 49, 81, 11, '#93AAB1'); b(48, 53, 17, 2, '#2F4859');
            b(7, 62, 99, 4, P.yellow); b(5, 10, 6, 55, P.yellow);
            for (let row = 12; row < 61; row += 10) { b(5, row, 3, 5, P.red); b(8, row + 4, 3, 5, P.red); }
            b(110, 8, 39, 60, '#E44B48'); b(111, 8, 35, 5, '#FF8B61');
            b(113, 17, 18, 13, '#253D53'); b(115, 18, 14, 5, '#81D2DB');
            b(113, 48, 18, 13, '#253D53'); b(115, 49, 14, 5, '#81D2DB');
            b(135, 22, 17, 35, '#16344B'); b(137, 24, 12, 29, '#64AFC1'); b(138, 25, 3, 24, '#A8E4E2');
            b(115, 32, 15, 14, '#E8F3ED'); b(118, 35, 9, 2, '#C43B41'); b(118, 39, 9, 2, '#C43B41');
            b(151, 20, 7, 8, P.yellow); b(151, 53, 7, 8, P.yellow); b(151, 32, 7, 17, '#BCC8BD');
            b(135, 7, 4, 10, P.ink); b(137, 4, 12, 3, '#718E9C');
            b(135, 61, 4, 10, P.ink); b(137, 70, 12, 3, '#718E9C');
            b(115, 12, 13, 4, '#3E90C6'); b(115, 62, 13, 4, '#3E90C6');
            if (moving && Math.floor(time * 9) % 2) { b(26, 71, 4, 2, '#B2C5C7'); b(124, 3, 5, 2, '#B2C5C7'); }
        }, x, y - 4);
        const pulse = Math.floor(time * 3) % 2;
        this._box(x + 232 + pulse * 8, y + 20, 12, 6, '#B8F7FD');
        this._box(x + 232 + (1 - pulse) * 8, y + 122, 12, 6, '#B8F7FD');
    }

    _pointList(value) {
        if (Array.isArray(value)) return value;
        if (!value || typeof value !== 'object') return [];
        return Object.entries(value).filter(([, point]) => point && Number.isFinite(point.x)).map(([id, point]) => ({ ...point, id }));
    }

    _mapObjects(map,mission,time,floor) {
        if(floor===0){
            const zone=dimensions(map.safeZone||MAP.safeZone);
            this._panel(zone.x,zone.y,zone.w,zone.h,'#285A4A','#B0E2A3',4);
            this._text('SECOURS',zone.x+zone.w/2,zone.y+8,'#C8EFBB',1,'center');
            this._box(zone.x+zone.w/2-3,zone.y+zone.h-29,6,22,'#85BC8A');
            this._box(zone.x+zone.w/2-11,zone.y+zone.h-21,22,6,'#85BC8A');
            const water=map.hydrant||MAP.hydrant;
            this._box(water.x-14,water.y-12,30,32,P.ink);this._box(water.x-10,water.y-16,22,34,'#D14B3E');
            this._box(water.x-8,water.y-22,18,8,P.orange);this._box(water.x-20,water.y-4,42,10,'#ED7951');
            this._box(water.x-16,water.y+16,34,6,'#91AAA2');
            const truck=map.truck||MAP.truck;
            this._truck(n(mission.truckX,truck.x),truck.y,time,mission.phase==='arrival');
            this._waterReserve(map,mission);
            if(mission.phase!=='arrival'){
                const pump=map.pump||MAP.pump;
                this._panel(pump.x-22,pump.y-20,44,34,'#92ADB2',P.ink,4);
                this._box(pump.x-12,pump.y-10,8,8,mission.supplyConnected?P.blue:'#42616E');
                this._box(pump.x+4,pump.y-10,8,8,P.yellow);
                const equipment=map.equipment||MAP.equipment;
                this._panel(equipment.x-22,equipment.y-18,44,36,'#3F5C70',P.ink,4);
                this._panel(equipment.x-13,equipment.y-12,26,24,mission.attackHoseStarted?'#638395':P.yellow,'#1A3343',4);
                const gear=map.ladder?.equipment;
                const taken=(mission.tasks||[]).some(task=>task.id==='BAL-ladder-gear'&&task.done);
                if(gear&&!taken&&!mission.ladderReady){
                    this._line([{x:gear.x-30,y:gear.y-10},{x:gear.x+30,y:gear.y-10}],P.ink,8);
                    this._line([{x:gear.x-30,y:gear.y+10},{x:gear.x+30,y:gear.y+10}],P.ink,8);
                    for(let rung=-24;rung<=24;rung+=12)this._box(gear.x+rung,gear.y-8,4,16,'#BBCDC5');
                }
            }
            if(map.division){
                const d=map.division;
                this._box(d.x-14,d.y-12,28,24,P.ink);
                this._box(d.x-10,d.y-8,20,16,mission.divisionReady?P.yellow:'#748A91');
                this._box(d.x-18,d.y-4,8,8,'#D7E5D8');this._box(d.x+10,d.y-10,8,8,'#D7E5D8');this._box(d.x+10,d.y+2,8,8,'#D7E5D8');
            }
        }
        this._ladder(map,mission,floor);
        this._taskMarkers(map,mission,floor,time);
        if(floor===0&&['unload','rally','orders'].includes(mission.phase))for(const point of this._pointList(map.rallyPoints))this._groundMark(point.x,point.y,P.white,22);
    }

    // La petite jauge reste attachée au camion, même pendant son arrivée.
    _waterReserve(map,mission) {
        const truck=map.truck||MAP.truck;
        const value=Math.round(limit(n(mission.water),0,100));
        const x=n(mission.truckX,truck.x)+(truck.w-180)/2,y=truck.y+truck.h+18;
        this._panel(x,y,180,44,P.navy,'#7BA9AE',2);
        this._text('EAU '+value+'%',x+90,y+6,P.blue,1,'center');
        this._box(x+12,y+26,156,10,P.ink);
        if(value>0)this._box(x+14,y+28,152*value/100,6,value<20?P.orange:P.blue);
    }

    _ladder(map, mission, floor) {
        if (!map.ladder) return;
        const ladder = map.ladder;
        const bottom = ladder.base || ladder.position || ladder.placement || ladder;
        const top = ladder.top || ladder.landing || ladder.upper || bottom;
        if (!Number.isFinite(bottom.x)) return;
        if (mission.ladderReady) {
            const from = bottom;
            const to = top;
            this._panel((map.building?.x || 620) - 2, top.y - 20, 20, 40, '#315E74', P.ink, 4);
            for (const dy of [-12, 12]) this._line([{ x: from.x, y: from.y + dy }, { x: to.x, y: to.y + dy }], P.ink, 8);
            for (const dy of [-12, 12]) this._line([{ x: from.x, y: from.y + dy }, { x: to.x, y: to.y + dy }], '#CAD8D0', 4);
            for (let rung = 0; rung <= 8; rung++) {
                const ratio = rung / 8;
                const x = from.x + (to.x - from.x) * ratio, y = from.y + (to.y - from.y) * ratio;
                this._line([{ x, y: y - 11 }, { x, y: y + 11 }], '#CAD8D0', 4);
            }
            const access = floor === 1 ? top : bottom;
            this._groundMark(access.x, access.y, P.blue, 34);
        }
    }



    _groundMark(x, y, color, size = 28) {
        const half = size / 2;
        const corners = [[-half,-half,10,3],[half-8,-half,10,3],[-half,half-2,10,3],[half-8,half-2,10,3],[-half,-half,3,10],[half,-half,3,10],[-half,half-8,3,10],[half,half-8,3,10]];
        corners.forEach(([dx,dy,w,h]) => this._box(x + dx, y + dy, w, h, color));
    }

    _taskMarkers(map,mission,floor,time) {
        if(mission.phase!=='mission')return;
        const tasks=(mission.tasks||[]).filter(task=>!task.done&&(!task.team||task.team===mission.team));
        const task=tasks[0];let point=null;
        if(task){
            const id=task.id.toLowerCase();
            if(/division/.test(id))point=floor===0?map.division:null;
            else if(/ladder-gear/.test(id))point=floor===0?map.ladder?.equipment:null;
            else if(/ladder/.test(id))point=floor===1?map.ladder?.landing:map.ladder;
            else if(/gear/.test(id))point=floor===0?map.equipment:null;
            else if(/line/.test(id))point=mission.attackHoseStarted?map.entry:map.equipment;
        }
        if(point&&Number.isFinite(point.x)){
            this._groundMark(point.x,point.y,Math.floor(time*2)%2?P.yellow:'#BC943A',40);
            if(Math.hypot(mission.player.x-point.x,mission.player.y-point.y)<66)this._tag('E',point.x,point.y-55,P.yellow);
        }
        const manualPending=tasks.some(item=>item.type==='manual');
        for(const room of manualPending?[]:mission.rooms||[]){
            if(floorOf(room)!==floor||room.team&&room.team!==mission.team)continue;
            if(room.searched)this._text('+',room.searchX-4,room.searchY-4,'#82CF90');
            else{
                this._groundMark(room.searchX,room.searchY,'#A7C9B8',28);
                if(Math.hypot(mission.player.x-room.searchX,mission.player.y-room.searchY)<66)this._tag('E',room.searchX,room.searchY-50,P.white);
            }
        }
    }

    _areaSmoke(map, mission, time, floor) {
        const fires = (mission.fires || []).filter(fire => floorOf(fire) === floor);
        const areas = (mission.rooms || []).filter(room=>floorOf(room)===floor);
        for (const area of areas) {
            const inside = fires.filter(fire=>fire.x>=area.x && fire.x<=area.x+area.w && fire.y>=area.y && fire.y<=area.y+area.h);
            if (!inside.length) continue;
            const strength = inside.reduce((sum,fire)=>sum+(fire.extinguished?0:limit(n(fire.hp)/Math.max(1,n(fire.maxHp,100)),0,1)),0)/inside.length;
            if (strength<=0) continue;
            const ctx=this.ctx;
            ctx.save();
            try {
                ctx.beginPath();ctx.rect(area.x+6,area.y+6,area.w-12,area.h-12);ctx.clip();
                for(let cloud=0;cloud<4;cloud++) {
                    const drift=Math.sin(time*.36+cloud*1.9)*12;
                    const x=area.x+18+(cloud%2)*(area.w*.46)+drift;
                    const y=area.y+36+Math.floor(cloud/2)*(area.h*.39)+Math.sin(time*.24+cloud)*7;
                    const w=112+(cloud%2)*20,h=62+Math.floor(cloud/2)*16;
                    const color=`rgba(145,163,167,${strength*.21})`;
                    this._box(x+12,y,w-24,h,color);
                    this._box(x,y+12,12,h-24,color);
                    this._box(x+w-12,y+12,12,h-24,color);
                    this._box(x+28,y-8,w-56,8,color);
                    this._box(x+22,y+h,72,10,`rgba(96,122,133,${strength*.13})`);
                }
            } finally {ctx.restore();}
        }
    }

    _fires(mission, time, floor) {
        const ctx = this.ctx;
        const sheet = this.assets.flames;
        const columns = Math.floor(n(sheet?.naturalWidth, n(sheet?.width)) / 64);
        const frames = Math.min(60, columns * Math.floor(n(sheet?.naturalHeight, n(sheet?.height)) / 64));
        for (const fire of mission.fires || []) {
            if (floorOf(fire) !== floor) continue;
            this._box(fire.x - 18, fire.y + 8, 38, 10, '#111D2C65');
            if (fire.extinguished || fire.hp <= 0) {
                this._box(fire.x - 12, fire.y, 24, 10, '#23323E');
                this._box(fire.x - 4, fire.y - 2, 8, 4, '#785A43');
                continue;
            }
            const seed = n(fire.seed);
            let drawn = false;
            if (sheet && sheet.complete !== false && frames > 0) {
                const frame = ((Math.floor(time * 20 + seed) % frames) + frames) % frames;
                try {
                    ctx.drawImage(sheet, frame % columns * 64, Math.floor(frame / columns) * 64, 64, 64, Math.round((fire.x - 40) / 2) * 2, Math.round((fire.y - 58) / 2) * 2, 80, 80);
                    drawn = true;
                } catch (_) { /* Le repli dessine toujours un foyer visible. */ }
            }
            if (!drawn) {
                const flicker = Math.floor(time * 8 + seed) % 3;
                this._box(fire.x - 18, fire.y - 18, 36, 34, '#BD3A37');
                this._box(fire.x - 12, fire.y - 36 - flicker * 2, 24, 48, '#FF7A35');
                this._box(fire.x + 4, fire.y - 48 + flicker * 4, 8, 30, P.orange);
                this._box(fire.x - 6, fire.y - 18, 14, 30, '#FFE47A');
                this._box(fire.x - 2, fire.y - 2, 8, 16, P.white);
            }
            for (let i = 0; i < 4; i++) {
                const p = ((time * .55 + seed * .07 + i * .23) % 1 + 1) % 1;
                this._box(fire.x + Math.sin(p * 5 + seed) * 14, fire.y - 28 - p * 70, 2, 4, p < .6 ? P.yellow : '#B75D3B');
            }
            const smoke = n(mission.smoke, .55);
            for (let i = 0; i < 3; i++) {
                const p = ((time * .22 + seed * .03 + i * .31) % 1 + 1) % 1;
                const x = fire.x + Math.sin(seed + p * 5) * (8 + p * 20), y = fire.y - 42 - p * 80;
                this._box(x - 10 - p * 8, y, 22 + p * 18, 12, `rgba(112,135,140,${(1-p)*.3*smoke})`);
                this._box(x - 6, y - 8, 14 + p * 16, 10, `rgba(112,135,140,${(1-p)*.2*smoke})`);
            }
        }
    }

    _ray(x, y, dx, dy, range, obstacles) {
        let distance = range;
        for (const raw of obstacles) {
            const rect = dimensions(raw);
            let entry = 0, exit = distance, hit = true;
            for (const [origin, direction, low, high] of [[x, dx, rect.x, rect.x + rect.w], [y, dy, rect.y, rect.y + rect.h]]) {
                if (Math.abs(direction) < 1e-6) { if (origin < low || origin > high) { hit = false; break; } }
                else {
                    const a = (low - origin) / direction, b = (high - origin) / direction;
                    entry = Math.max(entry, Math.min(a,b)); exit = Math.min(exit, Math.max(a,b));
                    if (entry > exit) { hit = false; break; }
                }
            }
            if (hit && exit >= 0 && entry <= distance) distance = Math.max(0, entry - 2);
        }
        return distance;
    }

    _spray(map, actor, aim, time, floor, subtle = false) {
        if (!aim) return;
        const angle = Math.atan2(aim.y - actor.y, aim.x - actor.x);
        const obstacles = [...(map.walls || []), ...(floor === 1 ? map.upperWalls || [] : [])].filter(wall => wall.floor === undefined || floorOf(wall) === floor);
        if (floor === 0) {
            if (map.truck) obstacles.push(map.truck);
        }
        for (let ray = -1; ray <= 1; ray++) {
            const dx = Math.cos(angle + ray * .09), dy = Math.sin(angle + ray * .09);
            const distance = this._ray(actor.x, actor.y, dx, dy, 260, obstacles);
            for (let dot = 0; dot < 14; dot++) {
                const p = ((dot / 14 + time * 1.8 + ray * .1) % 1 + 1) % 1;
                this._box(actor.x + dx * distance * p - 2, actor.y - 12 * (1 - p) + dy * distance * p, ray ? 4 : 6, ray ? 4 : 6, subtle ? '#61B9C7' : ray ? P.blue : '#DAFCF4');
            }
        }
    }

    _jets(map, mission, time, floor) {
        if (mission.spraying && floorOf(mission.player) === floor) this._spray(map, mission.player, mission.aim, time, floor);
        if (mission.phase !== 'mission') return;
        for (const actor of mission.crew || []) {
            if (actor === mission.player || actor.visible === false || floorOf(actor) !== floor || !actor.spraying) continue;
            const targets = (mission.fires || []).filter(fire => !fire.extinguished && fire.hp > 0 && floorOf(fire) === floor && (!fire.team || fire.team === actor.team));
            const target = targets.slice().sort((a,b) => Math.hypot(a.x-actor.x,a.y-actor.y)-Math.hypot(b.x-actor.x,b.y-actor.y))[0];
            if (actor.aim || target) this._spray(map, actor, actor.aim || target, time, floor, true);
        }
    }

    _victims(mission,time,floor) {
        for(const victim of mission.victims||[]){
            if(!victim.visible||floorOf(victim)!==floor)continue;
            const x=victim.x,y=victim.y;
            this._box(x-14,y+8,28,6,'#07141D55');
            if(victim.carried){
                this._panel(x-14,y-32,32,14,victim.rescued?'#79B67A':'#B97759',P.ink,2);
                this._box(x-20,y-34,14,14,'#F4BA8D');this._box(x-20,y-34,14,4,'#684D40');
            }else this._sprite('person-'+(victim.rescued?'safe':'waiting'),18,26,ctx=>{
                const b=(x,y,w,h,c)=>{ctx.fillStyle=c;ctx.fillRect(x,y,w,h);};
                b(4,1,11,12,P.ink);b(5,2,9,10,'#F3B98E');b(4,1,11,4,'#684D40');b(4,4,3,5,'#684D40');
                b(8,7,1,1,P.ink);b(12,7,1,1,P.ink);b(9,10,3,1,'#AF6752');b(3,13,13,9,P.ink);
                b(4,14,11,7,victim.rescued?'#78B67D':'#C87954');b(1,15,3,7,'#F3B98E');b(15,15,3,7,'#F3B98E');
                b(4,22,4,4,'#2D4C65');b(11,22,4,4,'#2D4C65');b(3,25,5,1,P.ink);b(11,25,5,1,P.ink);
            },x-18,y-42);
            if(!victim.carried&&!victim.rescued)this._text('!',x,y-48,P.yellow,1,'center');
        }
    }

    _firefighter(actor, mission, time) {
        const hero = Boolean(mission.team) && actor === mission.player;
        const partner = Boolean(mission.team) && actor.team === mission.team && actor !== mission.player;
        const walking = actor.walking ? Math.floor(time * 7) % 2 : 0;
        const accent = colorOf(actor);
        const x = n(actor.x), y = n(actor.y);
        this._box(x-14,y+7,30,8,'#07141D65');
        if (hero) this._groundMark(x, y + 8, P.yellow, 42);
        else if (partner) this._groundMark(x, y + 8, P.blue, 34);
        this._sprite(`crew-${actor.id||actor.role}-${walking}`,22,30,ctx=>{
            const b=(x,y,w,h,c)=>{ctx.fillStyle=c;ctx.fillRect(x,y,w,h);};
            b(4,2,14,12,P.ink);b(6,0,10,3,P.ink);b(5,2,12,8,P.yellow);b(7,1,8,4,'#FFE787');
            b(3,7,16,3,'#D6A645');b(4,10,14,4,P.ink);b(7,10,9,5,'#F4BC8F');
            b(7,10,2,2,P.ink);b(13,10,2,2,P.ink);b(10,13,3,1,'#A25B47');
            b(9,3,5,2,accent);b(6,14,11,11,P.ink);b(6,15,10,8,'#28526A');
            b(2,16+walking,4,9,P.ink);b(17,16-walking,4,9,P.ink);b(3,18+walking,3,3,P.yellow);b(17,18-walking,3,3,P.yellow);
            b(2,23+walking,4,3,'#EDC191');b(17,23-walking,4,3,'#EDC191');
            b(6,17,10,2,P.yellow);b(6,22,10,2,P.yellow);b(10,15,3,9,'#ACC5CB');b(11,15,1,9,P.white);
            b(6,25,4,4-walking,'#234258');b(13,25,4,3+walking,'#234258');
            b(5,28-walking,6,2,P.ink);b(12,27+walking,6,2,P.ink);
        },x-22,y-48,Math.cos(n(actor.dir))<-.2);
        if (hero) this._text('>', x - 5, y - 73, P.yellow);
        if (actor.spraying || hero && mission.attackHoseStarted) {
            const aim = hero ? mission.aim : actor.aim;
            const direction = aim ? Math.atan2(aim.y - y, aim.x - x) : n(actor.dir);
            const end = { x: x + Math.cos(direction) * 25, y: y - 16 + Math.sin(direction) * 25 };
            this._line([{x,y:y-16},end],P.ink,8);
            this._line([{x,y:y-16},end],P.yellow,4);
        }
        if (/recherch|search|fouille/i.test(actor.activity||'')) this._box(x+18,y-26,8,6,P.yellow);
        if (/commande|ordre|radio|command/i.test(actor.activity||'')) {
            this._box(x+17,y-34,6,12,P.ink);this._box(x+19,y-38,2,6,P.blue);
        }
    }

    _crew(mission, time, floor) {
        if (mission.phase === 'arrival') return;
        const source = mission.crew || [];
        const actors = source.includes(mission.player) ? source : [...source,mission.player].filter(Boolean);
        const visible = actors.filter(actor => actor.visible !== false && floorOf(actor) === floor).slice().sort((a,b)=>a.y-b.y);
        visible.forEach(actor=>this._firefighter(actor,mission,time));
        const labels={chief:'CHEF',driver:'COND','bat-chief':'BAT1','bat-partner':'BAT2','bal-chief':'BAL1','bal-partner':'BAL2'};
        for (const actor of visible) {
            if (Boolean(mission.team) && actor === mission.player) this._tag('VOUS',actor.x+30,actor.y-56,P.yellow,false);
            else if (['rally','orders'].includes(mission.phase)) this._tag(labels[actor.id]||'FPT',actor.x+30,actor.y-12,colorOf(actor),false);
        }
    }

    _bubble(mission,floor) {
        const bubble=mission.dialogue||mission.bubble;
        if(!bubble?.text)return;
        const actor=(mission.crew||[]).find(person=>person.id===bubble.actorId);
        if(!actor||actor.visible===false||floorOf(actor)!==floor)return;
        const labels={chief:'CHEF D’AGRES',driver:'CONDUCTEUR','bat-chief':'CHEF BAT','bat-partner':'EQUIPIER BAT','bal-chief':'CHEF BAL','bal-partner':'EQUIPIER BAL'};
        const speaker=bubble.speaker||labels[actor.id]||actor.role||'EQUIPE';
        const view=this._visibleWorld();
        const maxChars=Math.max(8,Math.min(29,Math.floor((view.w-48)/12)));
        const words=(speaker+' : '+bubble.text).split(/\s+/);
        const lines=[];let line='';
        for(const word of words){
            if(line&&(line+' '+word).length>maxChars){lines.push(line);line=word;}
            else line+=(line?' ':'')+word;
        }
        if(line)lines.push(line);
        const visible=lines.slice(0,Math.min(5,Math.max(2,Math.floor((view.h-48)/20))));
        if(visible.length<lines.length)visible[visible.length-1]=visible[visible.length-1].slice(0,maxChars-3)+'...';
        const width=Math.max(...visible.map(item=>item.length))*12+24,height=visible.length*20+20;
        const minX=view.x+12,minY=view.y+12;
        const x=limit(Math.max(230,actor.x+125),minX,Math.max(minX,view.x+view.w-width-12));
        const y=limit(actor.y-190-height,minY,Math.max(minY,view.y+view.h-height-12));
        this._box(x+6,y+6,width,height,'#07141D70');this._panel(x,y,width,height,P.white,P.ink,4);
        visible.forEach((text,index)=>this._text(text,x+12,y+10+index*20,P.ink));
        this._box(limit(actor.x,x+14,x+width-18),y+height,10,8,P.ink);
        this._box(limit(actor.x,x+14,x+width-18)+2,y+height-2,6,7,colorOf(actor));
    }

    // Le texte reste dans la zone visible sans changer la caméra parent.
    _visibleWorld() {
        const matrix=this.context.getTransform?.(),canvas=this.context.canvas;
        if(!matrix||!canvas||!Number.isFinite(matrix.a)||!Number.isFinite(matrix.d)||matrix.a<=0||matrix.d<=0||matrix.b||matrix.c)return{x:0,y:0,w:1440,h:900};
        const x=limit(-matrix.e/matrix.a,0,1440),y=limit(-matrix.f/matrix.d,0,900);
        const right=limit((canvas.width-matrix.e)/matrix.a,x,1440),bottom=limit((canvas.height-matrix.f)/matrix.d,y,900);
        return{x,y,w:right-x,h:bottom-y};
    }

}
