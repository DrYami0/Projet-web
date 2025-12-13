<?php
// Face Recognition Login Endpoint
// This is a template for integrating the Python face recognition script
// You may need to adjust paths, security, and data handling for production

function load_dotenv($path = __DIR__ . '/../../.env') {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        putenv("$name=$value");
    }
}
load_dotenv();

session_start();
header('Content-Type: application/json');

// Use system temp for image files to avoid creating project tmp/ with face pics
$GLOBALS['createdTempFiles'] = [];
register_shutdown_function(function() {
    foreach ($GLOBALS['createdTempFiles'] as $f) {
        if (is_string($f) && file_exists($f)) @unlink($f);
    }
});

// Debug: dump incoming $_FILES for troubleshooting (written to system temp)
@file_put_contents(sys_get_temp_dir() . '/face_upload_debug.txt', date('c') . " - FILES:\n" . print_r($_FILES, true) . "\n", FILE_APPEND);

// Try to load FACE_ENCRYPT_KEY early so command construction can include it on Windows
$faceKey = getenv('FACE_ENCRYPT_KEY');
if ($faceKey === false || $faceKey === null || $faceKey === '') {
    if (defined('FACE_ENCRYPT_KEY')) {
        $faceKey = FACE_ENCRYPT_KEY;
    } elseif (is_readable(__DIR__ . '/../../face_key.txt')) {
        $faceKey = trim(file_get_contents(__DIR__ . '/../../face_key.txt')) ?: null;
    }
}

// Example: Accept image data via POST (base64 or file upload)
// accept single image or multiple frames (image, image0, image1, image2)
// Accept base64 fields or uploaded files
$imageData = $_POST['image'] ?? null;
// optional expected login identifier (email or username) provided by client to bind recognition
$expected = trim((string)($_POST['expected'] ?? ''));

$images = [];
// If files were uploaded via multipart/form-data, prefer those (safer for large payloads)
for ($i = 0; $i < 3; $i++) {
    $k = 'image' . $i;
    if (!empty($_FILES[$k]) && is_uploaded_file($_FILES[$k]['tmp_name'])) {
        $dest = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pf_face_' . uniqid() . '.png';
        if (move_uploaded_file($_FILES[$k]['tmp_name'], $dest)) {
            $images[] = $dest; // store path so later code can detect file exists
            $GLOBALS['createdTempFiles'][] = $dest;
        }
    }
}
// legacy single image field (base64) or urlencoded image0..image2
if (!empty($imageData)) $images[] = $imageData;
for ($i = 0; $i < 3; $i++) {
    $k = 'image' . $i;
    if (!empty($_POST[$k])) $images[] = $_POST[$k];
}

if (count($images) === 0) {
    echo json_encode(['success' => false, 'error' => 'No image data provided.']);
    exit;
}

// Write each provided image to system temp and track created files for cleanup
foreach ($images as $idx => $b64) {
    // If $b64 is already a filepath (from moved $_FILES'), don't base64-decode it or overwrite it.
    if (is_string($b64) && file_exists($b64)) {
        $trialFile = $b64;
    } else {
        $trialFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pf_face_' . uniqid() . '.png';
        // Defensive: ensure we have something that looks like base64 before decoding
        if (is_string($b64) && strlen($b64) > 50) {
            @file_put_contents($trialFile, base64_decode($b64));
        } else {
            // write the raw content (best-effort), this will be logged as small/invalid
            @file_put_contents($trialFile, (string)$b64);
        }
        $GLOBALS['createdTempFiles'][] = $trialFile;
    }
    if (file_exists($trialFile)) {
        $size = filesize($trialFile);
        $sha1 = sha1_file($trialFile);
        @file_put_contents(sys_get_temp_dir() . '/face_saved_info.txt', json_encode(['path'=>$trialFile,'size'=>$size,'sha1'=>$sha1]) . "\n", FILE_APPEND);
    }
}

// Try local Python microservice first (faster and avoids subprocess environment issues)
function try_local_service($b64) {
    $url = 'http://127.0.0.1:5000/recognize';
    $attempts = 3;
    for ($a = 1; $a <= $attempts; $a++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // slightly longer timeouts to allow local service to warm up
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_string($b64) && file_exists($b64)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['path' => $b64]);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $b64]);
        }
        $res = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        @file_put_contents(__DIR__ . '/../tmp/face_local_debug.txt', date('c') . " attempt:$a code:$code err:" . ($err ?: 'OK') . " res_len:" . (is_string($res) ? strlen($res) : 0) . "\n", FILE_APPEND);

        if ($res === false || $res === null) {
            // small backoff before retry
            usleep(200000);
            continue;
        }
        $decoded = json_decode($res, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // log invalid json and retry
            @file_put_contents(__DIR__ . '/../tmp/face_local_debug.txt', date('c') . " invalid_json:" . json_last_error_msg() . " raw:" . substr($res,0,200) . "\n", FILE_APPEND);
            usleep(200000);
            continue;
        }
        return $decoded;
    }
    return false;
}

