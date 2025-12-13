<?php
require_once "../../PerFranMVC/Controller/GameC.php";
require_once "../../config.php";

// Validate user is logged in
$userId = $_SESSION['user_id'] ?? $_SESSION['user']['uid'] ?? null;
$username = $_SESSION['username'] ?? $_SESSION['uid'] ?? null;

if (!$userId) {
  header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
  exit;
}

$gameC = new GameC();
$rows = $gameC->getWaitingGames(); // Only get waiting games
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>PerFran — Salles de Jeu</title>
  <link rel="stylesheet" href="displayGames.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../../owl-mascot.css">
  <script src="https://cdn.socket.io/4.7.2/socket.io.js"></script>
</head>
<body>
  <header class="MainTitle smallHeader">
    <a href="../../index.php" class="brand">
      <img src="../Perfran.png" alt="PerFran" style="height: 60px;" />
      <div>
        <h1>PerFran — Salles de Jeu</h1>
        <p>Créer ou rejoindre une partie</p>
      </div>
    </a>
  </header>

  <main class="container">
    <section class="controls">
      <!-- Create Room Section -->
      <div class="create-room-section">
        <h2>Créer une nouvelle partie</h2>
        <div class="create-form">
          <select id="difficultySelect" style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;">
            <option value="">-- Choisir une difficulté --</option>
            <option value="Easy">Débutant (12 secondes)</option>
            <option value="Medium">Intermédiaire (10 secondes)</option>
            <option value="Hard">Avancé (8 secondes)</option>
          </select>
          <button id="createRoomBtn" class="btn" style="background: #28a745; margin-left: 10px;">Créer une Partie</button>
        </div>
      </div>

      <!-- Filter and Actions -->
      <input id="filterInput" type="search" placeholder="Filtrer par titre, jeu, difficulté ou type..." />
      <div class="actions">
        <a class="btn" href="../../index.php">Retour</a>
        <button class="btn refresh" onclick="location.reload()">Actualiser</button>
      </div>
    </section>

    <!-- Waiting Rooms Section -->
    <section class="tableWrap">
      <h2>Parties en attente de joueur</h2>
      <?php if (empty($rows)): ?>
        <div class="empty">Aucune salle de jeu en attente trouvée.</div>
      <?php else: ?>
        <table id="roomsTable" class="roomsTable" aria-describedby="tableDesc">
          <caption id="tableDesc">Liste des salles de jeu en attente de joueurs</caption>
          <thead>
            <tr>
              <th>ID</th>
              <th>Titre</th>
              <th>Jeu</th>
              <th>Difficulté</th>
              <th>Type</th>
              <th>Créateur</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['gid']); ?></td>
              <td class="cell-title"><?php echo htmlspecialchars($row['title']); ?></td>
              <td class="cell-game"><?php echo htmlspecialchars($row['game']); ?></td>
              <td class="cell-diff"><?php echo htmlspecialchars($row['difficulty']); ?></td>
              <td class="cell-type"><?php echo htmlspecialchars($row['type']); ?></td>
              <td><?php echo htmlspecialchars($row['player1id']); ?></td>
              <td><?php echo htmlspecialchars($row['status']); ?></td>
              <td>
                <button class="btn join" onclick="joinMultiplayerGame('<?php echo htmlspecialchars($row['gid']); ?>', '<?php echo htmlspecialchars($row['difficulty']); ?>')">
                  Rejoindre
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>

  <footer>
    <p>© PerFran — Prism Studio</p>
  </footer>

  <!-- User data for JavaScript -->
  <script>
    // User is guaranteed to be logged in (redirected otherwise)
    window.currentUserId = <?php echo json_encode($userId); ?>;
    window.currentUsername = <?php echo json_encode($username); ?>;
  </script>

  <script src="game1-multiplayer.js"></script>
  <script>
    // ==================== WAITING ROOMS MANAGEMENT ====================

    class WaitingRoomsManager {
        constructor() {
            this.socket = io('http://localhost:3001');
            this.setupSocketListeners();
            this.attachEventListeners();
        }

        setupSocketListeners() {
            // Room created
            this.socket.on('roomCreated', (data) => {
              console.log('Room created:', data.roomId);
              const diff = data.difficulty || document.getElementById('difficultySelect').value;
              
              // Show success popup
              Swal.fire({
                title: '🎮 Salle créée!',
                html: `
                  <div style="text-align: left;">
                    <p><strong>Room ID:</strong> ${data.roomId}</p>
                    <p><strong>Difficulté:</strong> ${diff === 'Easy' ? 'Débutant' : diff === 'Medium' ? 'Intermédiaire' : 'Avancé'}</p>
                    <p style="color: #00d4ff; margin-top: 15px;">✨ En attente d'un adversaire...</p>
                  </div>
                `,
                icon: 'success',
                confirmButtonText: '<i class="fa fa-play"></i> Rejoindre la salle',
                confirmButtonColor: '#00d4ff',
                background: '#1a2f4a',
                color: '#ffffff',
                iconColor: '#00d4ff'
              }).then(() => {
                // Open game in a new tab
                const url = `game1.php?mode=multiplayer&roomId=${encodeURIComponent(data.roomId)}&difficulty=${encodeURIComponent(diff)}`;
                window.open(url, '_blank');
              });
            });

            // Room available (broadcast)
            this.socket.on('roomAvailable', (data) => {
                console.log('Room available:', data.roomId);
                location.reload();
            });

            // Room filled
            this.socket.on('roomFilled', (data) => {
                console.log('Room filled:', data.roomId);
                location.reload();
            });

            // Error
            this.socket.on('error', (data) => {
                Swal.fire({
                  title: '❌ Erreur',
                  text: data.message,
                  icon: 'error',
                  confirmButtonText: '<i class="fa fa-check"></i> OK',
                  confirmButtonColor: '#00d4ff',
                  background: '#1a2f4a',
                  color: '#ffffff',
                  iconColor: '#ff4444'
                });
            });
        }

        attachEventListeners() {
            const createBtn = document.getElementById('createRoomBtn');
            const diffSelect = document.getElementById('difficultySelect');

            createBtn.addEventListener('click', () => {
              const difficulty = diffSelect.value;
              if (!difficulty) {
                Swal.fire({
                  title: '⚠️ Attention',
                  text: 'Veuillez choisir une difficulté',
                  icon: 'warning',
                  confirmButtonText: '<i class="fa fa-check"></i> OK',
                  confirmButtonColor: '#00d4ff',
                  background: '#1a2f4a',
                  color: '#ffffff',
                  iconColor: '#ffa500'
                });
                return;
              }

              // Create room via socket (include difficulty so server can persist it)
              this.socket.emit('createRoom', {
                playerId: window.currentUserId,
                username: window.currentUsername,
                difficulty: difficulty
              });
            });
        }
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', () => {
        window.waitingRoomsManager = new WaitingRoomsManager();
    });

    // ==================== JOIN ROOM FUNCTION ====================

    function joinMultiplayerGame(roomId, difficulty) {
      // Show joining popup
      Swal.fire({
        title: '🚀 Rejoindre la partie',
        html: `
          <div style="text-align: left;">
            <p><strong>Room ID:</strong> ${roomId}</p>
            <p><strong>Difficulté:</strong> ${difficulty === 'Easy' ? 'Débutant' : difficulty === 'Medium' ? 'Intermédiaire' : 'Avancé'}</p>
            <p style="color: #00d4ff; margin-top: 15px;">✨ Préparation de la partie...</p>
          </div>
        `,
        icon: 'info',
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false,
        background: '#1a2f4a',
        color: '#ffffff',
        iconColor: '#00d4ff'
      });
      
      // Emit join request to socket
      window.waitingRoomsManager.socket.emit('joinRoom', {
        roomId: roomId,
        playerId: window.currentUserId,
        username: window.currentUsername
      });

      // Redirect to game1.php with room info (via URL parameters, session validated)
      setTimeout(() => {
        window.location.href = `game1.php?mode=multiplayer&roomId=${encodeURIComponent(roomId)}&difficulty=${encodeURIComponent(difficulty)}`;
      }, 500);
    }
    // ==================== FILTER ====================

    (function () {
      const input = document.getElementById('filterInput');
      const table = document.getElementById('roomsTable');
      if (!input || !table) return;
      const rows = Array.from(table.tBodies[0].rows);
      input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        rows.forEach(r => {
          const title = r.querySelector('.cell-title')?.textContent.toLowerCase() || '';
          const game = r.querySelector('.cell-game')?.textContent.toLowerCase() || '';
          const diff = r.querySelector('.cell-diff')?.textContent.toLowerCase() || '';
          const type = r.querySelector('.cell-type')?.textContent.toLowerCase() || '';
          const show = !q || title.includes(q) || game.includes(q) || diff.includes(q) || type.includes(q);
          r.style.display = show ? '' : 'none';
        });
      });

      // Auto-refresh every 30 seconds to show new waiting games
      setInterval(() => {
        location.reload();
      }, 30000);
    })();
  </script>
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
  <script>
    window.PERFRAN_AI_API_URL = '<?= PERFRAN_AI_API_URL ?>';
  </script>
  <script defer>
    (function(){
      function loadMascot(){
        var s = document.createElement('script');
        s.src = '<?= BASE_URL ?>PerFranMVC/View/FrontOffice/assets/js/mascot.js';
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
</body>
</html>