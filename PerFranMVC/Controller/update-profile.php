<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/UserC.php';

if (!isset($_SESSION['uid'])) {
    header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/login.php');
    exit;
}


// Always initialize $username and $userC before any POST logic
$username = $_SESSION['uid'];
$userC = new UserC();

// Handle face data saving from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save_face_data') {
    header('Content-Type: application/json');
    $imageData = $_POST['image'] ?? null;
    if (!$imageData) {
        echo json_encode(['success' => false, 'error' => 'No image data provided.']);
        exit;
    }
    $tmpDir = __DIR__ . '/../../tmp';
    if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);
    $tmpFile = $tmpDir . '/face_input.png';

    $decoded = base64_decode($imageData, true);
    if ($decoded === false) {
        echo json_encode(['success' => false, 'error' => 'Invalid base64 image data.']);
        exit;
    }
    $written = @file_put_contents($tmpFile, $decoded);
    if ($written === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to write image to server. Check permissions.']);
        exit;
    }

    // Build robust Windows command that sets required env vars then runs the Python API mode
    $python = 'C:\\Users\\Yami\\AppData\\Local\\Programs\\Python\\Python311\\python.exe';
    $script = __DIR__ . '/../../face_recognition_login.py';
    $user_id = $userC->findByUsername($username)['uid'] ?? null;

    // Ensure we execute from project root so relative model paths resolve correctly
    $projectRoot = realpath(__DIR__ . '/../../');
    $escapedProjectRoot = str_replace('"', '\\"', $projectRoot);

    // read FACE_ENCRYPT_KEY if present
    $faceKeyFile = __DIR__ . '/../../face_key.txt';
    $faceKey = null;
    if (file_exists($faceKeyFile)) {
        $faceKey = trim(@file_get_contents($faceKeyFile));
    }

    // Prepare environment setters for Windows cmd /C
    $envParts = [];
    $mplconf = realpath(__DIR__ . '/../../mplconfig') ?: (__DIR__ . '/../../mplconfig');
    $envParts[] = 'set "MPLCONFIGDIR=' . addcslashes($mplconf, '\\"') . '"';
    if ($faceKey) $envParts[] = 'set "FACE_ENCRYPT_KEY=' . addcslashes($faceKey, '\\"') . '"';
    $envParts[] = 'set "API_FACE_HEADLESS=1"';
    $envParts[] = 'set "FACE_MIN_DET_CONF=0.35"';
    // When invoked from server UI, allow forced registration to bypass duplicate-checks
    $envParts[] = 'set "FORCE_REGISTER=1"';

    $envCmd = implode(' && ', $envParts);
    $pyCmd = '"' . $python . '" "' . $script . '" --mode register --input "' . $tmpFile . '" --user_id "' . $user_id . '" --username "' . $username . '"';
    // cd /d ensures drive letter switching works on Windows and uses project root for CWD
    $fullCmd = 'cmd /C "' . $envCmd . ' && cd /d "' . $escapedProjectRoot . '" && ' . $pyCmd . '"';

    // Execute and capture full output (stdout+stderr)
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];
    // Run the subprocess with project root as working directory to avoid relative path issues
    $proc = proc_open($fullCmd, $descriptors, $pipes, $projectRoot);
    $output = '';
    if (is_resource($proc)) {
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $ret = proc_close($proc);
        $output = trim($stdout . "\n" . $stderr);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to start Python subprocess.']);
        exit;
    }

    // Persist generated FACE_ENCRYPT_KEY if Python printed one
    if (preg_match('/FACE_ENCRYPT_KEY\s*env var to:\s*([A-Za-z0-9_\-+=\/]+)/i', $output, $m)) {
        $key = $m[1];
        @file_put_contents(__DIR__ . '/../face_key.txt', $key);
    }

    // Try to find the last JSON-like line in output
    $lines = array_reverse(array_map('trim', explode("\n", $output)));
    $jsonLine = null;
    foreach ($lines as $line) {
        if ($line && $line[0] === '{' && substr($line, -1) === '}') {
            $jsonLine = $line;
            break;
        }
    }
    // Write full raw output to tmp for debugging
    try {
        $dbgPath = __DIR__ . '/../tmp/update_profile_register_debug.txt';
        $dbgEntry = [ 'time' => date('c'), 'user' => $username, 'cmd_output' => $output ];
        file_put_contents($dbgPath, json_encode($dbgEntry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    } catch (Exception $e) {
    }
    $result = $jsonLine ? json_decode($jsonLine, true) : null;
    if (!$result || !isset($result['success'])) {
        echo json_encode(['success' => false, 'error' => 'Registration failed or invalid response.', 'debug' => $output]);
        exit;
    }
    if ($result['success']) {
        $userC->setFaceRecognitionEnabled($username, 1);
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Face not registered.', 'debug' => $output]);
        exit;
    }
}

$newUsername = trim($_POST['username'] ?? '');
$firstNamePost = trim($_POST['firstName'] ?? '');
$lastNamePost = trim($_POST['lastName'] ?? '');
$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$newPass  = $_POST['new_password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

if (!empty($newUsername) && $newUsername !== $username) {
    $existingUser = $userC->findByUsername($newUsername);
    if ($existingUser) {
        $_SESSION['error'] = "Ce nom d'utilisateur est déjà utilisé.";
        header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/account-profile.php');
        exit;
    }
}

$firstName = '';
$lastName = '';
if ($firstNamePost !== '' || $lastNamePost !== '') {
    $firstName = $firstNamePost;
    $lastName = $lastNamePost;
} else {
    $names = explode(' ', $fullname, 2);
    $firstName = $names[0] ?? '';
    $lastName  = $names[1] ?? '';
}

$passwordHash = null;

if (!empty($newPass)) {
    if ($newPass !== $confirm) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/account-profile.php');
        exit;
    }

    if (strlen($newPass) < 6) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 6 caractères.";
        header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/account-profile.php');
        exit;
    }

    $passwordHash = password_hash($newPass, PASSWORD_DEFAULT);
}

