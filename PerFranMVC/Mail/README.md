# Mail System

Ce dossier contient tous les fichiers liés au système d'email pour les suggestions de quiz.

## Fichiers

### QuizSuggestionEmailer.php
Classe qui gère l'envoi des emails de suggestion de quiz.
- Génère un token unique pour chaque suggestion
- Construit l'email HTML
- Envoie via `mail()` PHP et XAMPP sendmail

### handle_approval.php
Script qui traite les approbations/rejets depuis les liens dans l'email.
- Approuve → Sauvegarde en base de données
- Rejette → Supprime la suggestion

### email_config.php
Configuration pour le système d'email:
- Adresse email admin
- URL de base pour les liens d'approbation

### test_email.php
Page de test pour vérifier que XAMPP sendmail fonctionne correctement.

## Utilisation

1. Le joueur suggère un quiz via `View/FrontOffice/suggest.php`
2. `QuizSuggestionEmailer` envoie un email à l'admin
3. L'admin clique sur Approuver/Rejeter dans l'email
4. `handle_approval.php` traite l'action

## Configuration requise

- XAMPP sendmail configuré (`c:\xampp\sendmail\sendmail.ini`)
- PHP mail() activé (`c:\xampp\php\php.ini`)
- Gmail App Password configuré
