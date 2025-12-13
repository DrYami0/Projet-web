<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Controller/DictionaryC.php';
require_once "../../PerFranMVC/Model/Dictionary.php";

// Check if user is admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 1;
if (!$is_admin) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit();
}

$message = '';
$old = [
    'word' => '',
    'difficulty' => '',
    'type' => ''
];

$fieldErrors = [
    'word' => '',
    'difficulty' => '',
    'type' => ''
];

$allowedDifficulties = ['easy','medium','hard'];
$easyTypes = ['Nom/G.N','Adjectif','Verbe','Déterminant'];
$mediumExtra = ['Adverbe','Préposition'];
$hardExtra = ['Pronom','Conjonction'];

if (isset($_GET['wid'])) {
    $wid = intval($_GET['wid']);
    $dictC = new DictionaryC();
    $stmt = $dictC->displayWords();
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    foreach ($rows as $row) {
        if ($row['wid'] == $wid) {
            $old['word'] = $row['word'];
            $old['difficulty'] = $row['difficulty'];
            $old['type'] = $row['type'];
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['word'] = trim($_POST['word'] ?? '');
    $old['difficulty'] = $_POST['difficulty'] ?? '';
    $old['type'] = $_POST['type'] ?? '';

    $hasErrors = false;

    // Word validation
    if ($old['word'] === '') {
        $fieldErrors['word'] = 'Le champ "Mot" est obligatoire.';
        $hasErrors = true;
    } elseif (!preg_match("/^[\p{L}' ]+$/u", $old['word'])) {
        $fieldErrors['word'] = "Le mot ne doit contenir que des lettres, espaces et l'apostrophe (').";
        $hasErrors = true;
    }

    // Difficulty validation
    if (!in_array($old['difficulty'], $allowedDifficulties, true)) {
        $fieldErrors['difficulty'] = 'Veuillez choisir une difficulté.';
        $hasErrors = true;
    }

    // Type validation
    if (!$hasErrors) {
        // Build allowed types based on difficulty
        if ($old['difficulty'] === 'easy') {
            $allowedTypes = $easyTypes;
        } elseif ($old['difficulty'] === 'medium') {
            $allowedTypes = array_merge($easyTypes, $mediumExtra);
        } else { // hard
            $allowedTypes = array_merge($easyTypes, $mediumExtra, $hardExtra);
        }

        if ($old['type'] === '' || !in_array($old['type'], $allowedTypes, true)) {
            $fieldErrors['type'] = 'Veuillez choisir un type valide.';
            $hasErrors = true;
        }
    }

    if (!$hasErrors) {
        try {
            $dict = new Dictionary($old['word'], $old['type'], $old['difficulty']);
            $dictC->editWord($dict, $wid);
            header('Location: DisplayWords.php?edited=1');
            exit;
        } catch (Exception $e) {
            $message = 'Erreur lors de l\'édition : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>PerFran — Éditer un mot</title>
  <link rel="stylesheet" href="displayWords.css" />
  <link rel="stylesheet" href="addWord.css" />
  <style>
    .error-message {
        color: #d93025;
        font-size: 0.875rem;
        margin-top: 4px;
        display: block;
    }
    .field-error {
        border-color: #d93025 !important;
        background-color: #fff8f8 !important;
    }
  </style>
</head>
<body>
  <header class="MainTitle smallHeader">
    <a href="/projet-web/index.php" class="brand">
      <img src="../Perfran.png" alt="Perfran" />
      <div>
        <h1>Éditer un mot</h1>
        <p>Modifiez les informations du mot</p>
      </div>
    </a>
  </header>

  <main class="container">
    <section class="formWrap tableWrap">
      <?php if ($message): ?>
        <div class="empty" style="background:#fff6f6;color:#a33;margin-bottom:12px;"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <form method="post" action="editWord.php?wid=<?php echo htmlspecialchars($wid); ?>" class="addForm" id="editWordForm" novalidate>
        <label>
          Difficulté
          <select name="difficulty" id="difficultySelect" required class="<?php echo $fieldErrors['difficulty'] ? 'field-error' : ''; ?>">
            <option value="">-- Choisir --</option>
            <option value="easy"<?php if($old['difficulty']==='easy') echo ' selected'; ?>>Débutant</option>
            <option value="medium"<?php if($old['difficulty']==='medium') echo ' selected'; ?>>Intermédiaire</option>
            <option value="hard"<?php if($old['difficulty']==='hard') echo ' selected'; ?>>Avancé</option>
          </select>
          <?php if ($fieldErrors['difficulty']): ?>
            <span class="error-message"><?php echo htmlspecialchars($fieldErrors['difficulty']); ?></span>
          <?php endif; ?>
        </label>

        <label>
          Mot
          <input type="text" name="word" id="wordInput" required value="<?php echo htmlspecialchars($old['word']); ?>" placeholder="Votre mot" class="<?php echo $fieldErrors['word'] ? 'field-error' : ''; ?>" />
          <?php if ($fieldErrors['word']): ?>
            <span class="error-message"><?php echo htmlspecialchars($fieldErrors['word']); ?></span>
          <?php endif; ?>
        </label>

        <label>
          Type
          <select name="type" id="typeSelect" required class="<?php echo $fieldErrors['type'] ? 'field-error' : ''; ?>">
            <option value="">-- Choisir --</option>
          </select>
          <?php if ($fieldErrors['type']): ?>
            <span class="error-message"><?php echo htmlspecialchars($fieldErrors['type']); ?></span>
          <?php endif; ?>
        </label>

        <div class="formActions">
          <button type="submit" class="btn">Mettre à jour</button>
          <a class="btn secondary" href="DisplayWords.php">Annuler / Retour</a>
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
      const wordInput = document.getElementById('wordInput');
      const form = document.getElementById('editWordForm');

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

      // Real-time validation for word input
      wordInput.addEventListener('input', function() {
        const word = this.value.trim();
        if (word === '') {
          showFieldError(this, 'Le champ "Mot" est obligatoire.');
        } else if (!/^[\p{L}' ]+$/u.test(word)) {
          showFieldError(this, "Le mot ne doit contenir que des lettres, espaces et l'apostrophe (').");
        } else {
          clearFieldError(this);
        }
      });

      function showFieldError(field, message) {
        field.classList.add('field-error');
        let errorSpan = field.parentNode.querySelector('.error-message');
        if (!errorSpan) {
          errorSpan = document.createElement('span');
          errorSpan.className = 'error-message';
          field.parentNode.appendChild(errorSpan);
        }
        errorSpan.textContent = message;
      }

      function clearFieldError(field) {
        field.classList.remove('field-error');
        const errorSpan = field.parentNode.querySelector('.error-message');
        if (errorSpan) {
          errorSpan.remove();
        }
      }
    })();
  </script>
</body>
</html>