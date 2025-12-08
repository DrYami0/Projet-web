# Guide de Test - Jeu 3

## 📋 Prérequis

1. **XAMPP doit être démarré**
   - Apache doit être actif
   - MySQL/MariaDB doit être actif

2. **Base de données configurée**
   - La base `perfran` doit exister
   - Les tables `quiz` et `quiz_blanks` doivent être créées
   - Importer le fichier `database/perfran.sql` si nécessaire

3. **Configuration**
   - Vérifier `database/config.php` : host, database, user, password

## 🧪 Tests BackOffice (Administration)

### Test 1 : Créer un Quiz

1. **Accéder à la page d'ajout**
   ```
   http://localhost/perfran/PerFran-master/database/index.php?/backoffice/quiz/add
   ```
   OU
   ```
   http://localhost/perfran/PerFran-master/PerFranMVC/View/BackOffice/quiz_add.php
   ```

2. **Créer un quiz de test**
   - Écrire un texte dans l'éditeur, par exemple :
     ```
     Le [chat] de mon [voisin] est très [joueur].
     ```
   - Cliquer sur "Ajouter un Blank" pour insérer `[mot]` si nécessaire
   - Sélectionner la difficulté : **Easy**
   - Sélectionner le statut : **Approuvé**
   - Cliquer sur "Enregistrer"

3. **Vérifier**
   - Le quiz doit apparaître dans la liste
   - Les blanks doivent être créés automatiquement

### Test 2 : Voir la Liste des Quiz

1. **Accéder à la liste**
   ```
   http://localhost/perfran/PerFran-master/database/index.php?/backoffice
   ```
   OU
   ```
   http://localhost/perfran/PerFran-master/PerFranMVC/View/BackOffice/quiz_list.php
   ```

2. **Vérifier**
   - Tous les quiz doivent s'afficher
   - La recherche doit fonctionner
   - Les boutons "Éditer", "Supprimer", "Blanks" doivent être visibles

### Test 3 : Modifier un Quiz

1. **Cliquer sur "Éditer"** d'un quiz dans la liste

2. **Modifier le texte**
   - Changer un mot entre crochets, par exemple :
     ```
     Le [chien] de mon [voisin] est très [joueur].
     ```
     (changement de "chat" à "chien")

3. **Sauvegarder**

4. **Vérifier**
   - Le quiz doit être mis à jour
   - Le blank correspondant doit être modifié automatiquement

### Test 4 : Supprimer un Quiz

1. **Cliquer sur "Supprimer"** d'un quiz
2. **Confirmer la suppression**
3. **Vérifier**
   - Le quiz doit disparaître de la liste
   - Les blanks associés doivent être supprimés automatiquement

### Test 5 : Gérer les Blanks

1. **Cliquer sur "Blanks"** d'un quiz
2. **Vérifier**
   - Tous les blanks du quiz doivent s'afficher
   - Les positions et réponses correctes doivent être visibles

## 🎮 Tests FrontOffice (Jeu)

### Test 1 : Navigation vers le Jeu

1. **Accéder à la page d'accueil**
   ```
   http://localhost/perfran/PerFran-master/PerFranMVC/View/FrontOffice/index.html
   ```

2. **Cliquer sur "Jeux"** dans le menu
3. **Sélectionner "jeu3"**

4. **Vérifier**
   - La page `jeu3.html` doit s'afficher
   - Les boutons "Solo" et "Multijoueur" doivent être visibles

### Test 2 : Sélection du Mode Solo

1. **Cliquer sur "Solo"**

2. **Vérifier**
   - La page `jeu3_solo.html` doit s'afficher
   - Trois cartes de difficulté doivent être visibles : Easy, Medium, Hard

### Test 3 : Sélection de la Difficulté

1. **Cliquer sur une difficulté** (par exemple "Facile")

2. **Vérifier**
   - La page `quiz_play.php?difficulty=easy` doit s'afficher
   - Un quiz aléatoire de la difficulté choisie doit être chargé
   - Le paragraphe avec des zones vides (blanks) doit être visible
   - Une liste de mots à glisser doit être affichée

### Test 4 : Drag & Drop

1. **Glisser un mot** de la liste vers une zone vide (blank)

2. **Vérifier**
   - Le mot doit se placer dans le blank
   - Le mot doit disparaître de la liste (ou être marqué comme utilisé)
   - Le blank doit changer d'apparence (bordure solide, fond blanc)

3. **Tester plusieurs mots**
   - Remplir tous les blanks
   - Vérifier que tous les mots peuvent être glissés

### Test 5 : Retirer un Mot

1. **Cliquer sur un blank rempli**

2. **Vérifier**
   - Le mot doit être retiré du blank
   - Le mot doit réapparaître dans la liste
   - Le blank doit redevenir vide

### Test 6 : Validation des Réponses

1. **Remplir tous les blanks** avec des mots

2. **Cliquer sur "Valider les réponses"**

3. **Vérifier**
   - Un message d'erreur si tous les blanks ne sont pas remplis
   - Si tous remplis :
     - Les résultats doivent s'afficher
     - Le score doit être calculé (pourcentage)
     - Chaque blank doit être coloré :
       - **Vert** si correct
       - **Rouge** si incorrect
     - Les détails des réponses doivent être affichés

### Test 7 : Nouveau Quiz

1. **Après validation, cliquer sur "Nouveau Quiz"**

2. **Vérifier**
   - Un nouveau quiz aléatoire de la même difficulté doit être chargé
   - L'interface doit être réinitialisée

### Test 8 : Changer de Difficulté

