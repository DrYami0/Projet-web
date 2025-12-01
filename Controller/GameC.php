<?php
require_once __DIR__ . '/../config.php';

class GameC
{
    // Create a new game
    public function addGame($game) {
        $db = config::getConnexion();  
        
        try {
            $req = $db->prepare("INSERT INTO games (title, game, difficulty, type, player1id, status) 
                                VALUES (:title, :game, :difficulty, :type, :player1id, 'waiting')");
            $req->execute([
                "title" => $game->getTitle(),
                "game" => $game->getGame(),
                "difficulty" => $game->getDifficulty(),
                "type" => $game->getType(),
                "player1id" => $game->getPlayer1id()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Display all games (admin only)
    public function displayGames() {
        $sql = "SELECT * FROM games ORDER BY createdAt DESC";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Edit game with user verification
    public function editGame($game, $gid, $current_user_id = null, $is_admin = false) {
        $db = config::getConnexion();
        
        // Verify ownership if not admin
        if (!$is_admin && $current_user_id) {
            $existingGame = $this->getGameById($gid);
            if (!$existingGame || $existingGame['player1id'] != $current_user_id) {
                return false; // Not authorized
            }
        }
        
        try {
            $req = $db->prepare(
                "UPDATE games SET title=:title, game=:game, difficulty=:difficulty, 
                type=:type, player1id=:player1id, player2id=:player2id, status=:status,
                rounds_played=:rounds_played, game_state=:game_state
                WHERE gid=:gid"
            );
            $req->bindValue(':title', $game->getTitle());
            $req->bindValue(':game', $game->getGame());
            $req->bindValue(':difficulty', $game->getDifficulty());
            $req->bindValue(':type', $game->getType());
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Delete game with user verification
    public function deleteGame($gid, $current_user_id = null, $is_admin = false) {
        // Verify ownership if not admin
        if (!$is_admin && $current_user_id) {
            $existingGame = $this->getGameById($gid);
            if (!$existingGame || $existingGame['player1id'] != $current_user_id) {
                return false; // Not authorized
            }
        }
        
        $sql = "DELETE FROM games WHERE gid= :gid";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':gid', $gid);
        try {
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Join a game (player2 joins)
    public function joinGame($gid, $player2id) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("UPDATE games SET player2id = :player2id, status = 'active', startedAt = NOW() WHERE gid = :gid");
            $req->bindValue(':player2id', $player2id);
            $req->bindValue(':gid', $gid);
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Complete a game
    public function completeGame($gid, $winner, $rounds_played = null, $game_state = null) {
        $db = config::getConnexion();
        try {
            $sql = "UPDATE games SET status = 'completed', winner = :winner, endedAt = NOW()";
            
            if ($rounds_played !== null) {
                $sql .= ", rounds_played = :rounds_played";
            }
            if ($game_state !== null) {
                $sql .= ", game_state = :game_state";
            }
            
            $sql .= " WHERE gid = :gid";
            
            $req = $db->prepare($sql);
            $req->bindValue(':winner', $winner);
            $req->bindValue(':gid', $gid);
            
            if ($rounds_played !== null) {
                $req->bindValue(':rounds_played', $rounds_played);
            }
            if ($game_state !== null) {
                $req->bindValue(':game_state', $game_state);
            }
            
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Get waiting games (available to join)
    public function getWaitingGames() {
        $sql = "SELECT * FROM games WHERE status = 'waiting' ORDER BY createdAt DESC";
        $db = config::getConnexion();
        try {
            $req = $db->query($sql);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Get active games
    public function getActiveGames() {
        $sql = "SELECT * FROM games WHERE status = 'active' ORDER BY startedAt DESC";
        $db = config::getConnexion();
        try {
            $req = $db->query($sql);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Get completed games
    public function getCompletedGames() {
        $sql = "SELECT * FROM games WHERE status = 'completed' ORDER BY endedAt DESC";
        $db = config::getConnexion();
        try {
            $req = $db->query($sql);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Get game by ID
    public function getGameById($gid) {
        $sql = "SELECT * FROM games WHERE gid = :gid";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':gid', $gid);
            $req->execute();
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Get user's games
    public function getUserGames($user_id) {
        $sql = "SELECT * FROM games WHERE player1id = :user_id OR player2id = :user_id ORDER BY createdAt DESC";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':user_id', $user_id);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Get user's waiting games (for regular users)
    public function getUserWaitingGames($user_id) {
        $sql = "SELECT * FROM games WHERE status = 'waiting' AND player1id != :user_id ORDER BY createdAt DESC";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':user_id', $user_id);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Update game state (for ongoing games)
    public function updateGameState($gid, $game_state, $rounds_played = null) {
        $db = config::getConnexion();
        try {
            $sql = "UPDATE games SET game_state = :game_state";
            if ($rounds_played !== null) {
                $sql .= ", rounds_played = :rounds_played";
            }
            $sql .= " WHERE gid = :gid";
            
            $req = $db->prepare($sql);
            $req->bindValue(':game_state', $game_state);
            $req->bindValue(':gid', $gid);
            
            if ($rounds_played !== null) {
                $req->bindValue(':rounds_played', $rounds_played);
            }
            
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>