<?php ob_start('ob_gzhandler'); 
header('Cache-Control: public, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
header('Vary: Accept-Encoding');
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link href="game1.css" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" defer>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'" defer>
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"></noscript>
    <link href="../../owl-mascot.css" type="text/css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PerFran | Jeu de Grammaire</title>
  </head>
  <body>
    <div style="position: fixed; top: 10px; right: 10px; z-index: 10000;">
      <button id="themeToggle" class="theme-toggle" style="width: auto; margin: 0; padding: 8px 12px; background: rgba(255,255,255,0.1); border-radius: 8px; border: 1px solid rgba(255,255,255,0.15); color: #e8f4f8; cursor: pointer; font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease;">
        <i class="fas fa-sun"></i>
        <span>Thème clair</span>
      </button>
    </div>
    
    <a href="/projet-web/index.php" style="text-decoration: none">
      <div class="MainTitle">
        <img src="/projet-web/PerFranMVC/View/Perfran.png" alt="PerFran Logo" style="height: 60px;" loading="lazy" />
        <p>Étudiez et jouez, Deux en un!</p>
      </div>
    </a>

    <script src="https://cdn.socket.io/4.7.2/socket.io.js" defer></script>
    <script>
      // Get logged-in username from PHP session
      <?php
      session_start();
      
      // Use uid which contains the username
      $username = $_SESSION['uid'] ?? 'Joueur 1';
      
      echo "const currentUsername = " . json_encode($username) . ";";
      ?>

      // Initialize Socket.IO
      const socket = io('http://localhost:3001');
      let currentRoomId = null;
      let playerNumber = null;
      let gameReady = false;

      // Set username immediately when DOM is ready
      document.addEventListener('DOMContentLoaded', () => {
        const player1NameEl = document.getElementById('player1Name');
        if (player1NameEl) {
          player1NameEl.textContent = currentUsername;
          console.log('Username set to:', currentUsername);
        }
      });

      socket.on('connect', () => {
        console.log('Connected to server:', socket.id);
      });

      socket.on('room-created', (data) => {
        currentRoomId = data.roomId;
        playerNumber = data.playerNumber;
        console.log('Room created:', currentRoomId);
        document.getElementById('roomId').textContent = currentRoomId;
        document.getElementById('player1Name').textContent = currentUsername;
        
        // Player 1 buttons disabled until Player 2 joins
        document.getElementById('answersP1').classList.add('waiting-opponent');
      });

      socket.on('player-joined', (data) => {
        console.log('Player joined:', data);
        
        if (data.players.length === 2) {
          // Both players present - update UI
          const player2 = data.players.find(p => p.playerNumber === 2);
          document.getElementById('player2Name').textContent = player2.username;
          
          // Remove waiting state from Player 2
          document.getElementById('answersP2').classList.remove('waiting-opponent');
          
          // Both players ready - enable buttons and start game
          gameReady = true;
          document.getElementById('answersP1').classList.remove('waiting-opponent');
          
          // Auto-ready both players
          socket.emit('player-ready', { roomId: currentRoomId });
        }
      });

      socket.on('game-start', (data) => {
        console.log('Game starting!', data);
        // The countdown will be triggered from game1.js startGame function
      });

      socket.on('player-left', (data) => {
        console.log('Player left:', data);
        
        // Reset to waiting state
        document.getElementById('player2Name').textContent = 'En attente...';
        document.getElementById('answersP2').classList.add('waiting-opponent');
        document.getElementById('answersP1').classList.add('waiting-opponent');
        gameReady = false;
      });

      socket.on('room-error', (data) => {
        alert('Erreur: ' + data.message);
      });

      // Read URL parameters for room joining
      const urlParams = new URLSearchParams(window.location.search);
      const paramRoom = urlParams.get('roomId');
      const paramMode = urlParams.get('mode');
      const paramDiff = urlParams.get('difficulty');

      if (paramRoom) {
        // Joining existing room from DisplayGames
        window.roomId = paramRoom;
        window.gameMode = paramMode || 'multiplayer';
        window.gameDifficulty = paramDiff || null;
      } else {
        // Starting fresh (mode selection)
        window.roomId = null;
        window.gameMode = 'solo';
        window.gameDifficulty = null;
      }
    </script>

    <main class="container">
      <section class="modeChoice">
        <p>Choisissez le mode de jeu</p>
        <button id="buttonSolo">Solo</button>
        <button id="buttonMultiplayer">Multijoueur</button>
      </section>

      <section class="difficultyChoice hidden" style="animation: fadeInDown 0.5s ease;">
        <p>Choisissez la difficulté</p>
        <button data-diff="Easy" id="diffButtonEasy" class="difficulty-btn">Débutant</button>
        <button data-diff="Medium" id="diffButtonMedium" class="difficulty-btn">Intermédiaire</button>
        <button data-diff="Hard" id="diffButtonHard" class="difficulty-btn">Avancé</button>
        <button id="backToMode" class="small">Retour</button>
      </section>

      <!-- Solo area -->
      <section class="game soloGame hidden" aria-hidden="true" style="animation: fadeInDown 0.5s ease;">
        <div id="countdownSolo" class="countdown-overlay hidden">
          <div class="countdown-content">
            <p id="countdownValue">3</p>
          </div>
        </div>
        <div class="game-header-solo">
          <p id="labelTimerSolo" class="labelTimer">Bonne Chance!</p>
          <div class="wordSelector" id="wordSelectorSolo">À vos places!</div>
        </div>

        <div class="answers" id="answersSolo">
          <button class="answersBtn" id="Nom/G.N">Nom/G.N</button>
          <button class="answersBtn" id="Adjectif">Adjectif</button>
          <button class="answersBtn" id="Verbe">Verbe</button>
          <button class="answersBtn" id="Déterminant">Déterminant</button>
          <div class="mediumAnswers">
            <button class="answersBtn" id="Adverbe">Adverbe</button>
            <button class="answersBtn" id="Préposition">Préposition</button>
          </div>
          <div class="hardAnswers">
            <button class="answersBtn" id="Pronom">Pronom</button>
            <button class="answersBtn" id="Conjonction">Conjonction</button>
          </div>
        </div>

        <div class="score" id="scoreSolo"><p>0/15</p></div>
      </section>

      <!-- Multiplayer area (split-screen) -->
      <section class="multiplayer hidden" aria-hidden="true" style="animation: fadeInDown 0.5s ease;">
        <div id="countdownMulti" class="countdown-overlay hidden">
          <div class="countdown-content">
            <p id="countdownValueMulti">3</p>
          </div>
        </div>
        <div class="multiplayerHeader">
          <p>Room: <span id="roomId">-</span> (session placeholder)</p>
          <button id="leaveRoom" class="small">Quitter</button>
        </div>

        <div class="game-header-multiplayer">
          <p id="labelTimerMulti" class="labelTimer">Prêt</p>
          <div class="wordSelector" id="wordSelectorMulti">À vos places!</div>
        </div>

        <div class="split">
          <div class="playerPanel" id="player1">
            <h3 id="player1Name">Joueur 1</h3>
            <div class="answers waiting-opponent" id="answersP1">
              <button class="answersBtn" id="Nom/G.N">Nom/G.N</button>
              <button class="answersBtn" id="Adjectif">Adjectif</button>
              <button class="answersBtn" id="Verbe">Verbe</button>
              <button class="answersBtn" id="Déterminant">Déterminant</button>
            </div>
            <div class="score" id="scoreP1"><p>0/15</p></div>
          </div>

          <div class="divider" aria-hidden="true"></div>

          <div class="playerPanel" id="player2">
            <h3 id="player2Name">En attente...</h3>
            <div class="answers waiting-opponent" id="answersP2">
              <button class="answersBtn" id="Nom/G.N">Nom/G.N</button>
              <button class="answersBtn" id="Adjectif">Adjectif</button>
              <button class="answersBtn" id="Verbe">Verbe</button>
              <button class="answersBtn" id="Déterminant">Déterminant</button>
            </div>
            <div class="score" id="scoreP2"><p>0/15</p></div>
          </div>
        </div>
      </section>

      <section class="gameResults"><p id="resultsArea"></p></section>
    </main>

    <?php $v = file_exists(__DIR__ . '/game1.js') ? filemtime(__DIR__ . '/game1.js') : time(); ?>
    <script src="game1.js?v=<?php echo $v; ?>"></script>
    <script src="game1-multiplayer.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="../../assets/js/theme-toggle.js" defer></script>
    <script src="../../assets/js/game-encouragement.js" defer></script>
    <script defer>
      (function(){
        function loadMascot(){
          var s = document.createElement('script');
          s.src = '../../assets/js/mascot.js';
          s.defer = true;
          document.body.appendChild(s);
        }
        if(document.readyState === 'complete' || document.readyState === 'interactive'){
          setTimeout(loadMascot, 700);
        } else {
          window.addEventListener('DOMContentLoaded', function(){ setTimeout(loadMascot, 700); });
        }
      })();
    </script>
    <footer>
      <p>Copyright &copy; 2025 Prism Studio | All rights reserved</p>
    </footer>
  </body>
</html><?php ob_end_flush(); ?>