1. **Après validation, cliquer sur "Changer de difficulté"**

2. **Vérifier**
   - Retour à la page de sélection de difficulté
   - Possibilité de choisir une autre difficulté

## 🔍 Tests de Validation

### Test 1 : Réponses Correctes

1. **Créer un quiz simple** dans le BackOffice :
   ```
   Le [chat] est [noir].
   ```
   - Blank 1 : "chat"
   - Blank 2 : "noir"

2. **Jouer le quiz** et remplir avec les bonnes réponses

3. **Vérifier**
   - Score : 100%
   - Tous les blanks doivent être verts
   - Message de succès

### Test 2 : Réponses Incorrectes

1. **Utiliser le même quiz** mais remplir avec de mauvaises réponses

2. **Vérifier**
   - Score < 100%
   - Les blanks incorrects doivent être rouges
   - Les bonnes réponses doivent être affichées

### Test 3 : Mélange Correct/Incorrect

1. **Remplir partiellement correctement**

2. **Vérifier**
   - Score proportionnel au nombre de bonnes réponses
   - Feedback visuel correct pour chaque blank

## 🐛 Tests de Cas Limites

### Test 1 : Aucun Quiz Disponible

1. **Supprimer tous les quiz d'une difficulté** (par exemple "hard")

2. **Essayer de jouer** avec cette difficulté

3. **Vérifier**
   - Message d'erreur : "Aucun quiz disponible pour cette difficulté"

### Test 2 : Quiz Sans Blanks

1. **Créer un quiz sans blanks** (texte normal)

2. **Vérifier**
   - Le quiz ne devrait pas apparaître dans le jeu (car aucun blank)
   - OU le système doit gérer ce cas

### Test 3 : Validation Sans Remplir

1. **Ne pas remplir tous les blanks**

2. **Cliquer sur "Valider"**

3. **Vérifier**
   - Message d'alerte : "Veuillez remplir tous les espaces vides"

## 📱 Tests Responsive

1. **Tester sur différentes tailles d'écran**
   - Desktop (1920x1080)
   - Tablet (768px)
   - Mobile (375px)

2. **Vérifier**
   - L'interface doit être utilisable
   - Le drag & drop doit fonctionner sur mobile (touch)

## 🔧 Tests Techniques

### Test 1 : Console JavaScript

1. **Ouvrir la console du navigateur** (F12)

2. **Jouer un quiz**

3. **Vérifier**
   - Aucune erreur JavaScript
   - Les requêtes AJAX fonctionnent

### Test 2 : Réseau (Network)

1. **Ouvrir l'onglet Network** (F12)

2. **Valider les réponses**

3. **Vérifier**
   - Requête POST vers `quiz_validate.php`
   - Réponse JSON avec les résultats
   - Statut 200 OK

### Test 3 : Base de Données

1. **Vérifier les données** dans phpMyAdmin :
   ```sql
   SELECT * FROM quiz WHERE approved = 1;
   SELECT * FROM quiz_blanks;
   ```

2. **Vérifier**
   - Les quiz sont bien enregistrés
   - Les blanks sont correctement liés (qid)
   - Les positions sont correctes

## ✅ Checklist de Test Complète

### BackOffice
- [ ] Créer un quiz
- [ ] Voir la liste des quiz
- [ ] Modifier un quiz
- [ ] Supprimer un quiz
- [ ] Voir les blanks d'un quiz
- [ ] L'aperçu en temps réel fonctionne
- [ ] L'extraction des blanks fonctionne

### FrontOffice
- [ ] Navigation vers le jeu
- [ ] Sélection du mode Solo
- [ ] Sélection de la difficulté
- [ ] Affichage d'un quiz aléatoire
- [ ] Drag & drop fonctionne
- [ ] Retirer un mot fonctionne
- [ ] Validation des réponses
- [ ] Affichage des résultats
- [ ] Nouveau quiz
- [ ] Changer de difficulté

### Validation
- [ ] Réponses correctes → Score 100%
- [ ] Réponses incorrectes → Score < 100%
- [ ] Feedback visuel correct
- [ ] Message d'erreur si blanks non remplis

## 🚨 Problèmes Courants et Solutions

### Problème 1 : "Aucun quiz disponible"
**Solution** : Vérifier qu'il y a des quiz approuvés (`approved = 1`) pour la difficulté choisie

### Problème 2 : Drag & Drop ne fonctionne pas
**Solution** : 
- Vérifier que JavaScript est activé
- Vérifier la console pour les erreurs
- Tester sur un autre navigateur

### Problème 3 : Erreur 404 sur les pages
**Solution** : 
- Vérifier les chemins dans les liens
- Vérifier la configuration Apache (.htaccess)
- Utiliser les chemins absolus si nécessaire

### Problème 4 : Les blanks ne s'affichent pas
**Solution** :
- Vérifier le format du paragraphe dans la DB (doit contenir `[mot]`)
- Vérifier que les blanks sont bien créés dans `quiz_blanks`

### Problème 5 : Erreur de connexion à la base
**Solution** :
- Vérifier `database/config.php`
- Vérifier que MySQL est démarré
- Vérifier les credentials

## 📊 Résultats Attendus

Après tous les tests, vous devriez avoir :
- ✅ BackOffice fonctionnel pour créer/modifier/supprimer des quiz
- ✅ FrontOffice fonctionnel avec drag & drop
- ✅ Validation des réponses opérationnelle
- ✅ Interface responsive
- ✅ Aucune erreur dans la console
- ✅ Base de données cohérente

---

**Bon test ! 🎮**

