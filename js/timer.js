/**
 * timer.js — Chefly Recipe Step Timers (orologio analogico + digitale)
 */

(function () {
    'use strict';

    const timers = {};

    function playDone() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            [0, 0.18, 0.36].forEach((delay, i) => {
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.value = i === 2 ? 880 : 660;
                gain.gain.setValueAtTime(0.4, ctx.currentTime + delay);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + delay + 0.3);
                osc.start(ctx.currentTime + delay);
                osc.stop(ctx.currentTime + delay + 0.35);
            });
        } catch (_) {}
    }

    function fmt(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    function minuteAngle(remaining) {
        return (remaining / 60 % 60) * 6;
    }

    function secondAngle(remaining) {
        return (remaining % 60) * 6;
    }

    function updateUI(passoId) {
        const t     = timers[passoId];
        const block = document.querySelector(`.chefly-timer[data-passo-id="${passoId}"]`);
        if (!block) return;

        const display    = block.querySelector('.ct-display');
        const handMin    = block.querySelector('.ct-hand-minute');
        const handSec    = block.querySelector('.ct-hand-second');
        const btnPlay    = block.querySelector('.ct-btn-play');
        const btnPause   = block.querySelector('.ct-btn-pause');
        const btnReset   = block.querySelector('.ct-btn-reset');
        const stateLabel = block.querySelector('.ct-state-label');
        const clockFace  = block.querySelector('.ct-clock-face');

        // Aggiornamento tempo e rotazione lancette
        display.textContent = fmt(t.remaining);
        handMin.style.transform = `translateX(-50%) rotate(${minuteAngle(t.remaining)}deg)`;
        handSec.style.transform = `translateX(-50%) rotate(${secondAngle(t.remaining)}deg)`;

        // Gestione colori e stati visivi
        if (t.state === 'done') {
            display.style.color      = '#27ae60';
            clockFace.style.boxShadow = '0 0 0 3px #27ae6033';
            handSec.style.background  = '#27ae60';
        } else if (t.remaining <= 30 && t.state === 'running') {
            display.style.color      = '#e74c3c';
            clockFace.style.boxShadow = '0 0 0 3px #e74c3c33';
            handSec.style.background  = '#e74c3c';
        } else {
            display.style.color      = '#2d1b10';
            clockFace.style.boxShadow = '0 4px 20px rgba(45,27,16,0.10)';
            handSec.style.background  = '#a67c52';
        }

        // Visibilità bottoni
        btnPlay.style.display  = t.state === 'running' ? 'none' : 'flex';
        btnPause.style.display = t.state === 'running' ? 'flex' : 'none';
        btnReset.style.display = t.state === 'idle'    ? 'none' : 'flex';

        const labels = { idle: 'Pronto', running: 'In corso…', paused: 'In pausa', done: 'Completato!' };
        stateLabel.textContent = labels[t.state] || '';
        stateLabel.className   = `ct-state-label ct-state-${t.state}`;
    }

    function startTimer(passoId) {
        const t = timers[passoId];
        if (!t || t.state === 'running' || t.state === 'done') return;
        t.state = 'running';
        t.interval = setInterval(() => {
            t.remaining--;
            if (t.remaining <= 0) {
                t.remaining = 0;
                t.state = 'done';
                clearInterval(t.interval);
                playDone();
                if (Notification.permission === 'granted') {
                    new Notification('Chefly – Timer completato! 🍳', {
                        body: 'Il passo è pronto.',
                        icon: '../img/logo.png'
                    });
                }
            }
            updateUI(passoId);
        }, 1000);
        updateUI(passoId);
    }

    function pauseTimer(passoId) {
        const t = timers[passoId];
        if (!t || t.state !== 'running') return;
        clearInterval(t.interval);
        t.state = 'paused';
        updateUI(passoId);
    }

    function resetTimer(passoId) {
        const t = timers[passoId];
        if (!t) return;
        clearInterval(t.interval);
        t.remaining = t.total;
        t.state = 'idle';
        updateUI(passoId);
    }

    function buildClockMarks() {
        let marks = '';
        for (let i = 0; i < 60; i++) {
            const angle  = i * 6;
            const isMaj  = i % 5 === 0;
            const r      = 44;
            const len    = isMaj ? 8 : 4;
            const sw     = isMaj ? 2 : 1;
            const color  = isMaj ? '#2d1b10' : '#d0c8be';
            const rad    = angle * Math.PI / 180;
            const x1     = (50 + r * Math.sin(rad)).toFixed(2);
            const y1     = (50 - r * Math.cos(rad)).toFixed(2);
            const x2     = (50 + (r - len) * Math.sin(rad)).toFixed(2);
            const y2     = (50 - (r - len) * Math.cos(rad)).toFixed(2);
            marks += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="${color}" stroke-width="${sw}" stroke-linecap="round"/>`;
        }
        [12,1,2,3,4,5,6,7,8,9,10,11].forEach((n, i) => {
            const rad = i * 30 * Math.PI / 180;
            const rx  = (50 + 33 * Math.sin(rad)).toFixed(2);
            const ry  = (50 - 33 * Math.cos(rad)).toFixed(2);
            marks += `<text x="${rx}" y="${ry}" text-anchor="middle" dominant-baseline="central" font-size="7" font-family="Montserrat,sans-serif" font-weight="600" fill="#2d1b10">${n}</text>`;
        });
        return marks;
    }

    function buildWidget(passoId, durataMin, labelText) {
        const totalSec   = durataMin * 60;
        const clockMarks = buildClockMarks();

        // NOTA: Ho rimosso lo stile 'transform' inline dalle lancette per evitare errori IDE.
        // Sarà la funzione updateUI() a impostare la posizione iniziale.
        return `
<div class="chefly-timer" data-passo-id="${passoId}">
  <div class="ct-inner">

    <div class="ct-clock-wrap">
      <div class="ct-clock-face">
        <svg class="ct-clock-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">${clockMarks}</svg>
        <div class="ct-hand ct-hand-minute"></div>
        <div class="ct-hand ct-hand-second"></div>
        <div class="ct-clock-center"></div>
        <span class="ct-display">${fmt(totalSec)}</span>
      </div>
    </div>

    <div class="ct-controls">
      <p class="ct-label">${labelText}</p>
      <p class="ct-duration">${durataMin} min</p>
      <div class="ct-btns">
        <button class="ct-btn ct-btn-play"  onclick="CheflyTimer.start('${passoId}')" title="Avvia">
          <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5,3 19,12 5,21"/></svg>
        </button>
        <button class="ct-btn ct-btn-pause" onclick="CheflyTimer.pause('${passoId}')" title="Pausa" style="display:none">
          <svg viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        </button>
        <button class="ct-btn ct-btn-reset" onclick="CheflyTimer.reset('${passoId}')" title="Reset" style="display:none">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.5"/></svg>
        </button>
      </div>
      <span class="ct-state-label ct-state-idle">Pronto</span>
    </div>

  </div>
</div>`;
    }

    function init() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
        document.querySelectorAll('[data-chefly-timer]').forEach(el => {
            const passoId = el.dataset.cheflyTimer; // Supporta sia numeri che stringhe
            const durata  = parseInt(el.dataset.durata, 10) || 1;
            const label   = el.dataset.label || `Passo ${passoId}`;

            if (!passoId) return;

            timers[passoId] = { total: durata * 60, remaining: durata * 60, state: 'idle', interval: null };
            el.innerHTML = buildWidget(passoId, durata, label);

            // Applica immediatamente lo stato iniziale alle lancette
            updateUI(passoId);
        });
    }

    window.CheflyTimer = { start: startTimer, pause: pauseTimer, reset: resetTimer, init };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();