if (function_exists('curl_init')) {
    // Try each saved trial file against the local service before falling back to subprocess
    $lastSvcFail = null;
    foreach ($images as $idx => $_b) {
        $trialFile = __DIR__ . '/../tmp/face_input_' . $idx . '.png';
        if (!file_exists($trialFile)) continue;
        $svc = try_local_service($trialFile);
        if ($svc && isset($svc['success'])) {
            if ($svc['success'] === true) {
                require_once __DIR__ . '/UserC.php';
                $userC = new UserC();
                $user = null;
                if (!empty($svc['user_id'])) {
                    $user = $userC->findByUid((int)$svc['user_id']);
                }
                if (!$user && !empty($svc['username'])) {
                    $user = $userC->findByUsername($svc['username']);
                }
                if ($user) {
                    if (($user['status'] ?? 'Inactive') !== 'Active') {
                        $_SESSION['pending_username'] = $user['username'];
                        echo json_encode(['success' => false, 'error' => 'Account not active', 'redirect' => BASE_URL . 'PerFranMVC/View/FrontOffice/user-status.php']);
                        exit;
                    }
                    // If the client provided an expected identifier, make sure the recognized user matches it
                    if ($expected !== '') {
                        $matched = false;
                        $expectedLower = mb_strtolower($expected);
                        if (isset($svc['username']) && mb_strtolower($svc['username']) === $expectedLower) $matched = true;
                        if (!$matched && !empty($user['email']) && mb_strtolower($user['email']) === $expectedLower) $matched = true;
                        if (!$matched) {
                            echo json_encode(['success' => false, 'error' => 'Face matched a different account. Vérifiez que vous avez entré le bon email.']);
                            exit;
                        }
                    }
                    $_SESSION['uid'] = $user['username'];
                    $_SESSION['user'] = $user;
                    $_SESSION['role'] = $user['role'] ?? 0;
                    $_SESSION['user_id'] = $user['uid'];
                    $_SESSION['username'] = $user['username'];
                    echo json_encode(['success' => true, 'username' => $user['username']]);
                    exit;
                } else {
                    $_SESSION['uid'] = $svc['username'] ?? null;
                    $_SESSION['user_id'] = $svc['user_id'] ?? null;
                    echo json_encode(['success' => true, 'username' => $svc['username'], 'warning' => 'User record not found in DB']);
                    exit;
                }
            } else {
                // record last failure and continue trying other frames
                $lastSvcFail = $svc;
            }
        }
    }
    if ($lastSvcFail !== null) {
        echo json_encode($lastSvcFail);
        exit;
    }
}
// Call Python script for recognition (try each provided trial file)
// Prefer the project's venv python if present to match installed packages
$python = 'C:\\Users\\Yami\\AppData\\Local\\Programs\\Python\\Python311\\python.exe';
$venv = __DIR__ . '/../.venv/Scripts/python.exe';
if (file_exists($venv)) {
    $python = $venv;
}

