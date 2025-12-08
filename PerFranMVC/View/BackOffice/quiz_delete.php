<?php
session_start();
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Model/Quiz.php';

// Get quiz ID from POST parameter (from the form in quiz_list.php)
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Only delete if valid ID
if ($id > 0) {
    QuizController::remove($id);
    header('Location: quiz_list.php');
    exit;
} else {
    die('Erreur: ID de quiz invalide');
}
