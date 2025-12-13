<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/GameC.php';

// Check if user is admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 1;
if (!$is_admin) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit();
}

$gameC = new GameC();
$waitingGames = $gameC->getWaitingGames();
$activeGames = $gameC->getActiveGames();
$completedGames = $gameC->getCompletedGames();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>PerFran — Administration des Jeux</title>
  <link rel="stylesheet" href="displayGames.css" />
</head>
<body>
  <header class="MainTitle smallHeader">
    <a href="index.html" class="brand">
      <img src="../Perfran.png" alt="Perfran" />
      <div>
        <h1>PerFran — Administration des Jeux</h1>
        <p>Gestion complète des salles de jeu</p>
      </div>
    </a>
  </header>

  <main class="container">
    <section class="controls">
      <input id="filterInput" type="search" placeholder="Filtrer toutes les tables par titre, jeu, difficulté ou type..." />
      <div class="actions">
        <a class="btn" href="index.html">Retour</a>
        <button class="btn refresh" onclick="location.reload()">Actualiser</button>
      </div>
    </section>

    <!-- Waiting Games Section -->
    <section class="games-section">
      <h2 class="section-title">Salles en Attente (<?php echo count($waitingGames); ?>)</h2>

      <section class="tableWrap">
        <?php if (empty($waitingGames)): ?>
          <div class="empty">Aucune salle de jeu en attente.</div>
        <?php else: ?>
          <table id="waitingTable" class="gamesTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Jeu</th>
                <th>Difficulté</th>
                <th>Type</th>
                <th>Créateur</th>
                <th>Créé le</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($waitingGames as $game): ?>
              <tr>
                <td><?php echo htmlspecialchars($game['gid']); ?></td>
                <td class="cell-title"><?php echo htmlspecialchars($game['title']); ?></td>
                <td class="cell-game"><?php echo htmlspecialchars($game['game']); ?></td>
                <td class="cell-diff"><?php echo htmlspecialchars($game['difficulty']); ?></td>
                <td class="cell-type"><?php echo htmlspecialchars($game['type']); ?></td>
                <td><?php echo htmlspecialchars($game['player1id']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($game['createdAt'])); ?></td>
                <td class="actions-cell">
                  <a class="btn edit" href="EditGame.php?gid=<?php echo htmlspecialchars($game['gid']); ?>">Éditer</a>
                  <a class="btn delete" href="DeleteGame.php?gid=<?php echo htmlspecialchars($game['gid']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle ?');">Supprimer</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    </section>

    <!-- Active Games Section -->
    <section class="games-section">
      <h2 class="section-title">Parties en Cours (<?php echo count($activeGames); ?>)</h2>

      <section class="tableWrap">
        <?php if (empty($activeGames)): ?>
          <div class="empty">Aucune partie en cours.</div>
        <?php else: ?>
          <table id="activeTable" class="gamesTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Jeu</th>
                <th>Difficulté</th>
                <th>Type</th>
                <th>Joueur 1</th>
                <th>Joueur 2</th>
                <th>Débuté le</th>
                <th>Durée</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activeGames as $game): 
                // Calculate current duration
                $duration = '';
                if ($game['startedAt']) {
                  $start = new DateTime($game['startedAt']);
                  $now = new DateTime();
                  $interval = $start->diff($now);
                  $duration = $interval->format('%Hh %Im %Ss');
                }
              ?>
              <tr>
                <td><?php echo htmlspecialchars($game['gid']); ?></td>
                <td class="cell-title"><?php echo htmlspecialchars($game['title']); ?></td>
                <td class="cell-game"><?php echo htmlspecialchars($game['game']); ?></td>
                <td class="cell-diff"><?php echo htmlspecialchars($game['difficulty']); ?></td>
                <td class="cell-type"><?php echo htmlspecialchars($game['type']); ?></td>
                <td><?php echo htmlspecialchars($game['player1id']); ?></td>
                <td><?php echo htmlspecialchars($game['player2id'] ?? 'N/A'); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($game['startedAt'])); ?></td>
                <td class="duration"><?php echo $duration; ?></td>
                <td class="actions-cell">
                  <a class="btn edit" href="EditGame.php?gid=<?php echo htmlspecialchars($game['gid']); ?>">Éditer</a>
                  <a class="btn delete" href="DeleteGame.php?gid=<?php echo htmlspecialchars($game['gid']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette partie ?');">Supprimer</a>
                  <a class="btn view" href="ViewGame.php?gid=<?php echo htmlspecialchars($game['gid']); ?>">Voir</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    </section>

    <!-- Completed Games Section -->
    <section class="games-section">
      <h2 class="section-title">Parties Terminées (<?php echo count($completedGames); ?>)</h2>

      <section class="tableWrap">
        <?php if (empty($completedGames)): ?>
          <div class="empty">Aucune partie terminée.</div>
        <?php else: ?>
          <table id="completedTable" class="gamesTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Jeu</th>
                <th>Difficulté</th>
                <th>Type</th>
                <th>Joueur 1</th>
                <th>Joueur 2</th>
                <th>Vainqueur</th>
                <th>Durée</th>
                <th>Tours</th>
                <th>Terminé le</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($completedGames as $game): 
                // Calculate duration
                $duration = '';
                if ($game['startedAt'] && $game['endedAt']) {
                  $start = new DateTime($game['startedAt']);
                  $end = new DateTime($game['endedAt']);
                  $interval = $start->diff($end);
                  $duration = $interval->format('%Hh %Im %Ss');
                }
              ?>
              <tr>
                <td><?php echo htmlspecialchars($game['gid']); ?></td>
                <td class="cell-title"><?php echo htmlspecialchars($game['title']); ?></td>
                <td class="cell-game"><?php echo htmlspecialchars($game['game']); ?></td>
                <td class="cell-diff"><?php echo htmlspecialchars($game['difficulty']); ?></td>
                <td class="cell-type"><?php echo htmlspecialchars($game['type']); ?></td>
                <td><?php echo htmlspecialchars($game['player1id']); ?></td>
                <td><?php echo htmlspecialchars($game['player2id'] ?? 'N/A'); ?></td>
                <td>
                  <?php if ($game['winner']): ?>
                    <span class="winner <?php echo htmlspecialchars($game['winner']); ?>">
                      <?php echo htmlspecialchars($game['winner'] === 'player1' ? 'Joueur 1' : 'Joueur 2'); ?>
                    </span>
                  <?php else: ?>
                    <em>Non défini</em>
                  <?php endif; ?>
                </td>
                <td class="duration"><?php echo $duration; ?></td>
                <td><?php echo htmlspecialchars($game['rounds_played']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($game['endedAt'])); ?></td>
                <td class="actions-cell">
                  <a class="btn delete" href="DeleteGame.php?gid=<?php echo htmlspecialchars($game['gid']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette partie ?');">Supprimer</a>
                  <a class="btn view" href="ViewGame.php?gid=<?php echo htmlspecialchars($game['gid']); ?>">Détails</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    </section>
  </main>

  <footer>
    <p>© PerFran — Prism Studio</p>
  </footer>

  <script>
    (function () {
      const globalFilter = document.getElementById('globalFilter');
      
      // Function to filter a specific table
      function filterTable(tableId, filterValue) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = Array.from(table.tBodies[0].rows);
        rows.forEach(r => {
          const title = r.querySelector('.cell-title')?.textContent.toLowerCase() || '';
          const game = r.querySelector('.cell-game')?.textContent.toLowerCase() || '';
          const diff = r.querySelector('.cell-diff')?.textContent.toLowerCase() || '';
          const type = r.querySelector('.cell-type')?.textContent.toLowerCase() || '';
          const show = !filterValue || title.includes(filterValue) || game.includes(filterValue) || diff.includes(filterValue) || type.includes(filterValue);
          r.style.display = show ? '' : 'none';
        });
      }
      
      // Function to filter all tables
      function filterAllTables(filterValue) {
        filterTable('waitingTable', filterValue);
        filterTable('activeTable', filterValue);
        filterTable('completedTable', filterValue);
      }
      
      // Add event listener to the global filter
      if (globalFilter) {
        globalFilter.addEventListener('input', () => {
          const filterValue = globalFilter.value.trim().toLowerCase();
          filterAllTables(filterValue);
        });
      }

      // Auto-refresh every 30 seconds
      setInterval(() => {
        location.reload();
      }, 30000);
    })();
  </script>
</body>
</html>