// Locate the Python face recognition script. Try likely candidate locations.
$candidates = [
    __DIR__ . '/../face_recognition_login.py',      // PerFranMVC/face_recognition_login.py
    __DIR__ . '/../../face_recognition_login.py',   // project root face_recognition_login.py
    __DIR__ . '/../..//face_recognition_login.py',  // redundant fallback
];
$script = null;
foreach ($candidates as $c) {
    if (file_exists($c)) { $script = $c; break; }
}
if ($script === null) {
    // helpful debug output and clear JSON error so client sees why recognition failed
    $debug = 'Face Python script not found in expected locations: ' . implode(', ', $candidates);
    @file_put_contents(sys_get_temp_dir() . '/face_last_php_debug.txt', date('c') . " - script_missing: " . $debug . PHP_EOL, FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Recognition script not found on server.', 'debug' => $debug]);
    exit;
}

// Prepare environment for subprocess and ensure FACE_ENCRYPT_KEY is loaded from disk
$env = $_ENV;
$faceKey = getenv('FACE_ENCRYPT_KEY');
if ($faceKey === false || $faceKey === null || $faceKey === '') {
    $diskKeyFile = __DIR__ . '/../face_key.txt';
    if (is_readable($diskKeyFile)) {
        $diskKey = trim(file_get_contents($diskKeyFile));
        if ($diskKey !== '') $faceKey = $diskKey;
    }
}
if ($faceKey !== false && $faceKey !== null && $faceKey !== '') {
    $env['FACE_ENCRYPT_KEY'] = $faceKey;
}
$tmpHome = sys_get_temp_dir();
if (empty($env['HOME'])) $env['HOME'] = $tmpHome;
if (empty($env['USERPROFILE'])) $env['USERPROFILE'] = $tmpHome;
if (empty($env['MPLCONFIGDIR'])) $env['MPLCONFIGDIR'] = $tmpHome;
// Ensure Python uses UTF-8 for stdout/stderr so Unicode in script output won't fail on Windows
$env['PYTHONIOENCODING'] = 'utf-8';

// Ensure venv native DLLs and scripts are available to subprocesses when PHP runs under Apache
$venvRoot = __DIR__ . '/../.venv';
$venvScripts = realpath($venvRoot . '/Scripts');
$venvCvBin = realpath($venvRoot . '/Lib/site-packages/cv2/../../x64/vc17/bin');
$currentPath = getenv('PATH') ?: '';
$pathParts = [];
if ($venvScripts && is_dir($venvScripts)) $pathParts[] = $venvScripts;
if ($venvCvBin && is_dir($venvCvBin)) $pathParts[] = $venvCvBin;
if ($currentPath !== '') $pathParts[] = $currentPath;
$env['PATH'] = implode(';', $pathParts);
$env['VIRTUAL_ENV'] = realpath($venvRoot) ?: $venvRoot;
$descriptorspec = [1 => ['pipe','w'], 2 => ['pipe','w']];
// Ensure files are fully written and stable before launching subprocesses
function ensure_file_stable($path, $tries = 6, $wait_us = 100000) {
    if (!file_exists($path)) return false;
    $last = filesize($path);
    for ($i = 0; $i < $tries; $i++) {
        usleep($wait_us);
        clearstatcache(true, $path);
        $now = filesize($path);
        if ($now === $last) {
            // require several consecutive stable reads
            $stable = true;
            for ($j = 0; $j < 2; $j++) { usleep($wait_us); clearstatcache(true, $path); if (filesize($path) !== $now) { $stable = false; break; } }
            if ($stable) return true;
        }
        $last = $now;
    }
    return false;
}

$lastFullOutput = '';
$result = null;
foreach ($images as $idx => $_b) {
    $trialFile = __DIR__ . '/../tmp/face_input_' . $idx . '.png';
    // if our earlier processing wrote a system-temp file, prefer that
    if (isset($images[$idx]) && is_string($images[$idx]) && file_exists($images[$idx])) {
        $trialFile = $images[$idx];
    }
    if (!file_exists($trialFile)) continue;
    // Wait for the file to become stable (size stops changing) to avoid partial-write reads
    $stable = ensure_file_stable($trialFile);
    if (!$stable) {
        @file_put_contents(__DIR__ . '/../tmp/face_last_fulloutput.txt', "[file:$trialFile] file not stable before run\n", FILE_APPEND);
    }

    if (DIRECTORY_SEPARATOR === '\\') {
        $sets = [];
        $sets[] = 'set "MPLCONFIGDIR=' . str_replace('"', '\\"', $tmpHome) . '"';
        if (!empty($faceKey)) $sets[] = 'set "FACE_ENCRYPT_KEY=' . str_replace('"', '\\"', $faceKey) . '"';
        $minDet = getenv('FACE_MIN_DET_CONF'); if ($minDet === false || $minDet === null || $minDet === '') $minDet = '0.10';
        $sets[] = 'set "FACE_MIN_DET_CONF=' . str_replace('"', '\\"', $minDet) . '"';
        $sets[] = 'set "API_FACE_HEADLESS=1"';
        // Launch Python with -u (unbuffered) so stdout/stderr are flushed immediately
        $cmdInner = '"' . $python . '" -u "' . $script . '" --input "' . $trialFile . '" --mode login';
        $pythonCmd = 'cmd /C "' . implode(' && ', $sets) . ' && ' . $cmdInner . '"';
    } else {
        $pythonCmd = escapeshellcmd($python) . ' ' . escapeshellarg($script) . ' --input ' . escapeshellarg($trialFile) . ' --mode login';
    }

    // Log the exact script path, cwd and full command PHP will exec (helps locate alternate copies)
    @file_put_contents(sys_get_temp_dir() . '/face_last_php_debug.txt',
        "PHP will exec script: " . $script . PHP_EOL .
        "CWD: " . getcwd() . PHP_EOL .
        "Full command: " . $pythonCmd . PHP_EOL,
        FILE_APPEND
    );

    // Extra diagnostics: confirm which file PHP sees (exists, realpath, mtime, and head)
    $extra = [];
    $extra[] = 'Script exists: ' . (file_exists($script) ? 'yes' : 'no');
    $rp = @realpath($script);
    $extra[] = 'Realpath: ' . ($rp ?: 'NULL');
    if (file_exists($script)) {
        $extra[] = 'File mtime: ' . date('c', @filemtime($script));
        $head = @file_get_contents($script, false, null, 0, 200);
        $extra[] = 'Script head: ' . ($head === false ? 'READ_FAILED' : preg_replace("/[\r\n]+/", ' ', $head));
    }
    @file_put_contents(sys_get_temp_dir() . '/face_last_php_debug.txt', implode(PHP_EOL, $extra) . PHP_EOL . PHP_EOL, FILE_APPEND);

    $process = proc_open($pythonCmd, $descriptorspec, $pipes, __DIR__ . '/..', $env);
    $output = '';
    $err = '';
    if (is_resource($process)) {
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $return_value = proc_close($process);
    } else {
        @file_put_contents(sys_get_temp_dir() . '/face_last_python_cmd.txt', $pythonCmd . "\n", FILE_APPEND);
        @file_put_contents(sys_get_temp_dir() . '/face_last_fulloutput.txt', "[file:$trialFile] failed to start process\n", FILE_APPEND);
        continue;
    }

    $fullOutput = trim($output . "\n" . $err);
    $lastFullOutput = $fullOutput;
    @file_put_contents(sys_get_temp_dir() . '/face_last_python_cmd.txt', "[file:$trialFile] " . $pythonCmd . "\n", FILE_APPEND);
    @file_put_contents(sys_get_temp_dir() . '/face_last_fulloutput.txt', "[file:$trialFile] " . $fullOutput . "\n", FILE_APPEND);

    $lines = array_reverse(array_map('trim', explode("\n", $fullOutput)));
    $jsonLine = null;
    foreach ($lines as $line) {
        if ($line !== '' && $line[0] === '{' && substr($line, -1) === '}') { $jsonLine = $line; break; }
    }
    $result = $jsonLine ? json_decode($jsonLine, true) : null;
    if ($result && isset($result['success']) && $result['success'] === true) break;
}

if (!$result || !isset($result['success'])) {
    echo json_encode(['success' => false, 'error' => 'Recognition failed or invalid response.', 'debug' => $lastFullOutput]);
    exit;
}

if ($result['success']) {
    // Log user in (set session, matching the regular auth flow)
    // Load user controller to fetch full user record
    require_once __DIR__ . '/UserC.php';
    $userC = new UserC();
    $username = $result['username'] ?? null;
    $user = null;
    if (!empty($result['user_id'])) {
        $user = $userC->findByUid((int)$result['user_id']);
    }
    if (!$user && $username) {
        $user = $userC->findByUsername($username);
    }

    if ($user) {
        // If account is not active, redirect to pending page behavior
        if (($user['status'] ?? 'Inactive') !== 'Active') {
            $_SESSION['pending_username'] = $user['username'];
            echo json_encode(['success' => false, 'error' => 'Account not active', 'redirect' => BASE_URL . 'PerFranMVC/View/FrontOffice/user-status.php']);
            exit;
        }

        // If the client provided an expected identifier, ensure the recognized user matches it
        if ($expected !== '') {
            $matched = false;
            $expectedLower = mb_strtolower($expected);
            if (isset($result['username']) && mb_strtolower($result['username']) === $expectedLower) $matched = true;
            if (!$matched && !empty($user['email']) && mb_strtolower($user['email']) === $expectedLower) $matched = true;
            if (!$matched) {
                echo json_encode(['success' => false, 'error' => 'Face matched a different account. Vérifiez que vous avez entré le bon email.']);
                exit;
            }
        }

        // Set session variables used elsewhere in the app
        $_SESSION['uid'] = $user['username'];
        $_SESSION['user'] = $user;
        $_SESSION['role'] = $user['role'] ?? 0;
        $_SESSION['user_id'] = $user['uid'];
        $_SESSION['username'] = $user['username'];

        echo json_encode(['success' => true, 'username' => $user['username']]);
    } else {
        // If we couldn't find a corresponding DB user, set minimal session and warn
        $_SESSION['uid'] = $result['username'];
        $_SESSION['user_id'] = $result['user_id'] ?? null;
        echo json_encode(['success' => true, 'username' => $result['username'], 'warning' => 'User record not found in DB']);
    }
} else {
    echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Face not recognized.', 'debug' => $fullOutput]);
}
