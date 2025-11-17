-- ============================================
-- Base de données simplifiée pour PerFran Education
-- ============================================


USE perfran_db;

-- Table des jeux (configurations de jeux)
CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des textes de jeu (phrases avec blancs)
CREATE TABLE IF NOT EXISTS game_text (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    text_content TEXT NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    INDEX idx_game_order (game_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des mots/réponses (options de mots à placer)
CREATE TABLE IF NOT EXISTS words (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    word_text VARCHAR(255) NOT NULL,
    correct_order INT NOT NULL,
    is_correct BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    INDEX idx_game_order (game_id, correct_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Données initiales
-- ============================================

-- Insérer un jeu par défaut
INSERT INTO games (id, title, description, is_active) VALUES
(1, 'Compléter les phrases - Niveau 1', 'Jeu de complétion de phrases en français', TRUE)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Insérer les textes du jeu
INSERT INTO game_text (game_id, text_content, display_order) VALUES
(1, 'Le petit chat gris dort sur le <span class="blank" data-order="1" data-filled="false"></span> canapé.', 1),
(1, 'Il regarde par la <span class="blank" data-order="2" data-filled="false"></span> fenêtre.', 2),
(1, 'Dehors, les oiseaux <span class="blank" data-order="3" data-filled="false"></span> joyeusement.', 3)
ON DUPLICATE KEY UPDATE text_content=VALUES(text_content);

-- Insérer les mots/réponses
INSERT INTO words (game_id, word_text, correct_order, is_correct) VALUES
(1, 'vieux', 1, TRUE),
(1, 'petite', 2, TRUE),
(1, 'chantent', 3, TRUE)
ON DUPLICATE KEY UPDATE word_text=VALUES(word_text);
