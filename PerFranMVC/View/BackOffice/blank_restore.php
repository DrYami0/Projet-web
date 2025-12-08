<?php
session_start();
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Controller/QuizBlankController.php';
require_once __DIR__ . '/../../Model/Quiz.php';
require_once __DIR__ . '/../../Model/QuizBlank.php';

// Déléguer la restauration au contrôleur
QuizBlankController::restore($_SESSION['deleted_blank'] ?? []);
