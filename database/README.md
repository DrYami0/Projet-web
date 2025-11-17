# Base de données - PerFran Education

## Installation

### Méthode 1 : Utiliser le script d'installation PHP

Exécutez le script d'installation depuis la ligne de commande :

```bash
php database/install.php
```

### Méthode 2 : Utiliser phpMyAdmin ou un client MySQL

1. Ouvrez phpMyAdmin ou votre client MySQL préféré
2. Importez le fichier `schema.sql`
3. Vérifiez que la base de données `perfran_db` a été créée

### Méthode 3 : Ligne de commande MySQL

```bash
mysql -u root -p < database/schema.sql
```

## Configuration

Les paramètres de connexion sont définis dans `config/config.php` :

- **Host**: localhost
- **Database**: perfran_db
- **User**: root
- **Password**: (vide par défaut)

Modifiez ces valeurs selon votre configuration.

## Structure de la base de données

### Tables principales

1. **games** - Configuration des jeux
2. **game_texts** - Textes avec blancs à compléter
3. **words** - Mots/réponses disponibles
4. **game_sessions** - Sessions de jeu des utilisateurs
5. **user_responses** - Réponses des utilisateurs

## Utilisation dans le code

La connexion PDO est automatiquement initialisée dans la classe `Model`. Tous les modèles qui étendent `Model` ont accès à la connexion via `$this->db` ou `$this->getDb()`.

Exemple :

```php
class GameModel extends Model {
    public function getGameData() {
        $stmt = $this->getDb()->prepare("SELECT * FROM games WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
```

