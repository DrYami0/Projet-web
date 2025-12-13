'use strict';

const SAMPLE_EASY = [
  { word: 'chat', type: 'Nom/G.N' },
  { word: 'manger', type: 'Verbe' },
  { word: 'grand', type: 'Adjectif' },
  { word: 'le', type: 'Déterminant' },
  { word: 'vite', type: 'Adverbe' }
];
const SAMPLE_MEDIUM = SAMPLE_EASY.concat([
  { word: 'sur', type: 'Préposition' },
  { word: 'lui', type: 'Pronom' }
]);
const SAMPLE_HARD = SAMPLE_MEDIUM.concat([
  { word: 'mais', type: 'Conjonction' }
]);

const TIMES = { Easy: 12, Medium: 10, Hard: 8 };
const MAX_ROUNDS = 15;
// Debug: show the effective rounds value to help verify the loaded script version in browser
try { console.debug('[game1] MAX_ROUNDS =', MAX_ROUNDS); } catch(e) {}

// Helper: persist a game result to server-side endpoint
function saveGameResultToServer(payload){
  try{
    fetch('PerFranMVC/Controller/save-game-result.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function(res){ return res.json().catch(function(){ return {}; }); }).then(function(d){ console.log('[game1] save-game-result response', d); }).catch(function(err){ console.warn('[game1] save-game-result failed', err); });
  }catch(e){ console.warn('[game1] save-game-result exception', e); }
}

let wordCache = { easy: null, medium: null, hard: null };

async function fetchWords(difficulty) {
  const key = difficulty.toLowerCase();
  if (wordCache[key]) return wordCache[key];
  
  try {
    const response = await fetch(`getWords.php?difficulty=${key}`);
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const data = await response.json();
    wordCache[key] = data && data.length > 0 ? data : getDefaultSample(difficulty);
    return wordCache[key];
  } catch (error) {
    console.error('Failed to fetch words:', error);
    return getDefaultSample(difficulty);
  }
}

function getDefaultSample(difficulty) {
  const d = difficulty.toLowerCase();
  if (d === 'medium') return SAMPLE_MEDIUM;
  if (d === 'hard') return SAMPLE_HARD;
  return SAMPLE_EASY;
}

const els = {
  modeChoice: document.querySelector('.modeChoice'),
  difficultyChoice: document.querySelector('.difficultyChoice'),
  soloGame: document.querySelector('.soloGame'),
  multiplayer: document.querySelector('.multiplayer'),
  resultsArea: document.getElementById('resultsArea'),
  btnSolo: document.getElementById('buttonSolo'),
  btnMulti: document.getElementById('buttonMultiplayer'),
  backToMode: document.getElementById('backToMode'),
  diffButtons: Array.from(document.querySelectorAll('.difficultyChoice button[data-diff]')),
  labelTimerSolo: document.getElementById('labelTimerSolo'),
  wordSelectorSolo: document.getElementById('wordSelectorSolo'),
  answersSolo: document.getElementById('answersSolo'),
  scoreSoloP: document.querySelector('#scoreSolo p'),
  roomId: document.getElementById('roomId'),
  leaveRoom: document.getElementById('leaveRoom'),
  labelTimerP1: document.getElementById('labelTimerP1'),
  wordSelectorP1: document.getElementById('wordSelectorP1'),
  answersP1: document.getElementById('answersP1'),
  scoreP1: document.querySelector('#scoreP1 p'),
  labelTimerP2: document.getElementById('labelTimerP2'),
  wordSelectorP2: document.getElementById('wordSelectorP2'),
  answersP2: document.getElementById('answersP2'),
  scoreP2: document.querySelector('#scoreP2 p')
};

function createAnswerButtons(container, diff) {
  if (!container) return;
  let ids;
  if (diff === 'Easy') {
    ids = ['Nom/G.N', 'Adjectif', 'Verbe', 'Déterminant'];
  } else if (diff === 'Medium') {
    ids = ['Nom/G.N', 'Adjectif', 'Verbe', 'Déterminant', 'Adverbe', 'Préposition'];
  } else {
    ids = ['Nom/G.N', 'Adjectif', 'Verbe', 'Déterminant', 'Adverbe', 'Préposition', 'Pronom', 'Conjonction'];
  }
  container.innerHTML = '';
  ids.forEach(id => {
    const b = document.createElement('button');
    b.className = 'answersBtn';
    b.id = id;
    b.textContent = id;
    container.appendChild(b);
  });
}

class GameInstance {
  constructor({ arrayUsed = SAMPLE_EASY, time = 10, labelEl, wordEl, answersEl, scoreEl, maxRounds = MAX_ROUNDS }) {
    this.arrayUsed = arrayUsed.slice();
    this.time = time;
    this.labelEl = labelEl;
    this.wordEl = wordEl;
    this.answersEl = answersEl;
    this.scoreEl = scoreEl;
    this.maxRounds = maxRounds;

    this.round = 0;
    this.wrong = [];
    this.timerId = null;
    this.current = null;
    this.used = new Set();
    this.onEnd = () => {};

    this._onAnswer = this._onAnswer.bind(this);
    if (this.answersEl) this.answersEl.addEventListener('click', this._onAnswer);
    this._nextRound();
  }

  _pickRandomUnused() {
    // If the available pool is empty, bail out
    if (!this.arrayUsed || this.arrayUsed.length === 0) return null;

    // If we've used all unique items, allow repeats by clearing the used set.
    // This ensures the game can reach the configured `maxRounds` (e.g. 15)
    // even when the source word list is smaller than the number of rounds.
    if (this.used.size >= this.arrayUsed.length) {
      this.used.clear();
    }

    let item;
    // Pick a random item; prefer an unused one but allow some retries for small pools.
    let attempts = 0;
    do {
      item = this.arrayUsed[Math.floor(Math.random() * this.arrayUsed.length)];
      attempts++;
      if (attempts > 20) break; // safety bail
    } while (this.used.has(item));

    this.used.add(item);
    return item;
  }

  _updateUI() {
    if (this.wordEl) this.wordEl.textContent = this.current ? this.current.word : '—';
    if (this.scoreEl) this.scoreEl.textContent = `${this.round}/${this.maxRounds}`;
  }

  _startTimer() {
    this._stopTimer();
    let left = this.time;
    if (this.labelEl) this.labelEl.textContent = `${left}s`;
    this.timerId = setInterval(() => {
      left--;
      if (this.labelEl) this.labelEl.textContent = `${left}s`;
      if (left < 0) this._handleTimeout();
    }, 1000);
  }

  _stopTimer() {
    if (this.timerId) { clearInterval(this.timerId); this.timerId = null; }
  }

  _handleTimeout() {
    this._stopTimer();
    if (this.current) this.wrong.push(this.current);
    this.round++;
    this._updateUI();
    if (this.round >= this.maxRounds) return this.end();
    this._nextRound();
  }

  _onAnswer(e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    this._stopTimer();
    const selected = btn.id;
    
    // Only count wrong answers, don't increment round for correct ones
    if (!this.current || selected !== this.current.type) {
      this.wrong.push(this.current);
    }
    
    this.round++;
    this._updateUI();
    if (this.round >= this.maxRounds) return this.end();
    this._nextRound();
  }

  _nextRound() {
    this.current = this._pickRandomUnused();
    if (!this.current) {
      if (this.labelEl) this.labelEl.textContent = 'Pas de mots.';
      this.end();
      return;
    }
    this._updateUI();
    this._startTimer();
  }

  end() {
    this._stopTimer();
    if (this.answersEl) this.answersEl.removeEventListener('click', this._onAnswer);
    this.onEnd({ round: this.round, wrong: this.wrong.slice() });
  }

  cleanup() { this.end(); }
}

let chosenMode = null;

function hideAll() {
  els.difficultyChoice.classList.add('hidden');
  els.soloGame.classList.add('hidden');
  els.multiplayer.classList.add('hidden');
  els.resultsArea.textContent = '';
}

function showModeChoice() {
  hideAll();
  els.modeChoice.classList.remove('hidden');
}

function showDifficultyChoice() {
  els.modeChoice.classList.add('hidden');
  els.difficultyChoice.classList.remove('hidden');
}

els.btnSolo.addEventListener('click', () => { chosenMode = 'solo'; showDifficultyChoice(); });
els.btnMulti.addEventListener('click', () => { chosenMode = 'multiplayer'; showDifficultyChoice(); });
els.backToMode.addEventListener('click', showModeChoice);

els.diffButtons.forEach(btn => btn.addEventListener('click', () => {
  const diff = btn.dataset.diff || 'Easy';
  startGame(chosenMode, diff);
}));

let activeInstances = [];

async function startGame(mode, diff) {
  hideAll();
  const time = TIMES[diff] || TIMES.Easy;
  const arrayUsed = await fetchWords(diff);
  els.resultsArea.textContent = '';

  // Welcome popup with SweetAlert2
  await Swal.fire({
    title: '🎮 Prêt à jouer?',
    html: `<div style="text-align: left;">
      <p><strong>Mode:</strong> ${mode === 'solo' ? 'Solo' : 'Multijoueur'}</p>
      <p><strong>Difficulté:</strong> ${diff === 'Easy' ? 'Débutant' : diff === 'Medium' ? 'Intermédiaire' : 'Avancé'}</p>
      <p><strong>Temps par mot:</strong> ${time}s</p>
      <p><strong>Objectif:</strong> Identifie ${MAX_ROUNDS} types de mots!</p>
      <p style="color: #00d4ff; margin-top: 15px;">💡 Clique sur la bonne catégorie grammaticale avant la fin du temps!</p>
    </div>`,
    icon: 'info',
    confirmButtonText: '<i class="fa fa-play"></i> Commencer!',
    confirmButtonColor: '#00d4ff',
    background: '#1a2f4a',
    color: '#ffffff',
    iconColor: '#00d4ff',
    showClass: {
      popup: 'animate__animated animate__fadeInDown'
    }
  });

  if (mode === 'solo') {
    els.soloGame.classList.remove('hidden');
    createAnswerButtons(els.answersSolo, diff);
    const solo = new GameInstance({
      arrayUsed,
      time,
      labelEl: els.labelTimerSolo,
      wordEl: els.wordSelectorSolo,
      answersEl: els.answersSolo,
      scoreEl: els.scoreSoloP
    });
    activeInstances.push(solo);
    solo.onEnd = ({ round, wrong }) => {
      displayResults(wrong, round);
      activeInstances = activeInstances.filter(i => i !== solo);
      
      // Show completion popup
      const score = round - wrong.length;
      const percentage = Math.round((score / round) * 100);
      
      let icon = 'success';
      let title = '🏆 Excellent travail!';
      let message = 'Tu es un champion de la grammaire!';
      
      if (percentage === 100) {
        icon = 'success';
        title = '🎉 PARFAIT!';
        message = 'Aucune erreur! Tu maîtrises parfaitement!';
      } else if (percentage >= 80) {
        icon = 'success';
        title = '🌟 Très bien!';
        message = 'Continue comme ça!';
      } else if (percentage >= 60) {
        icon = 'info';
        title = '👍 Bon travail!';
        message = 'Tu progresses bien!';
      } else {
        icon = 'warning';
        title = '💪 Continue!';
        message = 'Ne lâche pas, tu vas y arriver!';
      }
      
      // Attempt to save result to server (single-player)
      try{
        (function(){
          var scoreToSave = (round - wrong.length) || 0;
          var payload = { gameType: 1, score: scoreToSave, maxScore: round, won: (scoreToSave >= Math.ceil(round/2)) };
          fetch('PerFranMVC/Controller/save-game-result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          }).then(function(res){ return res.json().catch(function(){ return {}; }); }).then(function(d){ console.log('[game1] save-game-result response', d); }).catch(function(err){ console.warn('[game1] save-game-result failed', err); });
        })();
      }catch(_){ }

      Swal.fire({
        title: title,
        html: `
          <div style="font-size: 18px;">
            <p>${message}</p>
            <hr style="border-color: #00d4ff; margin: 20px 0;">
            <p><strong>Score:</strong> ${score}/${round}</p>
            <p><strong>Précision:</strong> ${percentage}%</p>
            <p><strong>Erreurs:</strong> ${wrong.length}</p>
          </div>
        `,
        icon: icon,
        confirmButtonText: '<i class="fa fa-redo"></i> Rejouer',
        confirmButtonColor: '#00d4ff',
        background: '#1a2f4a',
        color: '#ffffff',
        iconColor: '#00d4ff',
        showCancelButton: true,
        showCloseButton: false,
        allowOutsideClick: true,
        allowEscapeKey: true,
        cancelButtonText: '<i class="fa fa-home"></i> Menu',
        cancelButtonColor: '#1a2f4a',
        footer: '<div style="text-align:right; width:100%;"><button id="swal-close-footer" style="background:transparent;border:1px solid rgba(255,255,255,0.06);color:#fff;padding:6px 10px;border-radius:6px;cursor:pointer">✕</button></div>',
        didOpen: (popup) => {
          try{
            var btnClose = document.getElementById('swal-close-footer');
            if(btnClose){ btnClose.addEventListener('click', function(e){ e.preventDefault(); window._swal_footer_replay = true; Swal.close(); }); }
          }catch(_){ }
        }
      }).then((result) => {
        try{
          if(window._swal_footer_replay){ window._swal_footer_replay = false; showDifficultyChoice(); return; }
        }catch(_){ }
        // Swap logic: Confirm (Rejouer) and Cancel (Menu) both go back to mode/menu.
        // Other dismissals (click outside / esc) will start difficulty choice (replay).
        if (result.isConfirmed) {
          showModeChoice();
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          showModeChoice();
        } else {
          showDifficultyChoice();
        }
      });
    };
  } else {
    els.multiplayer.classList.remove('hidden');
    
    // Create room via Socket.IO
    socket.emit('create-room', { 
      username: currentUsername, 
      difficulty: diff 
    });

    createAnswerButtons(els.answersP1, diff);
    createAnswerButtons(els.answersP2, diff);

    // Mark both answer panels as waiting initially
    els.answersP1.classList.add('waiting-opponent');
    els.answersP2.classList.add('waiting-opponent');

    // Wait for game-start event before creating game instances
    socket.once('game-start', () => {
      // Start countdown before game
      startCountdown('multi', () => {
        const p1 = new GameInstance({
          arrayUsed,
          time,
          labelEl: els.labelTimerP1,
          wordEl: els.wordSelectorP1,
          answersEl: els.answersP1,
          scoreEl: els.scoreP1
        });
        const p2 = new GameInstance({
          arrayUsed,
          time,
          labelEl: els.labelTimerP2,
          wordEl: els.wordSelectorP2,
          answersEl: els.answersP2,
          scoreEl: els.scoreP2
        });

        activeInstances.push(p1, p2);

        p1.onEnd = (res) => {
          displayResults(res.wrong, res.round);
          activeInstances = activeInstances.filter(i => i !== p1);
        };
        p2.onEnd = (res) => {
          displayResults(res.wrong, res.round);
          activeInstances = activeInstances.filter(i => i !== p2);
        };
      });
    });

    if (els.leaveRoom) {
      els.leaveRoom.onclick = () => {
        activeInstances.forEach(i => i.cleanup());
        activeInstances = [];
        socket.emit('leave-room', { roomId: currentRoomId });
        showModeChoice();
      };
    }
  }
}

function displayResults(wrong, round) {
  if (wrong.length === 0) {
    els.resultsArea.innerHTML = `<p style="color:#28a745;font-weight:600;font-size:20px;">Parfait! Aucune erreur — ${round}/${MAX_ROUNDS}</p>`;
    return;
  }

  let html = `
    <div class="resultsContainer">
      <h3 style="text-align:center;color:#017a8a;margin-bottom:16px;">Erreurs — ${wrong.length} sur ${round}</h3>
      <table class="resultsTable" aria-describedby="resultsDesc">
        <caption id="resultsDesc"></caption>
        <thead>
          <tr>
            <th>#</th>
            <th>Mot</th>
            <th>Type Correct</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
  `;

  wrong.forEach((item, i) => {
    html += `
      <tr>
        <td>${i + 1}</td>
        <td class="cell-word">${htmlEscape(item.word)}</td>
        <td class="cell-type">${htmlEscape(item.type)}</td>
        <td>
          <button class="btn-explain" data-word="${htmlEscape(item.word)}" data-type="${htmlEscape(item.type)}">
            Expliquez l'erreur
          </button>
        </td>
      </tr>
    `;
  });

  html += `
        </tbody>
      </table>
    </div>
  `;

  els.resultsArea.innerHTML = html;

  // attach explain button handlers
  document.querySelectorAll('.btn-explain').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const word = btn.dataset.word;
      const type = btn.dataset.type;
      explainError(word, type, btn);
    });
  });
}

