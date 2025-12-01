<?php
require_once "../../Controller/DictionaryC.php";
require_once "../../Model/Dictionary.php";

$message = '';
$old = [
    'word' => '',
    'difficulty' => '',
    'type' => ''
];

$allowedDifficulties = ['easy','medium','hard'];
$easyTypes = ['Nom/G.N','Adjectif','Verbe','Déterminant'];
$mediumExtra = ['Adverbe','Préposition'];
$hardExtra = ['Pronom','Conjonction'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['word'] = trim($_POST['word'] ?? '');
    $old['difficulty'] = $_POST['difficulty'] ?? '';
    $old['type'] = $_POST['type'] ?? '';

    // Word validation: only letters (including accents), spaces and apostrophe allowed
    if ($old['word'] === '') {
        $message = 'Le champ "Mot" est obligatoire.';
    } elseif (!preg_match("/^[\p{L}' ]+$/u", $old['word'])) {
        $message = "Le mot ne doit contenir que des lettres, espaces et l'apostrophe (').";
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
                // Store difficulty as string (easy/medium/hard) for readability
                $dict = new Dictionary($old['word'], $old['type'], $old['difficulty']);
                $dictC = new DictionaryC();
                $dictC->addWord($dict);
                header('Location: DisplayWords.php?added=1');
                exit;
            } catch (Exception $e) {
                $message = 'Erreur lors de l\'ajout : ' . $e->getMessage();
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
  <title>PerFran — Ajouter un mot</title>
  <link rel="stylesheet" href="displayWords.css" />
  <link rel="stylesheet" href="addWord.css" />
</head>
<body>
  <header class="MainTitle smallHeader">
    <a href="index.html" class="brand">
      <img src="../Perfran.png" alt="Perfran" />
      <div>
        <h1>Ajouter un mot</h1>
        <p>Remplissez le formulaire pour ajouter un mot au dictionnaire</p>
      </div>
    </a>
  </header>

  <main class="container">
    <section class="formWrap tableWrap">
      <?php if ($message): ?>
        <div class="empty" style="background:#fff6f6;color:#a33;margin-bottom:12px;"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <form method="post" action="AddWord.php" class="addForm" id="addWordForm" novalidate>
        <label>
          Difficulté (choisir d'abord)
          <select name="difficulty" id="difficultySelect" required>
            <option value="">-- Choisir --</option>
            <option value="easy"<?php if($old['difficulty']==='easy') echo ' selected'; ?>>Débutant</option>
            <option value="medium"<?php if($old['difficulty']==='medium') echo ' selected'; ?>>Intermédiaire</option>
            <option value="hard"<?php if($old['difficulty']==='hard') echo ' selected'; ?>>Avancé</option>
          </select>
        </label>

        <label>
          Mot
          <input type="text" name="word" id="wordInput" required value="<?php echo htmlspecialchars($old['word']); ?>" placeholder="Votre mot" />
        </label>

        <label>
          Type (défini après la difficulté)
          <select name="type" id="typeSelect" required disabled>
            <option value="">-- Choisir difficulté d'abord --</option>
          </select>
        </label>

        <div class="formActions">
          <button type="submit" class="btn">Ajouter</button>
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
      const form = document.getElementById('addWordForm');

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
        typeSelect.disabled = false;
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
          typeSelect.innerHTML = '<option value="">-- Choisir difficulté d\'abord --</option>';
          typeSelect.disabled = true;
          return;
        }
        buildTypes(d, '');
      });

      // client-side word validation before submit
      form.addEventListener('submit', (e) => {
        const word = wordInput.value.trim();
        const regex = /^[\p{L}' ]+$/u;
        if (!regex.test(word)) {
          e.preventDefault();
          alert("Le mot ne doit contenir que des lettres, des espaces et l'apostrophe (').");
          wordInput.focus();
          return false;
        }
        // ensure difficulty and type valid
        if (!difficultySelect.value) {
          e.preventDefault();
          alert('Veuillez choisir une difficulté.');
          difficultySelect.focus();
          return false;
        }
        if (!typeSelect.value) {
          e.preventDefault();
          alert('Veuillez choisir un type valide après la difficulté.');
          typeSelect.focus();
          return false;
        }
      });
    })();
  </script>
</body>
</html>