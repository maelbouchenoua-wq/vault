import { MAP, FireMission } from './mission.js';
import { FireRenderer } from './renderer.js';

const dialog = document.getElementById('fire-game');
const canvas = document.getElementById('fire-canvas');

function initialize() {
    const context = canvas.getContext('2d');
    if (!context) return;
    const ids = ['close', 'restart', 'result', 'result-title', 'result-text', 'result-replay',
        'resume', 'paused', 'notice', 'dialogue-live', 'menu-title', 'start', 'bal'];
    const ui = Object.fromEntries(ids.map(id => [id, document.getElementById(`fire-${id}`)]));
    const assets = {};
    const renderer = new FireRenderer(context, assets);
    const base = new URL(dialog.dataset.assets, location.href);
    const openers = [...document.querySelectorAll('[data-fire-open]')];
    const secretLogos = [...document.querySelectorAll('header .logo, .admin_sidemenu .navigation h2 > a')];
    const logoDelay = 3000;
    let logoClicks = 0, logoFirstClick = 0, logoTimer = null;
    const pressed = new Map(), directions = new Map(), spraySources = new Set(), consumed = new Set();
    let selectedTeam = 'BAT';
    const makeMission = () => new FireMission();
    let mission = makeMission();
    let state = 'playing', pausedFrom = 'playing';
    let unlocked = false, sequence = 0, interactPending = false;
    let animation = null, resizeRequest = null, previousTime = null, visualTime = 0;
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

    function debrief() {
        const won = mission.phase === 'won';
        write(ui['result-title'], won ? 'Feu maîtrisé' : 'Repli du binôme');
        write(ui['result-text'], won
            ? 'Tout le monde est à l’abri. L’intervention est terminée.'
            : 'Le binôme doit se replier. Tu peux recommencer l’intervention.');
    }

    function setState(next) {
        state = next;
        dialog.dataset.state = state;
        for (const child of dialog.children) child.inert = state === 'paused' && child !== ui.paused;
        ui.paused.hidden = state !== 'paused';
        ui.result.hidden = state !== 'result';
        write(ui.resume, pausedFrom === 'result' ? 'Retour au résultat' : 'Reprendre');
        if (ui.notice) ui.notice.hidden = true;
        noticeUntil = 0;
        resetInput();
        if (dialog.open) resize();
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
        if (!assetPromise) assetPromise = loadImage('flames.png').then(flames => Object.assign(assets, { flames }));
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

    function frame(timestamp) {
        if (!dialog.open) { animation = null; return; }
        const dt = previousTime === null ? 0 : Math.min(0.05, (timestamp - previousTime) / 1000);
        previousTime = timestamp;
        if (state !== 'paused') visualTime += dt;
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
        mission = makeMission();
        mission.startOnScene(selectedTeam);
        pointerAim = null; dialogueSignature = ''; hoseWasLimited = false;
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
        selectedTeam = 'BAT';
        freshMission(); setState('playing'); dialog.showModal();
        loadAssets().then(() => { if (dialog.open) draw(); });
        resize(); hud(); canvas.focus({ preventScroll: true });
        previousTime = null; animation = requestAnimationFrame(frame);
    }

    function close() { if (dialog.open) { dialog.close(); cleanup(); } }

    function cleanup() {
        if (animation !== null) cancelAnimationFrame(animation);
        if (resizeRequest !== null) cancelAnimationFrame(resizeRequest);
        animation = null; resizeRequest = null; previousTime = null;
        resetLogoTrigger(); resetInput(); sequence = 0; consumed.clear();
        if (scrollState) {
            document.documentElement.style.overflow = scrollState.html;
            document.body.style.overflow = scrollState.body;
            window.scrollTo({ left: scrollState.x, top: scrollState.y, behavior: 'instant' });
            scrollState = null;
        }
        if (previousFocus instanceof HTMLElement && previousFocus.isConnected) previousFocus.focus({ preventScroll: true });
        previousFocus = null;
    }

    // Une nouvelle intervention est jouable immédiatement, sans séquence d'introduction.
    function start() {
        freshMission(); setState('playing'); hud();
        previousTime = null;
        canvas.focus({ preventScroll: true });
    }

    function pause() {
        if (!dialog.open || state === 'paused') return;
        pausedFrom = state; setState('paused'); ui.resume.focus({ preventScroll: true });
    }

    function resume() {
        if (state !== 'paused') return;
        setState(pausedFrom); previousTime = null;
        const target = state === 'result' ? ui['result-replay'] : canvas;
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
        if (key === 'r' && state === 'paused' && !event.repeat) { event.preventDefault(); start(); return; }
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
    for (const button of dialog.querySelectorAll('[data-fire-quit]')) button.addEventListener('click', close);
    for (const button of dialog.querySelectorAll('[data-fire-pause]')) button.addEventListener('click', toggleMenu);
    ui.start.addEventListener('click', () => { selectedTeam = 'BAT'; start(); });
    ui.bal.addEventListener('click', () => { selectedTeam = 'BAL'; start(); });
    ui.close.addEventListener('click', close);
    ui.restart.addEventListener('click', start); ui['result-replay'].addEventListener('click', start);
    ui.resume.addEventListener('click', resume);
    dialog.addEventListener('cancel', event => { event.preventDefault(); toggleMenu(); });
    dialog.addEventListener('close', () => { if (!dialog.open) cleanup(); });
    window.addEventListener('blur', () => {
        resetLogoTrigger(); sequence = 0; consumed.clear(); resetInput(); if (state === 'playing') pause();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) { resetLogoTrigger(); sequence = 0; consumed.clear(); resetInput(); if (state === 'playing') pause(); }
    });
    window.addEventListener('resize', () => {
        if (!dialog.open) return;
        if (resizeRequest !== null) cancelAnimationFrame(resizeRequest);
        resizeRequest = requestAnimationFrame(() => { resizeRequest = null; resize(); });
    });
    setState('playing'); dialog.dataset.ready = 'true';
}

if (dialog && canvas && dialog.dataset.ready !== 'true') initialize();