$avatarPath = null;

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../PerFranMVC/View/FrontOffice/assets/uploads/avatars/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed) && $_FILES['avatar']['size'] < 5000000) {
        $filename = "avatar_{$username}_" . time() . ".$ext";
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
            $avatarPath = BASE_URL . 'PerFranMVC/View/FrontOffice/assets/uploads/avatars/' . $filename;
        }
    }

    // Provide feedback when avatar upload fails so user can see why
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Erreur: échec du téléversement de l\'avatar. Vérifiez la taille et le format.';
    } elseif (isset($_FILES['avatar']) && empty($avatarPath)) {
        // File was provided but not accepted (invalid ext or too large)
        $_SESSION['error'] = 'Avatar non enregistré: format non pris en charge ou fichier trop lourd (max 5MB).';
    }

    // Log upload debug info to tmp for troubleshooting (permissions, PHP upload errors)
    $tmpDir = __DIR__ . '/../tmp'; if (!is_dir($tmpDir)) @mkdir($tmpDir, 0755, true);
    $uinfo = [
        'time' => date('c'),
        'user' => $username,
        'files' => isset($_FILES['avatar']) ? array_replace($_FILES['avatar'], ['tmp_name' => basename($_FILES['avatar']['tmp_name'] ?? '')]) : null,
        'destination' => isset($destination) ? $destination : null,
        'avatarPath' => $avatarPath
    ];
    @file_put_contents($tmpDir . '/upload_avatar_debug.log', json_encode($uinfo, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
}

try {
    $profileData = [
        'firstName'    => $firstName,
        'lastName'     => $lastName,
        'email'        => $email,
        'phone'        => $phone ?: null,
        'passwordHash' => $passwordHash,
        'avatar'       => $avatarPath
    ];

    // Update username if changed
    if (!empty($newUsername) && $newUsername !== $username) {
        $profileData['username'] = $newUsername;
    }

    $userC->updateProfile($username, $profileData);

    // Update session with new username if changed
    $finalUsername = (!empty($newUsername) && $newUsername !== $username) ? $newUsername : $username;
    $updatedUser = $userC->findByUsername($finalUsername);
    $_SESSION['user'] = $updatedUser;
    $_SESSION['uid'] = $finalUsername;

    // Only set a success message if no earlier error was recorded (e.g. avatar upload failed)
    if (empty($_SESSION['error'])) {
        $_SESSION['success'] = "Profil mis à jour avec succès !";
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
}

header('Location: ' . BASE_URL . 'PerFranMVC/View/FrontOffice/account-profile.php');
exit;