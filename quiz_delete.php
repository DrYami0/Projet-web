<?php
session_start();
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Model/Quiz.php';

$controller = new QuizController();
$controller->delete();

