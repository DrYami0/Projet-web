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
const MAX_ROUNDS = 40;

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
  // solo elements
  labelTimerSolo: document.getElementById('labelTimerSolo'),
  wordSelectorSolo: document.getElementById('wordSelectorSolo'),
  answersSolo: document.getElementById('answersSolo'),
  scoreSoloP: document.querySelector('#scoreSolo p'),
  // multiplayer elements
  roomId: document.getElementById('roomId'),
  leaveRoom: document.getElementById('leaveRoom'),
  // player1
  labelTimerP1: document.getElementById('labelTimerP1'),
  wordSelectorP1: document.getElementById('wordSelectorP1'),
  answersP1: document.getElementById('answersP1'),
  scoreP1: document.querySelector('#scoreP1 p'),
  // player2
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
  } else { // Hard or default
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
    if (this.used.size >= this.arrayUsed.length) return null;
    let item;
    do { item = this.arrayUsed[Math.floor(Math.random() * this.arrayUsed.length)]; }
    while (this.used.has(item) && this.used.size < this.arrayUsed.length);
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
    if (this.round >= this.maxRounds) return this.end();
    this._nextRound();
  }

  _onAnswer(e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    this._stopTimer();
    const selected = btn.id;
    if (!this.current || selected !== this.current.type) this.wrong.push(this.current);
    this.round++;
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

/* UI flow */
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

/* bind mode buttons */
els.btnSolo.addEventListener('click', () => { chosenMode = 'solo'; showDifficultyChoice(); });
els.btnMulti.addEventListener('click', () => { chosenMode = 'multiplayer'; showDifficultyChoice(); });
els.backToMode.addEventListener('click', showModeChoice);

/* bind difficulty buttons */
els.diffButtons.forEach(btn => btn.addEventListener('click', () => {
  const diff = btn.dataset.diff || 'Easy';
  startGame(chosenMode, diff);
}));

let activeInstances = [];

function startGame(mode, diff) {
  hideAll();
  const time = TIMES[diff] || TIMES.Easy;
  const arrayUsed = diff === 'Hard' ? SAMPLE_HARD : (diff === 'Medium' ? SAMPLE_MEDIUM : SAMPLE_EASY);
  els.resultsArea.textContent = '';

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
      els.resultsArea.innerHTML = `<p>Fin Solo — erreurs: ${wrong.length}/${round}</p>` +
        wrong.map(w => `<p class="wrongChoices">${w.word} : ${w.type}</p>`).join('');
      activeInstances = activeInstances.filter(i => i !== solo);
    };
  } else {
    els.multiplayer.classList.remove('hidden');
    const room = 'ROOM-' + Math.random().toString(36).slice(2,7).toUpperCase();
    if (els.roomId) els.roomId.textContent = room;

    createAnswerButtons(els.answersP1, diff);
    createAnswerButtons(els.answersP2, diff);

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
      els.resultsArea.innerHTML += `<p>Joueur 1 terminé — erreurs: ${res.wrong.length}</p>`;
      activeInstances = activeInstances.filter(i => i !== p1);
    };
    p2.onEnd = (res) => {
      els.resultsArea.innerHTML += `<p>Joueur 2 terminé — erreurs: ${res.wrong.length}</p>`;
      activeInstances = activeInstances.filter(i => i !== p2);
    };

    if (els.leaveRoom) {
      els.leaveRoom.onclick = () => {
        activeInstances.forEach(i => i.cleanup());
        activeInstances = [];
        showModeChoice();
      };
    }
  }
}

/* init */
showModeChoice();