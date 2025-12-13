<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/TypeC.php';

// Check if user is admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 1;
if (!$is_admin) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit();
}

$typeC = new TypeC();
$stmt = $typeC->displayTypes();
$rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>PerFran — Types</title>
  <link rel="stylesheet" href="displayWords.css" />
</head>
<body>
  <header class="MainTitle smallHeader">
    <a href="index.html" class="brand">
      <img src="../Perfran.png" alt="Perfran" />
      <div>
        <h1>PerFran — Types</h1>
        <p>Liste des types disponibles</p>
      </div>
    </a>
  </header>

  <main class="container">
    <section class="controls">
      <input id="filterInput" type="search" placeholder="Filtrer par type ou difficulté..." />
      <div class="actions">
        <a class="btn" href="index.html">Retour</a>
        <a class="btn secondary" href="addType.php">Ajouter un type</a>
      </div>
    </section>

    <section class="tableWrap">
      <?php if (empty($rows)): ?>
        <div class="empty">Aucun type trouvé.</div>
      <?php else: ?>
        <table id="typesTable" class="wordsTable" aria-describedby="tableDesc">
          <caption id="tableDesc">Liste des types et leurs difficultés</caption>
          <thead>
            <tr>
              <th>ID</th>
              <th>Type</th>
              <th>Difficulté</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['wid'] ?? $row['id'] ?? ''); ?></td>
              <td class="cell-type"><?php echo htmlspecialchars($row['type']); ?></td>
              <td class="cell-diff"><?php echo htmlspecialchars($row['difficulty']); ?></td>
              <td>
                <a class="btn edit" href="EditType.php?wid=<?php echo htmlspecialchars($row['wid']); ?>">Éditer</a>
                <a class="btn delete" href="DeleteType.php?wid=<?php echo htmlspecialchars($row['wid']); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce type ?');">Supprimer</a>
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
      const table = document.getElementById('typesTable');
      if (!input || !table) return;
      const rows = Array.from(table.tBodies[0].rows);
      input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        rows.forEach(r => {
          const type = r.querySelector('.cell-type')?.textContent.toLowerCase() || '';
          const diff = r.querySelector('.cell-diff')?.textContent.toLowerCase() || '';
          const show = !q || type.includes(q) || diff.includes(q);
          r.style.display = show ? '' : 'none';
        });
      });
    })();
  </script>
</body>
</html>