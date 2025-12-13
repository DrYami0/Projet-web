<?php

require_once __DIR__ . '/../../config.php';

class UserC {
    
    public function addUser(User $user): bool {
        $pdo = config::getConnexion();
        $sql = "INSERT INTO users
            (username, firstName, lastName, email, phone, password_hash, status, token, role, social_id, provider, avatar, creationDate)
            VALUES
            (:username, :firstName, :lastName, :email, :phone, :password_hash, :status, :token, :role, :social_id, :provider, :avatar, NOW())";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':username'      => $user->getUsername(),
            ':firstName'     => $user->getFirstName(),
            ':lastName'      => $user->getLastName(),
            ':email'         => $user->getEmail(),
            ':phone'         => $user->getPhone(),
            ':password_hash' => $user->getPasswordHash(),
            ':status'        => $user->getStatus() ?? 'Inactive',
            ':token'         => 48,
            ':role'          => $user->getRole() ?? 0,
            ':social_id'     => $user->getSocialId(),
            ':provider'      => $user->getProvider(),
            ':avatar'        => $user->getAvatar(),
        ]);
    }

    public function findByEmail(string $email): ?array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByUsername(string $username): ?array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByUid(int $uid): ?array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE uid = ? LIMIT 1');
        $stmt->execute([$uid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findBySocialId(string $socialId, string $provider): ?array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE social_id = ? AND provider = ? LIMIT 1');
        $stmt->execute([$socialId, $provider]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createPending(array $data): bool {
        $pdo = config::getConnexion();
        $sql = "INSERT INTO users
            (username, firstName, lastName, email, phone, password_hash, status, token, role, social_id, provider, creationDate)
            VALUES
            (:username, :firstName, :lastName, :email, :phone, :password_hash, :status, :token, :role, :social_id, :provider, NOW())";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':username'      => $data['username'],
            ':firstName'     => $data['firstName'] ?? null,
            ':lastName'      => $data['lastName'] ?? null,
            ':email'         => $data['email'],
            ':phone'         => $data['phone'] ?? null,
            ':password_hash' => $data['passwordHash'] ?? $data['password_hash'] ?? null,
            ':status'        => $data['status'] ?? 'Inactive',
            ':token'         => $data['token'] ?? 48,
            ':role'          => (int)($data['role'] ?? 0),
            ':social_id'     => $data['social_id'] ?? null,
            ':provider'      => $data['provider'] ?? null,
        ]);
    }

    public function activateByUsername(string $username): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE users SET status = 'Active', token = 0 WHERE username = ?");
        return $stmt->execute([$username]);
    }

    public function updatePasswordByEmail(string $email, string $passwordHash): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
        return $stmt->execute([$passwordHash, $email]);
    }

    public function refuseByUsername(string $username): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('DELETE FROM users WHERE username = ?');
        return $stmt->execute([$username]);
    }

    public function deactivateByUsername(string $username): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE users SET status = 'Inactive' WHERE username = ?");
        return $stmt->execute([$username]);
    }

    public function displayUsers(?string $status = null): array {
        $pdo = config::getConnexion();
        if ($status) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE status = ? ORDER BY creationDate DESC');
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->query('SELECT * FROM users ORDER BY creationDate DESC');
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Alias for backward compatibility
    public function findAll(?string $status = null): array {
        return $this->displayUsers($status);
    }

    public function searchUsers(string $q): array {
        $pdo = config::getConnexion();
        $like = '%' . $q . '%';
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username LIKE ? OR email LIKE ? OR firstName LIKE ? OR lastName LIKE ? ORDER BY creationDate DESC');
        $stmt->execute([$like, $like, $like, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function banUserByUid(int $uid, ?string $until = null): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE users SET status = 'Inactive', bannedUntil = ?, token = 0 WHERE uid = ?");
        return $stmt->execute([$until, $uid]);
    }

    public function unbanUserByUid(int $uid): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE users SET bannedUntil = NULL, status = 'Active' WHERE uid = ?");
        return $stmt->execute([$uid]);
    }

    public function promoteToAdmin(int $uid): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('UPDATE users SET role = 1 WHERE uid = ?');
        return $stmt->execute([$uid]);
    }

    public function listDeleted(): array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listPending(): array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE status = 'Inactive' AND (deleted_at IS NULL) ORDER BY creationDate DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listBanned(): array {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT * FROM users WHERE bannedUntil IS NOT NULL AND bannedUntil > NOW() ORDER BY bannedUntil DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser(int $uid): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM users WHERE uid = ?");
        return $stmt->execute([$uid]);
    }

    // Alias for backward compatibility
    public function deleteByUid(int $uid): bool {
        return $this->deleteUser($uid);
    }

    public function deactivateByUid(int $uid): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE users SET status = 'Inactive' WHERE uid = ?");
        return $stmt->execute([$uid]);
    }

    public function restoreByUid(int $uid): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE users SET deleted_at = NULL, status = 'Active' WHERE uid = ?");
        return $stmt->execute([$uid]);
    }

    public function softDeleteByUsername(string $username): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW(), status = 'Inactive' WHERE username = ?");
        return $stmt->execute([$username]);
    }

    public function editUser(int $uid, array $data): bool {
        $pdo = config::getConnexion();
        $sql = "UPDATE users SET email = ?, role = ?, firstName = ?, lastName = ?, phone = ? WHERE uid = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['email'],
            $data['role'],
            $data['firstName'] ?? null,
            $data['lastName'] ?? null,
            $data['phone'] ?? null,
            $uid
        ]);
    }

    // Alias for backward compatibility
    public function updateUser(int $uid, array $data): bool {
        return $this->editUser($uid, $data);
    }

    public function updateProfile(string $username, array $data): bool {
        $pdo = config::getConnexion();
        $sql = "UPDATE users SET firstName = ?, lastName = ?, email = ?, phone = ?";
        $params = [$data['firstName'], $data['lastName'], $data['email'], $data['phone']];

        if (!empty($data['passwordHash'])) {
            $sql .= ", password_hash = ?";
            $params[] = $data['passwordHash'];
        }

        if (!empty($data['avatar'])) {
            $sql .= ", avatar = ?";
            $params[] = $data['avatar'];
        }

        if (!empty($data['username'])) {
            $sql .= ", username = ?";
            $params[] = $data['username'];
        }

        $sql .= " WHERE username = ?";
        $params[] = $username;

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Safely set only the face_recognition_enabled flag for a user
     * This avoids accidental overwriting of other profile fields when
     * partial data arrays are passed around.
     */
    public function setFaceRecognitionEnabled(string $username, int $enabled = 1): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('UPDATE users SET face_recognition_enabled = ? WHERE username = ?');
        return $stmt->execute([$enabled, $username]);
    }

    public function countUsers(): int {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        return (int)$stmt->fetch()['total'];
    }

    public function countActiveUsers(): int {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Active'");
        return (int)$stmt->fetchColumn();
    }

    public function countPendingUsers(): int {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'Inactive' AND deleted_at IS NULL");
        return (int)$stmt->fetchColumn();
    }

    public function countBannedUsers(): int {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE bannedUntil IS NOT NULL AND bannedUntil > NOW()");
        return (int)$stmt->fetchColumn();
    }

    public function countTodaySignups(): int {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(creationDate) = CURDATE()");
        return (int)$stmt->fetchColumn();
    }

    public function countWeekSignups(): int {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE creationDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        return (int)$stmt->fetchColumn();
    }

    public function countAdmins(): int {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 1");
        return (int)$stmt->fetchColumn();
    }

    public function getMonthlySignups(): array {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("
            SELECT 
                DATE_FORMAT(creationDate, '%Y-%m') as month,
                DATE_FORMAT(creationDate, '%b') as month_name,
                COUNT(*) as count 
            FROM users 
            WHERE creationDate >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(creationDate, '%Y-%m')
            ORDER BY month ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDailySignups(): array {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("
            SELECT 
                DATE(creationDate) as date,
                DATE_FORMAT(creationDate, '%a') as day_name,
                COUNT(*) as count 
            FROM users 
            WHERE creationDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(creationDate)
            ORDER BY date ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentUsers(int $limit = 5): array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM users ORDER BY creationDate DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function loginWithSocial(string $email, string $name, string $socialId, string $provider): array {
        $user = $this->findBySocialId($socialId, $provider);
        if ($user) return $user;

        $byEmail = $this->findByEmail($email);
        if ($byEmail) {
            if (empty($byEmail['social_id'])) {
                $pdo = config::getConnexion();
                $stmt = $pdo->prepare('UPDATE users SET social_id = ?, provider = ? WHERE uid = ?');
                $stmt->execute([$socialId, $provider, $byEmail['uid']]);
                $byEmail['social_id'] = $socialId;
                $byEmail['provider'] = $provider;
            }
            return $byEmail;
        }

        $username = preg_replace('/[^a-z0-9_]/i', '_', strtok($name, ' ')) . '_' . substr($socialId, 0, 6);
        $userData = [
            'username'     => $username,
            'firstName'    => $name,
            'lastName'     => null,
            'email'        => $email,
            'phone'        => null,
            'passwordHash' => null,
            'status'       => 'Inactive',
            'token'        => 48,
            'role'         => 0,
            'social_id'    => $socialId,
            'provider'     => $provider,
        ];

        $this->createPending($userData);

        return $this->findByUsername($username);
    }

    public function createResetToken(string $email): ?string {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        
        $pdo = config::getConnexion();
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?');
        $stmt->execute([$token, $expires, $email]);
        
        return $token;
    }

    public function findByResetToken(string $token): ?array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function resetPassword(string $token, string $passwordHash): bool {
        $user = $this->findByResetToken($token);
        if (!$user) return false;
        
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?');
        return $stmt->execute([$passwordHash, $token]);
    }

    public function clearResetToken(string $email): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE email = ?');
        return $stmt->execute([$email]);
    }

    // ============================================
    // GAME STATS METHODS (Integration with GameC)
    // ============================================

    /**
     * Update user stats after a game is completed
     * @param int $uid User ID
     * @param int $gameType Game type (1, 2, or 3)
     * @param int $score Score earned in this game
     * @param bool $won Whether the user won
     */
    public function updateGameStats(int $uid, int $gameType, int $score, bool $won): bool {
        $pdo = config::getConnexion();
        
        // Build dynamic column names based on game type
        $scoreCol = "totalScore{$gameType}";
        $gamesCol = "gamesPlayed{$gameType}";
        $dailyCol = "dailyScore{$gameType}";
        
        $sql = "UPDATE users SET 
                    {$scoreCol} = {$scoreCol} + :score,
                    {$gamesCol} = {$gamesCol} + 1,
                    {$dailyCol} = {$dailyCol} + :dailyScore,
                    wins = wins + :win,
                    losses = losses + :loss
                WHERE uid = :uid";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':score' => $score,
            ':dailyScore' => $score,
            ':win' => $won ? 1 : 0,
            ':loss' => $won ? 0 : 1,
            ':uid' => $uid
        ]);
    }

    /**
     * Update user's streak (called daily or after each game)
     */
    public function updateStreak(int $uid, bool $increment = true): bool {
        $pdo = config::getConnexion();
        
        if ($increment) {
            $stmt = $pdo->prepare('UPDATE users SET streak = streak + 1 WHERE uid = ?');
        } else {
            $stmt = $pdo->prepare('UPDATE users SET streak = 0 WHERE uid = ?');
        }
        
        return $stmt->execute([$uid]);
    }

    /**
     * Reset daily scores (should be called by a cron job at midnight)
     */
    public function resetDailyScores(): bool {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('UPDATE users SET dailyScore1 = 0, dailyScore2 = 0, dailyScore3 = 0');
        return $stmt->execute();
    }

    /**
     * Get user's game statistics
     */
    public function getUserGameStats(int $uid): array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare('SELECT 
            totalScore1, totalScore2, totalScore3,
            dailyScore1, dailyScore2, dailyScore3,
            gamesPlayed1, gamesPlayed2, gamesPlayed3,
            wins, losses, streak
            FROM users WHERE uid = ?');
        $stmt->execute([$uid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) return [];
        
        // Calculate totals
        $row['totalScore'] = ($row['totalScore1'] ?? 0) + ($row['totalScore2'] ?? 0) + ($row['totalScore3'] ?? 0);
        $row['totalGames'] = ($row['gamesPlayed1'] ?? 0) + ($row['gamesPlayed2'] ?? 0) + ($row['gamesPlayed3'] ?? 0);
        $row['winRate'] = ($row['wins'] + $row['losses']) > 0 
            ? round(($row['wins'] / ($row['wins'] + $row['losses'])) * 100, 1) 
            : 0;
        
        return $row;
    }

    /**
     * Get leaderboard (top players by total score)
     */
    public function getLeaderboard(int $limit = 10): array {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("
            SELECT uid, username, avatar,
                   (totalScore1 + totalScore2 + totalScore3) as totalScore,
                   (gamesPlayed1 + gamesPlayed2 + gamesPlayed3) as totalGames,
                   wins, losses, streak
            FROM users 
            WHERE status = 'Active'
            ORDER BY totalScore DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get leaderboard by specific game type
     */
    public function getLeaderboardByGame(int $gameType, int $limit = 10): array {
        $pdo = config::getConnexion();
        $scoreCol = "totalScore{$gameType}";
        $gamesCol = "gamesPlayed{$gameType}";
        
        $stmt = $pdo->prepare("
            SELECT uid, username, avatar, {$scoreCol} as score, {$gamesCol} as gamesPlayed
            FROM users 
            WHERE status = 'Active'
            ORDER BY {$scoreCol} DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
