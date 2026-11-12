import { MAP, FireMission } from './mission.js';
import { FireRenderer } from './renderer.js';
import { FireChallenge } from './challenge.js';
import { dailyGuard, newGuard, formatTime, bestTime, recordVictory } from './guards.js';

const dialog = document.getElementById('fire-game');
const canvas = document.getElementById('fire-canvas');

function initialize() {
    const context = canvas.getContext('2d');
    if (!context) return;
    const ids = ['start', 'close', 'sound', 'restart', 'skip', 'home', 'intro',
        'photo-one', 'photo-two', 'intro-label', 'intro-caption', 'intro-credit',
        'result', 'result-title', 'result-text', 'result-replay', 'resume', 'paused',
        'orders', 'order-bat', 'order-bal', 'notice', 'dialogue-live', 'menu-title',
        'daily', 'random', 'guard-info', 'duty-label', 'result-next', 'result-duty',
        'report-name', 'report-time', 'report-crew', 'report-best', 'challenge-open', 'challenge', 'challenge-menu-note'];
    const ui = Object.fromEntries(ids.map(id => [id, document.getElementById(`fire-${id}`)]));
    const challenge = new FireChallenge(ui.challenge, {
        apiUrl: dialog.dataset.challengeApi,
        csrfToken: dialog.dataset.csrfToken,
        isLoggedIn: dialog.dataset.challengeUser === '1'
    });
    const assets = {};
    const renderer = new FireRenderer(context, assets);
    const base = new URL(dialog.dataset.assets, location.href);
    const siren = new RadioSound();
    const openers = [...document.querySelectorAll('[data-fire-open]')];
    const secretLogos = [...document.querySelectorAll('header .logo, .admin_sidemenu .navigation h2 > a')];
    const logoDelay = 3000;
    let logoClicks = 0, logoFirstClick = 0, logoTimer = null;
    const pressed = new Map(), directions = new Map(), spraySources = new Set(), consumed = new Set();
    let guard = null;
    const makeMission = () => new FireMission({ guardSeed: guard?.seed });
    let mission = makeMission();
    let state = 'home', pausedFrom = 'home';
    let unlocked = false, sequence = 0, interactPending = false, ordersVisible = false;
    let animation = null, resizeRequest = null, previousTime = null, visualTime = 0, introElapsed = 0;
    let previousFocus = null, scrollState = null, assetPromise = null, pointerAim = null;
    let noticeUntil = 0, dialogueSignature = '', hoseWasLimited = false;
    let size = { width: MAP.width, height: MAP.height, ratio: 1 };
    let camera = { scale: 1, x: 0, y: 0 };
    const editable = target => target instanceof Element && (target.isContentEditable
        || !!target.closest('input, textarea, select, [contenteditable]:not([contenteditable="false"])'));
    const write = (element, value) => {
        if (!element) return;
        const target = element.querySelector('[data-fire-label]') || element;
        if (target.textContent !== String(value)) target.textContent = String(value);
    };
    const clamp = (n, low, high) => Math.max(low, Math.min(high, n));

    function resetInput() { pressed.clear(); directions.clear(); spraySources.clear(); interactPending = false; }

    function refreshGuardHome() {
        const today = dailyGuard();
        const records = ['BAT', 'BAL'].map(team => {
            const best = bestTime(today.seed, team);
            return best === null ? null : team + ' ' + formatTime(best);
        }).filter(Boolean);
        write(ui['guard-info'], records.length ? today.label + ' · ' + records.join(' / ')
            : today.label + ' · Même départ pour tous, record par binôme.');
    }

    function prepareOrders() {
        write(ui['duty-label'], guard ? guard.label + ' / Ordres' : 'Chef d’agrès / Ordres');
        for (const line of dialog.querySelectorAll('[data-fire-best]')) {
            const best = guard ? bestTime(guard.seed, line.dataset.fireBest) : null;
            line.hidden = best === null;
            if (best !== null) write(line, 'Record à battre · ' + formatTime(best));
        }
    }

    function debrief() {
        const won = mission.phase === 'won';
        write(ui['result-title'], won ? 'Intervention terminée' : 'Repli');
        write(ui['result-text'], won
            ? 'Chef d’agrès : les deux secteurs sont sûrs. On rassemble le matériel.'
            : 'Chef d’agrès : on se replie. Reprenez vos distances avant de repartir.');
        ui['result-duty'].hidden = !guard;
        ui['result-next'].hidden = !guard;
        write(ui['result-replay'], guard ? 'Rejouer cette garde' : 'Recommencer');
        if (!guard) return;
        const tenths = Math.round(mission.interventionSeconds * 10);
        const previous = won ? recordVictory(guard.seed, mission.team, tenths) : bestTime(guard.seed, mission.team);
        const rescued = mission.victims.filter(person => person.rescued).length;
        write(ui['report-name'], guard.label + ' · ' + mission.team);
        write(ui['report-time'], 'Temps en intervention · ' + formatTime(tenths));
        write(ui['report-crew'], 'Victimes à l’abri · ' + rescued + ' / ' + mission.victims.length);
        let feedback = 'Repli · aucun temps enregistré';
        if (won && previous === null) feedback = 'Premier record pour ce binôme.';
        else if (won && tenths < previous) feedback = 'Record battu de ' + formatTime(previous - tenths) + ' !';
        else if (won && tenths === previous) feedback = 'Record égalé !';
        else if (previous !== null) feedback = 'Record à battre · ' + formatTime(previous);
        write(ui['report-best'], feedback);
    }

    function setState(next) {
        state = next;
        dialog.dataset.state = state;
        for (const child of dialog.children) child.inert = state === 'paused' && child !== ui.paused;
        ui.home.hidden = state !== 'home';
        ui.challenge.hidden = state !== 'challenge' && !(state === 'paused' && pausedFrom === 'challenge');
        ui['challenge-menu-note'].hidden = state !== 'paused' || pausedFrom !== 'challenge';
        ui.intro.hidden = state !== 'intro' && !(state === 'paused' && pausedFrom === 'intro');
        ui.paused.hidden = state !== 'paused';
        ui.result.hidden = state !== 'result';
        ui.orders.hidden = !(state === 'playing' && mission.phase === 'orders');
        ui.skip.hidden = state !== 'intro';
        ui.restart.disabled = state === 'paused' && (pausedFrom === 'home' || pausedFrom === 'challenge');
        write(ui.resume, pausedFrom === 'home' ? 'Retour' : 'Reprendre');
        write(ui['menu-title'], 'MENU');
        if (ui.notice) ui.notice.hidden = true;
        noticeUntil = 0;
        resetInput();
        updateSound();
        if (dialog.open) resize();
    }

    function updateSound() {
        siren.setRunning(state === 'intro' || (state === 'playing' && mission.phase === 'arrival'));
    }

    function loadImage(name) {
        return new Promise(resolve => {
            const picture = new Image();
            let finished = false;
            const finish = value => {
                if (finished) return;
                finished = true; clearTimeout(timeout); resolve(value);
            };
            const timeout = setTimeout(() => finish(null), 8000);
            picture.onload = () => finish(picture);
            picture.onerror = () => finish(null);
            picture.src = new URL(name, base).href;
        });
    }

    function loadAssets() {
        if (!assetPromise) assetPromise = Promise.all(['flames.png', 'station.jpg', 'departure.jpg'].map(loadImage))
            .then(([flames, station, departure]) => Object.assign(assets, { flames, station, departure }));
        return assetPromise;
    }

    // La caméra remplit l'écran sans étirer les sprites ni laisser de colonnes vides.
    function updateCamera() {
        const scale = Math.max(size.width / MAP.width, size.height / MAP.height);
        const width = size.width / scale, height = size.height / scale;
        const active = mission.team && ['mission', 'won', 'lost'].includes(mission.phase);
        const focus = active ? mission.player : { x: MAP.truck.x + 95, y: 640 };
        const x = clamp(focus.x - width / 2, 0, Math.max(0, MAP.width - width));
        const y = clamp(focus.y - height / 2, 0, Math.max(0, MAP.height - height));
        camera = { scale, x, y };
    }

    function resize() {
        const rect = canvas.getBoundingClientRect();
        size = { width: Math.max(1, rect.width), height: Math.max(1, rect.height), ratio: Math.min(2, devicePixelRatio || 1) };
        canvas.width = Math.round(size.width * size.ratio);
        canvas.height = Math.round(size.height * size.ratio);
        draw();
    }

    function draw() {
        updateCamera();
        context.setTransform(size.ratio, 0, 0, size.ratio, 0, 0);
        context.fillStyle = '#244d44';
        context.fillRect(0, 0, size.width, size.height);
        context.setTransform(size.ratio * camera.scale, 0, 0, size.ratio * camera.scale,
            -camera.x * size.ratio * camera.scale, -camera.y * size.ratio * camera.scale);
        context.imageSmoothingEnabled = false;
        renderer.draw(mission, visualTime);
    }

    function showNotice(text, seconds = 3.8) {
        if (!text || state !== 'playing') return;
        write(ui.notice, text);
        ui.notice.hidden = false;
        noticeUntil = visualTime + seconds;
    }

    // Les échanges restent dans le décor ; seuls les messages hors champ sont sous-titrés.
    function hud() {
        dialog.dataset.phase = mission.phase;
        dialog.dataset.floor = String(mission.floor);
        const showOrders = state === 'playing' && mission.phase === 'orders';
        ui.orders.hidden = !showOrders;
        if (showOrders && !ordersVisible) {
            prepareOrders();
            dialog.querySelector('[data-fire-team="BAT"]')?.focus({ preventScroll: true });
        }
        ordersVisible = showOrders;
        const speech = mission.dialogue || mission.bubble;
        const signature = speech ? `${speech.id ?? ''}|${speech.actorId}|${speech.text}` : '';
        if (speech && signature !== dialogueSignature) {
            const actor = mission.crew.find(member => member.id === speech.actorId);
            const text = `${speech.speaker || actor?.role || 'Radio'} : ${speech.text}`;
            write(ui['dialogue-live'], text);
            const inView = actor?.visible && actor.floor === mission.floor
                && actor.x >= camera.x && actor.x <= camera.x + size.width / camera.scale
                && actor.y >= camera.y && actor.y <= camera.y + size.height / camera.scale;
            if (!inView) showNotice(text, 4.5);
        }
        dialogueSignature = signature;
        if (noticeUntil && visualTime >= noticeUntil) { ui.notice.hidden = true; noticeUntil = 0; }
        if (state === 'playing' && ['won', 'lost'].includes(mission.phase)) {
            debrief();
            setState('result');
            ui['result-replay'].focus({ preventScroll: true });
        }
        updateSound();
    }

    function input() {
        const movement = { moveX: 0, moveY: 0 };
        for (const direction of [...pressed.values(), ...directions.values()]) {
            if (direction === 'left') movement.moveX--;
            if (direction === 'right') movement.moveX++;
            if (direction === 'up') movement.moveY--;
            if (direction === 'down') movement.moveY++;
        }
        const aim = pointerAim ? {
            x: clamp(pointerAim.x / camera.scale + camera.x, 0, MAP.width),
            y: clamp(pointerAim.y / camera.scale + camera.y, 0, MAP.height),
        } : { x: mission.player.x + 260, y: mission.player.y };
        const result = { ...movement, aimX: aim.x, aimY: aim.y, spray: spraySources.size > 0, interact: interactPending };
        interactPending = false;
        return result;
    }

    function updateIntro() {
        const departure = introElapsed >= 3.8;
        ui['photo-one'].style.opacity = departure ? '0' : '1';
        ui['photo-two'].style.opacity = departure ? '1' : '0';
        ui['photo-one'].classList.toggle('is-active', !departure);
        ui['photo-two'].classList.toggle('is-active', departure);
        write(ui['intro-label'], departure ? 'EN ROUTE' : 'DÉPART CASERNE');
        write(ui['intro-caption'], departure ? 'Chef d’agrès : à l’arrivée, écoutez les ordres.' : 'Feu d’habitation. L’équipage est engagé.');
        for (const link of ui['intro-credit'].querySelectorAll('[data-fire-credit-photo]')) link.href = departure ? PHOTO_TWO : PHOTO_ONE;
        if (introElapsed >= 7.6) beginArrival();
    }

    function frame(timestamp) {
        if (!dialog.open) { animation = null; return; }
        const dt = previousTime === null ? 0 : Math.min(0.05, (timestamp - previousTime) / 1000);
        previousTime = timestamp;
        if (state !== 'paused') visualTime += dt;
        if (state === 'intro') { introElapsed += dt; updateIntro(); }
        if (state === 'playing') {
            const command = input();
            mission.update(dt, command);
            if (command.interact) showNotice(mission.message);
            const hoseLimited = mission.hoseLines?.some(line => line.team === mission.team && line.limited) || false;
            if (hoseLimited && !hoseWasLimited) showNotice('LAT : 60 m maximum (3 × 20 m). Revenez sur votre chemin pour récupérer du tuyau.');
            hoseWasLimited = hoseLimited;
        }
        hud(); draw();
        animation = requestAnimationFrame(frame);
    }

    function freshMission() {
        mission = makeMission(); pointerAim = null; dialogueSignature = ''; ordersVisible = false; hoseWasLimited = false;
    }

    function resetLogoTrigger() {
        if (logoTimer !== null) clearTimeout(logoTimer);
        logoClicks = 0; logoFirstClick = 0; logoTimer = null;
    }

    function unlockAndOpen() {
        resetLogoTrigger(); sequence = 0; unlocked = true;
        openers.forEach(button => { button.hidden = false; });
        open();
    }

    // Cinq clics dans une fenêtre fixe de trois secondes depuis le premier clic.
    function onLogoClick(event) {
        if (event.defaultPrevented || event.button !== 0
            || event.ctrlKey || event.altKey || event.metaKey || event.shiftKey) return;
        event.preventDefault();
        if (dialog.open) return;

        const now = performance.now();
        if (logoClicks > 0 && now - logoFirstClick >= logoDelay) resetLogoTrigger();
        if (logoClicks === 0) {
            logoFirstClick = now;
            const destination = event.currentTarget.href;
            logoTimer = setTimeout(() => {
                resetLogoTrigger();
                // Sans cinq clics, le lien garde son retour au catalogue.
                if (!dialog.open && destination !== location.href) location.assign(destination);
            }, logoDelay);
        }
        logoClicks++;
        if (logoClicks === 5) unlockAndOpen();
    }

    function open() {
        if (!unlocked || dialog.open) return;
        resetLogoTrigger();
        previousFocus = document.activeElement;
        scrollState = { x: scrollX, y: scrollY, html: document.documentElement.style.overflow, body: document.body.style.overflow };
        document.documentElement.style.overflow = 'hidden'; document.body.style.overflow = 'hidden';
        guard = null; refreshGuardHome(); freshMission(); setState('home'); dialog.showModal();
        loadAssets().then(() => { if (dialog.open) draw(); });
        resize(); hud(); ui.daily.focus({ preventScroll: true });
        previousTime = null; animation = requestAnimationFrame(frame);
    }

    function close() { if (dialog.open) { dialog.close(); cleanup(); } }

    function cleanup() {
        challenge.cancel();
        if (animation !== null) cancelAnimationFrame(animation);
        if (resizeRequest !== null) cancelAnimationFrame(resizeRequest);
        animation = null; resizeRequest = null; previousTime = null;
        resetLogoTrigger(); resetInput(); sequence = 0; consumed.clear(); siren.setRunning(false);
        if (scrollState) {
            document.documentElement.style.overflow = scrollState.html;
            document.body.style.overflow = scrollState.body;
            window.scrollTo({ left: scrollState.x, top: scrollState.y, behavior: 'instant' });
            scrollState = null;
        }
        if (previousFocus instanceof HTMLElement && previousFocus.isConnected) previousFocus.focus({ preventScroll: true });
        previousFocus = null;
    }

    function start() {
        if (challenge.active || state === 'challenge' || (state === 'paused' && pausedFrom === 'challenge')) return;
        freshMission(); introElapsed = 0; setState('intro'); updateIntro();
        ui.skip.focus({ preventScroll: true });
    }

    function beginArrival() { freshMission(); setState('playing'); canvas.focus({ preventScroll: true }); }

    function pause() {
        if (!dialog.open || state === 'paused') return;
        pausedFrom = state; setState('paused'); ui.resume.focus({ preventScroll: true });
    }

    function resume() {
        if (state !== 'paused') return;
        setState(pausedFrom); previousTime = null;
        if (state === 'challenge') { challenge.focus(); return; }
        const target = state === 'home' ? ui.daily : state === 'intro' ? ui.skip : state === 'result' ? ui['result-replay']
            : mission.phase === 'orders' ? dialog.querySelector('[data-fire-team="BAT"]') : canvas;
        target?.focus({ preventScroll: true });
    }
    const toggleMenu = () => state === 'paused' ? resume() : pause();
    const playable = () => state === 'playing' && mission.phase === 'mission';
    const directionFor = key => ['arrowleft', 'a', 'q'].includes(key) ? 'left' : ['arrowright', 'd'].includes(key) ? 'right'
        : ['arrowup', 'w', 'z'].includes(key) ? 'up' : ['arrowdown', 's'].includes(key) ? 'down' : null;

    document.addEventListener('keydown', event => {
        if (editable(event.target)) return;
        const key = event.key.toLowerCase(), identity = event.code || key;
        if (event.ctrlKey && !event.altKey && !event.metaKey && key.length === 1) {
            if (event.repeat && consumed.has(identity)) { event.preventDefault(); return; }
            if (event.repeat) return;
            if (key === 'feu'[sequence]) {
                event.preventDefault(); consumed.add(identity); sequence++;
                if (sequence === 3) unlockAndOpen();
            } else { if (sequence) { event.preventDefault(); consumed.add(identity); } sequence = 0; }
            return;
        }
        if (key !== 'control') sequence = 0;
        if (!dialog.open || event.altKey || event.metaKey || event.ctrlKey) return;
        if (key === 'tab' && state === 'paused') {
            const choices = [...ui.paused.querySelectorAll('button:not(:disabled), a[href], summary')]
                .filter(node => node.getBoundingClientRect().width > 0 && node.getBoundingClientRect().height > 0);
            if (choices.length) {
                event.preventDefault();
                const index = choices.indexOf(document.activeElement);
                choices[(index + (event.shiftKey ? -1 : 1) + choices.length) % choices.length].focus({ preventScroll: false });
            }
            return;
        }
        if (key === 'escape' || key === 'p') {
            event.preventDefault(); event.stopPropagation(); if (!event.repeat) toggleMenu(); return;
        }
        if (key === 'r' && state === 'paused' && pausedFrom !== 'home' && pausedFrom !== 'challenge' && !event.repeat) { event.preventDefault(); start(); return; }
        if (!playable()) return;
        const direction = directionFor(key);
        if (direction) { event.preventDefault(); pressed.set(identity, direction); }
        if (key === 'e' && !event.repeat) { event.preventDefault(); interactPending = true; }
        if (key === ' ' && event.target === canvas) { event.preventDefault(); spraySources.add('space'); }
    }, true);

    document.addEventListener('keyup', event => {
        const key = event.key.toLowerCase();
        consumed.delete(event.code || key);
        if (!event.ctrlKey || key === 'control') { sequence = 0; consumed.clear(); }
        if (directionFor(key)) pressed.delete(event.code || key);
        if (key === ' ') spraySources.delete('space');
    }, true);

    function aimAt(event) {
        const rect = canvas.getBoundingClientRect();
        pointerAim = { x: event.clientX - rect.left, y: event.clientY - rect.top };
    }
    canvas.addEventListener('pointermove', aimAt);
    canvas.addEventListener('pointerdown', event => {
        if (!playable() || event.button !== 0) return;
        event.preventDefault(); aimAt(event); spraySources.add(`canvas-${event.pointerId}`); canvas.focus({ preventScroll: true });
        try { canvas.setPointerCapture(event.pointerId); } catch (_) { /* Capture facultative. */ }
    });
    const releasePointer = event => {
        directions.delete(event.pointerId); spraySources.delete(`canvas-${event.pointerId}`); spraySources.delete(`touch-${event.pointerId}`);
    };
    window.addEventListener('pointerup', releasePointer); window.addEventListener('pointercancel', releasePointer);
    canvas.addEventListener('lostpointercapture', releasePointer);
    for (const button of dialog.querySelectorAll('[data-fire-move], [data-fire-spray]')) {
        button.addEventListener('pointerdown', event => {
            if (!playable()) return;
            event.preventDefault();
            if (button.dataset.fireMove) directions.set(event.pointerId, button.dataset.fireMove);
            else spraySources.add(`touch-${event.pointerId}`);
            try { button.setPointerCapture(event.pointerId); } catch (_) { /* Capture facultative. */ }
        });
        button.addEventListener('lostpointercapture', releasePointer);
    }
    for (const button of dialog.querySelectorAll('[data-fire-interact]')) button.addEventListener('click', () => { if (playable()) interactPending = true; });
    for (const logo of secretLogos) logo.addEventListener('click', onLogoClick);
    for (const button of openers) button.addEventListener('click', open);
    for (const button of dialog.querySelectorAll('[data-fire-team]')) button.addEventListener('click', () => {
        if (state === 'playing' && mission.phase === 'orders' && mission.chooseTeam(button.dataset.fireTeam)) {
            resetInput(); hud(); draw(); canvas.focus({ preventScroll: true });
        }
    });
    for (const button of dialog.querySelectorAll('[data-fire-quit]')) button.addEventListener('click', close);
    for (const button of dialog.querySelectorAll('[data-fire-pause]')) button.addEventListener('click', toggleMenu);
    for (const button of dialog.querySelectorAll('[data-fire-home]')) button.addEventListener('click', () => {
        challenge.cancel();
        guard = null; refreshGuardHome(); freshMission(); setState('home'); ui.daily.focus({ preventScroll: true });
    });
    ui['challenge-open'].addEventListener('click', () => {
        if (!dialog.open || state !== 'home') return;
        challenge.open(); setState('challenge'); challenge.focus();
    });
    const startRandom = () => { guard = newGuard(guard?.seed); start(); };
    ui.daily.addEventListener('click', () => { guard = dailyGuard(); start(); });
    ui.random.addEventListener('click', startRandom);
    ui['result-next'].addEventListener('click', startRandom);
    ui.start.addEventListener('click', () => { guard = null; start(); });
    ui.close.addEventListener('click', close);
    ui.restart.addEventListener('click', start); ui['result-replay'].addEventListener('click', start);
    ui.skip.addEventListener('click', beginArrival); ui.resume.addEventListener('click', resume);
    ui.sound.addEventListener('click', async () => {
        const enabled = await siren.toggle(); ui.sound.setAttribute('aria-pressed', String(enabled));
        write(ui.sound, enabled ? 'Son : activé' : 'Son : coupé');
        ui.sound.setAttribute('aria-label', enabled ? 'Couper le son' : 'Activer le son');
        ui.sound.title = enabled ? 'Couper le son' : 'Activer le son';
        const waves = ui.sound.querySelectorAll('svg path')[1];
        if (waves) waves.setAttribute('d', enabled ? 'M16 8a6 6 0 0 1 0 8M19 5a10 10 0 0 1 0 14' : 'm16 9 5 6m0-6-5 6');
    });
    dialog.addEventListener('cancel', event => { event.preventDefault(); toggleMenu(); });
    dialog.addEventListener('close', () => { if (!dialog.open) cleanup(); });
    window.addEventListener('pagehide', () => challenge.cancel());
    window.addEventListener('blur', () => {
        resetLogoTrigger(); sequence = 0; consumed.clear(); resetInput(); if (['intro', 'playing'].includes(state)) pause();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) { resetLogoTrigger(); sequence = 0; consumed.clear(); resetInput(); if (['intro', 'playing'].includes(state)) pause(); }
    });
    window.addEventListener('resize', () => {
        if (!dialog.open) return;
        if (resizeRequest !== null) cancelAnimationFrame(resizeRequest);
        resizeRequest = requestAnimationFrame(() => { resizeRequest = null; resize(); });
    });
    write(ui['order-bat'], 'BAT, prenez votre LAT de 3 × 20 m. Raccordez votre lance à la DAT alimentée par le BAL, puis reconnaissez le rez-de-chaussée.');
    write(ui['order-bal'], 'BAL, établissez la DAT au pied de l’habitation et alimentez le BAT. Sur mon nouvel ordre : reconnaissance au deuxième étage par l’échelle à coulisse, avec votre LAT de 60 m.');
    setState('home'); dialog.dataset.ready = 'true';
}

