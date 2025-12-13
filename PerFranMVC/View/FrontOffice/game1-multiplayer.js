// Replace the multiplayer section in your startGame function with this:

if (mode === 'multiplayer') {
  els.multiplayer.classList.remove('hidden');
  
  // Create room via Socket.IO
  socket.emit('create-room', { 
    username: currentUsername, 
    difficulty: diff 
  });

  createAnswerButtons(els.answersP1, diff);
  createAnswerButtons(els.answersP2, diff);

  // Initially disable all buttons and show waiting state
  if (window.multiplayerUtils) {
    window.multiplayerUtils.setWaitingState(true);
  } else {
    // Fallback if multiplayer utils not loaded
    const answersP1 = document.getElementById('answersP1');
    const answersP2 = document.getElementById('answersP2');
    if (answersP1) answersP1.classList.add('waiting-opponent');
    if (answersP2) answersP2.classList.add('waiting-opponent');
  }

  // Wait for game-start event before creating game instances
  socket.once('game-start', (data) => {
    console.log('[Game1] Game starting with data:', data);
    
    // Enable buttons
    if (window.multiplayerUtils) {
      window.multiplayerUtils.setWaitingState(false);
    } else {
      const answersP1 = document.getElementById('answersP1');
      const answersP2 = document.getElementById('answersP2');
      if (answersP1) answersP1.classList.remove('waiting-opponent');
      if (answersP2) answersP2.classList.remove('waiting-opponent');
    }
    
    // Start countdown before game
    startCountdown('multi', () => {
      const p1 = new GameInstance({
        arrayUsed,
        time,
        labelEl: document.getElementById('labelTimerMulti'),
        wordEl: document.getElementById('wordSelectorMulti'),
        answersEl: els.answersP1,
        scoreEl: els.scoreP1
      });
      
      const p2 = new GameInstance({
        arrayUsed,
        time,
        labelEl: document.getElementById('labelTimerMulti'),
        wordEl: document.getElementById('wordSelectorMulti'),
        answersEl: els.answersP2,
        scoreEl: els.scoreP2
      });

      activeInstances.push(p1, p2);

      p1.onEnd = (res) => {
        displayResults(res.wrong, res.round);
        activeInstances = activeInstances.filter(i => i !== p1);
        try{
          var scoreToSave = (res.round - (res.wrong && res.wrong.length ? res.wrong.length : 0)) || 0;
          var payload = { gameType: 2, score: scoreToSave, maxScore: res.round, won: (scoreToSave >= Math.ceil(res.round/2)) };
          fetch('PerFranMVC/Controller/save-game-result.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
          }).then(function(r){ return r.json().catch(function(){ return {}; }); }).then(function(d){ console.log('[game1-multi] save-game-result p1', d); }).catch(function(err){ console.warn('[game1-multi] save-game-result p1 failed', err); });
        }catch(_){ }
      };
      
      p2.onEnd = (res) => {
        displayResults(res.wrong, res.round);
        activeInstances = activeInstances.filter(i => i !== p2);
        try{
          var scoreToSave2 = (res.round - (res.wrong && res.wrong.length ? res.wrong.length : 0)) || 0;
          var payload2 = { gameType: 2, score: scoreToSave2, maxScore: res.round, won: (scoreToSave2 >= Math.ceil(res.round/2)) };
          fetch('PerFranMVC/Controller/save-game-result.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload2)
          }).then(function(r){ return r.json().catch(function(){ return {}; }); }).then(function(d){ console.log('[game1-multi] save-game-result p2', d); }).catch(function(err){ console.warn('[game1-multi] save-game-result p2 failed', err); });
        }catch(_){ }
      };
    });
  });

  // Handle leave room button
  if (els.leaveRoom) {
    els.leaveRoom.onclick = () => {
      activeInstances.forEach(i => i.cleanup());
      activeInstances = [];
      socket.emit('leave-room', { roomId: currentRoomId });
      Swal.close(); // Close any open popups
      showModeChoice();
    };
  }
}