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
const MAX_ROUNDS = 10;

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
  countdownSolo: document.getElementById('countdownSolo'),
  countdownValueSolo: document.getElementById('countdownValue'),
  roomId: document.getElementById('roomId'),
  leaveRoom: document.getElementById('leaveRoom'),
  labelTimerMulti: document.getElementById('labelTimerMulti'),
  wordSelectorMulti: document.getElementById('wordSelectorMulti'),
  answersP1: document.getElementById('answersP1'),
  scoreP1: document.querySelector('#scoreP1 p'),
  answersP2: document.getElementById('answersP2'),
  scoreP2: document.querySelector('#scoreP2 p'),
  countdownMulti: document.getElementById('countdownMulti'),
  countdownValueMulti: document.getElementById('countdownValueMulti')
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
    if (this.used.size >= this.arrayUsed.length) return null;
    let item;
    do { item = this.arrayUsed[Math.floor(Math.random() * this.arrayUsed.length)]; }
    while (this.used.has(item) && this.used.size < this.arrayUsed.length);
    this.used.add(item);
    return item;
  }

  _updateUI() {
    if (this.wordEl) this.wordEl.textContent = this.current ? this.current.word : '—';
    if (this.scoreEl) {
      this.scoreEl.textContent = `${this.round}/${this.maxRounds}`;
      // Trigger bounce animation
      const scoreContainer = this.scoreEl.closest('.score');
      if (scoreContainer) {
        scoreContainer.classList.remove('bounce');
        void scoreContainer.offsetWidth; // Trigger reflow to restart animation
        scoreContainer.classList.add('bounce');
      }
    }
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
    if (!this.current || selected !== this.current.type) this.wrong.push(this.current);
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

function showCountdown(countdownEl, countdownValueEl, duration = 3) {
  return new Promise((resolve) => {
    if (!countdownEl) {
      resolve();
      return;
    }
    
    // Show the overlay with fade-in animation
    countdownEl.classList.remove('hidden');
    countdownEl.style.display = 'grid';
    countdownEl.style.visibility = 'visible';
    
    let count = duration;
    if (countdownValueEl) countdownValueEl.textContent = count;

    const interval = setInterval(() => {
      count--;
      if (countdownValueEl) countdownValueEl.textContent = count;
      if (count <= 0) {
        clearInterval(interval);
        countdownEl.classList.add('hidden');
        resolve();
      }
    }, 1000);
  });
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

  if (mode === 'solo') {
    els.soloGame.classList.remove('hidden');
    createAnswerButtons(els.answersSolo, diff);
    
    // Show countdown before game starts
    await showCountdown(els.countdownSolo, els.countdownValueSolo, 3);
    
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
    };
  } else {
    els.multiplayer.classList.remove('hidden');
    const room = 'ROOM-' + Math.random().toString(36).slice(2, 7).toUpperCase();
    if (els.roomId) els.roomId.textContent = room;

    createAnswerButtons(els.answersP1, diff);
    createAnswerButtons(els.answersP2, diff);

    // Show countdown before game starts
    await showCountdown(els.countdownMulti, els.countdownValueMulti, 3);

    const p1 = new GameInstance({
      arrayUsed,
      time,
      labelEl: els.labelTimerMulti,
      wordEl: els.wordSelectorMulti,
      answersEl: els.answersP1,
      scoreEl: els.scoreP1
    });
    const p2 = new GameInstance({
      arrayUsed,
      time,
      labelEl: els.labelTimerMulti,
      wordEl: els.wordSelectorMulti,
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

    if (els.leaveRoom) {
      els.leaveRoom.onclick = () => {
        activeInstances.forEach(i => i.cleanup());
        activeInstances = [];
        showModeChoice();
      };
    }
  }
}

function displayResults(wrong, round) {
  if (wrong.length === 0) {
    els.resultsArea.innerHTML = `<p class="success-message">Parfait! Aucune erreur — ${round}/10</p>`;
    return;
  }

  let html = `
    <div class="resultsContainer">
      <h3 style="text-align:center;color:#017a8a;margin-bottom:16px;">Erreurs — ${wrong.length} sur 10</h3>
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
  const originalText = buttonEl.textContent;
  buttonEl.textContent = 'Chargement...';
  buttonEl.classList.add('loading');
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
    buttonEl.classList.remove('loading');
    buttonEl.disabled = false;
  }
}

function displayExplanation(word, explanation) {
  const modal = document.createElement('div');
  modal.className = 'explanation-modal';
  modal.innerHTML = `
    <div class="explanation-content">
      <button class="explanation-close">&times;</button>
      <h3>Explication pour "${htmlEscape(word)}"</h3>
      <p>${htmlEscape(explanation)}</p>
      <button class="explanation-ok">D'accord</button>
    </div>
  `;

  document.body.appendChild(modal);

  const closeBtn = modal.querySelector('.explanation-close');
  const okBtn = modal.querySelector('.explanation-ok');

  const removeModal = () => modal.remove();

  closeBtn.addEventListener('click', removeModal);
  okBtn.addEventListener('click', removeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) removeModal();
  });
}

showModeChoice();