<?php
header('Content-Type: application/json');
require_once "../../PerFranMVC/Controller/DictionaryC.php";

$difficulty = isset($_GET['difficulty']) ? strtolower($_GET['difficulty']) : 'easy';

$dictC = new DictionaryC();
$words = $dictC->getWordsByDifficulty($difficulty);

echo json_encode($words);
?>