const PHOTO_ONE = 'https://commons.wikimedia.org/wiki/File:Langon_-_33210_-_2026.07.10_-_SDIS_33_-_Fourgon_Pompe_Tonne_(vue_avant_gauche)_%C2%A9_Anthony_Levrot.jpg';
const PHOTO_TWO = PHOTO_ONE.replace('vue_avant_gauche', 'vue_avant_droit');

class RadioSound {
    constructor() { this.enabled = false; this.running = false; this.audio = null; this.timer = null; this.oscillator = null; }
    async toggle() {
        this.enabled = !this.enabled;
        if (this.enabled) {
            const Audio = window.AudioContext || window.webkitAudioContext;
            if (!Audio) { this.enabled = false; return false; }
            if (!this.audio) this.audio = new Audio();
            try { await this.audio.resume(); } catch (_) { this.enabled = false; }
        }
        this.sync(); return this.enabled;
    }
    setRunning(value) { if (this.running !== value) { this.running = value; this.sync(); } }
    sync() {
        if (this.oscillator) { try { this.oscillator.stop(); } catch (_) {} this.oscillator.disconnect(); this.oscillator = null; }
        if (this.timer) { clearInterval(this.timer); this.timer = null; }
        if (!this.enabled || !this.running || !this.audio) return;
        const oscillator = this.audio.createOscillator(), gain = this.audio.createGain();
        oscillator.type = 'sine'; gain.gain.value = 0.025; oscillator.connect(gain); gain.connect(this.audio.destination);
        let high = false;
        const tone = () => { high = !high; oscillator.frequency.setTargetAtTime(high ? 620 : 460, this.audio.currentTime, 0.09); };
        tone(); oscillator.start(); this.oscillator = oscillator; this.timer = setInterval(tone, 450);
        oscillator.onended = () => gain.disconnect();
    }
}

if (dialog && canvas && dialog.dataset.ready !== 'true') initialize();