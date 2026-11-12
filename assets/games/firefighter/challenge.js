// Défi autonome : le chrono continue même lorsque le menu du jeu est ouvert.
export class FireChallenge {
    constructor(panel, { apiUrl, csrfToken, isLoggedIn }) {
        this.panel = panel;
        this.apiUrl = apiUrl;
        this.csrfToken = csrfToken;
        this.isLoggedIn = isLoggedIn;
        this.ui = {};
        for (const name of ['start', 'target', 'save', 'replay', 'score', 'time', 'final-score',
            'result', 'message', 'login', 'arena', 'arena-hint']) {
            this.ui[name] = panel.querySelector('#fire-challenge-' + name);
        }
        this.homeButton = panel.querySelector('[data-fire-home]');
        this.generation = 0;
        this.opened = false;
        this.phase = 'idle';
        this.clientId = null;
        this.runId = null;
        this.score = 0;
        this.deadline = 0;
        this.frame = null;
        this.finishTimer = null;
        this.finishConfirmed = false;
        this.canSave = false;
        this.saving = false;
        this.saved = false;
        this.cancelPending = Promise.resolve();

        this.ui.start.addEventListener('click', () => this.start());
        this.ui.target.addEventListener('click', () => this.hit());
        this.ui.target.addEventListener('keydown', event => {
            if (event.repeat && (event.key === 'Enter' || event.key === ' ')) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
        this.ui.save.addEventListener('click', () => this.save());
        this.ui.replay.addEventListener('click', () => this.open());
        if (typeof ResizeObserver === 'function') {
            this.resizeObserver = new ResizeObserver(() => {
                if (this.phase === 'running') this.placeTarget(false);
            });
            this.resizeObserver.observe(this.ui.arena);
        }
        this.render();
    }

    get active() {
        return ['starting', 'running', 'finishing'].includes(this.phase);
    }

    open() {
        this.cancel();
        this.opened = true;
        this.message('Prêt : démarrez le défi, puis éteignez le plus de feux possible.');
        this.focus();
    }

    focus() {
        if (!this.opened || this.panel.hidden || this.panel.inert || !this.panel.getClientRects().length) return;
        let button = this.homeButton;
        if (this.phase === 'idle') button = this.ui.start;
        else if (this.phase === 'running') button = this.ui.target;
        else if (this.phase === 'finished') {
            button = this.ui.replay;
            if (this.canSave && !this.saved && !this.saving) button = this.ui.save;
        }
        if (button && !button.disabled) button.focus({ preventScroll: true });
    }

    // La génération empêche une ancienne réponse de rouvrir un défi annulé.
    cancel() {
        const clientId = this.clientId;
        const runId = this.runId;
        this.generation++;
        this.opened = false;
        this.stopTimers();
        this.phase = 'idle';
        this.clientId = null;
        this.runId = null;
        this.score = 0;
        this.deadline = 0;
        this.finishConfirmed = false;
        this.canSave = false;
        this.saving = false;
        this.saved = false;
        this.ui.time.textContent = '10.0';
        this.message('');
        this.render();
        if (clientId) this.cancelRun(clientId, runId);
    }

    current(generation, clientId) {
        return this.opened && generation === this.generation && clientId === this.clientId;
    }

    makeClientId() {
        if (typeof crypto.randomUUID === 'function') return crypto.randomUUID();
        const bytes = crypto.getRandomValues(new Uint8Array(16));
        bytes[6] = (bytes[6] & 15) | 64;
        bytes[8] = (bytes[8] & 63) | 128;
        const hex = Array.from(bytes, value => value.toString(16).padStart(2, '0')).join('');
        return hex.slice(0, 8) + '-' + hex.slice(8, 12) + '-' + hex.slice(12, 16)
            + '-' + hex.slice(16, 20) + '-' + hex.slice(20);
    }

    async start() {
        if (!this.opened || this.phase !== 'idle') return;
        const generation = ++this.generation;
        const clientId = this.makeClientId();
        this.clientId = clientId;
        this.phase = 'starting';
        this.message('Préparation du défi…');
        this.render();
        let startedRunId = null;
        try {
            await this.cancelPending;
            if (!this.current(generation, clientId)) return;
            const result = await this.request('start', { client_id: clientId });
            startedRunId = result.run_id;
            if (!this.current(generation, clientId)) {
                this.cancelRun(clientId, result.run_id);
                return;
            }
            if (result.state !== 'active' || !/^[a-f0-9]{32}$/i.test(result.run_id)
                || result.duration_ms !== 10000 || result.client_id !== clientId) {
                throw new Error('Le serveur n’a pas pu préparer ce défi.');
            }
            this.runId = result.run_id;
            this.deadline = performance.now() + 10000;
            this.phase = 'running';
            this.message('À vous de jouer !');
            this.render();
            this.placeTarget(true);
            this.focus();
            this.finishTimer = setTimeout(() => this.finish(generation), 10000);
            this.tick(generation);
        } catch (error) {
            this.cancelRun(clientId, startedRunId);
            if (!this.current(generation, clientId)) return;
            this.phase = 'idle';
            this.clientId = null;
            this.runId = null;
            this.message(error.message || 'Le défi est indisponible. Réessayez.', true);
            this.render();
            this.focus();
        }
    }

    hit() {
        if (!this.opened || this.phase !== 'running') return;
        if (performance.now() >= this.deadline) {
            this.finish(this.generation);
            return;
        }
        if (this.score >= 1000) return;
        this.score++;
        this.ui.score.textContent = String(this.score);
        this.placeTarget(true);
    }

    placeTarget(random) {
        const width = this.ui.arena.clientWidth;
        const height = this.ui.arena.clientHeight;
        if (width <= 0 || height <= 0) return;
        const maxX = Math.max(0, width - this.ui.target.offsetWidth);
        const maxY = Math.max(0, height - this.ui.target.offsetHeight);
        let x = Math.min(maxX, Math.max(0, parseFloat(this.ui.target.style.left) || 0));
        let y = Math.min(maxY, Math.max(0, parseFloat(this.ui.target.style.top) || 0));
        if (random) {
            const marginX = Math.min(8, maxX / 2);
            const marginY = Math.min(8, maxY / 2);
            x = marginX + Math.random() * Math.max(0, maxX - marginX * 2);
            y = marginY + Math.random() * Math.max(0, maxY - marginY * 2);
        }
        this.ui.target.style.left = Math.round(x) + 'px';
        this.ui.target.style.top = Math.round(y) + 'px';
    }

    tick(generation) {
        if (!this.opened || generation !== this.generation || this.phase !== 'running') return;
        const remaining = Math.max(0, this.deadline - performance.now());
        this.ui.time.textContent = (Math.ceil(remaining / 100) / 10).toFixed(1);
        if (remaining === 0) {
            this.finish(generation);
            return;
        }
        this.frame = requestAnimationFrame(() => this.tick(generation));
    }

    stopTimers() {
        if (this.frame !== null) cancelAnimationFrame(this.frame);
        if (this.finishTimer !== null) clearTimeout(this.finishTimer);
        this.frame = null;
        this.finishTimer = null;
    }

    async finish(generation) {
        if (!this.opened || generation !== this.generation || this.phase !== 'running') return;
        const clientId = this.clientId;
        const runId = this.runId;
        this.stopTimers();
        this.phase = 'finishing';
        this.ui.time.textContent = '0.0';
        this.message('Validation du score…');
        this.render();
        try {
            const result = await this.request('finish', { run_id: runId, score: this.score });
            if (!this.current(generation, clientId)) {
                this.cancelRun(clientId, runId);
                return;
            }
            if (result.state !== 'finished' || !Number.isInteger(result.score)
                || result.score < 0 || result.score > 1000) {
                throw new Error('Le serveur n’a pas pu valider ce score.');
            }
            this.score = result.score;
            this.finishConfirmed = true;
            this.canSave = this.isLoggedIn && result.can_save === true;
            this.phase = 'finished';
            this.message('Défi terminé : ' + this.score + ' feux éteints.');
        } catch (error) {
            if (!this.current(generation, clientId)) return;
            this.cancelRun(clientId, runId);
            this.phase = 'finished';
            this.message('Score non validé : ' + (error.message || 'réessayez un nouveau défi.'), true);
        }
        this.render();
        this.focus();
    }

    async save() {
        if (!this.opened || this.phase !== 'finished' || !this.finishConfirmed
            || !this.canSave || this.saving || this.saved) return;
        const generation = this.generation;
        const clientId = this.clientId;
        const runId = this.runId;
        this.saving = true;
        this.message('Sauvegarde du score…');
        this.render();
        try {
            const result = await this.request('save', { run_id: runId });
            if (!this.current(generation, clientId)) return;
            if (result.state !== 'saved' || result.saved !== true
                || !Number.isInteger(result.best_score) || result.best_score < 0) {
                throw new Error('La sauvegarde n’a pas été confirmée.');
            }
            this.saved = true;
            this.message('Score sauvegardé. Meilleur score enregistré : ' + result.best_score + '.');
        } catch (error) {
            if (!this.current(generation, clientId)) return;
            this.message(error.message || 'La sauvegarde a échoué. Réessayez.', true);
        }
        if (!this.current(generation, clientId)) return;
        this.saving = false;
        this.render();
        this.focus();
    }

    render() {
        this.panel.dataset.phase = this.phase;
        this.ui.score.textContent = String(this.score);
        this.ui['final-score'].textContent = String(this.score);
        this.ui.start.hidden = this.phase === 'finished';
        this.ui.start.disabled = this.phase !== 'idle';
        this.ui.target.hidden = this.phase !== 'running';
        this.ui.target.disabled = this.phase !== 'running';
        this.ui.result.hidden = this.phase !== 'finished';
        this.ui.save.hidden = this.phase !== 'finished' || !this.canSave || !this.finishConfirmed;
        this.ui.save.disabled = this.saving || this.saved || !this.finishConfirmed;
        this.ui.save.textContent = this.saved ? 'Score sauvegardé' : 'Sauvegarder mon score';
        this.ui.replay.disabled = this.saving;
        this.ui.login.hidden = this.phase !== 'finished' || this.canSave || !this.finishConfirmed;
        this.ui['arena-hint'].hidden = this.phase === 'running';
        let hint = 'Appuyez sur Démarrer pour commencer.';
        if (this.phase === 'starting') hint = 'Préparation du terrain…';
        else if (this.phase === 'finishing') hint = 'Vérification du résultat…';
        else if (this.phase === 'finished') hint = 'Défi terminé.';
        this.ui['arena-hint'].textContent = hint;
    }

    message(text, error = false) {
        this.ui.message.textContent = text;
        this.ui.message.classList.toggle('is-error', error);
    }

    async request(action, values, keepalive = false) {
        const response = await fetch(this.apiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ action, csrf_token: this.csrfToken, ...values }),
            keepalive,
        });
        let result;
        try {
            result = await response.json();
        } catch (error) {
            throw new Error('La réponse du serveur est illisible. Réessayez.');
        }
        if (!response.ok || !result || result.ok !== true) {
            let text = 'La demande a échoué. Réessayez.';
            if (result && typeof result.message === 'string') text = result.message;
            throw new Error(text);
        }
        return result;
    }

    cancelRun(clientId, runId) {
        const values = { client_id: clientId };
        if (typeof runId === 'string' && runId !== '') values.run_id = runId;
        const pending = this.request('cancel', values, true).catch(() => {
            // Une perte de réseau ne doit pas empêcher de quitter le jeu.
        });
        this.cancelPending = Promise.all([this.cancelPending, pending]).then(() => {});
        return pending;
    }
}