function htmlEscape(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

async function explainError(word, grammarType, buttonEl) {
  // Ensure any open result modal is closed so explanation can be shown
  try{ if (typeof Swal !== 'undefined' && Swal.close) Swal.close(); }catch(_){ }
  const originalText = buttonEl.textContent;
  buttonEl.textContent = 'Chargement...';
  buttonEl.disabled = true;

  try {
    const response = await fetch('explain-error.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        word: word,
        type: grammarType
      })
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.error) {
      displayExplanation(word, `Erreur: ${data.error}`);
    } else {
      displayExplanation(word, data.explanation);
    }
  } catch (error) {
    console.error('Error:', error);
    displayExplanation(word, 'Désolé, une erreur est survenue lors de la génération de l\'explication.');
  } finally {
    buttonEl.textContent = originalText;
    buttonEl.disabled = false;
  }
}

function displayExplanation(word, explanation) {
  Swal.fire({
    title: `📚 Explication pour "${word}"`,
    html: `<div style="text-align: left; font-size: 16px; line-height: 1.6;">${explanation}</div>`,
    icon: 'info',
    confirmButtonText: '<i class="fa fa-check"></i> Compris!',
    confirmButtonColor: '#00d4ff',
    background: '#1a2f4a',
    color: '#ffffff',
    iconColor: '#00d4ff',
    width: '600px'
  });
}

showModeChoice();