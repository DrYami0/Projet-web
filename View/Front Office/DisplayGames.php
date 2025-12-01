<?php
require_once "../../Controller/GameC.php";
$gameC = new GameC();
$rows = $gameC->getWaitingGames(); // Only get waiting games
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>PerFran — Salles de Jeu</title>
  <link rel="stylesheet" href="displayRooms.css" />
</head>
<body>
  <header class="MainTitle smallHeader">
    <a href="index.html" class="brand">
      <img src="../Perfran.png" alt="Perfran" />
      <div>
        <h1>PerFran — Salles de Jeu</h1>
        <p>Liste des salles disponibles (En attente)</p>
      </div>
    </a>
  </header>

  <main class="container">
    <section class="controls">
      <input id="filterInput" type="search" placeholder="Filtrer par titre, jeu, difficulté ou type..." />
      <div class="actions">
        <a class="btn" href="index.html">Retour</a>
        <button class="btn refresh" onclick="location.reload()">Actualiser</button>
      </div>
    </section>

    <section class="tableWrap">
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
                <a class="btn edit" href="EditGame.php?gid=<?php echo htmlspecialchars($row['gid']); ?>">Éditer</a>
                <a class="btn delete" href="DeleteGame.php?gid=<?php echo htmlspecialchars($row['gid']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette salle ?');">Supprimer</a>
                <?php if (empty($row['player2id'])): ?>
                  <a class="btn join" href="JoinGame.php?gid=<?php echo htmlspecialchars($row['gid']); ?>">Rejoindre</a>
                <?php endif; ?>
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

  <script>
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
</body>
</html>