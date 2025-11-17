<?php
// Charger la config
require_once __DIR__ . '/../config/config.php';

try {
    // Connexion MySQL sans base spécifique (pour pouvoir créer la base)
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lire le fichier SQL
    $sql = file_get_contents(__DIR__ . '/schema.sql');

    // Exécuter tout le script SQL
    $pdo->exec($sql);

    echo "Base de données et tables créées avec succès !\n";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
