<?php
require_once "../../Controller/TypeC.php";
require_once "../../Model/Type.php";

$message = '';
$old = [
    'type' => '',
    'difficulty' => ''
];

$allowedDifficulties = ['easy','medium','hard'];
$easyTypes = ['Nom/G.N','Adjectif','Verbe','Déterminant'];
$mediumExtra = ['Adverbe','Préposition'];
$hardExtra = ['Pronom','Conjonction'];

if (isset($_GET['wid'])) {
    $wid = intval($_GET['wid']);
    $typeC = new TypeC();
    $stmt = $typeC->displayTypes();
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    foreach ($rows as $row) {
        if ($row['wid'] == $wid) {
            $old['type'] = $row['type'];
            $old['difficulty'] = $row['difficulty'];
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['type'] = trim($_POST['type'] ?? '');
    $old['difficulty'] = $_POST['difficulty'] ?? '';

    // Type validation
    if ($old['type'] === '') {
        $message = 'Le champ "Type" est obligatoire.';
    } elseif (!in_array($old['difficulty'], $allowedDifficulties, true)) {
        $message = 'Difficulté invalide.';
    } else {
        // Build allowed types based on difficulty
        if ($old['difficulty'] === 'easy') {
            $allowedTypes = $easyTypes;
        } elseif ($old['difficulty'] === 'medium') {
            $allowedTypes = array_merge($easyTypes, $mediumExtra);
        } else { // hard
            $allowedTypes = array_merge($easyTypes, $mediumExtra, $hardExtra);
        }

        if ($old['type'] === '' || !in_array($old['type'], $allowedTypes, true)) {
            $message = 'Type invalide pour la difficulté choisie.';
        } else {
            try {
                $typeObj = new Type($old['type'], $old['difficulty']);
                $typeC->editType($typeObj, $wid);
                header('Location: DisplayTypes.php?edited=1');
                exit;
            } catch (Exception $e) {
                $message = 'Erreur lors de l\'édition : ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>PerFran — Éditer un type</title>
  <link rel="stylesheet" href="displayWords.css" />
  <link rel="stylesheet" href="addWord.css" />
</head>
<body>
  <header class="MainTitle smallHeader">
    <a href="index.html" class="brand">
      <img src="../Perfran.png" alt="Perfran" />
      <div>
        <h1>Éditer un type</h1>
        <p>Modifiez les informations du type</p>
      </div>
    </a>
  </header>

  <main class="container">
    <section class="formWrap tableWrap">
      <?php if ($message): ?>
        <div class="empty" style="background:#fff6f6;color:#a33;margin-bottom:12px;"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <form method="post" action="editType.php?wid=<?php echo htmlspecialchars($wid); ?>" class="addForm" id="editTypeForm" novalidate>
        <label>
          Difficulté
          <select name="difficulty" id="difficultySelect" required>
            <option value="">-- Choisir --</option>
            <option value="easy"<?php if($old['difficulty']==='easy') echo ' selected'; ?>>Débutant</option>
            <option value="medium"<?php if($old['difficulty']==='medium') echo ' selected'; ?>>Intermédiaire</option>
            <option value="hard"<?php if($old['difficulty']==='hard') echo ' selected'; ?>>Avancé</option>
          </select>
        </label>

        <label>
          Type
          <select name="type" id="typeSelect" required>
            <option value="">-- Choisir --</option>
          </select>
        </label>

        <div class="formActions">
          <button type="submit" class="btn">Mettre à jour</button>
          <a class="btn secondary" href="DisplayTypes.php">Annuler / Retour</a>
        </div>
      </form>
    </section>
  </main>

  <footer>
    <p>© PerFran — Prism Studio</p>
  </footer>

  <script>
    (function () {
      const easy = ['Nom/G.N','Adjectif','Verbe','Déterminant'];
      const mediumExtra = ['Adverbe','Préposition'];
      const hardExtra = ['Pronom','Conjonction'];

      const difficultySelect = document.getElementById('difficultySelect');
      const typeSelect = document.getElementById('typeSelect');
      const form = document.getElementById('editTypeForm');

      function buildTypes(diff, selectedType) {
        let types = easy.slice();
        if (diff === 'medium') types = types.concat(mediumExtra);
        if (diff === 'hard') types = types.concat(mediumExtra, hardExtra);
        typeSelect.innerHTML = '<option value="">-- Choisir --</option>';
        types.forEach(t => {
          const opt = document.createElement('option');
          opt.value = t;
          opt.textContent = t;
          if (t === selectedType) opt.selected = true;
          typeSelect.appendChild(opt);
        });
      }

      // initialize if server preserved previous selection
      const preDiff = '<?php echo htmlspecialchars($old['difficulty']); ?>';
      const preType = '<?php echo htmlspecialchars($old['type']); ?>';
      if (preDiff) {
        buildTypes(preDiff, preType);
      }

      difficultySelect.addEventListener('change', () => {
        const d = difficultySelect.value;
        if (!d) {
          typeSelect.innerHTML = '<option value="">-- Choisir --</option>';
          return;
        }
        buildTypes(d, preType);
      });
    })();
  </script>
</body>
</html>