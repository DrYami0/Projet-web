<?php
session_start();
require_once __DIR__ . '/../../Controller/QuizBlankController.php';

$controller = new QuizBlankController();
$controller->blank_delete();

