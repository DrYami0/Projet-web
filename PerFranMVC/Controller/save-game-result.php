<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

// Check if user is logged in - try multiple session keys
$userId = $_SESSION['user_id'] ?? null;
if (!$userId && isset($_SESSION['user']['uid'])) {
    $userId = $_SESSION['user']['uid'];
}

if (!$userId) {
    echo json_encode([
        'success' => false, 
        'error' => 'Not logged in',
        'debug' => [
            'session_id' => session_id(),
            'session_keys' => array_keys($_SESSION)
        ]
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$gameType = (int)($input['gameType'] ?? 1); // 1, 2, or 3
$score = (int)($input['score'] ?? 0);
$maxScore = (int)($input['maxScore'] ?? 10);
$won = isset($input['won']) ? (bool)$input['won'] : ($score >= $maxScore / 2);

// Validate game type
if ($gameType < 1 || $gameType > 3) {
    $gameType = 1;
}

try {
    $pdo = config::getConnexion();
    $pdo = config::getConnexion();

    // Ensure required columns exist in `users` table; add them if missing
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $requiredCols = [];
    for ($i = 1; $i <= 3; $i++){
        $requiredCols[] = "gamesPlayed{$i}";
        $requiredCols[] = "totalScore{$i}";
        $requiredCols[] = "dailyScore{$i}";
    }
    $requiredCols[] = 'wins';
    $requiredCols[] = 'losses';

    // Query existing columns
    $inClause = implode(',', array_fill(0, count($requiredCols), '?'));
    $colStmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = 'users' AND column_name IN ($inClause)");
    $params = array_merge([$dbName], $requiredCols);
    $colStmt->execute($params);
    $existing = $colStmt->fetchAll(PDO::FETCH_COLUMN);
    $missing = array_diff($requiredCols, $existing);
    if(!empty($missing)){
        // Build ALTER TABLE statements
        $alterSql = "ALTER TABLE users ";
        $adds = [];
        foreach($missing as $col){
            if(preg_match('/^gamesPlayed/i', $col) || preg_match('/^dailyScore/i', $col) || preg_match('/^totalScore/i', $col)){
                $adds[] = "ADD COLUMN `$col` INT DEFAULT 0";
            } else if(in_array($col, ['wins','losses'])){
                $adds[] = "ADD COLUMN `$col` INT DEFAULT 0";
            }
        }
        if(!empty($adds)){
            $alterSql .= implode(', ', $adds);
            try{
                $pdo->exec($alterSql);
            }catch(Exception $e){
                // If alter fails, log and continue to try update (will likely fail)
                error_log('[save-game-result] ALTER TABLE failed: '.$e->getMessage());
            }
        }
    }

    // Build column names based on game type
    $gamesPlayedCol = "gamesPlayed{$gameType}";
    $totalScoreCol = "totalScore{$gameType}";
    $dailyScoreCol = "dailyScore{$gameType}";

    // Update user stats
    $sql = "UPDATE users SET 
            {$gamesPlayedCol} = COALESCE({$gamesPlayedCol},0) + 1,
            {$totalScoreCol} = COALESCE({$totalScoreCol},0) + :score,
            {$dailyScoreCol} = COALESCE({$dailyScoreCol},0) + :score2,
            wins = COALESCE(wins,0) + :win,
            losses = COALESCE(losses,0) + :loss
            WHERE uid = :uid";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':score' => $score,
        ':score2' => $score,
        ':win' => $won ? 1 : 0,
        ':loss' => $won ? 0 : 1,
        ':uid' => $userId
    ]);
    
    // Log the activity
    $activitySql = "INSERT INTO game_activities (user_id, game_type, activity_type, score, max_score, won, status) 
                    VALUES (:user_id, :game_type, :activity_type, :score, :max_score, :won, 'Termin')";
    $activityStmt = $pdo->prepare($activitySql);
    $activityStmt->execute([
        ':user_id' => $userId,
        ':game_type' => $gameType,
        ':activity_type' => 'Match rapide',
        ':score' => $score,
        ':max_score' => $maxScore,
        ':won' => $won ? 1 : 0
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Game result saved',
        'data' => [
            'gameType' => $gameType,
            'score' => $score,
            'won' => $won
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
